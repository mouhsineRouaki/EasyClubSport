<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppCard from '@/shared/components/AppCard.vue'
import AppListState from '@/shared/components/AppListState.vue'
import AppPagination from '@/shared/components/AppPagination.vue'
import AppStatusMessage from '@/shared/components/ui/AppStatusMessage.vue'
import AppModalShell from '@/shared/components/ui/AppModalShell.vue'
import PresidentClubForm from '@/roles/president/clubs/components/PresidentClubForm.vue'
import PresidentClubListCard from '@/roles/president/clubs/components/PresidentClubListCard.vue'
import PresidentShellLayout from '@/roles/president/shared/components/PresidentShellLayout.vue'
import { authDelete, authGet, authPost, authPut } from '@/shared/services/apiClient'
import { notifyError, notifySuccess } from '@/shared/services/toastService'

const router = useRouter()

const chargement = ref(true)
const envoi = ref(false)
const clubs = ref([])
const pagination = ref(null)
const erreursValidation = ref({})
const succes = ref('')
const erreurChargement = ref('')
const afficherFormulaire = ref(false)
const modeEdition = ref(false)
const clubEditionId = ref(null)
const logoFichier = ref(null)
const logoPreview = ref('')
const utilisateurConnecte = ref(null)
const searchDebounce = ref(null)

const filtres = reactive({
  q: '',
  page: 1,
  per_page: 12,
})

const formulaire = reactive({
  nom: '',
  adresse: '',
  telephone: '',
  email: '',
  description: '',
  ville: '',
  pays: '',
  logo_url: '',
})

const titreFormulaire = computed(() => (modeEdition.value ? 'Modifier le club' : 'Nouveau club'))
const logoAffiche = computed(() => logoPreview.value || formulaire.logo_url || '')
const nomLogoSelectionne = computed(() => logoFichier.value?.name || (formulaire.logo_url ? 'Logo actuel charge' : 'Aucun logo selectionne'))
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
  formulaire.adresse = ''
  formulaire.telephone = ''
  formulaire.email = ''
  formulaire.description = ''
  formulaire.ville = ''
  formulaire.pays = ''
  formulaire.logo_url = ''
  logoFichier.value = null
  logoPreview.value = ''
  erreursValidation.value = {}
}

const mettreAJourChamp = (champ, valeur) => {
  formulaire[champ] = valeur
}

const chargerClubs = async () => {
  chargement.value = true
  erreurChargement.value = ''

  try {
    const reponse = await authGet('/president/clubs', {
      q: filtres.q,
      page: filtres.page,
      per_page: filtres.per_page,
    })

    clubs.value = reponse?.data?.clubs || []
    pagination.value = reponse?.data?.pagination || null
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    erreurChargement.value = error?.response?.message || error.message || 'Impossible de charger les clubs.'
    notifyError(erreurChargement.value)
  } finally {
    chargement.value = false
  }
}

const choisirLogo = (event) => {
  const fichier = event.target.files?.[0]

  if (!fichier) {
    return
  }

  logoFichier.value = fichier
  logoPreview.value = URL.createObjectURL(fichier)
}

const ouvrirCreation = () => {
  reinitialiserFormulaire()
  modeEdition.value = false
  clubEditionId.value = null
  afficherFormulaire.value = true
  succes.value = ''
}

const ouvrirEdition = (club) => {
  formulaire.nom = club.nom || ''
  formulaire.adresse = club.adresse || ''
  formulaire.telephone = club.telephone || ''
  formulaire.email = club.email || ''
  formulaire.description = club.description || ''
  formulaire.ville = club.ville || ''
  formulaire.pays = club.pays || ''
  formulaire.logo_url = club.logo_url || ''
  logoFichier.value = null
  logoPreview.value = ''
  erreursValidation.value = {}

  modeEdition.value = true
  clubEditionId.value = club.id
  afficherFormulaire.value = true
  succes.value = ''
}

const fermerFormulaire = () => {
  afficherFormulaire.value = false
  modeEdition.value = false
  clubEditionId.value = null
  reinitialiserFormulaire()
}

const construireDonneesClub = () => {
  const donnees = new FormData()
  donnees.append('nom', formulaire.nom)
  donnees.append('adresse', formulaire.adresse || '')
  donnees.append('telephone', formulaire.telephone || '')
  donnees.append('email', formulaire.email || '')
  donnees.append('description', formulaire.description || '')
  donnees.append('ville', formulaire.ville || '')
  donnees.append('pays', formulaire.pays || '')

  if (logoFichier.value) {
    donnees.append('logo', logoFichier.value)
  }

  return donnees
}

const enregistrerClub = async () => {
  envoi.value = true
  erreursValidation.value = {}
  succes.value = ''

  try {
    const donnees = construireDonneesClub()
    const endpoint = modeEdition.value ? `/president/clubs/${clubEditionId.value}` : '/president/clubs'
    const reponse = modeEdition.value ? await authPut(endpoint, donnees) : await authPost(endpoint, donnees)

    succes.value = reponse?.message || (modeEdition.value ? 'Club modifie avec succes.' : 'Club cree avec succes.')
    notifySuccess(succes.value)
    fermerFormulaire()

    if (!modeEdition.value) {
      filtres.page = 1
    }

    await chargerClubs()
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

const supprimerClub = async (club) => {
  const ok = window.confirm(`Supprimer le club "${club.nom}" ?`)

  if (!ok) {
    return
  }

  succes.value = ''

  try {
    const reponse = await authDelete(`/president/clubs/${club.id}`)
    succes.value = reponse?.message || 'Club supprime avec succes.'
    notifySuccess(succes.value)

    if (clubs.value.length === 1 && filtres.page > 1) {
      filtres.page -= 1
    }

    await chargerClubs()
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de supprimer ce club.')
    }
  }
}

const onChangePage = (page) => {
  filtres.page = page
  chargerClubs()
}

const onChangePerPage = (size) => {
  filtres.per_page = size
  filtres.page = 1
  chargerClubs()
}

watch(
  () => filtres.q,
  () => {
    if (searchDebounce.value) {
      clearTimeout(searchDebounce.value)
    }

    searchDebounce.value = setTimeout(() => {
      filtres.page = 1
      chargerClubs()
    }, 350)
  }
)

const deconnecter = () => {
  localStorage.removeItem('token_api')
  localStorage.removeItem('utilisateur_api')
  router.push('/login')
}

onMounted(() => {
  const utilisateurStocke = localStorage.getItem('utilisateur_api')

  if (utilisateurStocke) {
    try {
      utilisateurConnecte.value = JSON.parse(utilisateurStocke)
    } catch {
      utilisateurConnecte.value = null
    }
  }

  chargerClubs()
})
</script>

<template>
  <PresidentShellLayout
    breadcrumb="Clubs"
    title="Gestion des clubs"
    active-section="clubs"
    :utilisateur="utilisateurLayout"
    @logout="deconnecter"
  >
    <AppCard title="Clubs du president" subtitle="Creation, modification et suppression des clubs.">
      <template #actions>
        <button type="button" class="ecs-btn-primary" @click="ouvrirCreation">+ Nouveau club</button>
      </template>

      <div class="grid gap-3 md:grid-cols-[1fr_180px]">
        <label>
          <span class="text-xs font-bold text-[#64748b]">Recherche serveur</span>
          <input v-model="filtres.q" type="text" placeholder="Nom, ville, email..." class="ecs-input" />
        </label>

        <label>
          <span class="text-xs font-bold text-[#64748b]">Taille page</span>
          <select v-model.number="filtres.per_page" class="ecs-select" @change="onChangePerPage(filtres.per_page)">
            <option :value="6">6</option>
            <option :value="12">12</option>
            <option :value="24">24</option>
            <option :value="48">48</option>
          </select>
        </label>
      </div>
    </AppCard>

    <AppStatusMessage v-if="succes">{{ succes }}</AppStatusMessage>

    <AppModalShell v-if="afficherFormulaire" :title="titreFormulaire" max-width-class="max-w-4xl" @close="fermerFormulaire">
      <PresidentClubForm
        :model-value="formulaire"
        :errors="erreursValidation"
        :logo-preview="logoPreview"
        :current-logo-url="logoAffiche"
        :logo-file-name="nomLogoSelectionne"
        :loading="envoi"
        :submit-label="modeEdition ? 'Mettre a jour' : 'Creer le club'"
        @submit="enregistrerClub"
        @update-field="mettreAJourChamp"
        @choose-logo="choisirLogo"
      />
    </AppModalShell>

    <AppCard class="mt-4" title="Liste des clubs">
      <AppListState
        :loading="chargement"
        :has-data="clubs.length > 0"
        :error-message="erreurChargement"
        empty-title="Aucun club pour le moment."
        empty-description="Creez votre premier club pour commencer."
        @retry="chargerClubs"
      >
        <template #empty-action>
          <button type="button" class="mt-4 ecs-btn-primary" @click="ouvrirCreation">Creer le premier club</button>
        </template>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <PresidentClubListCard
            v-for="club in clubs"
            :key="club.id"
            :club="club"
            @edit="ouvrirEdition"
            @delete="supprimerClub"
          />
        </div>
      </AppListState>

      <AppPagination :pagination="pagination" @change-page="onChangePage" @change-per-page="onChangePerPage" />
    </AppCard>
  </PresidentShellLayout>
</template>
