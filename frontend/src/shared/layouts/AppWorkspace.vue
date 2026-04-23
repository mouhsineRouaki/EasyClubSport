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
  <section class="ecs-surface mt-6 overflow-hidden">
    <header class="border-b border-[#edf2ff] px-5 py-5 sm:px-7">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="ecs-kicker text-[#64748b]">Pages / {{ title }}</p>
          <h1 class="mt-1 text-2xl font-bold tracking-[-0.015em] text-[#111827]">{{ title }}</h1>
          <p v-if="subtitle" class="mt-2 text-sm text-[#64748b]">{{ subtitle }}</p>
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
              ? 'border-transparent bg-[linear-gradient(135deg,#172554_0%,#1d4ed8_100%)] text-white shadow-[0_10px_22px_rgba(29,78,216,0.18)]'
              : 'border-[#dbe5f2] bg-white text-[#4b5563] hover:border-[#c7d2fe] hover:bg-[#f8faff]'
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
