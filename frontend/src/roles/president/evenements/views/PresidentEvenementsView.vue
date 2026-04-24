<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppCard from '@/shared/components/AppCard.vue'
import AppListState from '@/shared/components/AppListState.vue'
import AppPagination from '@/shared/components/AppPagination.vue'
import AppButton from '@/shared/components/ui/AppButton.vue'
import AppStatusMessage from '@/shared/components/ui/AppStatusMessage.vue'
import AppModalShell from '@/shared/components/ui/AppModalShell.vue'
import PresidentEventForm from '@/roles/president/evenements/components/PresidentEventForm.vue'
import PresidentEventListCard from '@/roles/president/evenements/components/PresidentEventListCard.vue'
import PresidentShellLayout from '@/roles/president/shared/components/PresidentShellLayout.vue'
import { authDelete, authGet, authPost, authPut } from '@/shared/services/apiClient'
import { notifyError, notifySuccess } from '@/shared/services/toastService'

const router = useRouter()

const chargementClubs = ref(true)
const chargementEquipes = ref(false)
const chargementEquipesAdversaires = ref(false)
const chargementEvenements = ref(false)
const envoi = ref(false)
const clubs = ref([])
const equipes = ref([])
const equipesAdversaires = ref([])
const evenements = ref([])
const pagination = ref(null)
const selectedClubId = ref('')
const selectedEquipeId = ref('')
const utilisateurConnecte = ref(null)
const searchDebounce = ref(null)
const erreurChargement = ref('')
const succes = ref('')
const afficherFormulaire = ref(false)
const modeEdition = ref(false)
const evenementEditionId = ref(null)
const erreursValidation = ref({})

const filtres = reactive({
  q: '',
  type: '',
  statut: '',
  page: 1,
  per_page: 12,
})

const formulaire = reactive({
  titre: '',
  type: 'match',
  date_debut: '',
  date_fin: '',
  lieu: '',
  adversaire: '',
  adversaire_equipe_id: '',
  description: '',
  statut: 'planifie',
})

const titreFormulaire = computed(() => (modeEdition.value ? 'Modifier evenement' : 'Nouveau evenement'))
const clubActuel = computed(() => clubs.value.find((club) => String(club.id) === String(selectedClubId.value)) || null)
const equipeActuelle = computed(() => equipes.value.find((equipe) => String(equipe.id) === String(selectedEquipeId.value)) || null)
const equipesAdversairesDisponibles = computed(() => equipesAdversaires.value.filter((equipe) => String(equipe.id) !== String(selectedEquipeId.value)))
const equipeAdversaireSelectionnee = computed(() => equipesAdversaires.value.find((equipe) => String(equipe.id) === String(formulaire.adversaire_equipe_id)) || null)
const utilisateurLayout = computed(() => (utilisateurConnecte.value ? utilisateurConnecte.value : null))
const titreListe = computed(() => (selectedEquipeId.value ? 'Liste des evenements' : 'Selectionnez une equipe'))
const lireErreur = (champ) => erreursValidation.value?.[champ]?.[0] || ''
const logoEquipe = (equipe = {}) => equipe?.logo_url || equipe?.logo || equipe?.club?.logo_url || ''

const mettreAJourChamp = (champ, valeur) => {
  formulaire[champ] = valeur
}

const gerer401 = (error) => {
  if (error?.response?.code === 401) {
    localStorage.removeItem('token_api')
    localStorage.removeItem('utilisateur_api')
    router.push('/login')
    return true
  }

  return false
}

const formatDate = (date) => {
  if (!date) {
    return '-'
  }

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(date))
}

const convertirDatePourInput = (value) => {
  if (!value) {
    return ''
  }

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) {
    return ''
  }

  const pad = (n) => String(n).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
}

const reinitialiserFormulaire = () => {
  formulaire.titre = ''
  formulaire.type = 'match'
  formulaire.date_debut = ''
  formulaire.date_fin = ''
  formulaire.lieu = ''
  formulaire.adversaire = ''
  formulaire.adversaire_equipe_id = ''
  formulaire.description = ''
  formulaire.statut = 'planifie'
  erreursValidation.value = {}
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

const chargerEquipesAdversaires = async () => {
  chargementEquipesAdversaires.value = true

  try {
    const reponse = await authGet('/president/equipes/adversaires', {
      page: 1,
      per_page: 100,
      exclude_equipe_id: selectedEquipeId.value,
    })

    equipesAdversaires.value = reponse?.data?.equipes || []
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les equipes adversaires.')
    }
  } finally {
    chargementEquipesAdversaires.value = false
  }
}

const chargerEquipes = async () => {
  if (!selectedClubId.value) {
    equipes.value = []
    selectedEquipeId.value = ''
    return
  }

  chargementEquipes.value = true

  try {
    const reponse = await authGet(`/president/clubs/${selectedClubId.value}/equipes`, { page: 1, per_page: 100 })
    equipes.value = reponse?.data?.equipes || []

    if (equipes.value.length) {
      const equipeExiste = equipes.value.some((equipe) => String(equipe.id) === String(selectedEquipeId.value))
      if (!equipeExiste) {
        selectedEquipeId.value = String(equipes.value[0].id)
      }
    } else {
      selectedEquipeId.value = ''
    }
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les equipes.')
    }
  } finally {
    chargementEquipes.value = false
  }
}

const chargerEvenements = async () => {
  if (!selectedClubId.value || !selectedEquipeId.value) {
    evenements.value = []
    pagination.value = null
    return
  }

  chargementEvenements.value = true
  erreurChargement.value = ''

  try {
    const reponse = await authGet(`/president/clubs/${selectedClubId.value}/equipes/${selectedEquipeId.value}/evenements`, {
      q: filtres.q,
      type: filtres.type,
      statut: filtres.statut,
      page: filtres.page,
      per_page: filtres.per_page,
    })

    evenements.value = reponse?.data?.evenements || []
    pagination.value = reponse?.data?.pagination || null
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    erreurChargement.value = error?.response?.message || error.message || 'Impossible de charger les evenements.'
    notifyError(erreurChargement.value)
  } finally {
    chargementEvenements.value = false
  }
}

watch(selectedClubId, async () => {
  filtres.page = 1
  await chargerEquipes()
  await chargerEvenements()
})

watch(selectedEquipeId, async () => {
  filtres.page = 1
  if (String(formulaire.adversaire_equipe_id) === String(selectedEquipeId.value)) {
    formulaire.adversaire_equipe_id = ''
  }
  await chargerEvenements()
})

watch(
  () => formulaire.type,
  (type) => {
    if (type !== 'match') {
      formulaire.adversaire = ''
      formulaire.adversaire_equipe_id = ''
    }
  }
)

watch(
  () => [filtres.type, filtres.statut],
  () => {
    filtres.page = 1
    chargerEvenements()
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
      chargerEvenements()
    }, 350)
  }
)

const ouvrirCreation = () => {
  if (!selectedClubId.value || !selectedEquipeId.value) {
    notifyError('Selectionnez d abord un club et une equipe.')
    return
  }

  reinitialiserFormulaire()
  modeEdition.value = false
  evenementEditionId.value = null
  afficherFormulaire.value = true
}

const ouvrirEdition = (evenement) => {
  formulaire.titre = evenement.titre || ''
  formulaire.type = evenement.type || 'match'
  formulaire.date_debut = convertirDatePourInput(evenement.date_debut)
  formulaire.date_fin = convertirDatePourInput(evenement.date_fin)
  formulaire.lieu = evenement.lieu || ''
  formulaire.adversaire = evenement.adversaire || ''
  formulaire.adversaire_equipe_id = evenement.adversaire_equipe_id ? String(evenement.adversaire_equipe_id) : ''
  formulaire.description = evenement.description || ''
  formulaire.statut = evenement.statut || 'planifie'
  erreursValidation.value = {}

  modeEdition.value = true
  evenementEditionId.value = evenement.id
  afficherFormulaire.value = true
}

const fermerFormulaire = () => {
  afficherFormulaire.value = false
  modeEdition.value = false
  evenementEditionId.value = null
  reinitialiserFormulaire()
}

const construirePayloadFormData = () => {
  const data = new FormData()
  data.append('titre', formulaire.titre)
  data.append('type', formulaire.type)
  data.append('date_debut', formulaire.date_debut)
  data.append('date_fin', formulaire.date_fin || '')
  data.append('lieu', formulaire.lieu || '')
  data.append('adversaire', formulaire.type === 'match' ? equipeAdversaireSelectionnee.value?.nom || formulaire.adversaire || '' : '')
  data.append('adversaire_equipe_id', formulaire.type === 'match' ? formulaire.adversaire_equipe_id || '' : '')
  data.append('description', formulaire.description || '')
  data.append('statut', formulaire.statut || 'planifie')
  return data
}

const construirePayloadJson = () => ({
  titre: formulaire.titre,
  type: formulaire.type,
  date_debut: formulaire.date_debut,
  date_fin: formulaire.date_fin || null,
  lieu: formulaire.lieu || null,
  adversaire: formulaire.type === 'match' ? equipeAdversaireSelectionnee.value?.nom || formulaire.adversaire || null : null,
  adversaire_equipe_id: formulaire.type === 'match' ? formulaire.adversaire_equipe_id || null : null,
  description: formulaire.description || null,
  statut: formulaire.statut || 'planifie',
})

const enregistrerEvenement = async () => {
  if (!selectedClubId.value || !selectedEquipeId.value) {
    notifyError('Selection de club/equipe invalide.')
    return
  }

  envoi.value = true
  erreursValidation.value = {}

  try {
    if (modeEdition.value) {
      const endpoint = `/president/clubs/${selectedClubId.value}/equipes/${selectedEquipeId.value}/evenements/${evenementEditionId.value}`
      await authPut(endpoint, construirePayloadFormData())
      succes.value = 'Evenement modifie avec succes.'
    } else {
      const endpoint = `/president/clubs/${selectedClubId.value}/equipes/${selectedEquipeId.value}/evenements`
      await authPost(endpoint, construirePayloadJson())
      succes.value = 'Evenement cree avec succes.'
      filtres.page = 1
    }

    notifySuccess(succes.value)
    fermerFormulaire()
    await chargerEvenements()
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

const supprimerEvenement = async (evenement) => {
  if (!selectedClubId.value || !selectedEquipeId.value) {
    notifyError('Selection de club/equipe invalide.')
    return
  }

  const ok = window.confirm(`Supprimer l evenement "${evenement.titre}" ?`)
  if (!ok) {
    return
  }

  try {
    const endpoint = `/president/clubs/${selectedClubId.value}/equipes/${selectedEquipeId.value}/evenements/${evenement.id}`
    const reponse = await authDelete(endpoint)
    succes.value = reponse?.message || 'Evenement supprime avec succes.'
    notifySuccess(succes.value)

    if (evenements.value.length === 1 && filtres.page > 1) {
      filtres.page -= 1
    }

    await chargerEvenements()
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de supprimer cet evenement.')
    }
  }
}

const onChangePage = (page) => {
  filtres.page = page
  chargerEvenements()
}

const onChangePerPage = (size) => {
  filtres.per_page = size
  filtres.page = 1
  chargerEvenements()
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
  await chargerEquipesAdversaires()
  await chargerEquipes()
  await chargerEvenements()
})
</script>

<template>
  <PresidentShellLayout
    breadcrumb="Evenements"
    title="Gestion des evenements"
    active-section="evenements"
    :utilisateur="utilisateurLayout"
    @logout="deconnecter"
  >
    <AppCard title="Evenements sportifs" subtitle="Planifiez matchs, entrainements et reunions par equipe.">
      <template #actions>
        <AppButton type="button" @click="ouvrirCreation">+ Nouvel evenement</AppButton>
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
          <span class="text-xs font-bold text-[#64748b]">Equipe *</span>
          <select v-model="selectedEquipeId" class="ecs-select" :disabled="chargementEquipes || !selectedClubId">
            <option value="">Choisir une equipe</option>
            <option v-for="equipe in equipes" :key="equipe.id" :value="String(equipe.id)">{{ equipe.nom }}</option>
          </select>
        </label>

        <label>
          <span class="text-xs font-bold text-[#64748b]">Type</span>
          <select v-model="filtres.type" class="ecs-select" :disabled="!selectedEquipeId">
            <option value="">Tous</option>
            <option value="match">match</option>
            <option value="entrainement">entrainement</option>
            <option value="reunion">reunion</option>
          </select>
        </label>

        <label>
          <span class="text-xs font-bold text-[#64748b]">Statut</span>
          <select v-model="filtres.statut" class="ecs-select" :disabled="!selectedEquipeId">
            <option value="">Tous</option>
            <option value="planifie">planifie</option>
            <option value="termine">termine</option>
            <option value="annule">annule</option>
          </select>
        </label>
      </div>

      <div class="mt-3 grid gap-3 lg:grid-cols-[1fr_280px]">
        <label>
          <span class="text-xs font-bold text-[#64748b]">Recherche serveur</span>
          <input
            v-model="filtres.q"
            type="text"
            class="ecs-input"
            placeholder="Titre, type, lieu, adversaire..."
            :disabled="!selectedEquipeId"
          />
        </label>

        <label>
          <span class="text-xs font-bold text-[#64748b]">Contexte</span>
          <div class="ecs-input !py-2.5 !font-semibold !text-[#334155]">
            {{ clubActuel?.nom || '-' }} - {{ equipeActuelle?.nom || '-' }}
          </div>
        </label>
      </div>
    </AppCard>

    <AppStatusMessage v-if="succes">{{ succes }}</AppStatusMessage>

    <AppModalShell v-if="afficherFormulaire" :title="titreFormulaire" max-width-class="max-w-3xl" @close="fermerFormulaire">
      <PresidentEventForm
        :model-value="formulaire"
        :errors="erreursValidation"
        :loading="envoi"
        :adversaire-options="equipesAdversairesDisponibles"
        :equipe-locale="equipeActuelle"
        :submit-label="modeEdition ? 'Mettre a jour' : 'Creer evenement'"
        @submit="enregistrerEvenement"
        @update-field="mettreAJourChamp"
      />
    </AppModalShell>

    <AppCard class="mt-4" :title="titreListe">
      <AppListState
        :loading="chargementEvenements"
        :has-data="evenements.length > 0"
        :error-message="erreurChargement"
        :empty-title="selectedEquipeId ? 'Aucun evenement pour cette equipe.' : 'Choisissez une equipe pour afficher ses evenements.'"
        empty-description="Creez des matchs, entrainements et reunions avec suivi par statut."
        @retry="chargerEvenements"
      >
        <template #empty-action>
          <AppButton v-if="selectedEquipeId" type="button" class="mt-4" @click="ouvrirCreation">Creer le premier evenement</AppButton>
        </template>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <PresidentEventListCard
            v-for="evenement in evenements"
            :key="evenement.id"
            :evenement="evenement"
            :format-date="formatDate"
            @edit="ouvrirEdition"
            @delete="supprimerEvenement"
          />
        </div>
      </AppListState>

      <AppPagination :pagination="pagination" @change-page="onChangePage" @change-per-page="onChangePerPage" />
    </AppCard>
  </PresidentShellLayout>
</template>
