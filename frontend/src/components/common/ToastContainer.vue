<script setup>
import { useToasts } from '../../stores/toast'

const { toasts, removeToast } = useToasts()
</script>

<template>
  <div class="pointer-events-none fixed bottom-5 right-5 z-[100] flex w-[min(360px,calc(100vw-24px))] flex-col gap-2">
    <TransitionGroup name="toast">
      <article
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto flex items-start justify-between gap-3 rounded-xl border px-4 py-3 shadow-[0_12px_24px_rgba(15,23,42,0.18)] backdrop-blur"
        :class="toast.type === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800'"
      >
        <p class="text-sm font-semibold">{{ toast.message }}</p>
        <button
          type="button"
          class="rounded-md border border-current/20 px-2 py-1 text-xs font-bold opacity-70 transition hover:opacity-100"
          @click="removeToast(toast.id)"
        >
          Fermer
        </button>
      </article>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.2s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>
