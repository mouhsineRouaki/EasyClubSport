<script setup>
import { computed } from 'vue'
import AppAvatar from '@/shared/components/ui/AppAvatar.vue'
import AppButton from '@/shared/components/ui/AppButton.vue'
import AppField from '@/shared/components/ui/AppField.vue'
import AppModalShell from '@/shared/components/ui/AppModalShell.vue'

const props = defineProps({
  visible: { type: Boolean, default: false },
  equipe: { type: Object, default: null },
  codeInvitation: { type: String, default: '' },
  search: { type: String, default: '' },
  message: { type: String, default: '' },
  participants: { type: Array, default: () => [] },
  selectedIds: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  submitting: { type: Boolean, default: false },
  errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits([
  'close',
  'submit',
  'update:search',
  'update:message',
  'toggle-participant',
  'toggle-all',
])

const lireErreur = (champ) => props.errors?.[champ]?.[0] || ''
const selectionComplete = computed(() => props.participants.length > 0 && props.selectedIds.length === props.participants.length)
const nomComplet = (participant) => [participant?.prenom, participant?.nom].filter(Boolean).join(' ') || participant?.name || 'Participant'
const initials = (participant) => nomComplet(participant).split(' ').filter(Boolean).slice(0, 2).map((part) => part[0]).join('').toUpperCase() || 'PT'
</script>

<template>
  <AppModalShell
    v-if="visible"
    title="Notifier des membres"
    max-width-class="max-w-5xl"
    overlay-class="items-start py-6 sm:py-8"
    @close="emit('close')"
  >
    <form class="space-y-5" @submit.prevent="emit('submit')">
      <div class="max-h-[72vh] space-y-5 overflow-y-auto pr-1">
        <section class="ecs-panel-soft p-4">
          <p class="ecs-kicker">Invitation equipe</p>
          <h3 class="mt-2 text-2xl font-black text-[#0f172a]">{{ equipe?.nom || 'Equipe' }}</h3>
          <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div class="rounded-[22px] border border-[#dbe5f2] bg-white px-4 py-3">
              <p class="text-[11px] font-black uppercase tracking-[0.16em] text-[#94a3b8]">Code invitation</p>
              <p class="mt-2 text-lg font-black text-[#111827]">{{ codeInvitation || '-' }}</p>
            </div>

            <AppField label="Message optionnel" :error="lireErreur('message')">
              <textarea
                :value="message"
                rows="3"
                class="ecs-textarea"
                placeholder="Ex: Bonjour, vous pouvez rejoindre notre equipe avec ce code."
                @input="emit('update:message', $event.target.value)"
              ></textarea>
            </AppField>
          </div>
        </section>

        <section class="ecs-panel-soft p-4">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
              <p class="ecs-kicker">Destinataires</p>
              <p class="mt-1 text-sm font-medium text-[#64748b]">
                {{ selectedIds.length }} selection{{ selectedIds.length > 1 ? 's' : '' }} sur {{ participants.length }}
              </p>
            </div>

            <AppButton type="button" variant="secondary" size="sm" :disabled="participants.length === 0" @click="emit('toggle-all')">
              {{ selectionComplete ? 'Tout deselectionner' : 'Tout selectionner' }}
            </AppButton>
          </div>

          <div class="mt-4">
            <AppField label="Recherche">
              <input
                :value="search"
                type="text"
                placeholder="Rechercher un joueur ou un coach..."
                class="ecs-input"
                @input="emit('update:search', $event.target.value)"
              />
            </AppField>
          </div>

          <div v-if="loading" class="mt-4 grid gap-3">
            <div v-for="index in 4" :key="index" class="h-20 animate-pulse rounded-[24px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
          </div>

          <div v-else-if="participants.length === 0" class="mt-4 ecs-empty-state">
            <p class="ecs-empty-title">Aucune personne trouvee</p>
            <p class="ecs-empty-text">Essayez un autre nom ou email.</p>
          </div>

          <div v-else class="mt-4 grid gap-3">
            <label
              v-for="participant in participants"
              :key="participant.id"
              class="ecs-panel-muted flex cursor-pointer items-start gap-4 p-4 transition hover:border-[#cad6ea]"
            >
              <input
                type="checkbox"
                class="mt-1 h-5 w-5 rounded border-[#c7d2fe] text-[#2446d8] focus:ring-[#2446d8]"
                :checked="selectedIds.includes(participant.id)"
                @change="emit('toggle-participant', participant.id)"
              />

              <div class="flex min-w-0 flex-1 items-center gap-4">
                <AppAvatar
                  :src="participant.photo_url || participant.photo || ''"
                  :alt="nomComplet(participant)"
                  :initials="initials(participant)"
                  size-class="h-14 w-14"
                  rounded-class="rounded-[18px]"
                />

                <div class="min-w-0 flex-1">
                  <p class="truncate text-lg font-black text-[#0f172a]">{{ nomComplet(participant) }}</p>
                  <p class="truncate text-sm font-semibold text-[#64748b]">
                    {{ participant.role || 'membre' }}
                    <span v-if="participant.poste_principal"> · {{ participant.poste_principal }}</span>
                  </p>
                  <p class="truncate text-xs font-semibold text-[#94a3b8]">{{ participant.email || '-' }}</p>
                </div>
              </div>
            </label>
          </div>

          <p v-if="lireErreur('utilisateur_ids')" class="mt-3 text-xs font-semibold text-[#e11d48]">
            {{ lireErreur('utilisateur_ids') }}
          </p>
        </section>
      </div>

      <div class="flex justify-end">
        <AppButton type="submit" :disabled="submitting">
          {{ submitting ? 'Envoi...' : 'Envoyer les invitations' }}
        </AppButton>
      </div>
    </form>
  </AppModalShell>
</template>
