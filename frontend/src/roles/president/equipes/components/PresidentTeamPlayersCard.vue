<script setup>
defineProps({
  joueurs: {
    type: Array,
    default: () => [],
  },
  chargement: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <section class="rounded-[22px] bg-white p-4 lg:col-span-2">
    <div class="flex items-center justify-between gap-3">
      <p class="text-sm font-black text-[#111827]">Joueurs de l equipe</p>
      <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-[10px] font-black text-[#2446d8]">
        {{ joueurs.length }} joueur(s)
      </span>
    </div>

    <div v-if="chargement" class="mt-4 grid gap-2 sm:grid-cols-2">
      <div v-for="n in 4" :key="n" class="h-[60px] animate-pulse rounded-2xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
    </div>

    <div v-else-if="joueurs.length" class="mt-4 grid gap-2 sm:grid-cols-2">
      <div
        v-for="joueur in joueurs"
        :key="joueur.id"
        class="flex items-center gap-3 rounded-2xl bg-[#f8fbff] p-3"
      >
        <img
          v-if="joueur.photo_url"
          :src="joueur.photo_url"
          :alt="joueur.nom"
          class="h-10 w-10 rounded-xl object-cover"
        />
        <span v-else class="block h-10 w-10 shrink-0 rounded-xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)]"></span>
        <div class="min-w-0">
          <p class="truncate text-sm font-black text-[#111827]">
            {{ [joueur.prenom, joueur.nom].filter(Boolean).join(' ') || joueur.name || 'Joueur' }}
          </p>
          <p class="truncate text-xs font-semibold text-[#6b7280]">{{ joueur.email || '-' }}</p>
        </div>
      </div>
    </div>

    <p v-else class="mt-4 rounded-2xl border border-dashed border-[#cfdaf2] px-3 py-6 text-center text-xs font-semibold text-[#6b7280]">
      Aucun joueur dans cette equipe.
    </p>
  </section>
</template>
