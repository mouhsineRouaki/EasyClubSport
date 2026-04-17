<script setup>
const props = defineProps({
  joueur: {
    type: Object,
    required: true,
  },
  active: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['select'])

const nomComplet = () => {
  return [props.joueur.prenom, props.joueur.nom].filter(Boolean).join(' ') || props.joueur.name || 'Joueur'
}
</script>

<template>
  <button
    type="button"
    class="group relative overflow-hidden rounded-[26px] border bg-white p-3 text-left transition duration-300 hover:-translate-y-1 hover:bg-[#fbfcff]"
    :class="active ? 'border-[#2446d8] ring-4 ring-[#2446d8]/10' : 'border-[#edf1f7] hover:border-[#d7e0f5]'"
    @click="emit('select', joueur)"
  >
    <div class="flex items-start gap-3">
      <img
        v-if="joueur.photo_url"
        :src="joueur.photo_url"
        :alt="nomComplet()"
        class="h-[72px] w-[72px] shrink-0 rounded-[18px] object-cover ring-4 ring-[#f4f7fc] transition duration-300 group-hover:rotate-[-2deg] group-hover:scale-[1.03]"
      />
      <span v-else class="h-[72px] w-[72px] shrink-0 rounded-[18px] bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)] ring-4 ring-[#f4f7fc] transition duration-300 group-hover:rotate-[-2deg] group-hover:scale-[1.03]"></span>

      <div class="min-w-0 flex-1 pt-1">
        <div class="flex items-center justify-between gap-2">
          <p class="truncate text-[10px] font-black uppercase tracking-[0.14em] text-[#6b7280]">
            {{ joueur.role_equipe || 'Joueur' }}
          </p>
          <span class="rounded-full bg-[#111827] px-2.5 py-1 text-[9px] font-black text-white">
            Profil
          </span>
        </div>
        <h4 class="mt-2 line-clamp-1 text-xl font-black leading-tight tracking-normal text-[#111827]">
          {{ nomComplet() }}
        </h4>
        <p class="mt-1 line-clamp-1 text-xs font-semibold text-[#64748b]">
          {{ joueur.email || joueur.telephone || 'Contact non defini' }}
        </p>
      </div>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-2">
      <div class="rounded-[14px] bg-[#f5f7fb] px-2 py-2">
        <p class="truncate text-xs font-black text-[#111827]">{{ joueur.statut || 'actif' }}</p>
        <p class="text-[9px] font-black uppercase tracking-[0.1em] text-[#6b7280]">Statut</p>
      </div>
      <div class="rounded-[14px] bg-[#f5f7fb] px-2 py-2">
        <p class="truncate text-xs font-black text-[#111827]">{{ joueur.date_affectation || '-' }}</p>
        <p class="text-[9px] font-black uppercase tracking-[0.1em] text-[#6b7280]">Affecte</p>
      </div>
    </div>

    <div class="pointer-events-none absolute -bottom-10 -right-10 h-28 w-28 rounded-full bg-[#2446d8]/8 transition duration-300 group-hover:scale-125 group-hover:bg-[#2446d8]/12"></div>
  </button>
</template>
