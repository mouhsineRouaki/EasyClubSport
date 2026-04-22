<script setup>
import MatchCompositionPlayerCard from './MatchCompositionPlayerCard.vue'

const props = defineProps({
  composition: {
    type: Object,
    default: null,
  },
  chargement: {
    type: Boolean,
    default: false,
  },
  titre: {
    type: String,
    default: 'Composition du match',
  },
  description: {
    type: String,
    default: 'Vue de la composition preparee pour ce match.',
  },
  lectureSeule: {
    type: Boolean,
    default: true,
  },
})

const groupes = [
  { key: 'titulaires', label: 'Titulaires', ton: 'green' },
  { key: 'remplacants', label: 'Remplacants', ton: 'amber' },
  { key: 'absents', label: 'Absents', ton: 'slate' },
]
</script>

<template>
  <section class="mt-5 rounded-[22px] bg-white p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="text-sm font-black text-[#111827]">{{ titre }}</p>
        <p class="mt-1 text-xs font-semibold text-[#64748b]">{{ description }}</p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <span v-if="composition?.formation"
          class="rounded-full bg-[#eef4ff] px-3 py-1 text-[11px] font-black text-[#2446d8]">
          Formation {{ composition.formation }}
        </span>
        <span class="rounded-full px-3 py-1 text-[11px] font-black"
          :class="composition?.est_validee ? 'bg-[#ecfdf5] text-[#16a34a]' : 'bg-[#fff7ed] text-[#f59e0b]'">
          {{ composition?.est_validee ? 'Validee' : 'Brouillon' }}
        </span>
      </div>
    </div>

    <div v-if="chargement" class="mt-4 grid gap-3 lg:grid-cols-3">
      <div v-for="n in 3" :key="n" class="rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-4">
        <div class="h-6 w-32 animate-pulse rounded-full bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
        <div class="mt-4 space-y-3">
          <div v-for="m in 3" :key="m"
            class="h-[78px] animate-pulse rounded-[20px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
        </div>
      </div>
    </div>

    <div v-else-if="composition" class="mt-4 grid gap-4 lg:grid-cols-3">
      <section v-for="groupe in groupes" :key="groupe.key"
        class="rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-4">
        <div class="flex items-center justify-between gap-2">
          <p class="text-sm font-black text-[#111827]">{{ groupe.label }}</p>
          <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-black text-[#64748b]">
            {{ composition?.[groupe.key]?.length || 0 }}
          </span>
        </div>

        <div v-if="composition?.[groupe.key]?.length" class="mt-4 space-y-3">
          <MatchCompositionPlayerCard v-for="joueur in composition[groupe.key]" :key="`${groupe.key}-${joueur.id}`"
            :joueur="joueur" :ton="groupe.ton" />
        </div>

        <div v-else
          class="mt-4 rounded-[20px] border border-dashed border-[#d7e3f5] bg-white px-3 py-8 text-center text-xs font-semibold text-[#6b7280]">
          Aucun joueur dans ce groupe.
        </div>
      </section>
    </div>

    <div v-if="composition?.notes" class="mt-4 rounded-[20px] border border-[#e6edf8] bg-[#f8fbff] p-4">
      <p class="text-[11px] font-black uppercase tracking-[0.16em] text-[#7c8aa5]">Notes du match</p>
      <p class="mt-2 text-sm font-semibold leading-6 text-[#64748b]">{{ composition.notes }}</p>
    </div>

    <div v-else-if="!chargement && !composition && lectureSeule"
      class="mt-4 rounded-[20px] border border-dashed border-[#d7e3f5] bg-[#f8fbff] px-3 py-8 text-center text-xs font-semibold text-[#6b7280]">
      Aucune composition n'a encore ete enregistree pour ce match.
    </div>
  </section>
</template>
