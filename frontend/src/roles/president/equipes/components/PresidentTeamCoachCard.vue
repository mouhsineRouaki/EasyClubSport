<script setup>
import AppButton from '@/shared/components/ui/AppButton.vue'

defineProps({
  coach: {
    type: Object,
    default: null,
  },
  chargement: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['assigner', 'retirer'])
</script>

<template>
  <section class="rounded-[22px] bg-white p-4">
    <div class="flex items-center justify-between gap-3">
      <p class="text-sm font-black text-[#111827]">Coach</p>
      <AppButton
        v-if="coach"
        type="button"
        variant="danger"
        size="xs"
        :disabled="chargement"
        @click="emit('retirer')"
      >
        Retirer
      </AppButton>
    </div>

    <div v-if="coach" class="mt-4 flex items-center gap-3">
      <img
        v-if="coach.photo_url || coach.photo"
        :src="coach.photo_url || coach.photo"
        :alt="coach.nom"
        class="h-14 w-14 rounded-2xl object-cover"
      />
      <span v-else class="block h-14 w-14 rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)]"></span>
      <div>
        <p class="text-sm font-black text-[#111827]">
          {{ [coach.prenom, coach.nom].filter(Boolean).join(' ') || coach.name || 'Coach' }}
        </p>
        <p class="mt-1 text-xs font-semibold text-[#64748b]">{{ coach.email || '-' }}</p>
        <p class="mt-0.5 text-xs font-semibold text-[#94a3b8]">{{ coach.telephone || '-' }}</p>
      </div>
    </div>

    <div v-else class="mt-4">
      <p class="rounded-2xl border border-dashed border-[#cfdaf2] px-3 py-3 text-center text-xs font-semibold text-[#6b7280]">
        Aucun coach affecte.
      </p>
      <AppButton
        type="button"
        class="mt-3 w-full"
        :disabled="chargement"
        @click="emit('assigner')"
      >
        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
          <path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
        </svg>
        Assigner un coach
      </AppButton>
    </div>
  </section>
</template>
