<script setup>
import { computed } from 'vue'

const props = defineProps({
  equipe: {
    type: Object,
    required: true,
  },
  active: {
    type: Boolean,
    default: false,
  },
  fallbackImage: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['select'])

const imageEquipe = computed(() => {
  return props.equipe.image_url || props.equipe.logo_url || props.equipe.photo_url || props.equipe.club?.logo_url || props.fallbackImage
})

const backgroundEquipe = computed(() => {
  return `linear-gradient(180deg, rgba(7, 16, 58, 0.12), rgba(7, 16, 58, 0.78)), url(${imageEquipe.value})`
})
</script>

<template>
  <button
    type="button"
    class="group relative min-h-[220px] overflow-hidden rounded-[24px] border bg-cover bg-center text-left text-white transition duration-300 hover:-translate-y-1"
    :class="active ? 'border-white ring-4 ring-[#2446d8]/15' : 'border-white/70 hover:border-white'"
    :style="{ backgroundImage: backgroundEquipe }"
    @click="emit('select', equipe)"
  >
    <div class="absolute inset-0 bg-[#2446d8]/0 transition duration-300 group-hover:bg-[#2446d8]/20"></div>

    <div class="relative z-10 flex min-h-[220px] flex-col justify-between p-4">
      <div class="flex items-center justify-between gap-3">
        <span class="rounded-full border border-white/30 bg-white/16 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.16em] text-white backdrop-blur-md">
          {{ equipe.categorie || 'Equipe' }}
        </span>
        <span class="rounded-full bg-white px-2.5 py-1 text-[9px] font-black text-[#1f36bf]">
          {{ active ? 'Active' : 'Voir' }}
        </span>
      </div>

      <div>
        <h5 class="line-clamp-2 text-2xl font-black leading-tight text-white">
          {{ equipe.nom }}
        </h5>
        <p class="mt-2 line-clamp-1 text-xs font-semibold text-white/78">
          Coach : {{ equipe.coach?.nom || 'Non defini' }}
        </p>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <div class="rounded-2xl border border-white/18 bg-white/14 p-2 backdrop-blur-md">
          <p class="text-lg font-black text-white">{{ equipe.joueurs_total || equipe.joueurs?.length || 0 }}</p>
          <p class="text-[9px] font-bold uppercase text-white/68">Joueurs</p>
        </div>
        <div class="rounded-2xl border border-white/18 bg-white/14 p-2 backdrop-blur-md">
          <p class="text-lg font-black capitalize text-white">{{ equipe.statut || 'active' }}</p>
          <p class="text-[9px] font-bold uppercase text-white/68">Statut</p>
        </div>
      </div>
    </div>
  </button>
</template>
