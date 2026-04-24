<script setup>
import AppButton from '@/shared/components/ui/AppButton.vue'

defineProps({
  title: {
    type: String,
    default: '',
  },
  maxWidthClass: {
    type: String,
    default: 'max-w-3xl',
  },
  closeLabel: {
    type: String,
    default: 'Fermer',
  },
})

const emit = defineEmits(['close'])
</script>

<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4 backdrop-blur-[2px]"
    @click.self="emit('close')"
  >
    <section
      :class="[
        'w-full overflow-y-auto rounded-[28px] border border-[#e7edf8] bg-white p-4 shadow-[0_24px_50px_rgba(15,23,42,0.22)] sm:p-5',
        maxWidthClass,
      ]"
    >
      <div class="flex items-center justify-between gap-3">
        <h3 class="text-base font-bold text-[#1f2a44]">{{ title }}</h3>
        <AppButton type="button" variant="secondary" size="sm" @click="emit('close')">
          {{ closeLabel }}
        </AppButton>
      </div>

      <div class="mt-4">
        <slot />
      </div>
    </section>
  </div>
</template>
