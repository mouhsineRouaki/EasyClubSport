<script setup>
defineProps({
  title: {
    type: String,
    default: '',
  },
  subtitle: {
    type: String,
    default: '',
  },
  padded: {
    type: Boolean,
    default: true,
  },
})
</script>

<template>
  <section class="rounded-2xl border border-[#e8edf5] bg-white shadow-[0_18px_35px_rgba(15,23,42,0.06)]"
    :class="padded ? 'p-4 sm:p-5' : ''">
    <header v-if="title || subtitle || $slots.actions" class="mb-4 flex flex-wrap items-start justify-between gap-3">
      <div>
        <h2 v-if="title" class="text-lg font-bold text-[#1f2a44]">{{ title }}</h2>
        <p v-if="subtitle" class="mt-1 text-sm text-[#6b7280]">{{ subtitle }}</p>
      </div>
      <div v-if="$slots.actions">
        <slot name="actions" />
      </div>
    </header>

    <slot />
  </section>
</template>
