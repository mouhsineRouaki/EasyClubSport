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
    <p v-if="badge" class="ecs-kicker">
      {{ badge }}
    </p>
    <h3 v-if="titre" class="ecs-title-lg">
      {{ titre }}
    </h3>
    <p v-if="description" class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#64748b]">
      {{ description }}
    </p>

    <div v-if="$slots.default" class="mt-5">
      <slot />
    </div>
  </div>
</template>
