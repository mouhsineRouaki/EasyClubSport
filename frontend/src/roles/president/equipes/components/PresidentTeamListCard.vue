<script setup>
defineProps({
  equipe: {
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
        <img v-if="equipe.logo_url" :src="equipe.logo_url" :alt="equipe.nom" class="h-full w-full object-cover" />
        <span v-else>EQ</span>
      </span>
      <div class="min-w-0">
        <p class="truncate text-base font-bold text-[#1f2a44]">{{ equipe.nom }}</p>
        <p class="mt-1 text-xs text-[#64748b]">Categorie: {{ equipe.categorie || '-' }}</p>
        <p class="mt-1 text-xs capitalize text-[#475569]">Statut: {{ equipe.statut || '-' }}</p>
      </div>
    </div>

    <div class="mt-3 rounded-lg bg-[#f8fbff] px-3 py-2">
      <p class="text-[11px] font-bold uppercase text-[#64748b]">Code invitation</p>
      <p class="mt-1 font-mono text-sm font-bold text-[#1f2a44]">{{ equipe.code_invitation || '-' }}</p>
    </div>

    <p class="mt-3 line-clamp-2 text-xs leading-5 text-[#64748b]">{{ equipe.description || 'Aucune description.' }}</p>

    <div class="mt-4 flex items-center justify-between">
      <span class="text-xs text-[#64748b]">{{ equipe.coach?.name || 'Coach non assigne' }}</span>
      <div class="flex gap-2">
        <button class="ecs-btn-secondary !px-3 !py-1.5 !text-xs" type="button" @click="emit('edit', equipe)">Modifier</button>
        <button class="ecs-btn-danger" type="button" @click="emit('delete', equipe)">Supprimer</button>
      </div>
    </div>
  </article>
</template>
