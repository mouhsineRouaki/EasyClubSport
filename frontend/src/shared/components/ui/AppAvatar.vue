<script setup>
const props = defineProps({
  src: {
    type: String,
    default: '',
  },
  alt: {
    type: String,
    default: '',
  },
  initials: {
    type: String,
    default: 'EC',
  },
  sizeClass: {
    type: String,
    default: 'h-10 w-10',
  },
  roundedClass: {
    type: String,
    default: 'rounded-2xl',
  },
})
</script>

<template>
  <img
    v-if="src"
    :src="src"
    :alt="alt"
    class="object-cover"
    :class="[sizeClass, roundedClass]"
  />
  <span
    v-else
    class="ecs-avatar"
    :class="[sizeClass, roundedClass]"
  >
    {{ initials }}
  </span>
</template>
