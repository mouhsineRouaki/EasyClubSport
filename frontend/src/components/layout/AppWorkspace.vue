<script setup>
import { RouterLink } from 'vue-router'

const props = defineProps({
  title: {
    type: String,
    default: '',
  },
  subtitle: {
    type: String,
    default: '',
  },
  tabs: {
    type: Array,
    default: () => [],
  },
  activeTab: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <section class="mt-6 rounded-[30px] border border-[#dbe4ff] bg-white text-[#111827] shadow-[inset_0_1px_0_rgba(255,255,255,0.85),0_35px_80px_-42px_rgba(15,23,42,0.75)]">
    <header class="border-b border-[#edf2ff] px-5 py-5 sm:px-7">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[#6b7280]">Pages / {{ title }}</p>
          <h1 class="mt-1 text-2xl font-bold tracking-[-0.015em] text-[#111827]">{{ title }}</h1>
          <p v-if="subtitle" class="mt-2 text-sm text-[#6b7280]">{{ subtitle }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <slot name="actions" />
        </div>
      </div>

      <nav v-if="props.tabs.length" class="mt-4 flex flex-wrap gap-2">
        <RouterLink
          v-for="tab in props.tabs"
          :key="tab.id"
          :to="tab.to"
          class="rounded-full border px-3.5 py-1.5 text-xs font-semibold transition"
          :class="
            activeTab === tab.id
              ? 'border-[#2446d8] bg-[#edf2ff] text-[#1e35b8]'
              : 'border-[#e5e7eb] bg-white text-[#4b5563] hover:border-[#c7d2fe] hover:bg-[#f8faff]'
          "
        >
          {{ tab.label }}
        </RouterLink>
      </nav>
    </header>

    <div class="px-5 py-5 sm:px-7 sm:py-6">
      <slot />
    </div>
  </section>
</template>
