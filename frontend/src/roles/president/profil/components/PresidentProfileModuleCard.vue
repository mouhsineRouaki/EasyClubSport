<script setup>
defineProps({
  module: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <article class="overflow-hidden rounded-xl border border-[#e8edf5] bg-white">
    <div class="h-24" :style="{ background: module.image }"></div>
    <div class="p-4">
      <p class="text-sm font-bold text-[#1f2a44]">{{ module.titre }}</p>
      <p class="mt-1 text-xs text-[#64748b]">{{ module.description }}</p>
    </div>
  </article>
</template>
