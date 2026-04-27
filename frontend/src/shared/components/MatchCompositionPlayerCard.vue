<script setup>
defineProps({
  joueur: {
    type: Object,
    required: true,
  },
  ton: {
    type: String,
    default: 'blue',
  },
})

const classesTon = {
  blue: 'bg-[#f8fbff] border-[#e6edf8]',
  green: 'bg-[#f3fbf6] border-[#d9f1e1]',
  amber: 'bg-[#fff9f0] border-[#fde7c2]',
  slate: 'bg-[#f8fafc] border-[#e2e8f0]',
}

const initiale = (joueur) => {
  return (joueur?.prenom || joueur?.name || joueur?.nom || 'J').slice(0, 1).toUpperCase()
}
</script>

<template>
  <article class="rounded-[22px] border p-3 transition" :class="classesTon[ton] || classesTon.blue">
    <div class="flex items-center gap-3">
      <img v-if="joueur.photo_url" :src="joueur.photo_url"
        :alt="[joueur.prenom, joueur.nom].filter(Boolean).join(' ') || joueur.name || 'Joueur'"
        class="h-11 w-11 rounded-2xl object-cover" />
      <span v-else
        class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff,#dbe7ff_28%,#2446d8_72%)] text-sm font-black text-white">
        {{ initiale(joueur) }}
      </span>

      <div class="min-w-0">
        <p class="truncate text-sm font-black text-[#111827]">
          {{ [joueur.prenom, joueur.nom].filter(Boolean).join(' ') || joueur.name || 'Joueur' }}
        </p>
        <p class="truncate text-[11px] font-semibold text-[#64748b]">
          {{ joueur.email || 'Email non defini' }}
        </p>
      </div>
    </div>

    <p v-if="joueur.position_joueur" class="mt-3 text-[11px] font-black uppercase tracking-[0.14em] text-[#7c8aa5]">
      {{ joueur.position_joueur }}
    </p>
  </article>
</template>
