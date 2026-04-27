<script setup>
import AppNotificationsDropdown from './AppNotificationsDropdown.vue'

const props = defineProps({
  featureLinks: {
    type: Array,
    default: () => [],
  },
  activeModule: {
    type: String,
    default: '',
  },
  searchValue: {
    type: String,
    default: '',
  },
  searchPlaceholder: {
    type: String,
    default: 'Search',
  },
  refreshDisabled: {
    type: Boolean,
    default: false,
  },
  refreshLoading: {
    type: Boolean,
    default: false,
  },
  user: {
    type: Object,
    default: () => ({}),
  },
  notificationsProps: {
    type: Object,
    default: () => ({}),
  },
})

const emit = defineEmits([
  'update:searchValue',
  'select-module',
  'refresh',
  'open-profile',
  'toggle-notifications',
  'refresh-notifications',
  'mark-all-notifications',
  'notification-click',
  'notification-decision',
])
</script>

<template>
  <section class="relative z-30 -mt-[154px] min-h-screen pb-0">
    <article
      class="sticky top-2 z-40 mx-auto w-full max-w-[1220px] overflow-hidden rounded-[30px] border border-[#e6ebf8] bg-white text-[#111827] shadow-[0_1px_0_rgba(17,24,39,0.04),0_36px_70px_-54px_rgba(15,23,42,0.55)]"
    >
      <div
        class="sticky top-0 z-30 flex flex-wrap items-center justify-between gap-3 border-b border-[#ecf0f9] bg-white/95 px-3 py-3 backdrop-blur-md sm:px-4"
      >
        <div class="flex min-w-0 flex-1 flex-wrap items-center gap-2 text-[11px]">
          <button
            v-for="item in featureLinks"
            :key="item.key"
            type="button"
            class="group inline-flex items-center gap-2 rounded-full border px-3 py-1.5 font-bold transition"
            :class="
              activeModule === item.key
                ? 'border-transparent bg-[linear-gradient(135deg,#172554_0%,#1d4ed8_100%)] text-white shadow-[0_10px_22px_rgba(29,78,216,0.16)]'
                : 'border-[#edf2fb] text-[#6b7280] hover:border-[#dce4f7] hover:bg-[#f8fbff] hover:text-[#1f2a44]'
            "
            @click="emit('select-module', item.key)"
          >
            {{ item.label }}
          </button>
        </div>

        <div class="flex items-center gap-2">
          <label class="relative hidden sm:block">
            <input
              :value="searchValue"
              type="text"
              :placeholder="searchPlaceholder"
              class="ecs-search-input h-9 w-[180px] text-xs"
              @input="emit('update:searchValue', $event.target.value)"
            />
          </label>

          <button
            type="button"
            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff] disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="refreshDisabled"
            @click="emit('refresh')"
          >
            <svg class="h-3.5 w-3.5" :class="refreshLoading ? 'animate-spin' : ''" viewBox="0 0 20 20" fill="none">
              <path
                d="M16.25 9.25a6.25 6.25 0 1 0-1.72 4.31M16.25 9.25V5.5M16.25 9.25H12.5"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </button>

          <AppNotificationsDropdown
            v-bind="notificationsProps"
            @toggle="emit('toggle-notifications')"
            @refresh="emit('refresh-notifications')"
            @mark-all="emit('mark-all-notifications')"
            @notification-click="emit('notification-click', $event)"
            @decision="emit('notification-decision', $event)"
          />

          <button
            type="button"
            class="rounded-full transition hover:scale-[1.03]"
            title="Ouvrir le profil"
            @click="emit('open-profile')"
          >
            <img
              v-if="user.image"
              :src="user.image"
              :alt="user.nom"
              class="h-8 w-8 rounded-full object-cover"
            />
            <span
              v-else
              class="block h-8 w-8 rounded-full bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)] ring-1 ring-[#dbe2ef]"
            ></span>
          </button>
        </div>
      </div>

      <div class="px-3 py-4 sm:px-5 sm:py-5">
        <slot />
      </div>
    </article>
  </section>
</template>
