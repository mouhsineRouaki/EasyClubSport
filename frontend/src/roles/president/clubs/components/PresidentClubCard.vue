<script setup>
import { computed } from 'vue'

const props = defineProps({
  club: {
    type: Object,
    required: true,
  },
  fallbackImage: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['select'])

const imageClub = computed(() => props.club.logo_url || props.club.logo || props.fallbackImage)

const backgroundClub = computed(() => {
  return `linear-gradient(180deg, rgba(7, 16, 58, 0.08), rgba(7, 16, 58, 0.62)), url(${imageClub.value})`
})
</script>

<template>
  <button
    type="button"
    class="group relative min-h-[255px] overflow-hidden rounded-[26px] border border-white/70 bg-cover bg-center text-left text-white transition duration-300 hover:-translate-y-1 hover:border-white"
    :style="{ backgroundImage: backgroundClub }"
    @click="emit('select', club)"
  >
    <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(6,14,48,0.12),rgba(6,14,48,0.72))] transition duration-300 group-hover:bg-[linear-gradient(180deg,rgba(6,14,48,0.34),rgba(6,14,48,0.88))]"></div>
    <div class="absolute inset-0 bg-[#2446d8]/0 transition duration-300 group-hover:bg-[#2446d8]/20"></div>

    <div class="relative z-10 flex min-h-[255px] flex-col justify-between p-4">
      <div class="flex items-center justify-between gap-3">
        <span class="rounded-full border border-white/35 bg-white/20 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.16em] text-white backdrop-blur-md">
          {{ club.ville || 'Club' }}
        </span>
        <span class="rounded-full bg-white px-2.5 py-1 text-[9px] font-black text-[#1f36bf]">
          Club
        </span>
      </div>

      <div>
        <h4 class="line-clamp-2 text-2xl font-black leading-tight tracking-normal text-white">
          {{ club.nom }}
        </h4>
        <p class="mt-2 line-clamp-2 text-xs font-semibold leading-5 text-white/78 transition group-hover:text-white">
          {{ club.description || club.adresse || 'Aucune description disponible.' }}
        </p>
      </div>

      <div class="translate-y-3 opacity-0 transition duration-300 group-hover:translate-y-0 group-hover:opacity-100">
        <div class="grid grid-cols-3 gap-1.5 rounded-2xl border border-white/20 bg-white/16 p-2 text-center backdrop-blur-md">
          <div>
            <p class="text-lg font-black text-white">{{ club.equipes_total || 0 }}</p>
            <p class="text-[9px] font-bold uppercase text-white/68">Equipes</p>
          </div>
          <div>
            <p class="text-lg font-black text-white">{{ club.joueurs_total || 0 }}</p>
            <p class="text-[9px] font-bold uppercase text-white/68">Joueurs</p>
          </div>
          <div>
            <p class="text-lg font-black text-white">{{ club.coachs_total || 0 }}</p>
            <p class="text-[9px] font-bold uppercase text-white/68">Coachs</p>
          </div>
        </div>
        <div class="mt-2 rounded-2xl border border-white/15 bg-white/12 px-3 py-2 text-[10px] font-semibold leading-4 text-white/80 backdrop-blur-md">
          <p>Email : {{ club.email || '-' }}</p>
          <p>Telephone : {{ club.telephone || '-' }}</p>
        </div>
      </div>
    </div>
  </button>
</template>
