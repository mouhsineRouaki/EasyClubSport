<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import blueBackground from '../../assets/Background.jpg'
import logoMark from '../../assets/logo-easyclubsport-mark.svg'
import { authGet } from '../../services/api'
import { notifyError } from '../../stores/toast'

const router = useRouter()
const route = useRoute()

const chargement = ref(true)
const chargementRafraichissement = ref(false)
const dashboard = ref(null)
const utilisateurConnecte = ref(null)
const rafraichissementAuto = ref(true)
const derniereMiseAJour = ref(null)
const intervalRafraichissement = ref(null)

const statistiques = computed(() => dashboard.value?.statistiques || {})
const clubsRecents = computed(() => dashboard.value?.clubs_recents || [])
const equipesRecentes = computed(() => dashboard.value?.equipes_recentes || [])
const prochainsEvenements = computed(() => dashboard.value?.prochains_evenements || [])

const liensGlobaux = [
  { label: 'Dashboard', to: '/president' },
  { label: 'About us', href: '#about-easyclubsport' },
  { label: 'Contact us', href: '#contact-support' },
]

const liensFonctionnalites = [
  { label: 'Dashboard', to: '/president/dashboard' },
  { label: 'Clubs', to: '/president/clubs' },
  { label: 'Equipes', to: '/president/equipes' },
  { label: 'Joueurs', to: '/president/joueurs' },
  { label: 'Evenements', to: '/president/evenements' },
  { label: 'Annonces', to: '/president/annonces' },
  { label: 'Documents', to: '/president/documents' },
  { label: 'Messagerie', to: '/president/messagerie' },
]

const statsCards = computed(() => [
  {
    label: 'Joueurs',
    value: statistiques.value.joueurs_total || 0,
    detail: 'Joueurs enregistres',
    accent: 'bg-[#2446d8]',
  },
  {
    label: 'Clubs',
    value: statistiques.value.clubs_total || 0,
    detail: 'Clubs geres',
    accent: 'bg-[#22c55e]',
  },
  {
    label: 'Equipes',
    value: statistiques.value.equipes_total || 0,
    detail: 'Equipes actives',
    accent: 'bg-[#4c6fff]',
  },
  {
    label: 'Coachs',
    value: statistiques.value.coachs_total || 0,
    detail: 'Coachs rattaches',
    accent: 'bg-[#f59e0b]',
  },
  {
    label: 'Evenements',
    value: statistiques.value.evenements_a_venir_total || 0,
    detail: 'A venir',
    accent: 'bg-[#06b6d4]',
  },
  {
    label: 'Cotisations',
    value: statistiques.value.cotisations_en_attente_total || 0,
    detail: 'En attente',
    accent: 'bg-[#ef4444]',
  },
])

const evenementsDashboard = computed(() => prochainsEvenements.value.slice(0, 4))
const evenementsCarousel = computed(() => (evenementsDashboard.value.length ? [...evenementsDashboard.value, ...evenementsDashboard.value] : []))
const cartesEquipesRecentes = computed(() => equipesRecentes.value.slice(0, 6))

const utilisateurResume = computed(() => {
  const utilisateur = utilisateurConnecte.value || {}
  const nomComplet = [utilisateur.prenom, utilisateur.nom].filter(Boolean).join(' ')

  return {
    nom: nomComplet || utilisateur.name || 'President',
    email: utilisateur.email || 'email indisponible',
    role: utilisateur.role || 'president',
    image: utilisateur.photo_url || utilisateur.photo || '',
  }
})

const formatDate = (date) => {
  if (!date) return '-'
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(new Date(date))
}

const formatDateHeure = (date) => {
  if (!date) {
    return 'Jamais'
  }

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'short',
    timeStyle: 'medium',
  }).format(new Date(date))
}

const imageEvenement = (evenement = {}) => {
  return evenement.image_url || evenement.photo_url || evenement.media_url || evenement.image || blueBackground
}

const backgroundEvenement = (evenement = {}) => {
  return `linear-gradient(180deg, rgba(7, 16, 58, 0.18), rgba(7, 16, 58, 0.86)), url(${imageEvenement(evenement)})`
}

const imageEquipe = (equipe = {}) => {
  return equipe.image_url || equipe.logo_url || equipe.photo_url || equipe.club?.logo_url || blueBackground
}

const backgroundEquipe = (equipe = {}) => {
  return `linear-gradient(145deg, rgba(8, 18, 72, 0.86), rgba(36, 70, 216, 0.64)), url(${imageEquipe(equipe)})`
}

const recupererDashboard = async (silencieux = false) => {
  if (silencieux) {
    chargementRafraichissement.value = true
  } else {
    chargement.value = true
  }

  try {
    const reponse = await authGet('/president/dashboard')
    dashboard.value = reponse?.data || null
    derniereMiseAJour.value = new Date().toISOString()
  } catch (error) {
    if (error?.response?.code === 401) {
      localStorage.removeItem('token_api')
      localStorage.removeItem('utilisateur_api')
      router.push('/login')
      return
    }

    notifyError(error?.response?.message || error.message || 'Impossible de recuperer le dashboard.')
  } finally {
    if (silencieux) {
      chargementRafraichissement.value = false
    } else {
      chargement.value = false
    }
  }
}

const arreterRafraichissementAuto = () => {
  if (intervalRafraichissement.value) {
    clearInterval(intervalRafraichissement.value)
    intervalRafraichissement.value = null
  }
}

const demarrerRafraichissementAuto = () => {
  arreterRafraichissementAuto()

  if (!rafraichissementAuto.value) {
    return
  }

  intervalRafraichissement.value = setInterval(() => {
    recupererDashboard(true)
  }, 30000)
}

const rafraichirMaintenant = () => {
  recupererDashboard(true)
}

const deconnecter = () => {
  localStorage.removeItem('token_api')
  localStorage.removeItem('utilisateur_api')
  router.push('/login')
}

const lienFonctionnelActif = (item) => route.path === item.to

onMounted(() => {
  const utilisateurStocke = localStorage.getItem('utilisateur_api')

  if (utilisateurStocke) {
    try {
      utilisateurConnecte.value = JSON.parse(utilisateurStocke)
    } catch {
      utilisateurConnecte.value = null
    }
  }

  recupererDashboard()
  demarrerRafraichissementAuto()
})

watch(rafraichissementAuto, () => {
  demarrerRafraichissementAuto()
})

onBeforeUnmount(() => {
  arreterRafraichissementAuto()
})
</script>

<template>
  <main class="min-h-screen bg-[#f4f6fb] font-['Manrope',Inter,sans-serif] text-[#111827]">
    <div class="mx-auto max-w-[1450px] px-2 pb-5 pt-2 sm:px-4 sm:pt-3">
      <section
        class="relative overflow-hidden rounded-[28px] border border-[#2a43cd] bg-[#2446d8] px-4 pb-[180px] pt-4 text-white sm:px-7 sm:pb-[196px] sm:pt-5">
        <img :src="blueBackground" alt="Blue shell background" class="absolute inset-0 h-full w-full object-cover" />

        <header
          class="relative z-10 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/15 bg-white/10 px-3 py-2 backdrop-blur-md">
          <RouterLink to="/president/dashboard" class="flex items-center gap-2.5">
            <img :src="logoMark" alt="EasySportClub" class="h-10 w-10 rounded-xl bg-white/95 p-2" />
            <span class="text-lg font-bold">EasySportClub</span>
          </RouterLink>

          <nav class="flex flex-wrap items-center gap-2">
            <RouterLink v-for="item in liensGlobaux.filter((lien) => lien.to)" :key="item.to" :to="item.to"
              class="rounded-full border border-white/25 bg-white/10 px-4 py-1.5 text-[11px] font-semibold text-white/95 transition hover:bg-white/20">
              {{ item.label }}
            </RouterLink>
            <a v-for="item in liensGlobaux.filter((lien) => lien.href)" :key="item.href" :href="item.href"
              class="rounded-full border border-white/25 bg-white/10 px-4 py-1.5 text-[11px] font-semibold text-white/95 transition hover:bg-white/20">
              {{ item.label }}
            </a>
            <button type="button"
              class="rounded-full bg-white px-4 py-1.5 text-[11px] font-bold text-[#1f36bf] transition hover:bg-[#eef2ff]"
              @click="deconnecter">
              Deconnexion
            </button>
          </nav>
        </header>

        <div class="relative z-10 mx-auto mt-10 max-w-4xl text-center sm:mt-14">
          <h1 class="font-['Alfa_Slab_One'] text-3xl font-black leading-[1.16] tracking-normal sm:text-6xl">
            Pilotez votre club sportif
            <br class="hidden sm:block" />
            avec une interface claire
          </h1>
          <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">
            Le bleu garde l'identite du produit. Le grand espace blanc devient votre zone de travail pour toutes les
            fonctionnalites president.
          </p>

          <div class="mt-6 flex flex-wrap items-center justify-center gap-2.5">
            <RouterLink to="/president/clubs"
              class="rounded-full bg-white px-6 py-2 text-sm font-bold text-[#1f36bf] transition hover:bg-[#eef2ff]">
              Gerer les clubs</RouterLink>
            <RouterLink to="/president/evenements"
              class="rounded-full border border-white/35 bg-white/8 px-6 py-2 text-sm font-semibold text-white transition hover:bg-white/20">
              Voir les evenements</RouterLink>
          </div>
        </div>
      </section>

      <section class="relative -mt-[154px] z-30 min-h-screen pb-0">
        <article
          class="sticky top-2 z-40 mx-auto w-full max-w-[1220px] rounded-[24px] border border-[#e6ebf8] bg-white text-[#111827] shadow-[0_1px_0_rgba(17,24,39,0.04),0_36px_70px_-54px_rgba(15,23,42,0.55)]">
          <div>
            <div
              class="sticky top-0 z-30 flex flex-wrap items-center justify-between gap-3 rounded-t-[24px] border-b border-[#ecf0f9] bg-white/95 px-3 py-3 backdrop-blur-md sm:px-4">
              <div class="flex min-w-0 flex-1 flex-wrap items-center gap-2 text-[11px]">
                <RouterLink v-for="item in liensFonctionnalites" :key="item.to" :to="item.to"
                  class="group inline-flex items-center gap-2 rounded-full border px-2.5 py-1.5 font-bold transition"
                  :class="lienFonctionnelActif(item) ? 'border-[#d8e2fb] bg-[#f2f6ff] text-[#1f36bf]' : 'border-transparent text-[#6b7280] hover:border-[#dce4f7] hover:bg-[#f8fbff] hover:text-[#1f2a44]'">

                  {{ item.label }}
                </RouterLink>
              </div>

              <div class="flex items-center gap-2">
                <label class="relative hidden sm:block">
                  <input type="text" placeholder="Search"
                    class="h-8 w-[165px] rounded-full border border-[#dbe2ef] bg-white px-3 py-1 text-xs text-[#1f2a44] outline-none placeholder:text-[#94a3b8]" />
                </label>
                <span class="h-2.5 w-2.5 rounded-full bg-[#ef4444]"></span>
                <img v-if="utilisateurResume.image" :src="utilisateurResume.image" :alt="utilisateurResume.nom"
                  class="h-8 w-8 rounded-full object-cover" />
                <span v-else
                  class="block h-8 w-8 rounded-full bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)] ring-1 ring-[#dbe2ef]"></span>
              </div>
            </div>

            <div class="px-3 py-4 sm:px-5 sm:py-5">


              <div v-if="chargement" class="mt-6 space-y-4">
                <div class="h-28 animate-pulse rounded-3xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
                <div class="h-56 animate-pulse rounded-3xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
                <div class="h-44 animate-pulse rounded-3xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
              </div>

              <template v-else>
                <section class="mt-6">
                  <div class="text-center">
                    <h3 class="font-['Alfa_Slab_One'] text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">
                      Statistiques principales</h3>
                    <p class="mx-auto mt-1 max-w-xl text-xs font-semibold text-[#6b7280]">Etat rapide de l'organisation
                      sportive.</p>
                    <span
                      class="mt-3 inline-flex rounded-full bg-[#f2f6ff] px-3 py-1 text-[11px] font-bold text-[#1f36bf]">
                      Maj {{ formatDateHeure(derniereMiseAJour) }}
                    </span>
                  </div>

                  <div class="mt-5 flex flex-wrap justify-center gap-2.5">
                    <article v-for="card in statsCards" :key="card.label"
                      class="inline-flex min-w-[150px] items-center justify-between gap-4 rounded-full border border-[#e6edf8] bg-white px-4 py-3 transition hover:border-[#cfdaf2]">
                      <span class="text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">{{ card.label
                        }}</span>
                      <strong class="text-2xl font-black tracking-[-0.05em] text-[#111827]">{{ card.value }}</strong>
                    </article>
                  </div>
                </section>

                <section class="mt-7 rounded-[22px] border border-[#e6edf8] bg-white p-4">
                  <div class="text-center">
                    <h3 class="font-['Alfa_Slab_One'] text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">
                      Evenements proches</h3>
                    <p class="mx-auto mt-1 max-w-xl text-xs font-semibold text-[#6b7280]">Les prochains rendez-vous a
                      suivre en priorite.</p>
                    <RouterLink to="/president/evenements"
                      class="mt-3 inline-flex rounded-full border border-[#dbe2ef] px-3 py-1.5 text-xs font-extrabold text-[#1f36bf] transition hover:bg-[#f8fbff]">
                      Voir tous
                    </RouterLink>
                  </div>

                  <div v-if="evenementsDashboard.length"
                    class="mt-5 overflow-hidden rounded-[30px] bg-[#f7f9ff] p-3 [mask-image:linear-gradient(90deg,transparent,black_8%,black_92%,transparent)]">
                    <div class="dashboard-event-carousel flex w-max gap-4">
                      <article v-for="(evenement, index) in evenementsCarousel" :key="`${evenement.id}-${index}`"
                        class="relative h-[270px] w-[250px] shrink-0 overflow-hidden rounded-[30px] border border-white/60 bg-cover bg-center p-4 text-white sm:w-[320px]"
                        :style="{ backgroundImage: backgroundEvenement(evenement) }">
                        <div
                          class="absolute inset-0 bg-[radial-gradient(circle_at_25%_10%,rgba(255,255,255,0.34),transparent_28%),linear-gradient(180deg,transparent,rgba(0,0,0,0.22))]">
                        </div>
                        <div class="relative z-10 flex h-full flex-col items-center justify-center text-center">
                          <span
                            class="rounded-full border border-white/35 bg-white/18 px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] text-white backdrop-blur-md">
                            {{ formatDate(evenement.date_debut) }}
                          </span>
                          <h4
                            class="mt-4 font-['Alfa_Slab_One'] text-3xl font-black leading-tight tracking-normal text-white sm:text-4xl">
                            {{ evenement.titre }}
                          </h4>
                          <p class="mt-3 max-w-[220px] text-xs font-semibold leading-5 text-white/78">
                            {{ evenement.equipe?.nom || 'Equipe non definie' }}
                            <span v-if="evenement.lieu"> - {{ evenement.lieu }}</span>
                          </p>
                          <RouterLink to="/president/evenements"
                            class="mt-5 rounded-full bg-white px-5 py-2 text-xs font-black text-[#1f36bf] transition hover:bg-[#eef4ff]">
                            Ouvrir
                          </RouterLink>
                        </div>
                      </article>
                    </div>
                  </div>

                  <p v-else
                    class="mt-4 rounded-2xl border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-4 py-8 text-center text-sm font-semibold text-[#6b7280]">
                    Aucun evenement proche pour le moment.
                  </p>
                </section>

                <section class="mt-7 rounded-[22px] border border-[#e6edf8] bg-white p-4">
                  <div class="text-center">
                    <div
                      class="mx-auto grid h-11 w-11 place-items-center rounded-2xl bg-[#ecfdf5] text-[#22c55e] ring-1 ring-[#bbf7d0]">
                      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 20V9l8-5 8 5v11M8 20v-7h8v7" stroke="currentColor" stroke-width="2"
                          stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </div>
                    <h3
                      class="mt-3 font-['Bebas_Neue'] text-4xl font-black tracking-[0.04em] text-[#111827] sm:text-5xl">
                      Clubs recents</h3>
                    <p class="mx-auto mt-1 max-w-xl text-xs font-semibold text-[#6b7280]">Derniers clubs ajoutes ou
                      modifies.</p>
                    <RouterLink to="/president/clubs"
                      class="rounded-full border border-[#dbe2ef] px-3 py-1.5 text-xs font-extrabold text-[#1f36bf] transition hover:bg-[#f8fbff]">
                      Gerer clubs
                    </RouterLink>
                  </div>

                  <div v-if="clubsRecents.length"
                    class="mt-4 divide-y divide-[#edf2fb] rounded-2xl border border-[#e9eef9]">
                    <article v-for="club in clubsRecents.slice(0, 5)" :key="club.id"
                      class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                      <div class="flex items-center gap-3">
                        <img v-if="club.logo_url" :src="club.logo_url" :alt="club.nom"
                          class="h-12 w-12 rounded-2xl object-cover ring-1 ring-[#e6edf8]" />
                        <span v-else
                          class="grid h-12 w-12 place-items-center rounded-2xl bg-[#eef4ff] text-sm font-black text-[#2446d8]">
                          {{ initialesNom(club.nom) }}
                        </span>
                        <div>
                          <p class="text-sm font-black text-[#111827]">{{ club.nom }}</p>
                          <p class="mt-1 text-xs font-semibold text-[#6b7280]">
                            {{ club.ville || 'Ville non definie' }} - {{ club.email || 'Email non defini' }}
                          </p>
                        </div>
                      </div>

                      <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-[#f2f6ff] px-3 py-1 text-xs font-extrabold text-[#1f36bf]">
                          {{ club.equipes_total || 0 }} equipes
                        </span>
                        <RouterLink to="/president/clubs"
                          class="rounded-full bg-[#111827] px-4 py-2 text-xs font-extrabold text-white transition hover:bg-[#1f36bf]">
                          Details
                        </RouterLink>
                      </div>
                    </article>
                  </div>

                  <p v-else
                    class="mt-4 rounded-2xl border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-4 py-8 text-center text-sm font-semibold text-[#6b7280]">
                    Aucun club recent disponible.
                  </p>
                </section>
              </template>
            </div>
          </div>
        </article>

        <div class="mx-auto grid w-full max-w-[1220px] gap-3 px-1 pt-4 text-white/85 sm:grid-cols-2">
          <section id="about-easyclubsport" class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-md">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-white/60">About us</p>
            <h2 class="mt-2 text-lg font-extrabold text-white">EasySportClub centralise la gestion sportive.</h2>
            <p class="mt-2 text-sm leading-6 text-white/70">
              Le conteneur blanc reste l'espace principal pour les clubs, equipes, joueurs, evenements, documents,
              annonces et
              messages.
            </p>
          </section>

          <section id="contact-support" class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-md">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-white/60">Contact us</p>
            <h2 class="mt-2 text-lg font-extrabold text-white">Besoin d'aide sur la plateforme ?</h2>
            <p class="mt-2 text-sm leading-6 text-white/70">
              Utilisez la messagerie ou le profil president pour gerer les informations et suivre les prochaines
              integrations.
            </p>
          </section>
        </div>
      </section>
    </div>
  </main>
</template>

<style scoped>
.dashboard-event-carousel {
  animation: dashboard-event-marquee 34s linear infinite;
}

.dashboard-event-carousel:hover {
  animation-play-state: paused;
}

@keyframes dashboard-event-marquee {
  from {
    transform: translateX(0);
  }

  to {
    transform: translateX(calc(-50% - 0.5rem));
  }
}

@media (prefers-reduced-motion: reduce) {
  .dashboard-event-carousel {
    animation: none;
  }
}
</style>
