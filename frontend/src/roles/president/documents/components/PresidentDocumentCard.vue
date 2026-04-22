<script setup>
const props = defineProps({
  document: {
    type: Object,
    required: true,
  },
  active: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['select'])

const formatDate = (date) => {
  if (!date) {
    return '-'
  }

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
  }).format(new Date(date))
}
</script>

<template>
  <button
    type="button"
    class="group relative overflow-hidden rounded-[26px] border bg-white p-4 text-left transition duration-300 hover:-translate-y-1 hover:bg-[#fbfcff]"
    :class="active ? 'border-[#2446d8] ring-4 ring-[#2446d8]/10' : 'border-[#edf1f7] hover:border-[#d7e0f5]'"
    @click="emit('select', document)"
  >
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <p class="truncate text-[10px] font-black uppercase tracking-[0.14em] text-[#6b7280]">
          {{ document.type_document || 'Document' }}
        </p>
        <h4 class="mt-2 line-clamp-2 text-xl font-black leading-tight text-[#111827]">
          {{ document.nom }}
        </h4>
      </div>
      <span class="rounded-full bg-[#eef4ff] px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.12em] text-[#2446d8]">
        Fichier
      </span>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-2">
      <div class="rounded-[14px] bg-[#f5f7fb] px-3 py-2">
        <p class="truncate text-xs font-black text-[#111827]">{{ document.utilisateur?.nom || '-' }}</p>
        <p class="text-[9px] font-black uppercase tracking-[0.1em] text-[#6b7280]">Utilisateur</p>
      </div>
      <div class="rounded-[14px] bg-[#f5f7fb] px-3 py-2">
        <p class="truncate text-xs font-black text-[#111827]">{{ formatDate(document.date_ajout || document.created_at) }}</p>
        <p class="text-[9px] font-black uppercase tracking-[0.1em] text-[#6b7280]">Ajout</p>
      </div>
    </div>

    <p class="mt-4 truncate text-xs font-semibold text-[#64748b]">
      {{ document.utilisateur?.email || 'Aucun email disponible' }}
    </p>

    <div class="pointer-events-none absolute -bottom-10 -right-10 h-28 w-28 rounded-full bg-[#2446d8]/8 transition duration-300 group-hover:scale-125 group-hover:bg-[#2446d8]/12"></div>
  </button>
</template>
