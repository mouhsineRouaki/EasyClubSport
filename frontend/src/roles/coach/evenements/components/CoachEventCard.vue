<script setup>
import AppButton from '@/shared/components/ui/AppButton.vue'

defineProps({
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
</script>

<template>
  <article class="rounded-[26px] border border-[#e5ecfb] bg-white p-5">
    <div class="flex items-start justify-between gap-3">
      <div>
        <p class="text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">{{ evenement.type }}</p>
        <h2 class="mt-2 text-xl font-black text-[#0f172a]">{{ evenement.titre }}</h2>
      </div>
      <span class="rounded-full bg-[#f8fbff] px-3 py-1 text-[11px] font-black capitalize text-[#2446d8]">{{ evenement.statut }}</span>
    </div>

    <p class="mt-3 text-sm font-semibold text-[#475569]">{{ formatDate(evenement.date_debut) }}</p>
    <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ evenement.lieu || evenement.adversaire_equipe?.nom || evenement.adversaire || '-' }}</p>
    <p class="mt-4 line-clamp-3 text-sm text-[#475569]">{{ evenement.description || 'Aucune description.' }}</p>

    <div class="mt-5 flex gap-2">
      <AppButton type="button" variant="secondary" size="sm" @click="emit('edit', evenement)">Modifier</AppButton>
      <AppButton type="button" variant="danger" size="sm" @click="emit('delete', evenement)">Supprimer</AppButton>
    </div>
  </article>
</template>
