<script setup>
import { computed, reactive, watch } from 'vue'

const props = defineProps({
  statistiques: {
    type: Object,
    default: () => ({ resume: {}, joueurs: [] }),
  },
  composition: {
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
  joueurs: [],
})

const joueursComposition = computed(() => [
  ...(props.composition?.titulaires || []),
  ...(props.composition?.remplacants || []),
])

watch(
  [() => props.statistiques, joueursComposition],
  ([statistiques, joueurs]) => {
    const mapStats = new Map((statistiques?.joueurs || []).map((ligne) => [String(ligne.utilisateur_id), ligne]))
    formulaire.joueurs = joueurs.map((joueur) => {
      const stats = mapStats.get(String(joueur.id))
      return {
        utilisateur_id: joueur.id,
        joueur,
        buts: stats?.buts ?? 0,
        passes_decisives: stats?.passes_decisives ?? 0,
        cartons_jaunes: stats?.cartons_jaunes ?? 0,
        cartons_rouges: stats?.cartons_rouges ?? 0,
        minutes_jouees: stats?.minutes_jouees ?? 0,
      }
    })
  },
  { immediate: true, deep: true }
)

const enregistrer = () => {
  emit('enregistrer', {
    joueurs: formulaire.joueurs.map((ligne) => ({
      utilisateur_id: ligne.utilisateur_id,
      buts: Number(ligne.buts || 0),
      passes_decisives: Number(ligne.passes_decisives || 0),
      cartons_jaunes: Number(ligne.cartons_jaunes || 0),
      cartons_rouges: Number(ligne.cartons_rouges || 0),
      minutes_jouees: Number(ligne.minutes_jouees || 0),
    })),
  })
}

const nomJoueur = (joueur) =>
  [joueur?.prenom, joueur?.nom].filter(Boolean).join(' ') || joueur?.name || 'Joueur'
</script>

<template>
  <section class="mt-5 rounded-[22px] bg-white p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="text-sm font-black text-[#111827]">Statistiques du match</p>
        <p class="mt-1 text-xs font-semibold text-[#64748b]">
          Renseignez les statistiques individuelles des joueurs retenus dans la composition.
        </p>
      </div>

      <button
        type="button"
        class="rounded-full bg-[#111827] px-5 py-2.5 text-xs font-black text-white transition hover:bg-[#2446d8] disabled:cursor-not-allowed disabled:opacity-60"
        :disabled="chargement || enregistrement"
        @click="enregistrer"
      >
        {{ enregistrement ? 'Enregistrement...' : 'Enregistrer les statistiques' }}
      </button>
    </div>

    <div v-if="chargement" class="mt-4 grid gap-3">
      <div v-for="n in 4" :key="n" class="h-[168px] animate-pulse rounded-[24px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
    </div>

    <div v-else-if="formulaire.joueurs.length" class="mt-4 grid gap-3">
      <article
        v-for="ligne in formulaire.joueurs"
        :key="ligne.utilisateur_id"
        class="rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-4"
      >
        <div class="flex items-center gap-3">
          <span class="grid h-12 w-12 place-items-center overflow-hidden rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff,#dbe7ff_28%,#2446d8_72%)] text-sm font-black text-white">
            <img v-if="ligne.joueur?.photo_url" :src="ligne.joueur.photo_url" :alt="nomJoueur(ligne.joueur)" class="h-full w-full object-cover" />
            <span v-else>{{ nomJoueur(ligne.joueur).slice(0, 1) }}</span>
          </span>
          <div class="min-w-0">
            <p class="truncate text-sm font-black text-[#111827]">{{ nomJoueur(ligne.joueur) }}</p>
            <p class="truncate text-[11px] font-semibold text-[#64748b]">{{ ligne.joueur?.position_joueur || 'Position non definie' }}</p>
          </div>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
          <label class="block">
            <span class="text-[10px] font-black uppercase tracking-[0.12em] text-[#7c8aa5]">Buts</span>
            <input v-model="ligne.buts" type="number" min="0" class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-black text-[#111827] outline-none focus:border-[#4c6fff]" />
          </label>
          <label class="block">
            <span class="text-[10px] font-black uppercase tracking-[0.12em] text-[#7c8aa5]">Passes</span>
            <input v-model="ligne.passes_decisives" type="number" min="0" class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-black text-[#111827] outline-none focus:border-[#4c6fff]" />
          </label>
          <label class="block">
            <span class="text-[10px] font-black uppercase tracking-[0.12em] text-[#7c8aa5]">Jaunes</span>
            <input v-model="ligne.cartons_jaunes" type="number" min="0" class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-black text-[#111827] outline-none focus:border-[#4c6fff]" />
          </label>
          <label class="block">
            <span class="text-[10px] font-black uppercase tracking-[0.12em] text-[#7c8aa5]">Rouges</span>
            <input v-model="ligne.cartons_rouges" type="number" min="0" class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-black text-[#111827] outline-none focus:border-[#4c6fff]" />
          </label>
          <label class="block">
            <span class="text-[10px] font-black uppercase tracking-[0.12em] text-[#7c8aa5]">Minutes</span>
            <input v-model="ligne.minutes_jouees" type="number" min="0" class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-black text-[#111827] outline-none focus:border-[#4c6fff]" />
          </label>
        </div>
      </article>
    </div>

    <div
      v-else
      class="mt-4 rounded-[20px] border border-dashed border-[#d7e3f5] bg-[#f8fbff] px-3 py-8 text-center text-xs font-semibold text-[#6b7280]"
    >
      Ajoutez d'abord la composition du match pour enregistrer les statistiques.
    </div>
  </section>
</template>
