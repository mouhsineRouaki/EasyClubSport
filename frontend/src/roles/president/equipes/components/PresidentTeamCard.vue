<script setup>
import { computed } from 'vue'
import AppCoverCard from '@/shared/components/ui/AppCoverCard.vue'
import { resolveCoverImage } from '@/shared/utils/coverImage'

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

const imageEquipe = computed(() =>
  resolveCoverImage(
    props.equipe.image_url,
    props.equipe.logo_url,
    props.equipe.photo_url,
    props.equipe.club?.logo_url,
    props.fallbackImage,
  )
)
</script>

<template>
  <AppCoverCard
    :image="imageEquipe"
    :fallback-image="fallbackImage"
    :active="active"
    :badge="equipe.categorie || 'Equipe'"
    :status-label="active ? 'Active' : 'Voir'"
    @click="emit('select', equipe)"
  >
    <template #body>
      <div>
        <h5 class="line-clamp-2 text-2xl font-black leading-tight text-white">
          {{ equipe.nom }}
        </h5>
        <p class="mt-2 line-clamp-1 text-xs font-semibold text-white/78">
          Coach : {{ equipe.coach?.nom || 'Non defini' }}
        </p>
      </div>
    </template>
    <template #footer>
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
    </template>
  </AppCoverCard>
</template>
