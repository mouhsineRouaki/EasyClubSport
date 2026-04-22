<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import CoachShellLayout from '@/roles/coach/shared/components/CoachShellLayout.vue'
import PresidentConversationItem from '@/roles/president/messagerie/components/PresidentConversationItem.vue'
import PresidentMessageBubble from '@/roles/president/messagerie/components/PresidentMessageBubble.vue'
import { useAuthSession } from '@/shared/session/useAuthSession'
import { authDelete, authGet, authPost, authPut } from '@/shared/services/apiClient'
import { disconnectRealtime, subscribeToCanalMessages } from '@/shared/services/realtimeService'
import { notifyError, notifySuccess } from '@/shared/services/toastService'

const { utilisateur, chargerUtilisateur, deconnecter, gererErreurAuthentification } = useAuthSession()
const chargementCanaux = ref(true)
const chargementMessages = ref(false)
const canaux = ref([])
const messages = ref([])
const selectedCanalId = ref('')
const recherche = ref('')
const editionMessageId = ref(null)
const editionContenu = ref('')
const erreurs = ref({})
const formulaire = ref('')
const messagesViewport = ref(null)
const searchDebounce = ref(null)
const stopRealtimeSubscription = ref(() => {})

const canauxFiltres = computed(() => {
  const terme = recherche.value.trim().toLowerCase()

  if (!terme) {
    return canaux.value
  }

  return canaux.value.filter((canal) => {
    const contenuRecherche = [canal.nom, canal.description, canal.equipe?.nom, canal.club?.nom]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()

    return contenuRecherche.includes(terme)
  })
})

const canalActuel = computed(() => {
  return canaux.value.find((canal) => String(canal.id) === String(selectedCanalId.value)) || null
})

const utilisateurIdActuel = computed(() => utilisateur.value?.id ?? null)

const gererErreurMessagerie = (error) => {
  if (gererErreurAuthentification(error)) {
    disconnectRealtime()
    return true
  }

  return false
}

const scrollToBottom = async () => {
  await nextTick()

  if (messagesViewport.value) {
    messagesViewport.value.scrollTo({ top: messagesViewport.value.scrollHeight, behavior: 'smooth' })
  }
}

const normaliserMessage = (message) => ({
  id: message.id,
  canal_id: message.canal_id,
  equipe_id: message.equipe_id,
  expediteur_id: message.expediteur_id,
  contenu: message.contenu,
  type_message: message.type_message,
  expediteur: message.expediteur || null,
  created_at: message.created_at,
  updated_at: message.updated_at,
})

const chargerCanaux = async () => {
  chargementCanaux.value = true

  try {
    const reponse = await authGet('/coach/canaux')
    canaux.value = reponse?.data?.canaux || []

    if (!selectedCanalId.value && canaux.value.length) {
      selectedCanalId.value = String(canaux.value[0].id)
    }
  } catch (error) {
    if (!gererErreurMessagerie(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les canaux coach.')
    }
  } finally {
    chargementCanaux.value = false
  }
}

const chargerMessages = async () => {
  if (!selectedCanalId.value) {
    messages.value = []
    return
  }

  chargementMessages.value = true

  try {
    const reponse = await authGet(`/coach/canaux/${selectedCanalId.value}/messages`)
    messages.value = reponse?.data?.messages || []
    await scrollToBottom()
  } catch (error) {
    if (!gererErreurMessagerie(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les messages coach.')
    }
  } finally {
    chargementMessages.value = false
  }
}

const synchroniserRealtime = () => {
  stopRealtimeSubscription.value?.()
  stopRealtimeSubscription.value = () => {}

  if (!canalActuel.value?.id) {
    return
  }

  stopRealtimeSubscription.value = subscribeToCanalMessages(canalActuel.value.id, async (payload) => {
    const message = normaliserMessage(payload)

    if (messages.value.some((item) => item.id === message.id)) return
    if (String(message.canal_id) !== String(canalActuel.value?.id)) return

    messages.value = [...messages.value, message]
    await scrollToBottom()
  })
}

const envoyer = async () => {
  if (!selectedCanalId.value || !formulaire.value.trim()) return

  erreurs.value = {}

  try {
    const reponse = await authPost(`/coach/canaux/${selectedCanalId.value}/messages`, { contenu: formulaire.value })
    const message = reponse?.data?.message

    if (message && !messages.value.some((item) => item.id === message.id)) {
      messages.value = [...messages.value, message]
    }

    formulaire.value = ''
    notifySuccess(reponse?.message || 'Message envoye avec succes.')
    await scrollToBottom()
  } catch (error) {
    if (gererErreurMessagerie(error)) {
      return
    }

    erreurs.value = error?.response?.data || {}
    notifyError(error?.response?.message || error.message || 'Impossible d envoyer le message.')
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
    const reponse = await authPut(`/coach/messages/${message.id}`, { contenu: editionContenu.value })
    const messageMisAJour = reponse?.data?.message || { ...message, contenu: editionContenu.value }
    messages.value = messages.value.map((item) => (item.id === message.id ? normaliserMessage(messageMisAJour) : item))
    annulerEdition()
    notifySuccess(reponse?.message || 'Message modifie avec succes.')
  } catch (error) {
    if (!gererErreurMessagerie(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de modifier le message.')
    }
  }
}

const supprimerMessage = async (message) => {
  if (!window.confirm('Supprimer ce message ?')) return

  try {
    const reponse = await authDelete(`/coach/messages/${message.id}`)
    messages.value = messages.value.filter((item) => item.id !== message.id)
    notifySuccess(reponse?.message || 'Message supprime avec succes.')
  } catch (error) {
    if (!gererErreurMessagerie(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de supprimer le message.')
    }
  }
}

const deconnecterMessagerie = () => {
  disconnectRealtime()
  deconnecter()
}

watch(selectedCanalId, async () => {
  editionMessageId.value = null
  editionContenu.value = ''
  await chargerMessages()
  synchroniserRealtime()
})

watch(recherche, () => {
  if (searchDebounce.value) {
    clearTimeout(searchDebounce.value)
  }

  searchDebounce.value = setTimeout(() => {
    if (!canauxFiltres.value.some((canal) => String(canal.id) === String(selectedCanalId.value))) {
      selectedCanalId.value = canauxFiltres.value[0] ? String(canauxFiltres.value[0].id) : ''
    }
  }, 150)
})

onMounted(async () => {
  chargerUtilisateur()
  await chargerCanaux()
  await chargerMessages()
})

onBeforeUnmount(() => {
  if (searchDebounce.value) {
    clearTimeout(searchDebounce.value)
  }

  stopRealtimeSubscription.value?.()
})
</script>

<template>
  <CoachShellLayout
    title="Messagerie coach"
    subtitle="Conversations de vos equipes avec mise a jour en temps reel."
    active-tab="messagerie"
    :user="utilisateur"
    @logout="deconnecterMessagerie"
  >
    <div class="grid gap-6 xl:grid-cols-[340px_minmax(0,1fr)]">
      <aside class="overflow-hidden rounded-[28px] border border-[#e5ecfb] bg-white">
        <div class="border-b border-[#edf2ff] p-5">
          <h2 class="text-xl font-black text-[#0f172a]">Conversations</h2>
          <input v-model="recherche" type="text" placeholder="Rechercher une conversation..." class="mt-4 h-12 w-full rounded-2xl border border-[#dbe3f1] px-4 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]" />
        </div>

        <div class="max-h-[760px] overflow-y-auto p-4">
          <div v-if="chargementCanaux" class="space-y-3">
            <div v-for="item in 5" :key="item" class="h-28 animate-pulse rounded-[24px] border border-[#edf2ff] bg-[#f8fbff]"></div>
          </div>
          <div v-else class="space-y-3">
            <PresidentConversationItem v-for="canal in canauxFiltres" :key="canal.id" :canal="canal" :active="String(canal.id) === String(selectedCanalId)" @select="selectedCanalId = String($event.id)" />
            <div v-if="!canauxFiltres.length" class="rounded-[24px] border border-dashed border-[#d7e1fb] bg-[#f8fbff] p-6 text-center text-sm font-semibold text-[#64748b]">Aucune conversation.</div>
          </div>
        </div>
      </aside>

      <section class="flex min-h-[760px] flex-col overflow-hidden rounded-[28px] border border-[#e5ecfb] bg-white">
        <header class="border-b border-[#edf2ff] px-6 py-5">
          <h2 class="text-2xl font-black text-[#0f172a]">{{ canalActuel?.nom || 'Messagerie' }}</h2>
          <p class="mt-1 text-sm font-semibold text-[#64748b]">{{ canalActuel?.equipe?.nom || 'Choisissez une conversation' }}</p>
        </header>

        <div ref="messagesViewport" class="flex-1 overflow-y-auto bg-[#f8fbff] px-5 py-6">
          <div v-if="chargementMessages" class="space-y-4">
            <div v-for="item in 6" :key="item" class="h-20 animate-pulse rounded-[24px] border border-[#edf2ff] bg-white"></div>
          </div>
          <div v-else-if="!messages.length" class="grid min-h-full place-items-center text-center text-sm font-semibold text-[#64748b]">
            Aucun message dans ce canal.
          </div>
          <div v-else class="space-y-5">
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

        <form class="border-t border-[#edf2ff] bg-white p-4" @submit.prevent="envoyer">
          <div class="flex gap-3">
            <textarea v-model="formulaire" rows="3" placeholder="Ecrire un message..." class="min-h-[86px] flex-1 rounded-[22px] border border-[#dbe3f1] px-4 py-3 text-sm font-medium text-[#0f172a] outline-none focus:border-[#4c6fff]"></textarea>
            <button type="submit" class="rounded-full bg-[#0f172a] px-6 py-3 text-sm font-semibold text-white">Envoyer</button>
          </div>
          <span v-if="erreurs?.contenu?.[0]" class="mt-2 block text-xs font-semibold text-[#e11d48]">{{ erreurs.contenu[0] }}</span>
        </form>
      </section>
    </div>
  </CoachShellLayout>
</template>
