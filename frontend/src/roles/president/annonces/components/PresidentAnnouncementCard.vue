<script setup>
import { computed } from 'vue'

const props = defineProps({
  annonce: {
    type: Object,
    required: true,
  },
  active: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['select'])

const extrait = computed(() => {
  const contenu = props.annonce?.contenu || ''
  return contenu.length > 120 ? `${contenu.slice(0, 117)}...` : contenu
})

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
  <button type="button"
    class="group relative overflow-hidden rounded-[26px] border bg-white p-4 text-left transition duration-300 hover:-translate-y-1 hover:bg-[#fbfcff]"
    :class="active ? 'border-[#2446d8] ring-4 ring-[#2446d8]/10' : 'border-[#edf1f7] hover:border-[#d7e0f5]'"
    @click="emit('select', annonce)">
    <img v-if="annonce.image_url" :src="annonce.image_url" :alt="annonce.titre"
      class="mb-4 h-[135px] w-full rounded-[20px] object-cover" />
    <div v-else
      class="mb-4 h-[135px] w-full rounded-[20px] bg-[linear-gradient(135deg,#dbe7ff_0%,#eef4ff_45%,#f8fbff_100%)]">
    </div>

    <div class="flex items-center justify-between gap-3">
      <p class="truncate text-[10px] font-black uppercase tracking-[0.14em] text-[#6b7280]">
        {{ annonce.club?.nom || 'Club' }}
      </p>
      <span class="rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.12em]"
        :class="annonce.est_active ? 'bg-[#ecfdf5] text-[#16a34a]' : 'bg-[#f8fafc] text-[#64748b]'">
        {{ annonce.est_active ? 'Active' : 'Archivee' }}
      </span>
    </div>

    <h4 class="mt-3 line-clamp-2 text-xl font-black leading-tight text-[#111827]">
      {{ annonce.titre }}
    </h4>

    <p class="mt-3 line-clamp-4 min-h-[96px] text-sm font-semibold leading-6 text-[#64748b]">
      {{ extrait || 'Aucun contenu disponible pour cette annonce.' }}
    </p>

    <div class="mt-4 grid grid-cols-2 gap-2">
      <div class="rounded-[14px] bg-[#f5f7fb] px-3 py-2">
        <p class="truncate text-xs font-black text-[#111827]">{{ annonce.auteur?.nom || 'President' }}</p>
        <p class="text-[9px] font-black uppercase tracking-[0.1em] text-[#6b7280]">Auteur</p>
      </div>
      <div class="rounded-[14px] bg-[#f5f7fb] px-3 py-2">
        <p class="truncate text-xs font-black text-[#111827]">{{ formatDate(annonce.created_at) }}</p>
        <p class="text-[9px] font-black uppercase tracking-[0.1em] text-[#6b7280]">Publication</p>
      </div>
    </div>

    <div
      class="pointer-events-none absolute -bottom-10 -right-10 h-28 w-28 rounded-full bg-[#2446d8]/8 transition duration-300 group-hover:scale-125 group-hover:bg-[#2446d8]/12">
    </div>
  </button>
</template>
