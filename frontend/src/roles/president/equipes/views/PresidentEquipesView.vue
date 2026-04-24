<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppCard from '@/shared/components/AppCard.vue'
import AppListState from '@/shared/components/AppListState.vue'
import AppPagination from '@/shared/components/AppPagination.vue'
import AppButton from '@/shared/components/ui/AppButton.vue'
import AppStatusMessage from '@/shared/components/ui/AppStatusMessage.vue'
import AppModalShell from '@/shared/components/ui/AppModalShell.vue'
import PresidentTeamForm from '@/roles/president/equipes/components/PresidentTeamForm.vue'
import PresidentTeamListCard from '@/roles/president/equipes/components/PresidentTeamListCard.vue'
import PresidentShellLayout from '@/roles/president/shared/components/PresidentShellLayout.vue'
import { authDelete, authGet, authPost, authPut } from '@/shared/services/apiClient'
import { notifyError, notifySuccess } from '@/shared/services/toastService'

const router = useRouter()

const chargementClubs = ref(true)
const chargementEquipes = ref(false)
const envoi = ref(false)
const clubs = ref([])
const equipes = ref([])
const pagination = ref(null)
const selectedClubId = ref('')
const erreurChargement = ref('')
const erreursValidation = ref({})
const succes = ref('')
const afficherFormulaire = ref(false)
const modeEdition = ref(false)
const equipeEditionId = ref(null)
const logoFichier = ref(null)
const logoPreview = ref('')
const utilisateurConnecte = ref(null)
const searchDebounce = ref(null)

const filtres = reactive({
  q: '',
  statut: '',
  page: 1,
  per_page: 12,
})

const formulaire = reactive({
  nom: '',
  categorie: '',
  statut: 'active',
  description: '',
  logo_url: '',
})

const titreFormulaire = computed(() => (modeEdition.value ? 'Modifier l equipe' : 'Nouvelle equipe'))
const logoAffiche = computed(() => logoPreview.value || formulaire.logo_url || '')
const nomLogoSelectionne = computed(() => logoFichier.value?.name || (formulaire.logo_url ? 'Logo actuel charge' : 'Aucun logo selectionne'))
const clubActuel = computed(() => clubs.value.find((club) => String(club.id) === String(selectedClubId.value)) || null)
const utilisateurLayout = computed(() => (utilisateurConnecte.value ? utilisateurConnecte.value : null))
const lireErreur = (champ) => erreursValidation.value?.[champ]?.[0] || ''

const gerer401 = (error) => {
  if (error?.response?.code === 401) {
    localStorage.removeItem('token_api')
    localStorage.removeItem('utilisateur_api')
    router.push('/login')
    return true
  }

  return false
}

const reinitialiserFormulaire = () => {
  formulaire.nom = ''
  formulaire.categorie = ''
  formulaire.statut = 'active'
  formulaire.description = ''
  formulaire.logo_url = ''
  logoFichier.value = null
  logoPreview.value = ''
  erreursValidation.value = {}
}

const mettreAJourChamp = (champ, valeur) => {
  formulaire[champ] = valeur
}

const chargerClubs = async () => {
  chargementClubs.value = true

  try {
    const reponse = await authGet('/president/clubs', { page: 1, per_page: 100 })
    clubs.value = reponse?.data?.clubs || []

    if (!selectedClubId.value && clubs.value.length) {
      selectedClubId.value = String(clubs.value[0].id)
    }
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les clubs.')
    }
  } finally {
    chargementClubs.value = false
  }
}

const chargerEquipes = async () => {
  if (!selectedClubId.value) {
    equipes.value = []
    pagination.value = null
    return
  }

  chargementEquipes.value = true
  erreurChargement.value = ''

  try {
    const reponse = await authGet(`/president/clubs/${selectedClubId.value}/equipes`, {
      q: filtres.q,
      statut: filtres.statut,
      page: filtres.page,
      per_page: filtres.per_page,
    })

    equipes.value = reponse?.data?.equipes || []
    pagination.value = reponse?.data?.pagination || null
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    erreurChargement.value = error?.response?.message || error.message || 'Impossible de charger les equipes.'
    notifyError(erreurChargement.value)
  } finally {
    chargementEquipes.value = false
  }
}

watch(selectedClubId, async () => {
  filtres.page = 1
  await chargerEquipes()
})

watch(
  () => filtres.statut,
  () => {
    filtres.page = 1
    chargerEquipes()
  }
)

watch(
  () => filtres.q,
  () => {
    if (searchDebounce.value) {
      clearTimeout(searchDebounce.value)
    }

    searchDebounce.value = setTimeout(() => {
      filtres.page = 1
      chargerEquipes()
    }, 350)
  }
)

const choisirLogo = (event) => {
  const fichier = event.target.files?.[0]

  if (!fichier) {
    return
  }

  logoFichier.value = fichier
  logoPreview.value = URL.createObjectURL(fichier)
}

const ouvrirCreation = () => {
  if (!selectedClubId.value) {
    notifyError('Choisissez d abord un club.')
    return
  }

  reinitialiserFormulaire()
  modeEdition.value = false
  equipeEditionId.value = null
  afficherFormulaire.value = true
  succes.value = ''
}

const ouvrirEdition = (equipe) => {
  selectedClubId.value = String(equipe.club_id)
  formulaire.nom = equipe.nom || ''
  formulaire.categorie = equipe.categorie || ''
  formulaire.statut = equipe.statut || 'active'
  formulaire.description = equipe.description || ''
  formulaire.logo_url = equipe.logo_url || ''
  logoFichier.value = null
  logoPreview.value = ''
  erreursValidation.value = {}

  modeEdition.value = true
  equipeEditionId.value = equipe.id
  afficherFormulaire.value = true
  succes.value = ''
}

const fermerFormulaire = () => {
  afficherFormulaire.value = false
  modeEdition.value = false
  equipeEditionId.value = null
  reinitialiserFormulaire()
}

const construireDonneesEquipe = () => {
  const donnees = new FormData()
  donnees.append('nom', formulaire.nom)
  donnees.append('categorie', formulaire.categorie || '')
  donnees.append('statut', formulaire.statut || 'active')
  donnees.append('description', formulaire.description || '')

  if (logoFichier.value) {
    donnees.append('logo', logoFichier.value)
  }

  return donnees
}

const enregistrerEquipe = async () => {
  if (!selectedClubId.value) {
    notifyError('Aucun club selectionne.')
    return
  }

  envoi.value = true
  erreursValidation.value = {}
  succes.value = ''

  try {
    const donnees = construireDonneesEquipe()
    const endpoint = modeEdition.value
      ? `/president/clubs/${selectedClubId.value}/equipes/${equipeEditionId.value}`
      : `/president/clubs/${selectedClubId.value}/equipes`

    const reponse = modeEdition.value ? await authPut(endpoint, donnees) : await authPost(endpoint, donnees)

    succes.value = reponse?.message || (modeEdition.value ? 'Equipe modifiee avec succes.' : 'Equipe creee avec succes.')
    notifySuccess(succes.value)
    fermerFormulaire()
    if (!modeEdition.value) {
      filtres.page = 1
    }
    await chargerEquipes()
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    const reponseErreur = error.response || {}
    erreursValidation.value = reponseErreur.data || {}
  } finally {
    envoi.value = false
  }
}

const supprimerEquipe = async (equipe) => {
  const ok = window.confirm(`Supprimer l equipe "${equipe.nom}" ?`)

  if (!ok) {
    return
  }

  succes.value = ''

  try {
    const reponse = await authDelete(`/president/clubs/${equipe.club_id}/equipes/${equipe.id}`)
    succes.value = reponse?.message || 'Equipe supprimee avec succes.'
    notifySuccess(succes.value)

    if (equipes.value.length === 1 && filtres.page > 1) {
      filtres.page -= 1
    }

    await chargerEquipes()
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de supprimer cette equipe.')
    }
  }
}

const onChangePage = (page) => {
  filtres.page = page
  chargerEquipes()
}

const onChangePerPage = (size) => {
  filtres.per_page = size
  filtres.page = 1
  chargerEquipes()
}

const deconnecter = () => {
  localStorage.removeItem('token_api')
  localStorage.removeItem('utilisateur_api')
  router.push('/login')
}

onMounted(async () => {
  const utilisateurStocke = localStorage.getItem('utilisateur_api')

  if (utilisateurStocke) {
    try {
      utilisateurConnecte.value = JSON.parse(utilisateurStocke)
    } catch {
      utilisateurConnecte.value = null
    }
  }

  await chargerClubs()
  await chargerEquipes()
})
</script>

<template>
  <PresidentShellLayout
    breadcrumb="Equipes"
    title="Gestion des equipes"
    active-section="equipes"
    :utilisateur="utilisateurLayout"
    @logout="deconnecter"
  >
    <AppCard title="Equipes du president" subtitle="Selectionnez un club puis gerez ses equipes.">
      <template #actions>
        <AppButton type="button" @click="ouvrirCreation">+ Nouvelle equipe</AppButton>
      </template>

      <div class="grid gap-3 lg:grid-cols-4">
        <label>
          <span class="text-xs font-bold text-[#64748b]">Club *</span>
          <select v-model="selectedClubId" class="ecs-select" :disabled="chargementClubs">
            <option value="">Choisir un club</option>
            <option v-for="club in clubs" :key="club.id" :value="String(club.id)">{{ club.nom }}</option>
          </select>
        </label>

        <label>
          <span class="text-xs font-bold text-[#64748b]">Statut</span>
          <select v-model="filtres.statut" class="ecs-select" :disabled="!selectedClubId">
            <option value="">Tous</option>
            <option value="active">active</option>
            <option value="inactive">inactive</option>
          </select>
        </label>

        <label class="lg:col-span-2">
          <span class="text-xs font-bold text-[#64748b]">Recherche serveur</span>
          <input v-model="filtres.q" type="text" class="ecs-input" placeholder="Nom, categorie, code invitation..." :disabled="!selectedClubId" />
        </label>
      </div>
    </AppCard>

    <AppStatusMessage v-if="succes">{{ succes }}</AppStatusMessage>

    <AppModalShell v-if="afficherFormulaire" :title="titreFormulaire" max-width-class="max-w-4xl" @close="fermerFormulaire">
      <div class="mb-3 rounded-xl border border-[#e8edf5] bg-[#f8fbff] px-3 py-2.5">
        <span class="text-xs font-bold text-[#64748b]">Club selectionne</span>
        <p class="mt-1 text-sm font-semibold text-[#1f2a44]">{{ clubActuel?.nom || '-' }}</p>
      </div>
      <PresidentTeamForm
        :model-value="formulaire"
        :errors="erreursValidation"
        :logo-preview="logoPreview"
        :current-logo-url="logoAffiche"
        :logo-file-name="nomLogoSelectionne"
        :loading="envoi"
        :submit-label="modeEdition ? 'Mettre a jour' : 'Creer l equipe'"
        @submit="enregistrerEquipe"
        @update-field="mettreAJourChamp"
        @choose-logo="choisirLogo"
      />
    </AppModalShell>

    <AppCard class="mt-4" title="Liste des equipes">
      <AppListState
        :loading="chargementEquipes"
        :has-data="equipes.length > 0"
        :error-message="erreurChargement"
        :empty-title="selectedClubId ? 'Aucune equipe pour ce club.' : 'Choisissez un club pour afficher ses equipes.'"
        @retry="chargerEquipes"
      >
        <template #empty-action>
          <AppButton v-if="selectedClubId" type="button" class="mt-4" @click="ouvrirCreation">Creer la premiere equipe</AppButton>
        </template>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <PresidentTeamListCard
            v-for="equipe in equipes"
            :key="equipe.id"
            :equipe="equipe"
            @edit="ouvrirEdition"
            @delete="supprimerEquipe"
          />
        </div>
      </AppListState>

      <AppPagination :pagination="pagination" @change-page="onChangePage" @change-per-page="onChangePerPage" />
    </AppCard>
  </PresidentShellLayout>
</template>
