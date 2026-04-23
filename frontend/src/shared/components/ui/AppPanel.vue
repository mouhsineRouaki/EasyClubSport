<script setup>
const props = defineProps({
  padded: {
    type: Boolean,
    default: true,
  },
  soft: {
    type: Boolean,
    default: false,
  },
  muted: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <section
    :class="[
      soft ? 'ecs-panel-soft' : muted ? 'ecs-panel-muted' : 'ecs-panel',
      padded ? 'p-5 sm:p-6' : '',
    ]"
  >
    <slot />
  </section>
</template>
