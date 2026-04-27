<script setup>
import AppButton from '@/shared/components/ui/AppButton.vue'

defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  hasData: {
    type: Boolean,
    default: false,
  },
  emptyTitle: {
    type: String,
    default: 'Aucune donnee',
  },
  emptyDescription: {
    type: String,
    default: '',
  },
  skeletonCount: {
    type: Number,
    default: 6,
  },
  errorMessage: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['retry'])
</script>

<template>
  <template v-if="loading">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <div v-for="index in skeletonCount" :key="index"
        class="h-36 animate-pulse rounded-2xl border border-[#eef2f7] bg-[linear-gradient(110deg,#f7f9fc_8%,#f0f4f9_18%,#f7f9fc_33%)] bg-[length:200%_100%]">
      </div>
    </div>
  </template>

  <template v-else-if="errorMessage">
    <div class="rounded-2xl border border-[#fecaca] bg-[#fff1f2] px-5 py-8 text-center">
      <p class="text-sm font-semibold text-[#b91c1c]">Erreur de chargement</p>
      <p class="mt-1 text-xs text-[#9f1239]">{{ errorMessage }}</p>
      <slot name="error-action">
        <AppButton type="button" variant="secondary" size="sm" class="mt-4" @click="emit('retry')">Reessayer</AppButton>
      </slot>
    </div>
  </template>

  <template v-else-if="!hasData">
    <div class="rounded-2xl border border-dashed border-[#d6ddeb] bg-[#f9fbff] px-5 py-12 text-center">
      <p class="text-sm font-semibold text-[#334155]">{{ emptyTitle }}</p>
      <p v-if="emptyDescription" class="mt-1 text-xs text-[#64748b]">{{ emptyDescription }}</p>
      <slot name="empty-action" />
    </div>
  </template>

  <template v-else>
    <slot />
  </template>
</template>
