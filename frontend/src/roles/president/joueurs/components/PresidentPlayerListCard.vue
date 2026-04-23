<script setup>
defineProps({
  joueur: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['remove'])
</script>

<template>
  <article class="rounded-2xl border border-[#e8edf5] bg-white p-4 transition hover:-translate-y-[1px] hover:shadow-[0_10px_24px_rgba(15,23,42,0.08)]">
    <div class="flex items-start gap-3">
      <span class="grid h-14 w-14 shrink-0 place-items-center overflow-hidden rounded-xl bg-[linear-gradient(130deg,#0f172a,#334155)] text-xs font-bold text-white">
        <img v-if="joueur.photo_url" :src="joueur.photo_url" :alt="joueur.name || joueur.nom" class="h-full w-full object-cover" />
        <span v-else>JR</span>
      </span>
      <div class="min-w-0">
        <p class="truncate text-base font-bold text-[#1f2a44]">{{ `${joueur.prenom || ''} ${joueur.nom || ''}`.trim() || joueur.name || 'Joueur' }}</p>
        <p class="mt-1 truncate text-xs text-[#64748b]">{{ joueur.email || 'email non renseigne' }}</p>
        <p class="mt-1 text-xs text-[#475569]">{{ joueur.telephone || '-' }}</p>
      </div>
    </div>

    <div class="mt-3 rounded-lg bg-[#f8fbff] px-3 py-2">
      <p class="text-[11px] font-bold uppercase text-[#64748b]">Date d affectation</p>
      <p class="mt-1 text-sm font-semibold text-[#1f2a44]">{{ joueur.date_affectation || '-' }}</p>
    </div>

    <div class="mt-4 flex items-center justify-end">
      <button class="ecs-btn-danger" type="button" @click="emit('remove', joueur)">Retirer</button>
    </div>
  </article>
</template>
