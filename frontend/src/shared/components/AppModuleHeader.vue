<script setup>
defineProps({
  badge: {
    type: String,
    default: '',
  },
  titre: {
    type: String,
    default: '',
  },
  description: {
    type: String,
    default: '',
  },
  maxWidth: {
    type: String,
    default: 'max-w-3xl',
  },
})
</script>

<template>
  <div class="mx-auto text-center" :class="maxWidth">
    <p v-if="badge" class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">
      {{ badge }}
    </p>
    <h3 v-if="titre" class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">
      {{ titre }}
    </h3>
    <p v-if="description" class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#6b7280]">
      {{ description }}
    </p>

    <div v-if="$slots.default" class="mt-5">
      <slot />
    </div>
  </div>
</template>
