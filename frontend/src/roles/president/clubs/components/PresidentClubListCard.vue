<script setup>
import AppButton from '@/shared/components/ui/AppButton.vue'

defineProps({
  club: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['edit', 'delete'])
</script>

<template>
  <article class="rounded-2xl border border-[#e8edf5] bg-white p-4 transition hover:-translate-y-[1px] hover:shadow-[0_10px_24px_rgba(15,23,42,0.08)]">
    <div class="flex items-start gap-3">
      <span class="grid h-14 w-14 shrink-0 place-items-center overflow-hidden rounded-xl bg-[linear-gradient(130deg,#0f172a,#334155)] text-xs font-bold text-white">
        <img v-if="club.logo_url" :src="club.logo_url" :alt="club.nom" class="h-full w-full object-cover" />
        <span v-else>CL</span>
      </span>
      <div class="min-w-0">
        <p class="truncate text-base font-bold text-[#1f2a44]">{{ club.nom }}</p>
        <p class="mt-1 text-xs text-[#64748b]">
          {{ club.ville || '-' }}<span v-if="club.pays">, {{ club.pays }}</span>
        </p>
        <p class="mt-1 truncate text-xs text-[#475569]">{{ club.email || 'email non renseigne' }}</p>
      </div>
    </div>

    <p class="mt-3 line-clamp-2 text-xs leading-5 text-[#64748b]">{{ club.description || 'Aucune description.' }}</p>

    <div class="mt-4 flex items-center justify-between">
      <span class="text-xs text-[#64748b]">{{ club.telephone || '-' }}</span>
      <div class="flex gap-2">
        <AppButton type="button" variant="secondary" size="sm" @click="emit('edit', club)">Modifier</AppButton>
        <AppButton type="button" variant="danger" size="sm" @click="emit('delete', club)">Supprimer</AppButton>
      </div>
    </div>
  </article>
</template>
