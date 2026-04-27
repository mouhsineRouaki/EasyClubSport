<script setup>
import { computed } from 'vue'
import AppAvatar from '@/shared/components/ui/AppAvatar.vue'

const props = defineProps({
  canal: {
    type: Object,
    required: true,
  },
  active: {
    type: Boolean,
    default: false,
  },
  unread: {
    type: Number,
    default: 0,
  },
})

const emit = defineEmits(['select'])

const formatDate = (value) => {
  if (!value) {
    return ''
  }

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'short',
    timeStyle: 'short',
  }).format(new Date(value))
}

const initials = (canal) =>
  (canal?.nom || 'CN')
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase() || 'CN'

const previewText = computed(() => {
  return props.canal?.description || 'Conversation de l equipe.'
})

const infosSecondaires = computed(() => {
  return [props.canal?.club?.nom, props.canal?.equipe?.nom].filter(Boolean).join(' · ') || 'Canal du club'
})

const participantsLabel = computed(() => {
  const total = props.canal?.participants_total ?? 0
  return `${total} participants`
})
</script>

<template>
  <button
    type="button"
    class="group relative w-full rounded-[24px] border px-4 py-3 text-left transition duration-200"
    :class="
      active
        ? 'border-[#bfd0ff] bg-[linear-gradient(135deg,#f4f8ff_0%,#eef6ff_52%,#ecfffb_100%)] shadow-[0_16px_34px_rgba(37,99,235,0.10)]'
        : 'border-transparent bg-white hover:border-[#d9e4fb] hover:bg-[#f8fbff]'
    "
    @click="emit('select', canal)"
  >
    <div class="flex items-start gap-3">
      <AppAvatar
        :src="canal.image_url || canal.image || ''"
        :alt="canal.nom"
        :initials="initials(canal)"
        size-class="h-12 w-12"
        rounded-class="rounded-2xl"
      />

      <div class="min-w-0 flex-1">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="truncate text-sm font-black text-[#0f172a]">
              {{ canal.nom }}
            </p>
            <p class="mt-0.5 truncate text-[11px] font-semibold text-[#7c8aa5]">
              {{ infosSecondaires }}
            </p>
          </div>

          <div class="flex shrink-0 flex-col items-end gap-2">
            <span class="text-[11px] font-semibold" :class="active ? 'text-[#1d4ed8]' : 'text-[#94a3b8]'">
              {{ formatDate(canal.updated_at || canal.created_at) }}
            </span>

            <span
              v-if="unread > 0"
              class="grid min-h-5 min-w-5 place-items-center rounded-full bg-[#1d4ed8] px-1.5 text-[10px] font-black text-white shadow-[0_10px_18px_rgba(29,78,216,0.28)]"
            >
              {{ unread }}
            </span>
          </div>
        </div>

        <div class="mt-2 flex items-start justify-between gap-3">
          <p
            class="line-clamp-2 min-w-0 flex-1 text-xs leading-5"
            :class="active ? 'text-[#334155]' : 'text-[#64748b]'"
          >
            {{ previewText }}
          </p>
        </div>

        <div class="mt-3 flex items-center justify-between gap-3">
          <span class="inline-flex items-center rounded-full bg-[#eef4ff] px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-[#1d4ed8]">
            {{ canal.type_canal || 'Canal' }}
          </span>

          <span class="text-[11px] font-semibold text-[#64748b]">
            {{ participantsLabel }}
          </span>
        </div>
      </div>
    </div>
  </button>
</template>
