<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import logoEasyClubSportMark from '../../assets/logo-easyclubsport-mark.svg'
import { lireUtilisateurStocke } from '../../composables/useAuthSession'

const props = defineProps({
  breadcrumb: {
    type: String,
    default: 'Dashboard',
  },
  title: {
    type: String,
    default: 'Dashboard',
  },
  activeSection: {
    type: String,
    default: 'dashboard',
  },
  utilisateur: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['logout'])

const sidebarOpen = ref(false)
const utilisateurLocal = ref(null)

const sidebarLinks = [
  { id: 'dashboard', label: 'Dashboard', icon: 'DB', to: '/president/dashboard' },
  { id: 'clubs', label: 'Clubs', icon: 'CL', to: '/president/clubs' },
  { id: 'equipes', label: 'Equipes', icon: 'EQ', to: '/president/equipes' },
  { id: 'joueurs', label: 'Joueurs', icon: 'JR', to: '/president/joueurs' },
  { id: 'evenements', label: 'Evenements', icon: 'EV', to: '/president/evenements' },
  { id: 'annonces', label: 'Annonces', icon: 'AN', to: '/president/annonces' },
  { id: 'documents', label: 'Documents', icon: 'DO', to: '/president/documents' },
  { id: 'messagerie', label: 'Messagerie', icon: 'MS', to: '/president/messagerie' },
  { id: 'profil', label: 'Profil', icon: 'PR', to: '/president/profil' },
]

const utilisateurActif = computed(() => props.utilisateur || utilisateurLocal.value || {})

const profilConnecte = computed(() => {
  const utilisateur = utilisateurActif.value || {}
  const nomComplet = [utilisateur.prenom, utilisateur.nom].filter(Boolean).join(' ')

  return {
    nom: nomComplet || utilisateur.name || 'President',
    email: utilisateur.email || 'email non disponible',
    role: utilisateur.role || 'president',
    image: utilisateur.photo_url || utilisateur.photo || '',
    initiales: ((utilisateur.prenom?.[0] || '') + (utilisateur.nom?.[0] || '')).toUpperCase() || 'PR',
  }
})

const rafraichirUtilisateurLocal = () => {
  utilisateurLocal.value = lireUtilisateurStocke()
}

watch(
  () => props.utilisateur,
  (valeur) => {
    if (valeur) {
      utilisateurLocal.value = valeur
    }
  },
  { deep: true }
)

onMounted(() => {
  rafraichirUtilisateurLocal()
})
</script>

<template>
  <main class="min-h-screen bg-[radial-gradient(circle_at_top_left,#dbeafe_0%,#f8fafc_42%,#f8fafc_100%)] text-[#0f172a]">
    <div class="min-h-screen">
      <div
        v-if="sidebarOpen"
        class="fixed inset-0 z-40 bg-slate-950/35 backdrop-blur-[1px] lg:hidden"
        @click="sidebarOpen = false"
      ></div>

      <aside
        class="fixed inset-y-0 left-0 z-50 w-[274px] border-r border-white/60 bg-white/85 px-4 pb-5 pt-4 shadow-[0_22px_55px_rgba(15,23,42,0.12)] backdrop-blur-xl transition-transform lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
      >
        <div class="mb-4 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <img :src="logoEasyClubSportMark" alt="EasyClubSport" class="h-9 w-9 rounded-xl border border-[#d9e4ff] bg-white p-1.5" />
            <div>
              <p class="text-sm font-bold text-[#1f2a44]">EasyClubSport</p>
              <p class="text-[11px] text-[#64748b]">President Console</p>
            </div>
          </div>
          <button
            type="button"
            class="grid h-8 w-8 place-items-center rounded-lg border border-[#dbe2ef] text-[#64748b] lg:hidden"
            @click="sidebarOpen = false"
          >
            ✕
          </button>
        </div>

        <div class="h-px bg-[linear-gradient(90deg,transparent,#d7e1f5,transparent)]"></div>

        <nav class="mt-4 space-y-1.5">
          <RouterLink
            v-for="link in sidebarLinks"
            :key="link.id"
            :to="link.to"
            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
            :class="
              activeSection === link.id
                ? 'bg-[linear-gradient(120deg,#0f172a_0%,#1e293b_100%)] text-white shadow-[0_12px_28px_rgba(15,23,42,0.3)]'
                : 'text-[#475569] hover:bg-[#edf2ff] hover:text-[#0f172a]'
            "
            @click="sidebarOpen = false"
          >
            <span
              class="grid h-8 w-8 place-items-center rounded-lg text-[10px] font-bold"
              :class="activeSection === link.id ? 'bg-white/20 text-white' : 'bg-[#f1f5f9] text-[#334155]'"
            >
              {{ link.icon }}
            </span>
            {{ link.label }}
          </RouterLink>
        </nav>

        <div class="mt-6 rounded-2xl border border-white/40 bg-[linear-gradient(140deg,#0f172a_0%,#1e293b_45%,#334155_100%)] p-4 text-white shadow-[0_12px_24px_rgba(15,23,42,0.35)]">
          <p class="text-sm font-bold">Espace president</p>
          <p class="mt-1 text-xs text-white/75">Pilotage clubs, equipes et operations.</p>
          <RouterLink
            to="/president/dashboard"
            class="mt-4 block rounded-lg bg-white/95 px-3 py-2 text-center text-[11px] font-bold text-[#0f172a]"
            @click="sidebarOpen = false"
          >
            OUVRIR DASHBOARD
          </RouterLink>
        </div>
      </aside>

      <section class="px-4 pb-6 pt-4 sm:px-6 lg:ml-[274px] lg:px-7">
        <header class="mb-5 rounded-2xl border border-white/70 bg-white/85 px-4 py-3 shadow-[0_18px_32px_rgba(15,23,42,0.08)] backdrop-blur-xl sm:px-5">
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-start gap-3">
              <button
                type="button"
                class="grid h-10 w-10 place-items-center rounded-xl border border-[#dbe2ef] bg-white text-[#475569] lg:hidden"
                @click="sidebarOpen = true"
              >
                ☰
              </button>
              <div>
                <p class="text-xs text-[#64748b]">
                  Pages
                  <span class="px-1 text-[#334155]">/</span>
                  <span class="font-semibold text-[#1f2a44]">{{ breadcrumb }}</span>
                </p>
                <h1 class="mt-1 text-lg font-bold text-[#1f2a44]">{{ title }}</h1>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <label class="relative hidden sm:block">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[#94a3b8]">
                  <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none">
                    <path d="M9.2 15.4a6.2 6.2 0 1 0 0-12.4 6.2 6.2 0 0 0 0 12.4Zm4.5-1.1 3.3 3.3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                  </svg>
                </span>
                <input
                  type="text"
                  placeholder="Rechercher..."
                  class="h-10 w-[260px] rounded-xl border border-[#dbe2ef] bg-white px-3 py-2 pl-10 text-sm text-[#1f2a44] outline-none transition placeholder:text-[#94a3b8] focus:border-[#2563eb] focus:ring-2 focus:ring-[#2563eb]/15"
                />
              </label>

              <button class="relative rounded-xl border border-[#dbe2ef] bg-white px-3 py-2 text-xs font-semibold text-[#475569]" type="button" aria-label="Notifications">
                Alertes
                <span class="absolute -right-1 -top-1 h-2.5 w-2.5 rounded-full bg-[#ef4444] ring-2 ring-white"></span>
              </button>

              <div class="group relative">
                <button class="flex items-center gap-2 rounded-xl border border-[#dbe2ef] bg-white px-2 py-1.5" type="button" aria-label="Profile menu">
                  <img
                    v-if="profilConnecte.image"
                    :src="profilConnecte.image"
                    :alt="profilConnecte.nom"
                    class="h-8 w-8 rounded-lg object-cover"
                  />
                  <span v-else class="grid h-8 w-8 place-items-center rounded-lg bg-[linear-gradient(130deg,#1d4ed8,#0ea5e9)] text-xs font-bold text-white">
                    {{ profilConnecte.initiales }}
                  </span>
                  <span class="hidden max-w-[120px] truncate text-xs font-semibold text-[#334155] sm:block">{{ profilConnecte.nom }}</span>
                </button>

                <div class="invisible absolute right-0 top-11 z-40 w-[255px] rounded-2xl border border-[#dbe2ef] bg-white p-4 opacity-0 shadow-[0_22px_40px_rgba(15,23,42,0.18)] transition duration-200 group-hover:visible group-hover:opacity-100">
                  <div class="flex items-center gap-3 border-b border-[#edf2f7] pb-3">
                    <img
                      v-if="profilConnecte.image"
                      :src="profilConnecte.image"
                      :alt="profilConnecte.nom"
                      class="h-11 w-11 rounded-xl object-cover"
                    />
                    <span v-else class="grid h-11 w-11 place-items-center rounded-xl bg-[linear-gradient(130deg,#1d4ed8,#0ea5e9)] text-sm font-bold text-white">
                      {{ profilConnecte.initiales }}
                    </span>
                    <div>
                      <p class="text-sm font-bold text-[#1f2a44]">{{ profilConnecte.nom }}</p>
                      <p class="text-xs capitalize text-[#64748b]">{{ profilConnecte.role }}</p>
                      <p class="max-w-[150px] truncate text-xs text-[#64748b]">{{ profilConnecte.email }}</p>
                    </div>
                  </div>

                  <RouterLink to="/president/profil" class="mt-3 block rounded-lg px-3 py-2 text-sm font-semibold text-[#475569] transition hover:bg-[#eff6ff] hover:text-[#1d4ed8]">
                    Voir profil
                  </RouterLink>

                  <button
                    type="button"
                    class="mt-2 w-full rounded-lg bg-[linear-gradient(120deg,#0f172a_0%,#1e293b_100%)] px-3 py-2 text-sm font-bold text-white"
                    @click="emit('logout')"
                  >
                    Se deconnecter
                  </button>
                </div>
              </div>
            </div>
          </div>
        </header>

        <slot />
      </section>
    </div>
  </main>
</template>
