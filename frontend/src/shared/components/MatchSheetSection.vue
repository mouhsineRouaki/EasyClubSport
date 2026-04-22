<script setup>
const props = defineProps({
  feuilleMatch: {
    type: Object,
    default: null,
  },
  chargement: {
    type: Boolean,
    default: false,
  },
  titre: {
    type: String,
    default: 'Feuille de match',
  },
  description: {
    type: String,
    default: 'Resume global de la rencontre.',
  },
})

const scoreAffiche = (valeur) => (valeur === null || valeur === undefined || valeur === '' ? '-' : valeur)
</script>

<template>
  <section class="mt-5 rounded-[22px] bg-white p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="text-sm font-black text-[#111827]">{{ titre }}</p>
        <p class="mt-1 text-xs font-semibold text-[#64748b]">{{ description }}</p>
      </div>

      <span class="rounded-full px-3 py-1 text-[11px] font-black"
        :class="feuilleMatch?.est_validee ? 'bg-[#ecfdf5] text-[#16a34a]' : 'bg-[#eef2ff] text-[#2446d8]'">
        {{ feuilleMatch?.est_validee ? 'Feuille validee' : 'Feuille en cours' }}
      </span>
    </div>

    <div v-if="chargement" class="mt-4 grid gap-4 lg:grid-cols-[260px_1fr]">
      <div class="h-[154px] animate-pulse rounded-[24px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
      <div class="h-[154px] animate-pulse rounded-[24px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
    </div>

    <div v-else-if="feuilleMatch" class="mt-4 grid gap-4 lg:grid-cols-[260px_1fr]">
      <section class="rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-4">
        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-[#7c8aa5]">Score final</p>
        <div class="mt-4 grid grid-cols-[1fr_auto_1fr] items-center gap-3 text-center">
          <div class="rounded-[20px] bg-white px-3 py-4">
            <p class="text-[11px] font-black uppercase tracking-[0.12em] text-[#7c8aa5]">Equipe</p>
            <p class="mt-2 text-4xl font-black text-[#111827]">{{ scoreAffiche(feuilleMatch.score_equipe) }}</p>
          </div>
          <span class="rounded-full bg-[#111827] px-3 py-1 text-[10px] font-black text-white">VS</span>
          <div class="rounded-[20px] bg-white px-3 py-4">
            <p class="text-[11px] font-black uppercase tracking-[0.12em] text-[#7c8aa5]">Adversaire</p>
            <p class="mt-2 text-4xl font-black text-[#111827]">{{ scoreAffiche(feuilleMatch.score_adversaire) }}</p>
          </div>
        </div>
      </section>

      <section class="rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-4">
        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-[#7c8aa5]">Resume du match</p>
        <p class="mt-3 text-sm font-semibold leading-6 text-[#64748b]">
          {{ feuilleMatch.resume_match || 'Aucun resume du match n a encore ete enregistre.' }}
        </p>
      </section>
    </div>

    <div v-else
      class="mt-4 rounded-[20px] border border-dashed border-[#d7e3f5] bg-[#f8fbff] px-3 py-8 text-center text-xs font-semibold text-[#6b7280]">
      Aucune feuille de match n'a encore ete enregistree pour cette rencontre.
    </div>
  </section>
</template>
