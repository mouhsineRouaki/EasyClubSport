<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import AppBlueShell from '../layout/AppBlueShell.vue'
import AppTopNavbar from '../layout/AppTopNavbar.vue'
import AppWorkspace from '../layout/AppWorkspace.vue'

const props = defineProps({
  title: {
    type: String,
    default: 'Dashboard coach',
  },
  subtitle: {
    type: String,
    default: '',
  },
  activeTab: {
    type: String,
    default: 'dashboard',
  },
  user: {
    type: Object,
    default: () => ({}),
  },
})

const emit = defineEmits(['logout'])
const router = useRouter()

const links = [
  { id: 'dashboard', label: 'Dashboard', to: '/coach/dashboard' },
  { id: 'profil', label: 'Profil', to: '/coach/profil' },
  { id: 'equipes', label: 'Equipes', to: '/coach/equipes' },
  { id: 'joueurs', label: 'Joueurs', to: '/coach/joueurs' },
  { id: 'evenements', label: 'Evenements', to: '/coach/evenements' },
  { id: 'messagerie', label: 'Messagerie', to: '/coach/messagerie' },
]

const tabs = computed(() => links)

const deconnecter = () => {
  emit('logout')
}

const retourDashboard = () => {
  router.push('/coach/dashboard')
}
</script>

<template>
  <AppBlueShell>
    <AppTopNavbar :links="links" :active-key="activeTab" :user="user" profile-route="/coach/profil" @logout="deconnecter" />

    <AppWorkspace :title="title" :subtitle="subtitle" :tabs="tabs" :active-tab="activeTab">
      <template #actions>
        <button
          v-if="activeTab !== 'dashboard'"
          type="button"
          class="rounded-full border border-[#d7e1fb] bg-white px-4 py-2 text-xs font-semibold text-[#2446d8] transition hover:bg-[#f6f8ff]"
          @click="retourDashboard"
        >
          Retour dashboard
        </button>
      </template>

      <slot />
    </AppWorkspace>
  </AppBlueShell>
</template>
