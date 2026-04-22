<script setup>
import { RouterLink } from 'vue-router'

const props = defineProps({
  backgroundSrc: {
    type: String,
    required: true,
  },
  logoSrc: {
    type: String,
    required: true,
  },
  homeRoute: {
    type: String,
    required: true,
  },
  brandName: {
    type: String,
    default: 'EasySportClub',
  },
  globalLinks: {
    type: Array,
    default: () => [],
  },
})

defineEmits(['logout'])
</script>

<template>
  <section
    class="relative overflow-hidden rounded-[28px] border border-[#2a43cd] bg-[#2446d8] px-4 pb-[180px] pt-4 text-white sm:px-7 sm:pb-[196px] sm:pt-5"
  >
    <img :src="backgroundSrc" alt="Background" class="absolute inset-0 h-full w-full object-cover" />

    <header
      class="relative z-10 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/15 bg-white/10 px-3 py-2 backdrop-blur-md"
    >
      <RouterLink :to="homeRoute" class="flex items-center gap-2.5">
        <img :src="logoSrc" :alt="brandName" class="h-10 w-10 rounded-xl bg-white/95 p-2" />
        <span class="text-lg font-bold">{{ brandName }}</span>
      </RouterLink>

      <nav class="flex flex-wrap items-center gap-2">
        <RouterLink
          v-for="item in globalLinks.filter((link) => link.to)"
          :key="item.to"
          :to="item.to"
          class="rounded-full border border-white/25 bg-white/10 px-4 py-1.5 text-[11px] font-semibold text-white/95 transition hover:bg-white/20"
        >
          {{ item.label }}
        </RouterLink>
        <a
          v-for="item in globalLinks.filter((link) => link.href)"
          :key="item.href"
          :href="item.href"
          class="rounded-full border border-white/25 bg-white/10 px-4 py-1.5 text-[11px] font-semibold text-white/95 transition hover:bg-white/20"
        >
          {{ item.label }}
        </a>
        <button
          type="button"
          class="rounded-full bg-white px-4 py-1.5 text-[11px] font-bold text-[#1f36bf] transition hover:bg-[#eef2ff]"
          @click="$emit('logout')"
        >
          Deconnexion
        </button>
      </nav>
    </header>

    <div class="relative z-10 mx-auto mt-10 max-w-4xl text-center sm:mt-14">
      <h1 class="text-3xl font-black leading-[1.16] sm:text-6xl">
        <slot name="title" />
      </h1>
      <div class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">
        <slot name="description" />
      </div>
      <div class="mt-6 flex flex-wrap items-center justify-center gap-2.5">
        <slot name="actions" />
      </div>
    </div>
  </section>
</template>
