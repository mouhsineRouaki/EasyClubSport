<script setup>
import { computed, ref } from 'vue'
import AppModuleHeader from '@/shared/components/AppModuleHeader.vue'
import AppButton from '@/shared/components/ui/AppButton.vue'
import AppEmptyState from '@/shared/components/ui/AppEmptyState.vue'
import PresidentConversationItem from '@/roles/president/messagerie/components/PresidentConversationItem.vue'
import PresidentMessageBubble from '@/roles/president/messagerie/components/PresidentMessageBubble.vue'

const props = defineProps({
  canaux: { type: Array, default: () => [] },
  messages: { type: Array, default: () => [] },
  canalSelectionne: { type: Object, default: null },
  chargementCanaux: { type: Boolean, default: false },
  chargementMessages: { type: Boolean, default: false },
  envoiMessage: { type: Boolean, default: false },
})

const emit = defineEmits(['selectionner-canal', 'envoyer-message'])

const messageEnvoi = ref('')

const utilisateurConnecte = computed(() => {
  const utilisateurStocke = localStorage.getItem('utilisateur_api')

  if (!utilisateurStocke) {
    return null
  }

  try {
    return JSON.parse(utilisateurStocke)
  } catch {
    return null
  }
})

const utilisateurIdActuel = computed(() => utilisateurConnecte.value?.id ?? null)

const formatDateJour = (value) => {
  if (!value) {
    return ''
  }

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'full',
  }).format(new Date(value))
}

const formatHeure = (value) => {
  if (!value) {
    return ''
  }

  return new Intl.DateTimeFormat('fr-FR', {
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(value))
}

const nomExpediteur = (message) => {
  return (
    message?.expediteur?.nom ||
    [message?.expediteur?.prenom, message?.expediteur?.nom].filter(Boolean).join(' ') ||
    message?.expediteur?.name ||
    'Utilisateur'
  )
}

const envoyer = () => {
  if (!messageEnvoi.value.trim()) return
  emit('envoyer-message', messageEnvoi.value)
  messageEnvoi.value = ''
}
</script>

<template>
  <section class="mt-6 space-y-6">
    <AppModuleHeader
      badge="Gestion coach"
      titre="Gestion des messages"
      description="A gauche les conversations, a droite le fil de discussion en temps reel."
    />

    <div class="grid gap-6 xl:grid-cols-[340px_minmax(0,1fr)]">
      <aside class="ecs-message-shell">
        <div class="border-b border-[#eef2f8] p-5">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-lg font-black text-[#0f172a]">Conversations</p>
              <p class="mt-1 text-sm text-[#64748b]">Messages des equipes du coach.</p>
            </div>

            <div class="ecs-chip-soft">
              {{ canaux.length }} canal{{ canaux.length > 1 ? 'x' : '' }}
            </div>
          </div>
        </div>

        <div class="max-h-[780px] overflow-y-auto p-4">
          <div v-if="chargementCanaux" class="space-y-3">
            <div v-for="index in 5" :key="index" class="h-[118px] animate-pulse rounded-[26px] border border-[#eef2f7] bg-[#f8fbff]"></div>
          </div>

          <AppEmptyState
            v-else-if="canaux.length === 0"
            title="Aucune conversation"
            description="Le president n a pas encore active de canal pour vos equipes."
          />

          <div v-else class="space-y-3">
            <PresidentConversationItem
              v-for="canal in canaux"
              :key="canal.id"
              :canal="canal"
              :active="String(canal.id) === String(canalSelectionne?.id)"
              @select="emit('selectionner-canal', canal)"
            />
          </div>
        </div>
      </aside>

      <section class="ecs-message-shell flex min-h-[780px] flex-col">
        <header class="ecs-message-header">
          <div class="flex flex-wrap items-center justify-between gap-4">
            <div v-if="canalSelectionne">
              <p class="text-2xl font-black text-[#0f172a]">{{ canalSelectionne.nom }}</p>
              <p class="mt-1 text-sm font-medium text-[#64748b]">
                {{ canalSelectionne.club?.nom || 'Club' }} · {{ canalSelectionne.equipe?.nom || 'Equipe' }}
              </p>
            </div>

            <div v-else>
              <p class="text-2xl font-black text-[#0f172a]">Messagerie</p>
              <p class="mt-1 text-sm font-medium text-[#64748b]">Choisissez une conversation a gauche.</p>
            </div>

            <div v-if="canalSelectionne" class="ecs-chip-soft">
              {{ messages.length }} messages visibles
            </div>
          </div>
        </header>

        <div class="ecs-message-body">
          <div v-if="!canalSelectionne" class="grid min-h-full place-items-center">
            <div class="max-w-md text-center">
              <p class="text-3xl font-black text-[#0f172a]">Selectionnez une conversation</p>
              <p class="mt-3 text-sm font-medium text-[#64748b]">
                La liste est a gauche. Les nouveaux messages apparaissent automatiquement.
              </p>
            </div>
          </div>

          <div v-else-if="chargementMessages" class="space-y-4">
            <div v-for="index in 6" :key="index" class="h-20 animate-pulse rounded-[24px] border border-[#eef2f7] bg-white/90"></div>
          </div>

          <div v-else-if="messages.length === 0" class="grid min-h-full place-items-center">
            <div class="max-w-md text-center">
              <p class="text-3xl font-black text-[#0f172a]">Aucun message</p>
              <p class="mt-3 text-sm font-medium text-[#64748b]">
                Lancez la discussion avec votre equipe depuis ce canal.
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
              :message="{ ...message, expediteur: { ...(message.expediteur || {}), nom: nomExpediteur(message) } }"
              :own="utilisateurIdActuel && message.expediteur_id === utilisateurIdActuel"
            />
          </div>
        </div>

        <div class="ecs-message-composer">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <label class="block flex-1">
              <span class="sr-only">Message</span>
              <textarea
                v-model="messageEnvoi"
                rows="3"
                placeholder="Ecrire un message..."
                class="min-h-[86px] w-full rounded-[24px] border border-[#dbe3f1] bg-[#fbfcff] px-4 py-3 text-sm font-medium text-[#0f172a] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
                :disabled="!canalSelectionne || envoiMessage"
                @keydown.enter.exact.prevent="envoyer"
              ></textarea>
            </label>

            <AppButton
              type="button"
              size="lg"
              :disabled="!canalSelectionne || !messageEnvoi.trim() || envoiMessage"
              @click="envoyer"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m22 2-7 20-4-9-9-4 20-7Z" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              {{ envoiMessage ? 'Envoi...' : 'Envoyer' }}
            </AppButton>
          </div>
        </div>
      </section>
    </div>
  </section>
</template>
