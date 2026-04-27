<script setup>
import AppAvatar from '@/shared/components/ui/AppAvatar.vue'
import AppButton from '@/shared/components/ui/AppButton.vue'
import AppModalShell from '@/shared/components/ui/AppModalShell.vue'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  canal: {
    type: Object,
    default: null,
  },
  participants: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  removingId: {
    type: [Number, String],
    default: '',
  },
})

const emit = defineEmits(['close', 'remove'])

const nomComplet = (participant) =>
  [participant?.prenom, participant?.nom].filter(Boolean).join(' ') ||
  participant?.name ||
  'Participant'

const initials = (participant) =>
  nomComplet(participant)
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase() || 'PT'
</script>

<template>
  <AppModalShell
    v-if="visible"
    title="Participants"
    max-width-class="max-w-4xl"
    overlay-class="items-start py-6 sm:py-8"
    @close="emit('close')"
  >
    <div class="max-h-[70vh] space-y-4 overflow-y-auto pr-1">
      <div class="ecs-panel-soft p-4">
        <p class="text-lg font-black text-[#0f172a]">{{ canal?.nom || 'Conversation' }}</p>
        <p class="mt-1 text-sm font-medium text-[#64748b]">
          {{ participants.length }} participant{{ participants.length > 1 ? 's' : '' }}
        </p>
      </div>

      <div v-if="loading" class="grid gap-3">
        <div v-for="item in 4" :key="item" class="h-24 animate-pulse rounded-[24px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
      </div>

      <div v-else-if="participants.length === 0" class="ecs-empty-state">
        <p class="ecs-empty-title">Aucun participant</p>
        <p class="ecs-empty-text">Cette conversation ne contient pas encore de participants.</p>
      </div>

      <div v-else class="grid gap-3">
        <article
          v-for="participant in participants"
          :key="participant.id"
          class="ecs-panel-soft flex flex-wrap items-center justify-between gap-4 p-4"
        >
          <div class="flex min-w-0 items-center gap-4">
            <AppAvatar
              :src="participant.photo_url || participant.photo || ''"
              :alt="nomComplet(participant)"
              :initials="initials(participant)"
              size-class="h-14 w-14"
              rounded-class="rounded-[18px]"
            />

            <div class="min-w-0">
              <p class="truncate text-lg font-black text-[#0f172a]">{{ nomComplet(participant) }}</p>
              <p class="truncate text-sm font-semibold text-[#64748b]">
                {{ participant.role_equipe || participant.role || 'membre' }}
                <span v-if="participant.poste_principal"> · {{ participant.poste_principal }}</span>
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <span class="ecs-chip-soft">{{ participant.numero_joueur ? `#${participant.numero_joueur}` : 'Sans numero' }}</span>

            <AppButton
              v-if="canal?.type_canal === 'prive' && participant.role !== 'president'"
              type="button"
              variant="danger"
              size="sm"
              :disabled="String(removingId) === String(participant.id)"
              @click="emit('remove', participant)"
            >
              {{ String(removingId) === String(participant.id) ? 'Retrait...' : 'Retirer' }}
            </AppButton>
          </div>
        </article>
      </div>
    </div>
  </AppModalShell>
</template>
