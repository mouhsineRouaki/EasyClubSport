<script setup>
import blueBackground from '@/assets/Background.jpg'

const props = defineProps({
  equipe: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['show-players'])

const imageEquipe = (eq = {}) => eq?.logo_url || eq?.logo || blueBackground
const backgroundEquipe = (eq = {}) => `linear-gradient(145deg, rgba(8,18,72,0.86), rgba(36,70,216,0.64)), url(${imageEquipe(eq)})`
</script>

<template>
  <article
    class="group relative min-h-[255px] overflow-hidden rounded-[26px] border border-white/70 bg-cover bg-center p-4 text-white transition duration-300 hover:-translate-y-1 hover:border-white"
    :style="{ backgroundImage: backgroundEquipe(equipe) }"
  >
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_10%,rgba(255,255,255,0.32),transparent_26%),linear-gradient(180deg,rgba(6,14,48,0.12),rgba(6,14,48,0.72))]"></div>
    <div class="absolute inset-0 bg-[#2446d8]/0 transition duration-300 group-hover:bg-[#2446d8]/20"></div>

    <div class="relative z-10 flex min-h-[220px] flex-col justify-between">
      <div class="flex items-start justify-between gap-3">
        <span class="rounded-full border border-white/35 bg-white/20 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.16em] text-white backdrop-blur-md">
          {{ equipe.categorie || 'Equipe' }}
        </span>
        <span class="rounded-full bg-white px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.12em] text-[#1f36bf]">
          Coach
        </span>
      </div>

      <div>
        <h4 class="line-clamp-2 text-2xl font-black leading-tight text-white">
          {{ equipe.nom }}
        </h4>
        <p class="mt-2 line-clamp-2 text-xs font-semibold leading-5 text-white/78">
          {{ equipe.club?.nom || 'Club non defini' }}
        </p>
      </div>

      <div class="translate-y-3 opacity-0 transition duration-300 group-hover:translate-y-0 group-hover:opacity-100">
        <div class="grid grid-cols-3 gap-1.5 rounded-2xl border border-white/20 bg-white/16 p-2 text-center backdrop-blur-md">
          <div>
            <p class="text-lg font-black text-white">{{ equipe.joueurs_total || 0 }}</p>
            <p class="text-[9px] font-bold uppercase text-white/68">Joueurs</p>
          </div>
          <div>
            <p class="text-lg font-black text-white">{{ equipe.evenements_total || 0 }}</p>
            <p class="text-[9px] font-bold uppercase text-white/68">Events</p>
          </div>
          <div>
            <p class="text-lg font-black text-white">{{ equipe.statut === 'active' ? 1 : 0 }}</p>
            <p class="text-[9px] font-bold uppercase text-white/68">Active</p>
          </div>
        </div>
        <div class="mt-2 flex items-center justify-between gap-2 rounded-2xl border border-white/15 bg-white/12 px-3 py-2 text-[10px] font-semibold text-white/80 backdrop-blur-md">
          <span>{{ equipe.code_invitation || 'Code non defini' }}</span>
          <button
            type="button"
            class="rounded-full bg-white px-3 py-1.5 text-[10px] font-black text-[#1f36bf] hover:bg-[#eef4ff]"
            @click="emit('show-players', equipe.id)"
          >
            Voir joueurs
          </button>
        </div>
      </div>
    </div>
  </article>
</template>
