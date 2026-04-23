<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import logoEasyClubSportMark from '@/assets/logo-easyclubsport-mark.svg'
import AppAvatar from '@/shared/components/ui/AppAvatar.vue'
import AppButton from '@/shared/components/ui/AppButton.vue'

const props = defineProps({
  links: {
    type: Array,
    default: () => [],
  },
  activeKey: {
    type: String,
    default: '',
  },
  user: {
    type: Object,
    default: () => ({}),
  },
  profileRoute: {
    type: String,
    default: '/president/profil',
  },
})

defineEmits(['logout'])

const userDisplay = computed(() => {
  const profile = props.user || {}
  const nomComplet = profile.nomComplet || [profile.prenom, profile.nom].filter(Boolean).join(' ')
  const initiales = (nomComplet || 'President')
    .split(' ')
    .slice(0, 2)
    .map((x) => x[0] || '')
    .join('')
    .toUpperCase()

  return {
    nom: nomComplet || profile.name || 'President',
    role: profile.role || 'president',
    email: profile.email || 'email indisponible',
    image: profile.image || profile.photo_url || profile.photo || '',
    initiales: initiales || 'PR',
  }
})
</script>

<template>
  <header class="ecs-shell">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="flex items-center gap-2.5">
        <span class="grid h-10 w-10 place-items-center rounded-2xl bg-white/95 shadow-[0_10px_24px_rgba(15,23,42,0.16)]">
          <img :src="logoEasyClubSportMark" alt="EasyClubSport" class="h-6 w-6" />
        </span>
        <div>
          <p class="text-sm font-semibold text-white/90">EasyClubSport</p>
          <p class="text-xs text-white/70">Sports Club Platform</p>
        </div>
      </div>

      <nav class="hidden flex-wrap items-center gap-1.5 lg:flex">
        <RouterLink
          v-for="link in links"
          :key="link.id"
          :to="link.to"
          class="rounded-full px-3.5 py-2 text-xs font-semibold transition"
          :class="
            activeKey === link.id
              ? 'bg-white text-[#1d4ed8] shadow-[0_10px_18px_rgba(255,255,255,0.18)]'
              : 'bg-white/0 text-white/90 hover:bg-white/12 hover:text-white'
          "
        >
          {{ link.label }}
        </RouterLink>
      </nav>

      <div class="flex items-center gap-2">
        <label class="relative hidden md:block">
          <input
            type="text"
            placeholder="Rechercher..."
            class="h-10 w-[190px] rounded-full border border-white/20 bg-white/12 px-4 py-2 text-sm text-white outline-none transition placeholder:text-white/60 focus:border-white/40 focus:bg-white/15"
          />
        </label>

        <button type="button" class="relative rounded-full border border-white/20 bg-white/12 px-3 py-2 text-xs font-semibold text-white/95 transition hover:bg-white/18">
          Alertes
          <span class="absolute -right-1 -top-1 h-2.5 w-2.5 rounded-full bg-[#ef4444] ring-2 ring-[#2446d8]"></span>
        </button>

        <div class="group relative">
          <button type="button" class="flex items-center gap-2 rounded-full border border-white/20 bg-white/12 p-1.5 pr-3 transition hover:bg-white/18">
            <AppAvatar
              :src="userDisplay.image"
              :alt="userDisplay.nom"
              :initials="userDisplay.initiales"
              size-class="h-8 w-8"
              rounded-class="rounded-full"
            />
            <span class="hidden max-w-[115px] truncate text-xs font-semibold text-white md:block">{{ userDisplay.nom }}</span>
          </button>

          <div class="invisible absolute right-0 top-12 z-50 w-[260px] rounded-[28px] border border-[#dbe4ff] bg-white p-4 text-[#111827] opacity-0 transition duration-200 group-hover:visible group-hover:opacity-100 shadow-[0_1px_0_rgba(17,24,39,0.05),0_24px_60px_-24px_rgba(15,23,42,0.45)]">
            <div class="flex items-center gap-3 border-b border-[#edf2ff] pb-3">
              <AppAvatar
                :src="userDisplay.image"
                :alt="userDisplay.nom"
                :initials="userDisplay.initiales"
              />
              <div>
                <p class="text-sm font-bold">{{ userDisplay.nom }}</p>
                <p class="text-xs capitalize text-[#6b7280]">{{ userDisplay.role }}</p>
                <p class="max-w-[150px] truncate text-xs text-[#6b7280]">{{ userDisplay.email }}</p>
              </div>
            </div>

            <RouterLink :to="profileRoute" class="mt-3 block rounded-full border border-[#dbe5f2] px-3 py-2 text-sm font-semibold text-[#1f2a44] transition hover:bg-[#f8faff]">
              Ouvrir profil
            </RouterLink>
            <AppButton type="button" block class="mt-2" @click="$emit('logout')">
              Se deconnecter
            </AppButton>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>
