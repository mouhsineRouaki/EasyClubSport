<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  coachs: {
    type: Array,
    default: () => [],
  },
  chargement: {
    type: Boolean,
    default: false,
  },
  enAssignation: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close', 'search', 'assigner'])

const recherche = ref('')
let debounce = null

watch(recherche, (val) => {
  if (debounce) clearTimeout(debounce)
  debounce = setTimeout(() => emit('search', val), 300)
})

watch(() => props.visible, (val) => {
  if (val) {
    recherche.value = ''
    emit('search', '')
  }
})
</script>

<template>
  <Teleport to="body">
    <div
      v-if="visible"
      class="fixed inset-0 z-[9999] flex items-center justify-center bg-[#07103a]/55 backdrop-blur-sm"
      @click.self="emit('close')"
    >
      <div class="relative mx-4 w-full max-w-lg overflow-hidden rounded-[28px] border border-[#e6edf8] bg-white shadow-[0_32px_80px_-40px_rgba(15,23,42,0.55)]">
        <div class="flex items-center justify-between border-b border-[#eef2fb] px-5 py-4">
          <div>
            <p class="text-sm font-black text-[#111827]">Assigner un coach</p>
            <p class="mt-0.5 text-xs font-semibold text-[#64748b]">Recherchez et selectionnez un coach disponible.</p>
          </div>
          <button
            type="button"
            class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#dbe2ef] text-[#6b7280] transition hover:bg-[#f8fbff]"
            @click="emit('close')"
          >
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
              <path d="M5.5 5.5 14.5 14.5M14.5 5.5 5.5 14.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" />
            </svg>
          </button>
        </div>

        <div class="p-4">
          <input
            v-model="recherche"
            type="text"
            placeholder="Rechercher par nom ou email..."
            class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
          />

          <div class="mt-3 max-h-[340px] overflow-y-auto space-y-2">
            <div v-if="chargement" class="space-y-2">
              <div v-for="n in 5" :key="n" class="h-[68px] animate-pulse rounded-2xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
            </div>

            <p v-else-if="!coachs.length" class="rounded-2xl border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-4 py-6 text-center text-xs font-semibold text-[#6b7280]">
              Aucun coach trouve.
            </p>

            <button
              v-for="coach in coachs"
              v-else
              :key="coach.id"
              type="button"
              class="flex w-full items-center gap-3 rounded-2xl border border-[#e6edf8] bg-white p-3 text-left transition hover:border-[#4c6fff] hover:bg-[#f8fbff] disabled:opacity-50"
              :disabled="enAssignation"
              @click="emit('assigner', coach)"
            >
              <img
                v-if="coach.photo_url || coach.photo"
                :src="coach.photo_url || coach.photo"
                :alt="coach.nom"
                class="h-12 w-12 shrink-0 rounded-2xl object-cover"
              />
              <span v-else class="block h-12 w-12 shrink-0 rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)]"></span>
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-black text-[#111827]">
                  {{ [coach.prenom, coach.nom].filter(Boolean).join(' ') || coach.name || 'Coach' }}
                </p>
                <p class="truncate text-xs font-semibold text-[#64748b]">{{ coach.email || '-' }}</p>
                <p class="truncate text-xs font-semibold text-[#94a3b8]">{{ coach.telephone || '-' }}</p>
              </div>
              <svg v-if="enAssignation" class="h-4 w-4 animate-spin text-[#2446d8]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M16.25 9.25a6.25 6.25 0 1 0-1.72 4.31" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
              </svg>
              <svg v-else class="h-4 w-4 shrink-0 text-[#2446d8]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M7.5 10.5 9.5 12.5l3-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                <circle cx="10" cy="10" r="7.25" stroke="currentColor" stroke-width="1.8" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>
