<script setup>
import { computed } from 'vue'
import AppCoverCard from '@/shared/components/ui/AppCoverCard.vue'
import { resolveCoverImage } from '@/shared/utils/coverImage'

const props = defineProps({
  annonce: {
    type: Object,
    required: true,
  },
  active: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['select'])

const extrait = computed(() => {
  const contenu = props.annonce?.contenu || ''
  return contenu.length > 120 ? `${contenu.slice(0, 117)}...` : contenu
})
const imageAnnonce = computed(() =>
  resolveCoverImage(props.annonce?.image_url, props.annonce?.club?.logo_url)
)

const formatDate = (date) => {
  if (!date) {
    return '-'
  }

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
  }).format(new Date(date))
}
</script>

<template>
  <AppCoverCard
    :image="imageAnnonce"
    :active="active"
    :badge="annonce.club?.nom || 'Club'"
    :status-label="annonce.est_active ? 'Active' : 'Archivee'"
    :status-class="annonce.est_active ? 'bg-[#ecfdf5] text-[#16a34a]' : 'bg-[#f8fafc] text-[#64748b]'"
    min-height-class="min-h-[320px]"
    @click="emit('select', annonce)"
  >
    <template #body>
      <div>
        <h4 class="line-clamp-2 text-2xl font-black leading-tight text-white">
          {{ annonce.titre }}
        </h4>
        <p class="mt-3 line-clamp-4 min-h-[96px] text-sm font-semibold leading-6 text-white/80">
          {{ extrait || 'Aucun contenu disponible pour cette annonce.' }}
        </p>
      </div>
    </template>
    <template #footer>
      <div class="grid grid-cols-2 gap-2">
        <div class="rounded-2xl border border-white/18 bg-white/14 p-2 backdrop-blur-md">
          <p class="truncate text-xs font-black text-white">{{ annonce.auteur?.nom || 'President' }}</p>
          <p class="text-[9px] font-bold uppercase text-white/68">Auteur</p>
        </div>
        <div class="rounded-2xl border border-white/18 bg-white/14 p-2 backdrop-blur-md">
          <p class="truncate text-xs font-black text-white">{{ formatDate(annonce.created_at) }}</p>
          <p class="text-[9px] font-bold uppercase text-white/68">Publication</p>
        </div>
      </div>
    </template>
  </AppCoverCard>
</template>
