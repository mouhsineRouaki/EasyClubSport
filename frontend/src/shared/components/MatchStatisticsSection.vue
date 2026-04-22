<script setup>
const props = defineProps({
  statistiques: {
    type: Object,
    default: () => ({ resume: {}, joueurs: [] }),
  },
  chargement: {
    type: Boolean,
    default: false,
  },
  titre: {
    type: String,
    default: 'Statistiques du match',
  },
  description: {
    type: String,
    default: 'Vue globale des performances du match.',
  },
})

const resumeItems = [
  { key: 'joueurs_total', label: 'Joueurs' },
  { key: 'buts_total', label: 'Buts' },
  { key: 'passes_decisives_total', label: 'Passes' },
  { key: 'cartons_jaunes_total', label: 'Jaunes' },
  { key: 'cartons_rouges_total', label: 'Rouges' },
  { key: 'minutes_jouees_total', label: 'Minutes' },
]

const nomJoueur = (joueur) =>
  [joueur?.prenom, joueur?.nom].filter(Boolean).join(' ') || joueur?.name || 'Joueur'
</script>

<template>
  <section class="mt-5 rounded-[22px] bg-white p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="text-sm font-black text-[#111827]">{{ titre }}</p>
        <p class="mt-1 text-xs font-semibold text-[#64748b]">{{ description }}</p>
      </div>
    </div>

    <div v-if="chargement" class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
      <div v-for="n in 6" :key="n"
        class="h-[88px] animate-pulse rounded-[20px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
    </div>

    <template v-else>
      <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
        <article v-for="item in resumeItems" :key="item.key"
          class="rounded-[20px] border border-[#e6edf8] bg-[#f8fbff] px-4 py-3 text-center">
          <p class="text-[11px] font-black uppercase tracking-[0.16em] text-[#7c8aa5]">{{ item.label }}</p>
          <p class="mt-2 text-2xl font-black text-[#111827]">{{ statistiques?.resume?.[item.key] || 0 }}</p>
        </article>
      </div>

      <div v-if="statistiques?.joueurs?.length" class="mt-4 grid gap-3 lg:grid-cols-2">
        <article v-for="ligne in statistiques.joueurs" :key="ligne.utilisateur_id"
          class="rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-4">
          <div class="flex items-center gap-3">
            <span
              class="grid h-12 w-12 place-items-center overflow-hidden rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff,#dbe7ff_28%,#2446d8_72%)] text-sm font-black text-white">
              <img v-if="ligne.joueur?.photo_url" :src="ligne.joueur.photo_url" :alt="nomJoueur(ligne.joueur)"
                class="h-full w-full object-cover" />
              <span v-else>{{ nomJoueur(ligne.joueur).slice(0, 1) }}</span>
            </span>
            <div class="min-w-0">
              <p class="truncate text-sm font-black text-[#111827]">{{ nomJoueur(ligne.joueur) }}</p>
              <p class="truncate text-[11px] font-semibold text-[#64748b]">{{ ligne.joueur?.email || 'Email non defini'
                }}</p>
            </div>
          </div>

          <div class="mt-4 grid grid-cols-3 gap-2">
            <div class="rounded-[16px] bg-white px-3 py-2 text-center">
              <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#7c8aa5]">Buts</p>
              <p class="mt-1 text-lg font-black text-[#111827]">{{ ligne.buts || 0 }}</p>
            </div>
            <div class="rounded-[16px] bg-white px-3 py-2 text-center">
              <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#7c8aa5]">Passes</p>
              <p class="mt-1 text-lg font-black text-[#111827]">{{ ligne.passes_decisives || 0 }}</p>
            </div>
            <div class="rounded-[16px] bg-white px-3 py-2 text-center">
              <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#7c8aa5]">Minutes</p>
              <p class="mt-1 text-lg font-black text-[#111827]">{{ ligne.minutes_jouees || 0 }}</p>
            </div>
            <div class="rounded-[16px] bg-white px-3 py-2 text-center">
              <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#7c8aa5]">Jaunes</p>
              <p class="mt-1 text-lg font-black text-[#f59e0b]">{{ ligne.cartons_jaunes || 0 }}</p>
            </div>
            <div class="rounded-[16px] bg-white px-3 py-2 text-center">
              <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#7c8aa5]">Rouges</p>
              <p class="mt-1 text-lg font-black text-[#ef4444]">{{ ligne.cartons_rouges || 0 }}</p>
            </div>
          </div>
        </article>
      </div>

      <div v-else
        class="mt-4 rounded-[20px] border border-dashed border-[#d7e3f5] bg-[#f8fbff] px-3 py-8 text-center text-xs font-semibold text-[#6b7280]">
        Aucune statistique n'a encore ete enregistree pour ce match.
      </div>
    </template>
  </section>
</template>
