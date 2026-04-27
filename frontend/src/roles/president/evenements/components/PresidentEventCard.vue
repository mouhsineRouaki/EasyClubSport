<script setup>
import { computed } from 'vue'
import AppCoverCard from '@/shared/components/ui/AppCoverCard.vue'
import { resolveCoverImage } from '@/shared/utils/coverImage'

const props = defineProps({
  evenement: {
    type: Object,
    required: true,
  },
  active: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['select'])

const formatDate = (date) => {
  if (!date) {
    return '-'
  }

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(date))
}

const logoEquipe = (equipe) => equipe?.logo_url || equipe?.logo || equipe?.club?.logo_url || ''
const imageEvenement = computed(() =>
  resolveCoverImage(
    props.evenement?.image_url,
    props.evenement?.photo_url,
    props.evenement?.image,
    logoEquipe(props.evenement?.equipe),
    logoEquipe(props.evenement?.adversaire_equipe),
  )
)
const libelleInvitation = (statut) => ({
  en_attente: 'En attente',
  accepte: 'Accepte',
  refuse: 'Refuse',
}[statut] || '')
</script>

<template>
  <AppCoverCard
    :image="imageEvenement"
    :active="active"
    :badge="evenement.type || 'Evenement'"
    :status-label="evenement.statut || 'planifie'"
    :status-class="evenement.statut === 'annule' ? 'bg-red-50 text-red-600' : evenement.statut === 'termine' ? 'bg-[#f1f5f9] text-[#475569]' : 'bg-[#ecfdf5] text-[#16a34a]'"
    min-height-class="min-h-[260px]"
    @click="emit('select', evenement)"
  >
    <template #body>
      <div>
        <p v-if="evenement.type === 'match' && libelleInvitation(evenement.statut_invitation_adversaire)" class="text-[10px] font-black uppercase tracking-[0.12em] text-[#fde68a]">
          {{ libelleInvitation(evenement.statut_invitation_adversaire) }}
        </p>
        <h4 class="mt-2 line-clamp-2 text-2xl font-black leading-tight text-white">
          {{ evenement.titre }}
        </h4>
        <p class="mt-2 text-xs font-semibold text-white/78">{{ formatDate(evenement.date_debut) }}</p>
        <p class="mt-1 line-clamp-1 text-xs font-semibold text-white/68">{{ evenement.lieu || 'Lieu non defini' }}</p>
      </div>
    </template>
    <template #footer>
      <div v-if="evenement.type === 'match'" class="grid grid-cols-[1fr_auto_1fr] items-center gap-2 rounded-2xl border border-white/18 bg-white/14 p-2 backdrop-blur-md">
        <div class="min-w-0 text-center">
          <p class="truncate text-xs font-black text-white">{{ evenement.equipe?.nom || 'Equipe' }}</p>
        </div>
        <span class="rounded-full bg-white px-2.5 py-1 text-[9px] font-black text-[#111827]">VS</span>
        <div class="min-w-0 text-center">
          <p class="truncate text-xs font-black text-white">{{ evenement.adversaire_equipe?.nom || evenement.adversaire || 'Adversaire' }}</p>
        </div>
      </div>
      <div v-else class="rounded-2xl border border-white/18 bg-white/14 p-3 backdrop-blur-md">
        <p class="text-sm font-black text-white">{{ evenement.type === 'entrainement' ? 'Entrainement' : 'Reunion' }}</p>
        <p class="mt-1 text-xs font-semibold text-white/75">{{ evenement.equipe?.nom || 'Equipe' }}</p>
      </div>
    </template>
  </AppCoverCard>
</template>
