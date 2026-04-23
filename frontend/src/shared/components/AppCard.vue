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
  <section class="ecs-card" :class="padded ? '' : 'p-0'">
    <header v-if="title || subtitle || $slots.actions" class="mb-4 flex flex-wrap items-start justify-between gap-3">
      <div>
        <h2 v-if="title" class="ecs-heading">{{ title }}</h2>
        <p v-if="subtitle" class="ecs-subheading">{{ subtitle }}</p>
      </div>
      <div v-if="$slots.actions">
        <slot name="actions" />
      </div>
    </header>

    <slot />
  </section>
</template>
