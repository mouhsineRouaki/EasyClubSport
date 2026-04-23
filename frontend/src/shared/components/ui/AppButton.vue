<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'button',
  },
  variant: {
    type: String,
    default: 'primary',
  },
  size: {
    type: String,
    default: 'md',
  },
  block: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

const variantClass = computed(() => {
  if (props.variant === 'secondary') return 'ecs-btn-secondary'
  if (props.variant === 'ghost') return 'ecs-btn-ghost'
  if (props.variant === 'danger') return 'ecs-btn-danger'
  return 'ecs-btn-primary'
})

const sizeClass = computed(() => {
  if (props.size === 'sm') return 'px-3 py-1.5 text-xs'
  if (props.size === 'lg') return 'h-14 px-6 text-sm'
  return 'px-4 py-2 text-sm'
})
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    :class="[variantClass, sizeClass, block ? 'w-full' : '']"
  >
    <slot />
  </button>
</template>
