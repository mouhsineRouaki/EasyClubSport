<script setup>
defineProps({
  joueur: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <article class="rounded-[26px] border border-[#e5ecfb] bg-white p-5">
    <div class="flex items-center gap-4">
      <img v-if="joueur.photo_url" :src="joueur.photo_url" :alt="joueur.nom" class="h-16 w-16 rounded-2xl object-cover" />
      <div v-else class="grid h-16 w-16 place-items-center rounded-2xl bg-[linear-gradient(135deg,#2446d8_0%,#4c6fff_100%)] text-sm font-black text-white">
        {{ joueur.nom?.slice(0, 2).toUpperCase() }}
      </div>
      <div>
        <h2 class="text-lg font-black text-[#0f172a]">{{ joueur.nom }}</h2>
        <p class="mt-1 text-sm font-semibold text-[#64748b]">{{ joueur.email || '-' }}</p>
      </div>
    </div>

    <div class="mt-5 grid gap-3 sm:grid-cols-2">
      <div class="rounded-[18px] bg-[#f8fbff] p-3">
        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#64748b]">Telephone</p>
        <p class="mt-2 text-sm font-semibold text-[#0f172a]">{{ joueur.telephone || '-' }}</p>
      </div>
      <div class="rounded-[18px] bg-[#f8fbff] p-3">
        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#64748b]">Statut</p>
        <p class="mt-2 text-sm font-semibold text-[#0f172a]">{{ joueur.statut || '-' }}</p>
      </div>
    </div>
  </article>
</template>
