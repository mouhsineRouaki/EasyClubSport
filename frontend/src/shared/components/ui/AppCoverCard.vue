<script setup>
import { computed } from 'vue'
import { createCoverBackground } from '@/shared/utils/coverImage'

const props = defineProps({
  type: {
    type: String,
    default: 'button',
  },
  image: {
    type: String,
    default: '',
  },
  fallbackImage: {
    type: String,
    default: '',
  },
  active: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  badge: {
    type: String,
    default: '',
  },
  statusLabel: {
    type: String,
    default: '',
  },
  statusClass: {
    type: String,
    default: 'bg-white text-[#1f36bf]',
  },
  title: {
    type: String,
    default: '',
  },
  subtitle: {
    type: String,
    default: '',
  },
  minHeightClass: {
    type: String,
    default: 'min-h-[220px]',
  },
})

const emit = defineEmits(['click'])

const backgroundStyle = computed(() => ({
  backgroundImage: createCoverBackground(props.image, props.fallbackImage),
}))
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    class="group relative overflow-hidden rounded-[24px] border bg-cover bg-center text-left text-white transition duration-300 hover:-translate-y-1"
    :class="[minHeightClass, active ? 'border-white ring-4 ring-[#2446d8]/15' : 'border-white/70 hover:border-white']"
    :style="backgroundStyle"
    @click="emit('click')"
  >
    <div class="absolute inset-0 bg-[#2446d8]/0 transition duration-300 group-hover:bg-[#2446d8]/20"></div>

    <div class="relative z-10 flex h-full flex-col justify-between p-4" :class="minHeightClass">
      <div class="flex items-center justify-between gap-3">
        <span
          v-if="badge"
          class="rounded-full border border-white/30 bg-white/16 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.16em] text-white backdrop-blur-md"
        >
          {{ badge }}
        </span>
        <span v-else></span>

        <span
          v-if="statusLabel"
          class="rounded-full px-2.5 py-1 text-[9px] font-black"
          :class="statusClass"
        >
          {{ statusLabel }}
        </span>
      </div>

      <div>
        <slot name="body">
          <h5 class="line-clamp-2 text-2xl font-black leading-tight text-white">{{ title }}</h5>
          <p v-if="subtitle" class="mt-2 line-clamp-2 text-xs font-semibold text-white/78">{{ subtitle }}</p>
        </slot>
      </div>

      <div v-if="$slots.footer">
        <slot name="footer" />
      </div>
    </div>
  </button>
</template>
