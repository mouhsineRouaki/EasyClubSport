<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'success',
  },
})

const typeClass = computed(() => {
  if (props.type === 'error') return 'ecs-note-error'
  if (props.type === 'warning') return 'ecs-note-warning'
  return 'ecs-note-success'
})
</script>

<template>
  <div :class="typeClass">
    <slot />
  </div>
</template>
