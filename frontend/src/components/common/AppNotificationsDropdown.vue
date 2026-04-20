<script setup>
const props = defineProps({
  open: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  unreadTotal: { type: Number, default: 0 },
  notifications: { type: Array, default: () => [] },
  actionInProgress: { type: String, default: '' },
  title: { type: String, default: 'Notifications' },
  emptyText: { type: String, default: 'Aucune notification pour le moment.' },
  formatter: {
    type: Function,
    default: (value) => value || '',
  },
})

const emit = defineEmits(['toggle', 'refresh', 'mark-all', 'notification-click', 'decision'])

const badgeClass = (notification) => {
  if (notification?.statut_action === 'en_attente') return 'bg-[#fff7ed] text-[#f59e0b]'
  if (notification?.statut_action === 'accepte' || notification?.statut_action === 'confirme') return 'bg-[#ecfdf5] text-[#16a34a]'
  if (notification?.statut_action === 'refuse' || notification?.statut_action === 'absent') return 'bg-[#fef2f2] text-[#ef4444]'
  return 'bg-[#eef2ff] text-[#1f36bf]'
}
</script>

<template>
  <div class="relative">
    <button type="button"
      class="relative inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]"
      aria-label="Notifications" title="Notifications" @click="emit('toggle')">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path
          d="M18.25 9.4c0-3.4-2.45-5.9-6.25-5.9s-6.25 2.5-6.25 5.9v2.56c0 .72-.26 1.42-.72 1.96L4 15.13h16l-1.03-1.21a3 3 0 0 1-.72-1.96V9.4ZM9.75 18.25a2.45 2.45 0 0 0 4.5 0"
          stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      <span v-if="unreadTotal"
        class="absolute -right-0.5 -top-0.5 grid h-4 min-w-4 place-items-center rounded-full bg-[#ef4444] px-1 text-[9px] font-black text-white">
        {{ unreadTotal }}
      </span>
    </button>

    <div v-if="open"
      class="absolute right-0 top-10 z-50 w-[360px] overflow-hidden rounded-[24px] border border-[#e6edf8] bg-white text-left shadow-[0_22px_60px_-40px_rgba(15,23,42,0.55)]">
      <div class="flex items-center justify-between border-b border-[#eef2fb] px-4 py-3">
        <div>
          <p class="text-sm font-black text-[#111827]">{{ title }}</p>
          <p class="text-[11px] font-semibold text-[#64748b]">{{ unreadTotal }} non lue(s)</p>
        </div>
        <div class="flex items-center gap-2">
          <button type="button" class="rounded-full bg-[#f2f6ff] px-3 py-1 text-[11px] font-black text-[#1f36bf]"
            @click="emit('refresh')">
            ↻
          </button>
          <button type="button"
            class="rounded-full border border-[#dbe2ef] px-3 py-1 text-[11px] font-black text-[#475569]"
            @click="emit('mark-all')">
            Tout lire
          </button>
        </div>
      </div>

      <div class="max-h-[430px] overflow-y-auto p-3">
        <p v-if="loading" class="rounded-2xl bg-[#f8fbff] p-4 text-xs font-bold text-[#64748b]">Chargement des
          notifications...</p>
        <p v-else-if="!notifications.length" class="rounded-2xl bg-[#f8fbff] p-4 text-xs font-bold text-[#64748b]">{{
          emptyText }}</p>

        <article v-for="notification in notifications" v-else :key="notification.id"
          class="mb-2 rounded-[20px] border border-[#edf2fb] bg-[#fbfdff] p-3 transition hover:border-[#dbe4fb]">
          <button type="button" class="w-full text-left" @click="emit('notification-click', notification)">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-sm font-black text-[#111827]" :class="notification.est_lue ? 'opacity-60' : ''">{{
                  notification.titre }}</p>
                <p class="mt-1 text-xs font-semibold leading-5 text-[#64748b]">{{ notification.contenu }}</p>
                <p class="mt-2 text-[11px] font-bold text-[#94a3b8]">{{ formatter(notification.created_at) }}</p>
              </div>
              <span class="rounded-full px-2.5 py-1 text-[10px] font-black" :class="badgeClass(notification)">
                {{ notification.statut_action || notification.type_notification }}
              </span>
            </div>
          </button>

          <div v-if="notification.action === 'match_invitation' && notification.statut_action === 'en_attente'"
            class="mt-3 grid grid-cols-2 gap-2">
            <button type="button"
              class="rounded-full bg-[#111827] px-3 py-2 text-[11px] font-black text-white transition hover:bg-[#1f36bf] disabled:opacity-60"
              :disabled="actionInProgress === `${notification.id}-accepte`"
              @click.stop="emit('decision', { notification, decision: 'accepte' })">
              Accepter
            </button>
            <button type="button"
              class="rounded-full border border-[#fecaca] bg-white px-3 py-2 text-[11px] font-black text-[#ef4444] transition hover:bg-[#fef2f2] disabled:opacity-60"
              :disabled="actionInProgress === `${notification.id}-refuse`"
              @click.stop="emit('decision', { notification, decision: 'refuse' })">
              Refuser
            </button>
          </div>
        </article>
      </div>
    </div>
  </div>
</template>
