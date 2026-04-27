<script setup>
defineProps({
  label: {
    type: String,
    default: '',
  },
  error: {
    type: String,
    default: '',
  },
  hint: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <label class="ecs-field">
    <span v-if="label" class="ecs-field-label">{{ label }}</span>
    <slot />
    <span v-if="error" class="text-xs font-semibold text-[#e11d48]">{{ error }}</span>
    <span v-else-if="hint" class="text-xs font-medium text-[#64748b]">{{ hint }}</span>
  </label>
</template>
