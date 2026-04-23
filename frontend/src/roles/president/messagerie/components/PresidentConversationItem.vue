<script setup>
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
</script>

<template>
  <button
    type="button"
    class="group w-full rounded-[26px] border px-4 py-3 text-left transition duration-200"
    :class="
      active
        ? 'border-transparent bg-[linear-gradient(135deg,#eff4ff_0%,#eef7ff_42%,#ecfffb_100%)] shadow-[0_20px_40px_rgba(37,99,235,0.12)]'
        : 'border-[#e7edf7] bg-white hover:border-[#cad7ff] hover:bg-[#f9fbff]'
    "
    @click="emit('select', canal)"
  >
    <div class="flex items-start gap-3">
      <span class="mt-0.5 h-11 w-11 rounded-2xl bg-[linear-gradient(135deg,#172554_0%,#1d4ed8_62%,#14b8a6_100%)]"></span>

      <div class="min-w-0 flex-1">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="truncate text-sm font-bold text-[#0f172a]">{{ canal.nom }}</p>
            <p class="mt-0.5 truncate text-[11px] font-semibold text-[#64748b]">
              {{ canal.club?.nom || 'Club' }} · {{ canal.equipe?.nom || 'Equipe' }}
            </p>
          </div>

          <span class="shrink-0 text-[11px] font-semibold text-[#94a3b8]">
            {{ formatDate(canal.updated_at || canal.created_at) }}
          </span>
        </div>

        <p class="mt-2 line-clamp-2 text-xs leading-5 text-[#475569]">
          {{ canal.description || 'Canal de discussion de l equipe.' }}
        </p>

        <div class="mt-3 flex items-center justify-between gap-2">
          <span class="ecs-chip text-[#1d4ed8]">
            {{ canal.participants_total ?? 0 }} participants
          </span>

          <span
            v-if="unread > 0"
            class="rounded-full bg-[#111827] px-2 py-1 text-[10px] font-bold text-white"
          >
            {{ unread }}
          </span>
        </div>
      </div>
    </div>
  </button>
</template>
