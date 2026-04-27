<script setup>
import { computed } from 'vue'
import AppAvatar from '@/shared/components/ui/AppAvatar.vue'
import AppButton from '@/shared/components/ui/AppButton.vue'
import AppField from '@/shared/components/ui/AppField.vue'
import AppModalShell from '@/shared/components/ui/AppModalShell.vue'

const props = defineProps({
  visible: { type: Boolean, default: false },
  clubs: { type: Array, default: () => [] },
  equipes: { type: Array, default: () => [] },
  participants: { type: Array, default: () => [] },
  clubId: { type: String, default: '' },
  equipeId: { type: String, default: '' },
  conversationName: { type: String, default: '' },
  search: { type: String, default: '' },
  selectedIds: { type: Array, default: () => [] },
  loadingEquipes: { type: Boolean, default: false },
  loadingParticipants: { type: Boolean, default: false },
  submitting: { type: Boolean, default: false },
  errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits([
  'close',
  'submit',
  'update:club-id',
  'update:equipe-id',
  'update:conversation-name',
  'update:search',
  'update:image',
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
    title="Nouvelle conversation"
    max-width-class="max-w-5xl"
    overlay-class="items-start py-6 sm:py-8"
    @close="emit('close')"
  >
    <form class="space-y-5" @submit.prevent="emit('submit')">
      <div class="max-h-[70vh] space-y-5 overflow-y-auto pr-1">
      <section class="ecs-panel-soft p-4">
        <div class="grid gap-4 md:grid-cols-2">
          <AppField label="Nom de conversation" :error="lireErreur('nom')">
            <input
              :value="conversationName"
              type="text"
              placeholder="Ex: Preparation match weekend"
              class="ecs-input"
              @input="emit('update:conversation-name', $event.target.value)"
            />
          </AppField>

          <AppField label="Image de conversation" :error="lireErreur('image')" hint="Optionnel. JPG, PNG ou WEBP.">
            <input
              type="file"
              accept="image/png,image/jpeg,image/webp"
              class="ecs-input h-auto py-3"
              @change="emit('update:image', $event.target.files?.[0] || null)"
            />
          </AppField>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
          <AppField label="Club">
            <select
              :value="clubId"
              class="ecs-select"
              @change="emit('update:club-id', $event.target.value)"
            >
              <option value="">Choisir un club</option>
              <option v-for="club in clubs" :key="club.id" :value="String(club.id)">
                {{ club.nom }}
              </option>
            </select>
          </AppField>

          <AppField label="Equipe">
            <select
              :value="equipeId"
              class="ecs-select"
              :disabled="loadingEquipes || !clubId"
              @change="emit('update:equipe-id', $event.target.value)"
            >
              <option value="">Choisir une equipe</option>
              <option v-for="equipe in equipes" :key="equipe.id" :value="String(equipe.id)">
                {{ equipe.nom }}
              </option>
            </select>
          </AppField>
        </div>
      </section>

      <section class="ecs-panel-soft p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <p class="ecs-kicker">Participants</p>
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
              placeholder="Rechercher une personne..."
              class="ecs-input"
              @input="emit('update:search', $event.target.value)"
            />
          </AppField>
        </div>

        <div v-if="loadingParticipants" class="mt-4 grid gap-3">
          <div v-for="index in 4" :key="index" class="h-20 animate-pulse rounded-[24px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
        </div>

        <div v-else-if="participants.length === 0" class="mt-4 ecs-empty-state">
          <p class="ecs-empty-title">Aucune personne trouvee</p>
          <p class="ecs-empty-text">Choisissez une equipe puis recherchez les membres a inviter.</p>
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
                  {{ participant.role_equipe || participant.role || 'membre' }}
                  <span v-if="participant.poste_principal"> · {{ participant.poste_principal }}</span>
                </p>
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
          {{ submitting ? 'Creation...' : 'Creer la conversation' }}
        </AppButton>
      </div>
    </form>
  </AppModalShell>
</template>
