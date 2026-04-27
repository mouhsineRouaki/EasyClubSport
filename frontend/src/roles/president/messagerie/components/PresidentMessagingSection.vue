<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import PresidentConversationItem from './PresidentConversationItem.vue'
import PresidentConversationCreateModal from './PresidentConversationCreateModal.vue'
import PresidentConversationParticipantsModal from './PresidentConversationParticipantsModal.vue'
import PresidentMessageBubble from './PresidentMessageBubble.vue'
import { authDelete, authGet, authPost, authPut } from '@/shared/services/apiClient'
import {
  createPresidentConversation,
  fetchConversationParticipants,
  removeConversationParticipant,
} from '@/roles/president/messagerie/services/presidentMessagingService'
import { disconnectRealtime, subscribeToCanalMessages } from '@/shared/services/realtimeService'
import { notifyError, notifySuccess } from '@/shared/services/toastService'

const props = defineProps({
  searchTerm: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['update:searchTerm'])
const router = useRouter()

const chargementClubs = ref(false)
const chargementEquipes = ref(false)
const chargementCanaux = ref(false)
const chargementMessages = ref(false)
const chargementEquipesCreation = ref(false)
const chargementParticipantsCreation = ref(false)
const chargementParticipantsCanal = ref(false)
const creationCanal = ref(false)
const envoiMessage = ref(false)
const clubs = ref([])
const equipes = ref([])
const equipesCreation = ref([])
const canaux = ref([])
const messages = ref([])
const paginationCanaux = ref(null)
const paginationMessages = ref(null)
const selectedClubId = ref('')
const selectedEquipeId = ref('')
const selectedCanalId = ref('')
const utilisateurConnecte = ref(null)
const afficherCreationCanal = ref(false)
const afficherParticipants = ref(false)
const participantsCreation = ref([])
const participantsCanal = ref([])
const erreursValidationCanal = ref({})
const erreursValidationMessage = ref({})
const erreurCanaux = ref('')
const erreurMessages = ref('')
const editionMessageId = ref(null)
const editionContenu = ref('')
const searchDebounce = ref(null)
const searchParticipantsDebounce = ref(null)
const stopRealtimeSubscription = ref(() => {})
const unreadByCanal = ref({})
const messagesViewport = ref(null)
const retraitParticipantId = ref('')

const filtresCanaux = reactive({
  page: 1,
  per_page: 12,
})

const formulaireCanal = reactive({
  club_id: '',
  equipe_id: '',
  nom: '',
  image: null,
  recherche_participant: '',
  utilisateur_ids: [],
})

const formulaireMessage = reactive({
  contenu: '',
})

const clubActuel = computed(() => clubs.value.find((club) => String(club.id) === String(selectedClubId.value)) || null)
const equipeActuelle = computed(() => equipes.value.find((equipe) => String(equipe.id) === String(selectedEquipeId.value)) || null)
const canalActuel = computed(() => canaux.value.find((canal) => String(canal.id) === String(selectedCanalId.value)) || null)
const utilisateurIdActuel = computed(() => utilisateurConnecte.value?.id ?? null)

const lireErreurCanal = (champ) => erreursValidationCanal.value?.[champ]?.[0] || ''
const lireErreurMessage = (champ) => erreursValidationMessage.value?.[champ]?.[0] || ''

const gerer401 = (error) => {
  if (error?.response?.code === 401) {
    localStorage.removeItem('token_api')
    localStorage.removeItem('utilisateur_api')
    disconnectRealtime()
    router.push('/login')
    return true
  }

  return false
}

const formatDateJour = (value) => {
  if (!value) {
    return ''
  }

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'full',
  }).format(new Date(value))
}

const scrollToBottom = async (behavior = 'smooth') => {
  await nextTick()

  if (messagesViewport.value) {
    messagesViewport.value.scrollTo({
      top: messagesViewport.value.scrollHeight,
      behavior,
    })
  }
}

const reinitialiserFormulaireCanal = () => {
  formulaireCanal.club_id = ''
  formulaireCanal.equipe_id = ''
  formulaireCanal.nom = ''
  formulaireCanal.image = null
  formulaireCanal.recherche_participant = ''
  formulaireCanal.utilisateur_ids = []
  equipesCreation.value = []
  participantsCreation.value = []
  erreursValidationCanal.value = {}
}

const normaliserMessage = (message) => ({
  id: message.id,
  canal_id: message.canal_id,
  equipe_id: message.equipe_id,
  expediteur_id: message.expediteur_id,
  contenu: message.contenu,
  type_message: message.type_message,
  expediteur: message.expediteur || null,
  equipe: message.equipe || null,
  club: message.club || null,
  created_at: message.created_at,
  updated_at: message.updated_at,
})

const pousserMessage = async (message, { incrementUnread = false } = {}) => {
  if (!message?.id) {
    return
  }

  const messageNormalise = normaliserMessage(message)

  if (messages.value.some((item) => item.id === messageNormalise.id)) {
    return
  }

  if (!canalActuel.value || String(canalActuel.value.id) !== String(messageNormalise.canal_id)) {
    if (incrementUnread) {
      const canalCible = canaux.value.find((item) => String(item.id) === String(messageNormalise.canal_id))
      if (canalCible) {
        unreadByCanal.value = {
          ...unreadByCanal.value,
          [canalCible.id]: (unreadByCanal.value[canalCible.id] || 0) + 1,
        }
      }
    }
    return
  }

  messages.value = [...messages.value, messageNormalise]
  await scrollToBottom()
}

const synchroniserRealtime = () => {
  stopRealtimeSubscription.value?.()
  stopRealtimeSubscription.value = () => {}

  if (!canalActuel.value?.equipe_id) {
    return
  }

  stopRealtimeSubscription.value = subscribeToCanalMessages(canalActuel.value.id, async (payload) => {
    await pousserMessage(payload, { incrementUnread: true })
  })
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
    selectedEquipeId.value = ''
    return
  }

  chargementEquipes.value = true

  try {
    const reponse = await authGet(`/president/clubs/${selectedClubId.value}/equipes`, {
      page: 1,
      per_page: 100,
    })

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

const chargerEquipesCreation = async () => {
  if (!formulaireCanal.club_id) {
    formulaireCanal.equipe_id = ''
    equipesCreation.value = []
    participantsCreation.value = []
    return
  }

  chargementEquipesCreation.value = true

  try {
    const reponse = await authGet(`/president/clubs/${formulaireCanal.club_id}/equipes`, {
      page: 1,
      per_page: 100,
    })

    const equipesDisponibles = reponse?.data?.equipes || []
    equipesCreation.value = equipesDisponibles
    const equipeExiste = equipesDisponibles.some((equipe) => String(equipe.id) === String(formulaireCanal.equipe_id))
    formulaireCanal.equipe_id = equipeExiste ? formulaireCanal.equipe_id : (equipesDisponibles[0] ? String(equipesDisponibles[0].id) : '')
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les equipes pour cette conversation.')
    }
  } finally {
    chargementEquipesCreation.value = false
  }
}

const chargerParticipantsCreation = async () => {
  if (!afficherCreationCanal.value || !formulaireCanal.club_id || !formulaireCanal.equipe_id) {
    participantsCreation.value = []
    return
  }

  chargementParticipantsCreation.value = true

  try {
    const reponse = await authGet(
      `/president/clubs/${formulaireCanal.club_id}/equipes/${formulaireCanal.equipe_id}/canaux/participants`,
      {
        q: formulaireCanal.recherche_participant,
      }
    )

    participantsCreation.value = reponse?.data?.participants || []
    formulaireCanal.utilisateur_ids = formulaireCanal.utilisateur_ids.filter((id) =>
      participantsCreation.value.some((participant) => Number(participant.id) === Number(id))
    )
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les personnes de cette equipe.')
    }
  } finally {
    chargementParticipantsCreation.value = false
  }
}

const basculerParticipantCreation = (participantId) => {
  const id = Number(participantId)

  if (formulaireCanal.utilisateur_ids.includes(id)) {
    formulaireCanal.utilisateur_ids = formulaireCanal.utilisateur_ids.filter((currentId) => Number(currentId) !== id)
    return
  }

  formulaireCanal.utilisateur_ids = [...formulaireCanal.utilisateur_ids, id]
}

const basculerTousParticipantsCreation = () => {
  if (participantsCreation.value.length === 0) {
    return
  }

  if (formulaireCanal.utilisateur_ids.length === participantsCreation.value.length) {
    formulaireCanal.utilisateur_ids = []
    return
  }

  formulaireCanal.utilisateur_ids = participantsCreation.value.map((participant) => Number(participant.id))
}

const chargerCanaux = async (page = 1) => {
  chargementCanaux.value = true
  erreurCanaux.value = ''

  try {
    const endpoint =
      selectedClubId.value && selectedEquipeId.value
        ? `/president/clubs/${selectedClubId.value}/equipes/${selectedEquipeId.value}/canaux`
        : '/president/canaux'

    const reponse = await authGet(endpoint, {
      q: props.searchTerm,
      page,
      per_page: filtresCanaux.per_page,
    })

    canaux.value = reponse?.data?.canaux || []
    paginationCanaux.value = reponse?.data?.pagination || null

    if (canaux.value.length) {
      const existe = canaux.value.some((canal) => String(canal.id) === String(selectedCanalId.value))
      if (!existe) {
        selectedCanalId.value = String(canaux.value[0].id)
      }
    } else {
      selectedCanalId.value = ''
      messages.value = []
      paginationMessages.value = null
    }
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    erreurCanaux.value = error?.response?.message || error.message || 'Impossible de charger les conversations.'
    notifyError(erreurCanaux.value)
  } finally {
    chargementCanaux.value = false
  }
}

const chargerMessages = async (page = 1, { appendOlder = false } = {}) => {
  if (!selectedCanalId.value) {
    messages.value = []
    paginationMessages.value = null
    return
  }

  chargementMessages.value = true
  erreurMessages.value = ''

  try {
    const reponse = await authGet(`/president/canaux/${selectedCanalId.value}/messages`, {
      page,
      per_page: 20,
    })

    const liste = [...(reponse?.data?.messages || [])].reverse()

    if (appendOlder) {
      const connus = new Set(messages.value.map((message) => message.id))
      messages.value = [...liste.filter((message) => !connus.has(message.id)), ...messages.value]
    } else {
      messages.value = liste
    }

    paginationMessages.value = reponse?.data?.pagination || null

    if (!appendOlder) {
      await scrollToBottom('auto')
    }
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    erreurMessages.value = error?.response?.message || error.message || 'Impossible de charger les messages.'
    notifyError(erreurMessages.value)
  } finally {
    chargementMessages.value = false
  }
}

const refreshCurrent = async () => {
  await chargerCanaux(paginationCanaux.value?.current_page || 1)
  await chargerMessages(1)
}

const ouvrirCreationCanal = () => {
  reinitialiserFormulaireCanal()
  formulaireCanal.club_id = selectedClubId.value
  formulaireCanal.equipe_id = selectedEquipeId.value
  formulaireCanal.nom = equipeActuelle.value ? `Conversation - ${equipeActuelle.value.nom}` : ''
  afficherCreationCanal.value = true

  if (formulaireCanal.club_id) {
    chargerEquipesCreation().then(() => chargerParticipantsCreation())
  }
}

const fermerCreationCanal = () => {
  afficherCreationCanal.value = false
  erreursValidationCanal.value = {}
}

const creerCanal = async () => {
  if (!formulaireCanal.club_id || !formulaireCanal.equipe_id) {
    notifyError('Selection de club ou equipe invalide.')
    return
  }

  if (!formulaireCanal.utilisateur_ids.length) {
    erreursValidationCanal.value = {
      utilisateur_ids: ['Selectionnez au moins une personne pour commencer la conversation.'],
    }
    return
  }

  if (!formulaireCanal.nom.trim()) {
    erreursValidationCanal.value = {
      nom: ['Le nom de la conversation est obligatoire.'],
    }
    return
  }

  creationCanal.value = true
  erreursValidationCanal.value = {}

  try {
    const reponse = await createPresidentConversation(formulaireCanal.club_id, formulaireCanal.equipe_id, {
      type_canal: 'prive',
      nom: formulaireCanal.nom,
      image: formulaireCanal.image,
      utilisateur_ids: formulaireCanal.utilisateur_ids,
    })

    notifySuccess(reponse?.message || 'Conversation creee avec succes.')
    selectedClubId.value = formulaireCanal.club_id
    selectedEquipeId.value = formulaireCanal.equipe_id
    fermerCreationCanal()
    await chargerCanaux(1)
    if (reponse?.data?.canal?.id) {
      selectedCanalId.value = String(reponse.data.canal.id)
    }
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    erreursValidationCanal.value = error?.response?.data || {}
  } finally {
    creationCanal.value = false
  }
}

const ouvrirParticipants = async () => {
  if (!selectedCanalId.value) {
    return
  }

  afficherParticipants.value = true
  chargementParticipantsCanal.value = true

  try {
    participantsCanal.value = await fetchConversationParticipants(selectedCanalId.value)
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les participants.')
    }
  } finally {
    chargementParticipantsCanal.value = false
  }
}

const fermerParticipants = () => {
  afficherParticipants.value = false
  participantsCanal.value = []
  retraitParticipantId.value = ''
}

const retirerParticipant = async (participant) => {
  if (!selectedCanalId.value) {
    return
  }

  const ok = window.confirm(`Retirer ${participant.prenom || participant.nom || participant.name || 'ce participant'} ?`)

  if (!ok) {
    return
  }

  retraitParticipantId.value = participant.id

  try {
    const reponse = await removeConversationParticipant(selectedCanalId.value, participant.id)
    notifySuccess(reponse?.message || 'Participant retire avec succes.')
    participantsCanal.value = participantsCanal.value.filter((item) => String(item.id) !== String(participant.id))
    await chargerCanaux(paginationCanaux.value?.current_page || 1)
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de retirer ce participant.')
    }
  } finally {
    retraitParticipantId.value = ''
  }
}

const envoyerMessage = async () => {
  if (!selectedCanalId.value) {
    notifyError('Selectionnez une conversation.')
    return
  }

  envoiMessage.value = true
  erreursValidationMessage.value = {}

  try {
    const reponse = await authPost(`/president/canaux/${selectedCanalId.value}/messages`, {
      contenu: formulaireMessage.contenu,
    })

    const message = reponse?.data?.message
    if (message) {
      await pousserMessage(message)
    }

    formulaireMessage.contenu = ''
    notifySuccess(reponse?.message || 'Message envoye avec succes.')
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    erreursValidationMessage.value = error?.response?.data || {}
  } finally {
    envoiMessage.value = false
  }
}

const commencerEdition = (message) => {
  editionMessageId.value = message.id
  editionContenu.value = message.contenu || ''
}

const annulerEdition = () => {
  editionMessageId.value = null
  editionContenu.value = ''
}

const enregistrerEdition = async (message) => {
  try {
    const reponse = await authPut(`/president/messages/${message.id}`, {
      contenu: editionContenu.value,
    })

    const messageMaj = reponse?.data?.message
    messages.value = messages.value.map((item) => (item.id === message.id ? normaliserMessage(messageMaj || { ...item, contenu: editionContenu.value }) : item))
    annulerEdition()
    notifySuccess(reponse?.message || 'Message modifie avec succes.')
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de modifier ce message.')
    }
  }
}

const supprimerMessage = async (message) => {
  const ok = window.confirm('Supprimer ce message ?')

  if (!ok) {
    return
  }

  try {
    const reponse = await authDelete(`/president/messages/${message.id}`)
    messages.value = messages.value.filter((item) => item.id !== message.id)
    notifySuccess(reponse?.message || 'Message supprime avec succes.')
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de supprimer ce message.')
    }
  }
}

const selectionnerCanal = (canal) => {
  selectedCanalId.value = String(canal.id)
  unreadByCanal.value = {
    ...unreadByCanal.value,
    [canal.id]: 0,
  }
}

const chargerMessagesPlusAnciens = async () => {
  if (!paginationMessages.value || paginationMessages.value.current_page >= paginationMessages.value.last_page) {
    return
  }

  await chargerMessages(paginationMessages.value.current_page + 1, { appendOlder: true })
}

watch(selectedClubId, async () => {
  filtresCanaux.page = 1
  selectedCanalId.value = ''
  await chargerEquipes()
  await chargerCanaux(1)
})

watch(selectedEquipeId, async () => {
  filtresCanaux.page = 1
  selectedCanalId.value = ''
  await chargerCanaux(1)
})

watch(
  () => props.searchTerm,
  () => {
    if (searchDebounce.value) {
      clearTimeout(searchDebounce.value)
    }

    searchDebounce.value = setTimeout(() => {
      chargerCanaux(1)
    }, 300)
  }
)

watch(selectedCanalId, async () => {
  messages.value = []
  paginationMessages.value = null
  annulerEdition()
  participantsCanal.value = []
  afficherParticipants.value = false
  await chargerMessages(1)
  synchroniserRealtime()
})

watch(
  () => formulaireCanal.club_id,
  async (nouveauClubId) => {
    if (!afficherCreationCanal.value) {
      return
    }

    formulaireCanal.utilisateur_ids = []

    if (!nouveauClubId) {
      formulaireCanal.equipe_id = ''
      participantsCreation.value = []
      return
    }

    await chargerEquipesCreation()
    await chargerParticipantsCreation()
  }
)

watch(
  () => formulaireCanal.equipe_id,
  async () => {
    if (!afficherCreationCanal.value) {
      return
    }

    formulaireCanal.utilisateur_ids = []
    await chargerParticipantsCreation()
  }
)

watch(
  () => formulaireCanal.recherche_participant,
  () => {
    if (!afficherCreationCanal.value) {
      return
    }

    if (searchParticipantsDebounce.value) {
      clearTimeout(searchParticipantsDebounce.value)
    }

    searchParticipantsDebounce.value = setTimeout(() => {
      chargerParticipantsCreation()
    }, 250)
  }
)

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
})

onBeforeUnmount(() => {
  if (searchDebounce.value) {
    clearTimeout(searchDebounce.value)
  }

  if (searchParticipantsDebounce.value) {
    clearTimeout(searchParticipantsDebounce.value)
  }

  stopRealtimeSubscription.value?.()
})

defineExpose({
  refreshCurrent,
})
</script>

<template>
  <section class="mt-6 space-y-6">
    <header class="mx-auto max-w-3xl text-center">
      <p class="text-sm font-bold uppercase tracking-[0.35em] text-[#4c6fff]">Gestion president</p>
      <h2 class="mt-3 text-5xl font-black tracking-[-0.05em] text-[#0f172a]">Gestion des messages</h2>
      <p class="mt-4 text-lg font-medium text-[#64748b]">
        A gauche les conversations, a droite le fil de discussion en temps reel.
      </p>
    </header>

    <div class="grid gap-6 xl:grid-cols-[340px_minmax(0,1fr)]">
      <aside class="overflow-hidden rounded-[32px] border border-[#e7edf7] bg-white shadow-[0_24px_60px_rgba(15,23,42,0.08)]">
        <div class="border-b border-[#eef2f8] p-5">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-lg font-black text-[#0f172a]">Conversations</p>
              <p class="mt-1 text-sm text-[#64748b]">Organisation des canaux par equipe.</p>
            </div>

            <button
              type="button"
              class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-[#0f172a] text-white transition hover:scale-[1.02]"
              @click="ouvrirCreationCanal"
            >
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M12 5v14M5 12h14" stroke-linecap="round" />
              </svg>
            </button>
          </div>

          <div class="mt-5 space-y-3">
            <select
              v-model="selectedClubId"
              class="h-12 w-full rounded-2xl border border-[#dbe3f1] bg-[#fbfcff] px-4 text-sm font-semibold text-[#1e293b] outline-none focus:border-[#4c6fff]"
              :disabled="chargementClubs"
            >
              <option value="">Choisir un club</option>
              <option v-for="club in clubs" :key="club.id" :value="String(club.id)">
                {{ club.nom }}
              </option>
            </select>

            <select
              v-model="selectedEquipeId"
              class="h-12 w-full rounded-2xl border border-[#dbe3f1] bg-[#fbfcff] px-4 text-sm font-semibold text-[#1e293b] outline-none focus:border-[#4c6fff]"
              :disabled="chargementEquipes || !selectedClubId"
            >
              <option value="">Choisir une equipe</option>
              <option v-for="equipe in equipes" :key="equipe.id" :value="String(equipe.id)">
                {{ equipe.nom }}
              </option>
            </select>

            <label class="block">
              <span class="sr-only">Recherche conversations</span>
              <input
                :value="searchTerm"
                type="text"
                placeholder="Rechercher une conversation..."
                class="h-12 w-full rounded-2xl border border-[#dbe3f1] bg-[#fbfcff] px-4 text-sm font-semibold text-[#1e293b] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
                @input="emit('update:searchTerm', $event.target.value)"
              />
            </label>
          </div>
        </div>

        <div class="max-h-[780px] overflow-y-auto p-4">
          <div v-if="erreurCanaux" class="rounded-[24px] border border-[#fecdd3] bg-[#fff1f2] px-4 py-3 text-sm font-semibold text-[#be123c]">
            {{ erreurCanaux }}
          </div>

          <div v-else-if="chargementCanaux" class="space-y-3">
            <div v-for="index in 5" :key="index" class="h-[118px] animate-pulse rounded-[26px] border border-[#eef2f7] bg-[#f8fbff]"></div>
          </div>

          <div v-else-if="canaux.length === 0" class="rounded-[28px] border border-dashed border-[#d8e2fb] bg-[#f8fbff] px-6 py-10 text-center">
            <p class="text-xl font-black text-[#0f172a]">Aucune conversation</p>
            <p class="mt-2 text-sm text-[#64748b]">Commencez par creer un canal pour l equipe selectionnee.</p>
            <button
              type="button"
              class="mt-5 rounded-full bg-[#0f172a] px-5 py-3 text-sm font-semibold text-white"
              @click="ouvrirCreationCanal"
            >
              Creer une conversation
            </button>
          </div>

          <div v-else class="space-y-3">
            <PresidentConversationItem
              v-for="canal in canaux"
              :key="canal.id"
              :canal="canal"
              :active="String(canal.id) === String(selectedCanalId)"
              :unread="unreadByCanal[canal.id] || 0"
              @select="selectionnerCanal"
            />
          </div>
        </div>
      </aside>

      <section class="flex min-h-[780px] flex-col overflow-hidden rounded-[32px] border border-[#e7edf7] bg-white shadow-[0_24px_60px_rgba(15,23,42,0.08)]">
        <header class="border-b border-[#eef2f8] px-6 py-5">
          <div class="flex flex-wrap items-center justify-between gap-4">
            <div v-if="canalActuel" class="flex items-center gap-4">
              <img
                v-if="canalActuel.image_url"
                :src="canalActuel.image_url"
                :alt="canalActuel.nom"
                class="h-16 w-16 rounded-[22px] object-cover shadow-[0_18px_40px_rgba(36,70,216,0.18)]"
              />
              <div
                v-else
                class="grid h-16 w-16 place-items-center rounded-[22px] bg-[linear-gradient(135deg,#0f172a,#2446d8)] text-lg font-black text-white shadow-[0_18px_40px_rgba(36,70,216,0.18)]"
              >
                {{ canalActuel.nom?.slice(0, 2)?.toUpperCase() || 'CV' }}
              </div>

              <div>
                <p class="text-2xl font-black text-[#0f172a]">{{ canalActuel.nom }}</p>
                <p class="mt-1 text-sm font-medium text-[#64748b]">
                {{ canalActuel.club?.nom || clubActuel?.nom || 'Club' }} · {{ canalActuel.equipe?.nom || equipeActuelle?.nom || 'Equipe' }}
                </p>
              </div>
            </div>

            <div v-else>
              <p class="text-2xl font-black text-[#0f172a]">Messagerie</p>
              <p class="mt-1 text-sm font-medium text-[#64748b]">Choisissez une conversation a gauche.</p>
            </div>

            <div class="flex items-center gap-2">
              <button
                v-if="canalActuel"
                type="button"
                class="rounded-full border border-[#d7e1fb] px-4 py-2 text-xs font-semibold text-[#2446d8] transition hover:bg-[#f5f8ff]"
                @click="ouvrirParticipants"
              >
                Participants
              </button>

              <button
                v-if="paginationMessages && paginationMessages.current_page < paginationMessages.last_page"
                type="button"
                class="rounded-full border border-[#d7e1fb] px-4 py-2 text-xs font-semibold text-[#2446d8] transition hover:bg-[#f5f8ff]"
                @click="chargerMessagesPlusAnciens"
              >
                Charger les messages precedents
              </button>

              <div v-if="canalActuel" class="rounded-full border border-[#d7e1fb] bg-[#f8fbff] px-4 py-2 text-xs font-semibold text-[#4b5563]">
                {{ messages.length }} messages visibles
              </div>
            </div>
          </div>
        </header>

        <div
          ref="messagesViewport"
          class="flex-1 overflow-y-auto bg-[linear-gradient(180deg,#f8fbff_0%,#f4f7ff_100%)] px-5 py-6 sm:px-6"
        >
          <div v-if="erreurMessages" class="rounded-[24px] border border-[#fecdd3] bg-[#fff1f2] px-4 py-3 text-sm font-semibold text-[#be123c]">
            {{ erreurMessages }}
          </div>

          <div v-else-if="!canalActuel" class="grid min-h-full place-items-center">
            <div class="max-w-md text-center">
              <p class="text-3xl font-black text-[#0f172a]">Selectionnez une conversation</p>
              <p class="mt-3 text-sm font-medium text-[#64748b]">
                La liste est a gauche. Des que vous entrez dans un canal, les nouveaux messages arrivent en direct.
              </p>
            </div>
          </div>

          <div v-else-if="chargementMessages && messages.length === 0" class="space-y-4">
            <div
              v-for="index in 6"
              :key="index"
              class="h-20 animate-pulse rounded-[24px] border border-[#eef2f7] bg-white/90"
            ></div>
          </div>

          <div v-else-if="messages.length === 0" class="grid min-h-full place-items-center">
            <div class="max-w-md text-center">
              <p class="text-3xl font-black text-[#0f172a]">Aucun message</p>
              <p class="mt-3 text-sm font-medium text-[#64748b]">
                Ouvrez la discussion avec le premier message pour l equipe selectionnee.
              </p>
            </div>
          </div>

          <div v-else class="space-y-5">
            <div class="flex justify-center">
              <span class="rounded-full border border-[#dbe3f1] bg-white px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-[#64748b]">
                {{ formatDateJour(messages[messages.length - 1]?.created_at) }}
              </span>
            </div>

            <PresidentMessageBubble
              v-for="message in messages"
              :key="message.id"
              :message="message"
              :own="utilisateurIdActuel && message.expediteur_id === utilisateurIdActuel"
              :editing="editionMessageId === message.id"
              :editing-value="editionContenu"
              @update:editing-value="editionContenu = $event"
              @edit="commencerEdition"
              @cancel-edit="annulerEdition"
              @save-edit="enregistrerEdition"
              @delete="supprimerMessage"
            />
          </div>
        </div>

        <form class="border-t border-[#eef2f8] bg-white p-4 sm:p-5" @submit.prevent="envoyerMessage">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <label class="block flex-1">
              <span class="sr-only">Message</span>
              <textarea
                v-model="formulaireMessage.contenu"
                rows="3"
                placeholder="Ecrire un message..."
                class="min-h-[86px] w-full rounded-[24px] border border-[#dbe3f1] bg-[#fbfcff] px-4 py-3 text-sm font-medium text-[#0f172a] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
                :disabled="!selectedCanalId || envoiMessage"
                @keydown.enter.exact.prevent="envoyerMessage"
              ></textarea>
              <span v-if="lireErreurMessage('contenu')" class="mt-2 block text-xs font-semibold text-[#e11d48]">
                {{ lireErreurMessage('contenu') }}
              </span>
            </label>

            <button
              type="submit"
              class="inline-flex h-14 items-center justify-center gap-2 rounded-full bg-[#0f172a] px-6 text-sm font-semibold text-white transition hover:scale-[1.01] disabled:cursor-not-allowed disabled:opacity-50"
              :disabled="!selectedCanalId || envoiMessage"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m22 2-7 20-4-9-9-4 20-7Z" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              {{ envoiMessage ? 'Envoi...' : 'Envoyer' }}
            </button>
          </div>
        </form>
      </section>
    </div>

    <PresidentConversationCreateModal
      :visible="afficherCreationCanal"
      :clubs="clubs"
      :equipes="equipesCreation"
      :participants="participantsCreation"
      :club-id="formulaireCanal.club_id"
      :equipe-id="formulaireCanal.equipe_id"
      :conversation-name="formulaireCanal.nom"
      :search="formulaireCanal.recherche_participant"
      :selected-ids="formulaireCanal.utilisateur_ids"
      :loading-equipes="chargementEquipesCreation"
      :loading-participants="chargementParticipantsCreation"
      :submitting="creationCanal"
      :errors="erreursValidationCanal"
      @close="fermerCreationCanal"
      @submit="creerCanal"
      @update:club-id="formulaireCanal.club_id = $event"
      @update:equipe-id="formulaireCanal.equipe_id = $event"
      @update:conversation-name="formulaireCanal.nom = $event"
      @update:search="formulaireCanal.recherche_participant = $event"
      @update:image="formulaireCanal.image = $event"
      @toggle-participant="basculerParticipantCreation"
      @toggle-all="basculerTousParticipantsCreation"
    />

    <PresidentConversationParticipantsModal
      :visible="afficherParticipants"
      :canal="canalActuel"
      :participants="participantsCanal"
      :loading="chargementParticipantsCanal"
      :removing-id="retraitParticipantId"
      @close="fermerParticipants"
      @remove="retirerParticipant"
    />
  </section>
</template>
