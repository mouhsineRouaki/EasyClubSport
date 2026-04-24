<script setup>
import AppCoverCard from '@/shared/components/ui/AppCoverCard.vue'
import { resolveCoverImage } from '@/shared/utils/coverImage'

const props = defineProps({
  document: {
    type: Object,
    required: true,
  },
  active: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['select'])
const imageDocument = () => resolveCoverImage(props.document?.club?.logo_url)

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
    :image="imageDocument()"
    :active="active"
    :badge="document.type_document || 'Document'"
    status-label="Fichier"
    min-height-class="min-h-[280px]"
    @click="emit('select', document)"
  >
    <template #body>
      <div>
        <h4 class="line-clamp-2 text-2xl font-black leading-tight text-white">
          {{ document.nom }}
        </h4>
        <p class="mt-3 truncate text-xs font-semibold text-white/78">
          {{ document.utilisateur?.email || 'Aucun email disponible' }}
        </p>
      </div>
    </template>
    <template #footer>
      <div class="grid grid-cols-2 gap-2">
        <div class="rounded-2xl border border-white/18 bg-white/14 p-2 backdrop-blur-md">
          <p class="truncate text-xs font-black text-white">{{ document.utilisateur?.nom || '-' }}</p>
          <p class="text-[9px] font-bold uppercase text-white/68">Utilisateur</p>
        </div>
        <div class="rounded-2xl border border-white/18 bg-white/14 p-2 backdrop-blur-md">
          <p class="truncate text-xs font-black text-white">{{ formatDate(document.date_ajout || document.created_at) }}</p>
          <p class="text-[9px] font-bold uppercase text-white/68">Ajout</p>
        </div>
      </div>
    </template>
  </AppCoverCard>
</template>
