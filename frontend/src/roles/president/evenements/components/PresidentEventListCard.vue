<script setup>
import AppButton from '@/shared/components/ui/AppButton.vue'

const props = defineProps({
  evenement: {
    type: Object,
    required: true,
  },
  formatDate: {
    type: Function,
    required: true,
  },
})

const emit = defineEmits(['edit', 'delete'])

const badgeInvitation = {
  en_attente: 'En attente',
  accepte: 'Accepte',
  refuse: 'Refuse',
}
</script>

<template>
  <article class="rounded-2xl border border-[#e8edf5] bg-white p-4 transition hover:-translate-y-[1px] hover:shadow-[0_10px_24px_rgba(15,23,42,0.08)]">
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-[#6b7280]">
          {{ evenement.type || 'evenement' }}
        </p>
        <h4 class="mt-2 line-clamp-2 text-xl font-black leading-tight tracking-normal text-[#111827]">
          {{ evenement.titre }}
        </h4>
      </div>
      <span class="rounded-full px-2.5 py-1 text-[9px] font-black capitalize" :class="evenement.statut === 'annule' ? 'bg-red-50 text-red-600' : evenement.statut === 'termine' ? 'bg-[#f1f5f9] text-[#475569]' : 'bg-[#ecfdf5] text-[#16a34a]'">
        {{ evenement.statut || 'planifie' }}
      </span>
    </div>

    <p v-if="evenement.type === 'match' && badgeInvitation[evenement.statut_invitation_adversaire]" class="mt-2 text-[10px] font-black uppercase tracking-[0.12em] text-[#f59e0b]">
      {{ badgeInvitation[evenement.statut_invitation_adversaire] }}
    </p>

    <div class="mt-4 rounded-[22px] bg-[#f5f7fb] p-3">
      <p class="text-xs font-black text-[#111827]">{{ formatDate(evenement.date_debut) }}</p>
      <p class="mt-2 line-clamp-1 text-xs font-semibold text-[#64748b]">
        {{ evenement.lieu || evenement.adversaire_equipe?.nom || evenement.adversaire || 'Lieu non defini' }}
      </p>
    </div>

    <p class="mt-4 line-clamp-3 text-sm text-[#475569]">{{ evenement.description || 'Aucune description.' }}</p>

    <div class="mt-5 flex gap-2">
      <AppButton type="button" variant="secondary" size="sm" @click="emit('edit', evenement)">Modifier</AppButton>
      <AppButton type="button" variant="danger" size="sm" @click="emit('delete', evenement)">Supprimer</AppButton>
    </div>
  </article>
</template>
