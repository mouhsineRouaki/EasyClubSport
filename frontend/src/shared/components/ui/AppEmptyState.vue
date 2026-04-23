<script setup>
defineProps({
  title: {
    type: String,
    default: 'Aucune donnee',
  },
  description: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <div class="ecs-empty-state">
    <p class="ecs-empty-title">{{ title }}</p>
    <p v-if="description" class="ecs-empty-text">{{ description }}</p>

    <div v-if="$slots.default" class="mt-5">
      <slot />
    </div>
  </div>
</template>
