<script setup>
import { computed } from 'vue'
import AppCoverCard from '@/shared/components/ui/AppCoverCard.vue'
import { resolveCoverImage } from '@/shared/utils/coverImage'

const props = defineProps({
  joueur: {
    type: Object,
    required: true,
  },
  active: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['select'])

const nomComplet = computed(() => {
  return [props.joueur?.prenom, props.joueur?.nom].filter(Boolean).join(' ') || props.joueur?.name || 'Joueur'
})

const imageJoueur = computed(() => {
  return resolveCoverImage(props.joueur?.photo_url, props.joueur?.photo)
})

const contactJoueur = computed(() => {
  return props.joueur?.email || props.joueur?.telephone || 'Contact non defini'
})

const statutJoueur = computed(() => {
  return props.joueur?.statut || 'actif'
})

const dateAffectation = computed(() => {
  return props.joueur?.date_affectation || '-'
})
</script>

<template>
  <AppCoverCard
    :image="imageJoueur"
    :active="active"
    :badge="joueur.role_equipe || 'Joueur'"
    status-label="Profil"
    min-height-class="min-h-[260px]"
    @click="emit('select', joueur)"
  >
    <template #body>
      <div>
        <h4 class="mt-2 line-clamp-2 text-2xl font-black leading-tight text-white">
          {{ nomComplet }}
        </h4>
        <p class="mt-2 line-clamp-1 text-xs font-semibold text-white/78">
          {{ contactJoueur }}
        </p>
      </div>
    </template>

    <template #footer>
      <div class="grid grid-cols-2 gap-2">
        <div class="rounded-2xl border border-white/18 bg-white/14 p-2 backdrop-blur-md">
          <p class="truncate text-xs font-black text-white">{{ statutJoueur }}</p>
          <p class="text-[9px] font-bold uppercase text-white/68">Statut</p>
        </div>

        <div class="rounded-2xl border border-white/18 bg-white/14 p-2 backdrop-blur-md">
          <p class="truncate text-xs font-black text-white">{{ dateAffectation }}</p>
          <p class="text-[9px] font-bold uppercase text-white/68">Affecte</p>
        </div>
      </div>
    </template>
  </AppCoverCard>
</template>
