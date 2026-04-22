<script setup>
import { computed } from 'vue'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  clubs: {
    type: Array,
    default: () => [],
  },
  equipes: {
    type: Array,
    default: () => [],
  },
  participants: {
    type: Array,
    default: () => [],
  },
  clubId: {
    type: String,
    default: '',
  },
  equipeId: {
    type: String,
    default: '',
  },
  search: {
    type: String,
    default: '',
  },
  selectedIds: {
    type: Array,
    default: () => [],
  },
  loadingEquipes: {
    type: Boolean,
    default: false,
  },
  loadingParticipants: {
    type: Boolean,
    default: false,
  },
  submitting: {
    type: Boolean,
    default: false,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
})

const emit = defineEmits([
  'close',
  'submit',
  'update:clubId',
  'update:equipeId',
  'update:search',
  'toggle-participant',
  'toggle-all',
])

const lireErreur = (champ) => props.errors?.[champ]?.[0] || ''
const selectionComplete = computed(() => props.participants.length > 0 && props.selectedIds.length === props.participants.length)
</script>

<template>
  <div
    v-if="visible"
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4 backdrop-blur-[2px]"
    @click.self="emit('close')"
  >
    <section class="w-full max-w-4xl rounded-[32px] border border-[#e7edf7] bg-white p-6 shadow-[0_30px_60px_rgba(15,23,42,0.18)]">
      <div class="flex items-center justify-between gap-3">
        <div>
          <p class="text-2xl font-black text-[#0f172a]">Nouvelle conversation</p>
          <p class="mt-1 text-sm text-[#64748b]">
            Choisissez une equipe puis selectionnez les personnes a inclure dans cette conversation.
          </p>
        </div>

        <button
          type="button"
          class="rounded-full border border-[#d7e1fb] px-4 py-2 text-xs font-semibold text-[#334155]"
          @click="emit('close')"
        >
          Fermer
        </button>
      </div>

      <form class="mt-6 space-y-5" @submit.prevent="emit('submit')">
        <div class="grid gap-4 md:grid-cols-2">
          <label class="block">
            <span class="mb-2 block text-xs font-bold uppercase tracking-[0.18em] text-[#64748b]">Club</span>
            <select
              :value="clubId"
              class="h-12 w-full rounded-2xl border border-[#dbe3f1] bg-[#fbfcff] px-4 text-sm font-semibold text-[#1e293b] outline-none focus:border-[#4c6fff]"
              @change="emit('update:clubId', $event.target.value)"
            >
              <option value="">Choisir un club</option>
              <option v-for="club in clubs" :key="club.id" :value="String(club.id)">
                {{ club.nom }}
              </option>
            </select>
          </label>

          <label class="block">
            <span class="mb-2 block text-xs font-bold uppercase tracking-[0.18em] text-[#64748b]">Equipe</span>
            <select
              :value="equipeId"
              class="h-12 w-full rounded-2xl border border-[#dbe3f1] bg-[#fbfcff] px-4 text-sm font-semibold text-[#1e293b] outline-none focus:border-[#4c6fff]"
              :disabled="loadingEquipes || !clubId"
              @change="emit('update:equipeId', $event.target.value)"
            >
              <option value="">Choisir une equipe</option>
              <option v-for="equipe in equipes" :key="equipe.id" :value="String(equipe.id)">
                {{ equipe.nom }}
              </option>
            </select>
          </label>
        </div>

        <label class="block">
          <span class="mb-2 block text-xs font-bold uppercase tracking-[0.18em] text-[#64748b]">Recherche</span>
          <input
            :value="search"
            type="text"
            placeholder="Rechercher une personne..."
            class="h-12 w-full rounded-2xl border border-[#dbe3f1] bg-[#fbfcff] px-4 text-sm font-semibold text-[#1e293b] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
            @input="emit('update:search', $event.target.value)"
          />
        </label>

        <div class="overflow-hidden rounded-[28px] border border-[#e7edf7] bg-white">
          <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#eef2f8] bg-[#f8fbff] px-5 py-4">
            <div>
              <p class="text-sm font-black uppercase tracking-[0.18em] text-[#4c6fff]">Participants</p>
              <p class="mt-1 text-sm font-medium text-[#64748b]">
                {{ selectedIds.length }} selection{{ selectedIds.length > 1 ? 's' : '' }} sur {{ participants.length }}
              </p>
            </div>

            <button
              type="button"
              class="rounded-full border border-[#d7e1fb] px-4 py-2 text-xs font-semibold text-[#2446d8] transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-50"
              :disabled="participants.length === 0"
              @click="emit('toggle-all')"
            >
              {{ selectionComplete ? 'Tout deselectionner' : 'Tout selectionner' }}
            </button>
          </div>

          <div class="max-h-[360px] overflow-y-auto p-4">
            <div v-if="loadingParticipants" class="space-y-3">
              <div v-for="index in 4" :key="index" class="h-20 animate-pulse rounded-[24px] border border-[#eef2f7] bg-[#f8fbff]"></div>
            </div>

            <div
              v-else-if="participants.length === 0"
              class="rounded-[24px] border border-dashed border-[#d8e2fb] bg-[#f8fbff] px-6 py-10 text-center"
            >
              <p class="text-lg font-black text-[#0f172a]">Aucune personne trouvee</p>
              <p class="mt-2 text-sm text-[#64748b]">
                Choisissez une equipe puis recherchez les joueurs ou le coach a inviter.
              </p>
            </div>

            <div v-else class="space-y-3">
              <label
                v-for="participant in participants"
                :key="participant.id"
                class="flex cursor-pointer items-start gap-4 rounded-[24px] border border-[#e7edf7] bg-white p-4 transition hover:border-[#cddcff] hover:bg-[#fbfdff]"
              >
                <input
                  type="checkbox"
                  class="mt-1 h-5 w-5 rounded border-[#c7d2fe] text-[#2446d8] focus:ring-[#2446d8]"
                  :checked="selectedIds.includes(participant.id)"
                  @change="emit('toggle-participant', participant.id)"
                />

                <div class="flex min-w-0 flex-1 items-center gap-4">
                  <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[radial-gradient(circle_at_top,#ffffff_0%,#99b4ff_30%,#2446d8_100%)] text-lg font-black text-white shadow-[0_18px_40px_rgba(36,70,216,0.24)]">
                    {{ (participant.prenom?.[0] || participant.nom?.[0] || participant.name?.[0] || 'U').toUpperCase() }}
                  </div>

                  <div class="min-w-0 flex-1">
                    <p class="truncate text-lg font-black text-[#0f172a]">
                      {{ participant.prenom }} {{ participant.nom }}
                    </p>
                    <p class="truncate text-sm font-semibold text-[#64748b]">{{ participant.email }}</p>
                    <p class="mt-1 text-xs font-bold uppercase tracking-[0.18em] text-[#94a3b8]">
                      {{ participant.role_equipe || participant.role || 'membre' }}
                    </p>
                  </div>
                </div>
              </label>
            </div>
          </div>
        </div>

        <p v-if="lireErreur('utilisateur_ids')" class="text-xs font-semibold text-[#e11d48]">
          {{ lireErreur('utilisateur_ids') }}
        </p>

        <div class="flex justify-end">
          <button
            type="submit"
            class="rounded-full bg-[#0f172a] px-6 py-3 text-sm font-semibold text-white transition hover:scale-[1.01] disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="submitting"
          >
            {{ submitting ? 'Creation...' : 'Creer la conversation' }}
          </button>
        </div>
      </form>
    </section>
  </div>
</template>
