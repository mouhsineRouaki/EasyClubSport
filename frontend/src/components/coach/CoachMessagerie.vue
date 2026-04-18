<script setup>
import { computed, ref } from 'vue'
import PresidentConversationItem from '../president/PresidentConversationItem.vue'

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
    <header class="mx-auto max-w-3xl text-center">
      <p class="text-sm font-bold uppercase tracking-[0.35em] text-[#4c6fff]">Gestion coach</p>
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
              <p class="mt-1 text-sm text-[#64748b]">Messages des equipes du coach.</p>
            </div>

            <div class="rounded-full border border-[#d7e1fb] bg-[#f8fbff] px-4 py-2 text-xs font-semibold text-[#4b5563]">
              {{ canaux.length }} canal{{ canaux.length > 1 ? 'x' : '' }}
            </div>
          </div>
        </div>

        <div class="max-h-[780px] overflow-y-auto p-4">
          <div v-if="chargementCanaux" class="space-y-3">
            <div v-for="index in 5" :key="index" class="h-[118px] animate-pulse rounded-[26px] border border-[#eef2f7] bg-[#f8fbff]"></div>
          </div>

          <div v-else-if="canaux.length === 0" class="rounded-[28px] border border-dashed border-[#d8e2fb] bg-[#f8fbff] px-6 py-10 text-center">
            <p class="text-xl font-black text-[#0f172a]">Aucune conversation</p>
            <p class="mt-2 text-sm text-[#64748b]">Le president n a pas encore active de canal pour vos equipes.</p>
          </div>

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

      <section class="flex min-h-[780px] flex-col overflow-hidden rounded-[32px] border border-[#e7edf7] bg-white shadow-[0_24px_60px_rgba(15,23,42,0.08)]">
        <header class="border-b border-[#eef2f8] px-6 py-5">
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

            <div v-if="canalSelectionne" class="rounded-full border border-[#d7e1fb] bg-[#f8fbff] px-4 py-2 text-xs font-semibold text-[#4b5563]">
              {{ messages.length }} messages visibles
            </div>
          </div>
        </header>

        <div class="flex-1 overflow-y-auto bg-[linear-gradient(180deg,#f8fbff_0%,#f4f7ff_100%)] px-5 py-6 sm:px-6">
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

            <article
              v-for="message in messages"
              :key="message.id"
              class="flex"
              :class="utilisateurIdActuel && message.expediteur_id === utilisateurIdActuel ? 'justify-end' : 'justify-start'"
            >
              <div class="max-w-[78%]">
                <div
                  class="mb-1 flex items-center gap-2 px-1"
                  :class="utilisateurIdActuel && message.expediteur_id === utilisateurIdActuel ? 'justify-end' : 'justify-start'"
                >
                  <p class="text-[11px] font-semibold text-[#64748b]">
                    {{ nomExpediteur(message) }}
                  </p>
                  <span class="text-[10px] text-[#94a3b8]">{{ formatHeure(message.created_at) }}</span>
                </div>

                <div
                  class="rounded-[24px] px-4 py-3 shadow-[0_18px_35px_rgba(15,23,42,0.06)]"
                  :class="
                    utilisateurIdActuel && message.expediteur_id === utilisateurIdActuel
                      ? 'rounded-br-md bg-[linear-gradient(135deg,#2446d8_0%,#4c6fff_100%)] text-white'
                      : 'rounded-bl-md border border-[#e8edf6] bg-white text-[#0f172a]'
                  "
                >
                  <p class="whitespace-pre-wrap text-sm leading-6">{{ message.contenu }}</p>
                </div>
              </div>
            </article>
          </div>
        </div>

        <div class="border-t border-[#eef2f8] bg-white p-4 sm:p-5">
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

            <button
              type="button"
              class="inline-flex h-14 items-center justify-center gap-2 rounded-full bg-[#0f172a] px-6 text-sm font-semibold text-white transition hover:scale-[1.01] disabled:cursor-not-allowed disabled:opacity-50"
              :disabled="!canalSelectionne || !messageEnvoi.trim() || envoiMessage"
              @click="envoyer"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m22 2-7 20-4-9-9-4 20-7Z" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              {{ envoiMessage ? 'Envoi...' : 'Envoyer' }}
            </button>
          </div>
        </div>
      </section>
    </div>
  </section>
</template>
