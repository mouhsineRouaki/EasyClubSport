<script setup>
import { computed } from 'vue'
import AppCoverCard from '@/shared/components/ui/AppCoverCard.vue'
import { resolveCoverImage } from '@/shared/utils/coverImage'

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

const imageClub = computed(() =>
  resolveCoverImage(props.club.logo_url, props.club.logo, props.fallbackImage)
)
</script>

<template>
  <AppCoverCard
    :image="imageClub"
    :fallback-image="fallbackImage"
    :badge="club.ville || 'Club'"
    status-label="Club"
    min-height-class="min-h-[255px]"
    @click="emit('select', club)"
  >
    <template #body>
      <div>
        <h4 class="line-clamp-2 text-2xl font-black leading-tight tracking-normal text-white">
          {{ club.nom }}
        </h4>
        <p class="mt-2 line-clamp-2 text-xs font-semibold leading-5 text-white/78 transition group-hover:text-white">
          {{ club.description || club.adresse || 'Aucune description disponible.' }}
        </p>
      </div>
    </template>
    <template #footer>
      <div>
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
    </template>
  </AppCoverCard>
</template>
