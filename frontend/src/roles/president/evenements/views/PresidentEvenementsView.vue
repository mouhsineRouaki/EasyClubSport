<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppCard from '@/shared/components/AppCard.vue'
import AppListState from '@/shared/components/AppListState.vue'
import AppPagination from '@/shared/components/AppPagination.vue'
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
        <button type="button" class="ecs-btn-primary" @click="ouvrirCreation">+ Nouvel evenement</button>
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

    <div v-if="succes" class="ecs-note-success">{{ succes }}</div>

    <div
      v-if="afficherFormulaire"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4 backdrop-blur-[2px]"
      @click.self="fermerFormulaire"
    >
      <section class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl border border-[#e7edf8] bg-white p-4 shadow-[0_24px_50px_rgba(15,23,42,0.22)] sm:p-5">
        <div class="flex items-center justify-between gap-3">
          <h3 class="text-base font-bold text-[#1f2a44]">{{ titreFormulaire }}</h3>
          <button class="ecs-btn-secondary text-xs" type="button" @click="fermerFormulaire">Fermer</button>
        </div>

        <form class="mt-4 grid gap-3" @submit.prevent="enregistrerEvenement">
          <div class="grid gap-3 md:grid-cols-2">
            <label>
              <span class="text-xs font-bold text-[#64748b]">Titre *</span>
              <input v-model="formulaire.titre" type="text" class="ecs-input" />
              <span v-if="lireErreur('titre')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('titre') }}</span>
            </label>

            <label>
              <span class="text-xs font-bold text-[#64748b]">Type *</span>
              <select v-model="formulaire.type" class="ecs-select">
                <option value="match">match</option>
                <option value="entrainement">entrainement</option>
                <option value="reunion">reunion</option>
              </select>
              <span v-if="lireErreur('type')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('type') }}</span>
            </label>
          </div>

          <div class="grid gap-3 md:grid-cols-2">
            <label>
              <span class="text-xs font-bold text-[#64748b]">Date debut *</span>
              <input v-model="formulaire.date_debut" type="datetime-local" class="ecs-input" />
              <span v-if="lireErreur('date_debut')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('date_debut') }}</span>
            </label>

            <label>
              <span class="text-xs font-bold text-[#64748b]">Date fin</span>
              <input v-model="formulaire.date_fin" type="datetime-local" class="ecs-input" />
              <span v-if="lireErreur('date_fin')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('date_fin') }}</span>
            </label>
          </div>

          <div class="grid gap-3 md:grid-cols-2">
            <label>
              <span class="text-xs font-bold text-[#64748b]">Lieu</span>
              <input v-model="formulaire.lieu" type="text" class="ecs-input" />
              <span v-if="lireErreur('lieu')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('lieu') }}</span>
            </label>

            <label v-if="formulaire.type === 'match'">
              <span class="text-xs font-bold text-[#64748b]">Equipe adversaire *</span>
              <select v-model="formulaire.adversaire_equipe_id" class="ecs-select" :disabled="chargementEquipesAdversaires">
                <option value="">Choisir une equipe adversaire</option>
                <option v-for="equipe in equipesAdversairesDisponibles" :key="equipe.id" :value="String(equipe.id)">
                  {{ equipe.nom }}{{ equipe.club?.nom ? ` - ${equipe.club.nom}` : '' }}
                </option>
              </select>
              <span v-if="lireErreur('adversaire_equipe_id')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('adversaire_equipe_id') }}</span>
            </label>

            <div v-else class="rounded-xl border border-[#dbe2ef] bg-[#f8fbff] px-3 py-2">
              <span class="text-xs font-bold text-[#64748b]">Affichage</span>
              <p class="mt-1 text-sm font-bold text-[#1f2a44]">{{ formulaire.type === 'entrainement' ? 'Entrainement' : 'Reunion' }}</p>
            </div>
          </div>

          <div class="rounded-xl border border-[#e7edf8] bg-[#f8fbff] p-3">
            <div v-if="formulaire.type === 'match'" class="grid gap-3 sm:grid-cols-[1fr_auto_1fr] sm:items-center">
              <div class="flex items-center gap-3 rounded-xl bg-white p-3">
                <img v-if="logoEquipe(equipeActuelle)" :src="logoEquipe(equipeActuelle)" :alt="equipeActuelle?.nom || 'Equipe'" class="h-11 w-11 rounded-xl object-cover" />
                <span v-else class="block h-11 w-11 rounded-xl bg-[#dbe7ff]"></span>
                <div>
                  <p class="text-sm font-bold text-[#1f2a44]">{{ equipeActuelle?.nom || 'Equipe locale' }}</p>
                  <p class="text-xs text-[#64748b]">{{ clubActuel?.nom || 'Club actuel' }}</p>
                </div>
              </div>
              <span class="mx-auto rounded-full bg-[#111827] px-3 py-1 text-xs font-bold text-white">VS</span>
              <div class="flex items-center gap-3 rounded-xl bg-white p-3">
                <img v-if="logoEquipe(equipeAdversaireSelectionnee)" :src="logoEquipe(equipeAdversaireSelectionnee)" :alt="equipeAdversaireSelectionnee?.nom || 'Adversaire'" class="h-11 w-11 rounded-xl object-cover" />
                <span v-else class="block h-11 w-11 rounded-xl bg-[#e2e8f0]"></span>
                <div>
                  <p class="text-sm font-bold text-[#1f2a44]">{{ equipeAdversaireSelectionnee?.nom || 'Adversaire a choisir' }}</p>
                  <p class="text-xs text-[#64748b]">{{ equipeAdversaireSelectionnee?.club?.nom || 'Equipe de la plateforme' }}</p>
                </div>
              </div>
            </div>
            <div v-else class="flex items-center gap-3">
              <img v-if="logoEquipe(equipeActuelle)" :src="logoEquipe(equipeActuelle)" :alt="equipeActuelle?.nom || 'Equipe'" class="h-11 w-11 rounded-xl object-cover" />
              <span v-else class="block h-11 w-11 rounded-xl bg-[#dbe7ff]"></span>
              <div>
                <p class="text-sm font-bold text-[#1f2a44]">{{ formulaire.type === 'entrainement' ? 'Entrainement' : 'Reunion' }}</p>
                <p class="text-xs text-[#64748b]">{{ equipeActuelle?.nom || 'Equipe locale' }}</p>
              </div>
            </div>
          </div>

          <label>
            <span class="text-xs font-bold text-[#64748b]">Statut *</span>
            <select v-model="formulaire.statut" class="ecs-select">
              <option value="planifie">planifie</option>
              <option value="termine">termine</option>
              <option value="annule">annule</option>
            </select>
            <span v-if="lireErreur('statut')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('statut') }}</span>
          </label>

          <label>
            <span class="text-xs font-bold text-[#64748b]">Description</span>
            <textarea v-model="formulaire.description" rows="3" class="ecs-textarea"></textarea>
            <span v-if="lireErreur('description')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('description') }}</span>
          </label>

          <div class="flex justify-end">
            <button :disabled="envoi" class="ecs-btn-primary" type="submit">
              {{ envoi ? 'Enregistrement...' : modeEdition ? 'Mettre a jour' : 'Creer evenement' }}
            </button>
          </div>
        </form>
      </section>
    </div>

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
          <button v-if="selectedEquipeId" type="button" class="mt-4 ecs-btn-primary" @click="ouvrirCreation">Creer le premier evenement</button>
        </template>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <article v-for="evenement in evenements" :key="evenement.id" class="rounded-2xl border border-[#e8edf5] bg-white p-4 transition hover:-translate-y-[1px] hover:shadow-[0_10px_24px_rgba(15,23,42,0.08)]">
            <div class="flex items-start justify-between gap-2">
              <p class="line-clamp-2 text-base font-bold text-[#1f2a44]">{{ evenement.titre }}</p>
              <span class="rounded-full bg-[#f8fbff] px-2.5 py-1 text-[11px] font-bold uppercase text-[#475569]">{{ evenement.type }}</span>
            </div>

            <p class="mt-2 text-xs text-[#64748b]">Debut: {{ formatDate(evenement.date_debut) }}</p>
            <p class="mt-1 text-xs text-[#64748b]">Fin: {{ formatDate(evenement.date_fin) }}</p>

            <div class="mt-3 rounded-lg bg-[#f8fbff] px-3 py-2 text-xs text-[#64748b]">
              <p>Lieu: <span class="font-semibold text-[#1f2a44]">{{ evenement.lieu || '-' }}</span></p>
              <div v-if="evenement.type === 'match'" class="mt-2 flex items-center gap-2">
                <img v-if="logoEquipe(evenement.equipe)" :src="logoEquipe(evenement.equipe)" :alt="evenement.equipe?.nom || 'Equipe'" class="h-8 w-8 rounded-lg object-cover" />
                <span v-else class="block h-8 w-8 rounded-lg bg-[#dbe7ff]"></span>
                <span class="rounded-full bg-[#111827] px-2 py-0.5 text-[10px] font-bold text-white">VS</span>
                <img v-if="logoEquipe(evenement.adversaire_equipe)" :src="logoEquipe(evenement.adversaire_equipe)" :alt="evenement.adversaire_equipe?.nom || 'Adversaire'" class="h-8 w-8 rounded-lg object-cover" />
                <span v-else class="block h-8 w-8 rounded-lg bg-[#e2e8f0]"></span>
                <span class="font-semibold text-[#1f2a44]">{{ evenement.adversaire_equipe?.nom || evenement.adversaire || '-' }}</span>
              </div>
              <p v-else class="mt-1">Equipe: <span class="font-semibold text-[#1f2a44]">{{ evenement.equipe?.nom || equipeActuelle?.nom || '-' }}</span></p>
              <p class="mt-1">Statut: <span class="font-semibold capitalize text-[#1f2a44]">{{ evenement.statut || '-' }}</span></p>
            </div>

            <p class="mt-3 line-clamp-2 text-xs leading-5 text-[#64748b]">{{ evenement.description || 'Aucune description.' }}</p>

            <div class="mt-4 flex justify-end gap-2">
              <button class="ecs-btn-secondary !px-3 !py-1.5 !text-xs" type="button" @click="ouvrirEdition(evenement)">Modifier</button>
              <button class="ecs-btn-danger" type="button" @click="supprimerEvenement(evenement)">Supprimer</button>
            </div>
          </article>
        </div>
      </AppListState>

      <AppPagination :pagination="pagination" @change-page="onChangePage" @change-per-page="onChangePerPage" />
    </AppCard>
  </PresidentShellLayout>
</template>
