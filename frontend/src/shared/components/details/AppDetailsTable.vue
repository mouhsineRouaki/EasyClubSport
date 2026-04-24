<script setup>
defineProps({
  items: {
    type: Array,
    default: () => [],
  },
  columns: {
    type: Number,
    default: 2,
  },
})

const columnsClassMap = {
  1: 'grid-cols-1',
  2: 'grid-cols-1 sm:grid-cols-2',
  3: 'grid-cols-1 sm:grid-cols-2 xl:grid-cols-3',
  4: 'grid-cols-1 sm:grid-cols-2 xl:grid-cols-4',
}
</script>

<template>
  <div :class="['grid gap-3', columnsClassMap[columns] || columnsClassMap[2]]">
    <article
      v-for="item in items"
      :key="item.label"
      class="rounded-[18px] border border-[#e8edf6] bg-[#f8fbff] px-4 py-3"
    >
      <p class="text-[10px] font-black uppercase tracking-[0.16em] text-[#94a3b8]">{{ item.label }}</p>
      <p class="mt-1 text-sm font-black text-[#111827]">{{ item.value || '-' }}</p>
      <p v-if="item.helper" class="mt-1 text-xs font-semibold text-[#64748b]">{{ item.helper }}</p>
    </article>
  </div>
</template>
