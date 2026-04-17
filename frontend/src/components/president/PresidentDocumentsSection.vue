<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import PresidentDocumentCard from './PresidentDocumentCard.vue'
import PresidentDocumentForm from './PresidentDocumentForm.vue'
import { authDelete, authGet, authPost, authPut } from '../../services/api'
import { notifyError, notifySuccess } from '../../stores/toast'

const props = defineProps({
  searchTerm: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['update:searchTerm'])
const router = useRouter()

const chargementClubs = ref(false)
const chargementDocuments = ref(false)
const envoiDocument = ref(false)
const clubs = ref([])
const documents = ref([])
const pagination = ref(null)
const erreurChargement = ref('')
const selectedClubId = ref('')
const documentSelectionne = ref(null)
const mode = ref('liste')
const erreursValidation = ref({})
const debounceRecherche = ref(null)
const fichierDocument = ref(null)

const formulaire = reactive({
  club_id: '',
  utilisateur_id: '',
  nom: '',
  type_document: '',
})

const nomFichierSelectionne = computed(() => fichierDocument.value?.name || '')
const clubFormulaire = computed(() => clubs.value.find((club) => String(club.id) === String(formulaire.club_id)) || null)

const formatDate = (date) => {
  if (!date) {
    return '-'
  }

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(date))
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

const reinitialiserFormulaire = () => {
  formulaire.club_id = selectedClubId.value || (clubs.value[0] ? String(clubs.value[0].id) : '')
  formulaire.utilisateur_id = ''
  formulaire.nom = ''
  formulaire.type_document = ''
  fichierDocument.value = null
  erreursValidation.value = {}
}

const remplirFormulaire = (document) => {
  formulaire.club_id = selectedClubId.value || ''
  formulaire.utilisateur_id = String(document.utilisateur_id || '')
  formulaire.nom = document.nom || ''
  formulaire.type_document = document.type_document || ''
  fichierDocument.value = null
  erreursValidation.value = {}
}

const mettreAJourChamp = (champ, valeur) => {
  formulaire[champ] = valeur
}

const choisirFichier = (event) => {
  const fichier = event?.target?.files?.[0]

  if (!fichier) {
    return
  }

  fichierDocument.value = fichier
}

const construirePayload = () => {
  const donnees = new FormData()
  donnees.append('nom', formulaire.nom || '')
  donnees.append('type_document', formulaire.type_document || '')

  if (mode.value !== 'edition') {
    donnees.append('utilisateur_id', formulaire.utilisateur_id || '')
  }

  if (fichierDocument.value) {
    donnees.append('fichier', fichierDocument.value)
  }

  return donnees
}

const chargerClubs = async () => {
  chargementClubs.value = true

  try {
    const reponse = await authGet('/president/clubs', { page: 1, per_page: 50 })
    clubs.value = reponse?.data?.clubs || []

    if (!selectedClubId.value && clubs.value.length) {
      selectedClubId.value = String(clubs.value[0].id)
    }

    if (!formulaire.club_id && clubs.value.length) {
      formulaire.club_id = String(clubs.value[0].id)
    }
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    notifyError(error?.response?.message || error.message || 'Impossible de charger les clubs pour les documents.')
  } finally {
    chargementClubs.value = false
  }
}

const chargerDocumentDetail = async (id) => {
  try {
    const reponse = await authGet(`/president/documents/${id}`)
    documentSelectionne.value = reponse?.data?.document || documentSelectionne.value
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    notifyError(error?.response?.message || error.message || 'Impossible de charger le detail de ce document.')
  }
}

const chargerDocuments = async (page = 1) => {
  chargementDocuments.value = true
  erreurChargement.value = ''

  try {
    const endpoint = selectedClubId.value ? `/president/clubs/${selectedClubId.value}/documents` : '/president/documents'
    const reponse = await authGet(endpoint, {
      q: props.searchTerm,
      page,
      per_page: 8,
    })

    documents.value = reponse?.data?.documents || []
    pagination.value = reponse?.data?.pagination || null

    if (documentSelectionne.value) {
      const documentFrais = documents.value.find((document) => document.id === documentSelectionne.value.id)
      documentSelectionne.value = documentFrais || documentSelectionne.value
    }
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    erreurChargement.value = error?.response?.message || error.message || 'Impossible de charger les documents.'
    notifyError(erreurChargement.value)
  } finally {
    chargementDocuments.value = false
  }
}

const refreshCurrent = async (page = pagination.value?.current_page || 1) => {
  await chargerDocuments(page)

  if (documentSelectionne.value?.id) {
    await chargerDocumentDetail(documentSelectionne.value.id)
  }
}

const ouvrirCreation = () => {
  if (!clubs.value.length) {
    notifyError('Aucun club disponible pour creer un document.')
    return
  }

  reinitialiserFormulaire()
  mode.value = 'creation'
}

const selectionnerDocument = (document) => {
  documentSelectionne.value = document
  mode.value = 'detail'
}

const retourListe = () => {
  mode.value = 'liste'
}

const ouvrirEdition = () => {
  if (!documentSelectionne.value) {
    return
  }

  remplirFormulaire(documentSelectionne.value)
  mode.value = 'edition'
}

const enregistrerDocument = async () => {
  if (!formulaire.club_id && mode.value !== 'edition') {
    notifyError('Selectionnez un club pour creer le document.')
    return
  }

  envoiDocument.value = true
  erreursValidation.value = {}

  try {
    if (mode.value === 'edition' && documentSelectionne.value) {
      const reponse = await authPut(`/president/documents/${documentSelectionne.value.id}`, construirePayload())
      documentSelectionne.value = reponse?.data?.document || documentSelectionne.value
      notifySuccess(reponse?.message || 'Document modifie avec succes.')
      mode.value = 'detail'
      await chargerDocuments(pagination.value?.current_page || 1)
      return
    }

    const reponse = await authPost(`/president/clubs/${formulaire.club_id}/documents`, construirePayload())
    documentSelectionne.value = reponse?.data?.document || null
    notifySuccess(reponse?.message || 'Document cree avec succes.')
    mode.value = documentSelectionne.value ? 'detail' : 'liste'
    await chargerDocuments(1)
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    erreursValidation.value = error?.response?.data || {}
    notifyError(error?.response?.message || error.message || 'Impossible d enregistrer ce document.')
  } finally {
    envoiDocument.value = false
  }
}

const supprimerDocumentSelectionne = async () => {
  if (!documentSelectionne.value) {
    return
  }

  const confirmation = window.confirm(`Supprimer le document "${documentSelectionne.value.nom}" ?`)

  if (!confirmation) {
    return
  }

  try {
    await authDelete(`/president/documents/${documentSelectionne.value.id}`)
    notifySuccess('Document supprime avec succes.')
    documentSelectionne.value = null
    mode.value = 'liste'

    const pageCourante = pagination.value?.current_page || 1
    const pageCible = documents.value.length === 1 && pageCourante > 1 ? pageCourante - 1 : pageCourante
    await chargerDocuments(pageCible)
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    notifyError(error?.response?.message || error.message || 'Impossible de supprimer ce document.')
  }
}

watch(selectedClubId, async () => {
  if (mode.value === 'creation') {
    formulaire.club_id = selectedClubId.value || (clubs.value[0] ? String(clubs.value[0].id) : '')
  }

  await chargerDocuments(1)
})

watch(
  () => props.searchTerm,
  () => {
    if (debounceRecherche.value) {
      clearTimeout(debounceRecherche.value)
    }

    debounceRecherche.value = setTimeout(() => {
      chargerDocuments(1)
    }, 350)
  },
)

onMounted(async () => {
  await chargerClubs()
  await chargerDocuments(1)
})

onBeforeUnmount(() => {
  if (debounceRecherche.value) {
    clearTimeout(debounceRecherche.value)
  }
})

defineExpose({
  refreshCurrent,
})
</script>

<template>
  <section class="mt-6">
    <template v-if="mode === 'liste'">
      <div class="mx-auto max-w-3xl text-center">
        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Gestion president</p>
        <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Gestion des documents</h3>
        <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#6b7280]">
          Choisissez un club, recherchez un document, puis ouvrez sa fiche pour modifier ou supprimer.
        </p>

        <div class="mx-auto mt-5 max-w-4xl rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-2">
          <div class="grid gap-2 md:grid-cols-[260px_1fr]">
            <select
              v-model="selectedClubId"
              class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]"
              :disabled="chargementClubs"
            >
              <option v-for="club in clubs" :key="club.id" :value="String(club.id)">
                {{ club.nom }}
              </option>
            </select>

            <input
              :value="searchTerm"
              type="text"
              placeholder="Rechercher un document..."
              class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
              @input="emit('update:searchTerm', $event.target.value)"
            />
          </div>
        </div>

        <button
          type="button"
          class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white transition hover:bg-[#2446d8]"
          @click="ouvrirCreation"
        >
          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" />
          </svg>
          Nouveau document
        </button>
      </div>

      <div v-if="chargementDocuments" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <div v-for="n in 8" :key="n" class="h-[220px] animate-pulse rounded-[26px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
      </div>

      <p v-else-if="erreurChargement" class="mt-6 rounded-2xl border border-red-100 bg-red-50 px-4 py-4 text-sm font-semibold text-red-700">
        {{ erreurChargement }}
      </p>

      <template v-else>
        <div v-if="documents.length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          <PresidentDocumentCard
            v-for="document in documents"
            :key="document.id"
            :document="document"
            :active="documentSelectionne?.id === document.id"
            @select="selectionnerDocument"
          />
        </div>

        <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
          <h4 class="text-2xl text-[#111827]">Aucun document trouve</h4>
          <p class="mt-2 text-sm font-semibold text-[#6b7280]">Ajoutez un premier document pour ce club.</p>
          <button
            type="button"
            class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white transition hover:bg-[#2446d8]"
            @click="ouvrirCreation"
          >
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
              <path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" />
            </svg>
            Ajouter le premier document
          </button>
        </div>

        <div v-if="pagination" class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#e6edf8] bg-[#f8fbff] px-4 py-3">
          <p class="text-xs font-bold text-[#6b7280]">Page {{ pagination.current_page || 1 }} / {{ pagination.last_page || 1 }}</p>
          <div class="flex gap-2">
            <button
              type="button"
              class="rounded-full border border-[#dbe2ef] px-4 py-2 text-xs font-black text-[#1f2a44] disabled:opacity-40"
              :disabled="(pagination.current_page || 1) <= 1"
              @click="chargerDocuments((pagination.current_page || 1) - 1)"
            >
              Precedent
            </button>
            <button
              type="button"
              class="rounded-full bg-[#111827] px-4 py-2 text-xs font-black text-white disabled:opacity-40"
              :disabled="(pagination.current_page || 1) >= (pagination.last_page || 1)"
              @click="chargerDocuments((pagination.current_page || 1) + 1)"
            >
              Suivant
            </button>
          </div>
        </div>
      </template>
    </template>

    <template v-else-if="mode === 'detail' && documentSelectionne">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <button
          type="button"
          class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]"
          aria-label="Retour a la liste"
          title="Retour a la liste"
          @click="retourListe"
        >
          <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M15.25 5.75 9 12l6.25 6.25M9.75 12H20" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>

        <div class="flex gap-2">
          <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]"
            aria-label="Modifier"
            title="Modifier"
            @click="ouvrirEdition"
          >
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="m14.75 5.25 4 4M4.75 19.25l4.45-.9 9.05-9.05a2.83 2.83 0 0 0-4-4L5.2 14.35l-.45 4.9Z" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
          <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#ef4444] text-white transition hover:bg-[#dc2626]"
            aria-label="Supprimer"
            title="Supprimer"
            @click="supprimerDocumentSelectionne"
          >
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M9.75 4.75h4.5m-7.5 4h10.5m-9.35 0 .73 9.5a2.25 2.25 0 0 0 2.24 2.08h2.26a2.25 2.25 0 0 0 2.24-2.08l.73-9.5M10.5 11.75v5M13.5 11.75v5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
        </div>
      </div>

      <div class="mt-5 rounded-[30px] bg-[#f3f6fb] p-4">
        <section class="rounded-[22px] bg-white p-5">
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#4c6fff]">Document</p>
              <h3 class="mt-2 text-3xl font-black text-[#111827]">{{ documentSelectionne.nom }}</h3>
              <p class="mt-2 text-sm font-semibold text-[#64748b]">
                {{ documentSelectionne.type_document || '-' }}
              </p>
            </div>
            <a
              v-if="documentSelectionne.fichier_url"
              :href="documentSelectionne.fichier_url"
              target="_blank"
              rel="noopener"
              class="rounded-full bg-[#111827] px-4 py-2 text-xs font-black text-white transition hover:bg-[#2446d8]"
            >
              Ouvrir
            </a>
          </div>

          <div class="mt-5 grid gap-4 md:grid-cols-3">
            <section class="rounded-[18px] bg-[#f8fbff] p-4">
              <p class="text-sm font-black text-[#111827]">Utilisateur</p>
              <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ documentSelectionne.utilisateur?.nom || '-' }}</p>
            </section>
            <section class="rounded-[18px] bg-[#f8fbff] p-4">
              <p class="text-sm font-black text-[#111827]">Email</p>
              <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ documentSelectionne.utilisateur?.email || '-' }}</p>
            </section>
            <section class="rounded-[18px] bg-[#f8fbff] p-4">
              <p class="text-sm font-black text-[#111827]">Date ajout</p>
              <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ formatDate(documentSelectionne.date_ajout || documentSelectionne.created_at) }}</p>
            </section>
          </div>

          <section class="mt-5 rounded-[18px] bg-[#f8fbff] p-4">
            <p class="text-sm font-black text-[#111827]">Fichier</p>
            <p class="mt-3 break-all text-sm font-semibold leading-7 text-[#64748b]">
              {{ documentSelectionne.fichier || 'Aucun fichier disponible.' }}
            </p>
          </section>
        </section>
      </div>
    </template>

    <template v-else>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <button
          type="button"
          class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]"
          aria-label="Retour"
          title="Retour"
          @click="mode = documentSelectionne ? 'detail' : 'liste'"
        >
          <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M15.25 5.75 9 12l6.25 6.25M9.75 12H20" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
        <div class="text-right">
          <h3 class="text-2xl text-[#111827]">{{ mode === 'edition' ? 'Modifier le document' : 'Ajouter un document' }}</h3>
          <p class="text-xs font-semibold text-[#64748b]">{{ clubFormulaire?.nom || 'Choisissez un club' }}</p>
        </div>
      </div>

      <PresidentDocumentForm
        :model-value="formulaire"
        :errors="erreursValidation"
        :clubs="clubs"
        :file-name="nomFichierSelectionne"
        :loading="envoiDocument"
        :disable-club="mode === 'edition'"
        :require-file="mode !== 'edition'"
        :submit-label="mode === 'edition' ? 'Enregistrer les modifications' : 'Creer le document'"
        :loading-label="mode === 'edition' ? 'Enregistrement...' : 'Creation...'"
        @choose-file="choisirFichier"
        @update-field="mettreAJourChamp"
        @submit="enregistrerDocument"
      />
    </template>
  </section>
</template>
