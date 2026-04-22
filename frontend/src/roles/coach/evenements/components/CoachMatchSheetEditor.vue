<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  feuilleMatch: {
    type: Object,
    default: null,
  },
  chargement: {
    type: Boolean,
    default: false,
  },
  enregistrement: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['enregistrer'])

const formulaire = reactive({
  score_equipe: '',
  score_adversaire: '',
  resume_match: '',
})

watch(
  () => props.feuilleMatch,
  (feuilleMatch) => {
    formulaire.score_equipe = feuilleMatch?.score_equipe ?? ''
    formulaire.score_adversaire = feuilleMatch?.score_adversaire ?? ''
    formulaire.resume_match = feuilleMatch?.resume_match || ''
  },
  { immediate: true, deep: true }
)

const normaliserScore = (valeur) => {
  if (valeur === '' || valeur === null || valeur === undefined) {
    return null
  }

  const nombre = Number(valeur)
  return Number.isNaN(nombre) ? null : nombre
}

const enregistrer = () => {
  emit('enregistrer', {
    score_equipe: normaliserScore(formulaire.score_equipe),
    score_adversaire: normaliserScore(formulaire.score_adversaire),
    resume_match: formulaire.resume_match?.trim() || null,
  })
}
</script>

<template>
  <section class="mt-5 rounded-[22px] bg-white p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="text-sm font-black text-[#111827]">Feuille de match</p>
        <p class="mt-1 text-xs font-semibold text-[#64748b]">
          Enregistrez le score et le resume officiel de la rencontre.
        </p>
      </div>

      <button
        type="button"
        class="rounded-full bg-[#111827] px-5 py-2.5 text-xs font-black text-white transition hover:bg-[#2446d8] disabled:cursor-not-allowed disabled:opacity-60"
        :disabled="chargement || enregistrement"
        @click="enregistrer"
      >
        {{ enregistrement ? 'Enregistrement...' : 'Enregistrer la feuille' }}
      </button>
    </div>

    <div v-if="chargement" class="mt-4 grid gap-4 lg:grid-cols-[260px_1fr]">
      <div class="h-[154px] animate-pulse rounded-[24px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
      <div class="h-[154px] animate-pulse rounded-[24px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
    </div>

    <div v-else class="mt-4 grid gap-4 lg:grid-cols-[260px_1fr]">
      <section class="rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-4">
        <p class="text-[11px] font-black uppercase tracking-[0.16em] text-[#7c8aa5]">Score du match</p>
        <div class="mt-4 grid grid-cols-[1fr_auto_1fr] items-end gap-3">
          <label class="block">
            <span class="text-[11px] font-black uppercase tracking-[0.12em] text-[#7c8aa5]">Equipe</span>
            <input
              v-model="formulaire.score_equipe"
              type="number"
              min="0"
              class="mt-2 h-12 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-center text-xl font-black text-[#111827] outline-none focus:border-[#4c6fff]"
            />
          </label>
          <span class="rounded-full bg-[#111827] px-3 py-1 text-[10px] font-black text-white">VS</span>
          <label class="block">
            <span class="text-[11px] font-black uppercase tracking-[0.12em] text-[#7c8aa5]">Adversaire</span>
            <input
              v-model="formulaire.score_adversaire"
              type="number"
              min="0"
              class="mt-2 h-12 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-center text-xl font-black text-[#111827] outline-none focus:border-[#4c6fff]"
            />
          </label>
        </div>
      </section>

      <section class="rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-4">
        <label class="text-[11px] font-black uppercase tracking-[0.16em] text-[#7c8aa5]">Resume du match</label>
        <textarea
          v-model="formulaire.resume_match"
          rows="5"
          class="mt-3 w-full resize-none rounded-2xl border border-[#dbe2ef] bg-white px-4 py-3 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]"
          placeholder="Resume du scenario, temps forts, remarques utiles..."
        ></textarea>
      </section>
    </div>
  </section>
</template>
