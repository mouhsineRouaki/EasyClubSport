<script setup>
import AppButton from '@/shared/components/ui/AppButton.vue'
import AppCoverCard from '@/shared/components/ui/AppCoverCard.vue'
import { resolveCoverImage } from '@/shared/utils/coverImage'

const props = defineProps({
  convocation: {
    type: Object,
    required: true,
  },
  equipe: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['open', 'reply'])

const statutConvocation = (statut) =>
  ({
    convoque: { label: 'Convoque', classe: 'bg-[#eef2ff] text-[#1f36bf]' },
    confirme: { label: 'Confirme', classe: 'bg-[#ecfdf5] text-[#16a34a]' },
    refuse: { label: 'Refuse', classe: 'bg-[#fef2f2] text-[#ef4444]' },
    en_attente: { label: 'En attente', classe: 'bg-[#fff7ed] text-[#f59e0b]' },
  }[statut] || { label: statut || 'Statut', classe: 'bg-[#f8fbff] text-[#64748b]' })

const formatDate = (date) => {
  if (!date) return '-'
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'long' }).format(new Date(date))
}

const imageConvocation = (convocation) =>
  resolveCoverImage(
    convocation?.evenement?.image_url,
    convocation?.equipe?.logo_url,
    convocation?.evenement?.adversaire_equipe?.logo_url,
    convocation?.club?.logo_url,
  )

const nomEquipeDomicile = (convocation) => convocation?.equipe?.nom || props.equipe?.nom || 'Mon equipe'

const nomEquipeAdverse = (convocation) =>
  convocation?.evenement?.adversaire_equipe?.nom
  || convocation?.evenement?.adversaire
  || 'Adversaire'

const peutRepondre = (statut) => ['convoque', 'en_attente'].includes(statut)
</script>

<template>
  <article class="group relative overflow-hidden rounded-[26px] border border-[#edf1f7] bg-white p-4 transition duration-300 hover:-translate-y-1 hover:border-[#d7e0f5] hover:bg-[#fbfcff]">
    <AppCoverCard
      :image="imageConvocation(convocation)"
      :badge="convocation.evenement?.type || 'Evenement'"
      :status-label="statutConvocation(convocation.statut).label"
      :status-class="statutConvocation(convocation.statut).classe"
      min-height-class="min-h-[190px]"
      @click="emit('open', convocation)"
    >
      <template #body>
        <div>
          <p class="text-[11px] font-bold text-white/70">{{ convocation.club?.nom || 'EasyClubSport' }}</p>
          <h4 class="mt-2 text-2xl font-black leading-tight text-white">
            {{ nomEquipeDomicile(convocation) }}
            <span class="px-2 text-white/75">vs</span>
            {{ nomEquipeAdverse(convocation) }}
          </h4>
          <p class="mt-2 text-sm font-semibold text-white/80">{{ convocation.evenement?.titre || 'Evenement du club' }}</p>
          <p class="mt-1 text-xs text-white/65">
            {{ formatDate(convocation.evenement?.date_debut) }}
            <span v-if="convocation.evenement?.lieu"> · {{ convocation.evenement.lieu }}</span>
          </p>
        </div>
      </template>
    </AppCoverCard>

    <div class="mt-4 rounded-[22px] bg-[#f5f7fb] p-3">
      <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2">
        <div class="min-w-0 text-center">
          <p class="truncate text-xs font-black text-[#111827]">{{ nomEquipeDomicile(convocation) }}</p>
        </div>
        <span class="rounded-full bg-[#111827] px-2.5 py-1 text-[9px] font-black text-white">VS</span>
        <div class="min-w-0 text-center">
          <p class="truncate text-xs font-black text-[#111827]">{{ nomEquipeAdverse(convocation) }}</p>
        </div>
      </div>
    </div>

    <div class="mt-4 flex items-center gap-3">
      <AppButton
        v-if="peutRepondre(convocation.statut)"
        type="button"
        class="flex-1"
        @click="emit('reply', { convocation, reponse: 'confirme' })"
      >
        Confirmer
      </AppButton>
      <AppButton
        v-if="peutRepondre(convocation.statut)"
        type="button"
        variant="secondary"
        class="flex-1"
        @click="emit('reply', { convocation, reponse: 'refuse' })"
      >
        Refuser
      </AppButton>
      <AppButton
        v-else
        type="button"
        variant="secondary"
        block
        @click="emit('open', convocation)"
      >
        Voir les details
      </AppButton>
    </div>
  </article>
</template>
