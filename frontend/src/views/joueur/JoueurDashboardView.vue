<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import blueBackground from '../../assets/Background.jpg'
import logoMark from '../../assets/logo-easyclubsport-mark.svg'
import { authGet, authPost, authPut } from '../../services/api'
import { notifyError, notifySuccess } from '../../stores/toast'
import { subscribeToCanalMessages, disconnectRealtime } from '../../services/realtime'

import JoueurDashboardHome from '../../components/joueur/JoueurDashboardHome.vue'
import JoueurEvenements from '../../components/joueur/JoueurEvenements.vue'
import JoueurConvocations from '../../components/joueur/JoueurConvocations.vue'
import JoueurMessagerie from '../../components/joueur/JoueurMessagerie.vue'

const router = useRouter()

const chargement = ref(true)
const chargementRafraichissement = ref(false)
const moduleActif = ref('dashboard')
const utilisateurConnecte = ref(null)
const notificationsJoueur = ref([])
const notificationsNonLuesTotal = ref(0)
const chargementNotifications = ref(false)
const notificationOuverte = ref(false)
const derniereMiseAJour = ref(null)
const intervalRafraichissement = ref(null)

const equipe = ref(null)
const evenementsEquipeDashboard = ref([])
const evenementsEquipeModule = ref([])
const convocationsEquipe = ref([])
const chargementEvenements = ref(false)
const chargementConvocations = ref(false)
const rechercheEvenements = ref('')
const rechercheConvocations = ref('')
const debounceEvenements = ref(null)
const modalRejoindreEquipeVisible = ref(false)
const codeInvitationEquipe = ref('')
const chargementRejoindreEquipe = ref(false)
const erreurCodeInvitation = ref('')

const chargementCanaux = ref(false)
const canaux = ref([])
const canalSelectionne = ref(null)
const chargementMessages = ref(false)
const messages = ref([])
const envoiMessage = ref(false)
const stopRealtimeJoueur = ref(() => {})

const notificationsRecentes = computed(() => notificationsJoueur.value.slice(0, 6))

const liensFonctionnalites = [
  { key: 'dashboard', label: 'Dashboard' },
  { key: 'evenements', label: 'Evenements' },
  { key: 'convocations', label: 'Convocations' },
  { key: 'messagerie', label: 'Messagerie' },
]

const liensGlobaux = [
  { label: 'Dashboard', to: '/joueur/dashboard' },
  { label: 'About us', href: '#about-easyclubsport' },
  { label: 'Contact us', href: '#contact-support' },
]

const utilisateurResume = computed(() => {
  const u = utilisateurConnecte.value || {}
  return {
    nom: [u.prenom, u.nom].filter(Boolean).join(' ') || u.name || 'Joueur',
    email: u.email || '',
    image: u.photo_url || u.photo || '',
  }
})

const rechercheNavigation = computed({
  get() {
    if (moduleActif.value === 'evenements') return rechercheEvenements.value
    if (moduleActif.value === 'convocations') return rechercheConvocations.value
    return ''
  },
  set(value) {
    if (moduleActif.value === 'evenements') { rechercheEvenements.value = value; return }
    if (moduleActif.value === 'convocations') { rechercheConvocations.value = value }
  },
})

const formatDateHeure = (date) => {
  if (!date) return 'Jamais'
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'short', timeStyle: 'medium' }).format(new Date(date))
}

const normaliserMessage = (message) => {
  if (!message) return null

  const expediteur = message.expediteur
    ? {
        ...message.expediteur,
        nom:
          message.expediteur.nom ||
          [message.expediteur.prenom, message.expediteur.nom_famille].filter(Boolean).join(' ') ||
          message.expediteur.name ||
          '',
      }
    : utilisateurConnecte.value && String(message.expediteur_id) === String(utilisateurConnecte.value.id)
      ? {
          id: utilisateurConnecte.value.id,
          nom: [utilisateurConnecte.value.prenom, utilisateurConnecte.value.nom].filter(Boolean).join(' ') || utilisateurConnecte.value.name || 'Utilisateur',
          email: utilisateurConnecte.value.email || '',
        }
      : null

  return {
    id: message.id,
    canal_id: message.canal_id || canalSelectionne.value?.id || null,
    equipe_id: message.equipe_id || canalSelectionne.value?.equipe_id || canalSelectionne.value?.equipe?.id || null,
    expediteur_id: message.expediteur_id,
    contenu: message.contenu,
    type_message: message.type_message,
    expediteur,
    equipe: message.equipe || null,
    club: message.club || null,
    created_at: message.created_at || new Date().toISOString(),
    updated_at: message.updated_at || message.created_at || new Date().toISOString(),
  }
}

const pousserMessage = (message) => {
  const messageNormalise = normaliserMessage(message)

  if (!messageNormalise?.id) return

  const indexExistant = messages.value.findIndex((item) => String(item.id) === String(messageNormalise.id))
  if (indexExistant >= 0) {
    const copie = [...messages.value]
    copie[indexExistant] = {
      ...copie[indexExistant],
      ...messageNormalise,
      expediteur: messageNormalise.expediteur || copie[indexExistant].expediteur,
    }
    messages.value = copie
    return
  }

  messages.value = [...messages.value, messageNormalise]
}

const afficherModule = async (key) => {
  moduleActif.value = key
  if (key === 'evenements') {
    if (!equipe.value) await chargerDashboard()
    await chargerEvenements()
  }
  if (key === 'convocations') {
    if (!equipe.value) await chargerDashboard()
    await chargerConvocations()
  }
  if (key === 'messagerie') await chargerCanaux()
}

const actualiserModuleActif = async () => {
  chargementRafraichissement.value = true
  try {
    if (moduleActif.value === 'dashboard') await chargerDashboard()
    else if (moduleActif.value === 'evenements') await chargerEvenements()
    else if (moduleActif.value === 'convocations') await chargerConvocations()
    else if (moduleActif.value === 'messagerie') await chargerCanaux()
    derniereMiseAJour.value = new Date().toISOString()
  } finally {
    chargementRafraichissement.value = false
  }
}

const chargerDashboard = async () => {
  chargement.value = true
  try {
    const [repDash, repProfil] = await Promise.all([
      authGet('/joueur/dashboard'),
      authGet('/auth/moi'),
    ])
    equipe.value = repDash?.data?.equipe || null
    evenementsEquipeDashboard.value = repDash?.data?.evenements || []
    convocationsEquipe.value = repDash?.data?.convocations || []
    utilisateurConnecte.value = repProfil?.data?.utilisateur || repProfil?.data || null
    derniereMiseAJour.value = new Date().toISOString()
  } catch (error) {
    if (error?.response?.code === 401 || error?.status === 401) {
      localStorage.removeItem('token_api')
      localStorage.removeItem('utilisateur_api')
      router.push('/login')
      return
    }
    notifyError(error?.response?.message || error.message || 'Impossible de charger le dashboard.')
  } finally {
    chargement.value = false
  }
}

const chargerNotifications = async () => {
  chargementNotifications.value = true
  try {
    const rep = await authGet('/joueur/notifications')
    notificationsJoueur.value = rep?.data?.notifications || []
    notificationsNonLuesTotal.value = notificationsJoueur.value.filter((n) => !n.est_lue).length
  } catch {
    // silencieux
  } finally {
    chargementNotifications.value = false
  }
}

const marquerNotificationLue = async (notification) => {
  if (notification.est_lue) return
  try {
    await authPut(`/joueur/notifications/${notification.id}/lecture`)
    notification.est_lue = true
    notificationsNonLuesTotal.value = Math.max(0, notificationsNonLuesTotal.value - 1)
  } catch { /* silencieux */ }
}

const chargerEvenements = async () => {
  chargementEvenements.value = true
  try {
    const rep = await authGet('/joueur/evenements', { q: rechercheEvenements.value })
    evenementsEquipeModule.value = rep?.data?.evenements || []
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les evenements.')
  } finally {
    chargementEvenements.value = false
  }
}

const chargerConvocations = async () => {
  chargementConvocations.value = true
  try {
    const rep = await authGet('/joueur/convocations')
    convocationsEquipe.value = rep?.data?.convocations || []
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les convocations.')
  } finally {
    chargementConvocations.value = false
  }
}

const repondreConvocation = async ({ convocation, reponse }) => {
  try {
    await authPut(`/joueur/convocations/${convocation.id}`, { statut: reponse })
    convocation.statut = reponse
    notifySuccess(reponse === 'confirme' ? 'Convocation confirmee.' : 'Convocation refusee.')
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de repondre a la convocation.')
  }
}

const chargerCanaux = async () => {
  chargementCanaux.value = true
  try {
    const rep = await authGet('/joueur/canaux')
    canaux.value = rep?.data?.canaux || []
    if (!canalSelectionne.value && canaux.value.length) await selectionnerCanal(canaux.value[0])
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les canaux.')
  } finally {
    chargementCanaux.value = false
  }
}

const selectionnerCanal = async (canal) => {
  stopRealtimeJoueur.value()
  canalSelectionne.value = canal
  chargementMessages.value = true
  try {
    const rep = await authGet(`/joueur/canaux/${canal.id}/messages`)
    messages.value = (rep?.data?.messages || []).map(normaliserMessage).filter(Boolean)
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les messages.')
  } finally {
    chargementMessages.value = false
  }

  const id = canal.id
  if (!id) return

  stopRealtimeJoueur.value = subscribeToCanalMessages(id, (payload) => {
    const msg = payload?.message || payload
    pousserMessage(msg)
  })
}

const envoyerMessage = async (contenu) => {
  if (!canalSelectionne.value) return
  envoiMessage.value = true
  try {
    const rep = await authPost(`/joueur/canaux/${canalSelectionne.value.id}/messages`, { contenu })
    pousserMessage(rep?.data?.message)
  } catch (error) {
    notifyError(error?.response?.message || error.message || "Impossible d'envoyer le message.")
  } finally {
    envoiMessage.value = false
  }
}

const ouvrirModalRejoindreEquipe = () => {
  erreurCodeInvitation.value = ''
  codeInvitationEquipe.value = ''
  modalRejoindreEquipeVisible.value = true
}

const fermerModalRejoindreEquipe = () => {
  if (chargementRejoindreEquipe.value) return
  modalRejoindreEquipeVisible.value = false
  erreurCodeInvitation.value = ''
  codeInvitationEquipe.value = ''
}

const rejoindreEquipe = async () => {
  erreurCodeInvitation.value = ''

  const code = codeInvitationEquipe.value.trim().toUpperCase()
  if (!code) {
    erreurCodeInvitation.value = 'Veuillez saisir le code d invitation.'
    return
  }

  chargementRejoindreEquipe.value = true
  try {
    await authPost('/joueur/rejoindre-equipe', { code_invitation: code })
    notifySuccess('Equipe rejointe avec succes.')
    fermerModalRejoindreEquipe()
    await chargerDashboard()
    await chargerNotifications()
  } catch (error) {
    erreurCodeInvitation.value =
      error?.response?.errors?.code_invitation?.[0] ||
      error?.response?.message ||
      error?.message ||
      'Impossible de rejoindre cette equipe.'
  } finally {
    chargementRejoindreEquipe.value = false
  }
}

const demarrerRafraichissementAuto = () => {
  intervalRafraichissement.value = setInterval(async () => {
    await chargerNotifications()
    if (moduleActif.value === 'dashboard') {
      chargementRafraichissement.value = true
      await chargerDashboard()
      chargementRafraichissement.value = false
    }
  }, 60000)
}

const arreterRafraichissementAuto = () => {
  if (intervalRafraichissement.value) {
    clearInterval(intervalRafraichissement.value)
    intervalRafraichissement.value = null
  }
}

const deconnecter = () => {
  localStorage.removeItem('token_api')
  localStorage.removeItem('utilisateur_api')
  router.push('/login')
}

watch(rechercheEvenements, () => {
  clearTimeout(debounceEvenements.value)
  debounceEvenements.value = setTimeout(() => {
    if (moduleActif.value === 'evenements') chargerEvenements()
  }, 350)
})

onMounted(async () => {
  await chargerDashboard()
  await chargerNotifications()
  demarrerRafraichissementAuto()
})

onBeforeUnmount(() => {
  arreterRafraichissementAuto()
  clearTimeout(debounceEvenements.value)
  stopRealtimeJoueur.value()
  disconnectRealtime()
})
</script>

<template>
  <main class="min-h-screen bg-[#f4f6fb] font-['Plus_Jakarta_Sans',Inter,sans-serif] text-[#111827]">
    <div class="mx-auto max-w-[1450px] px-2 pb-5 pt-2 sm:px-4 sm:pt-3">

      <section class="relative overflow-hidden rounded-[28px] border border-[#2a43cd] bg-[#2446d8] px-4 pb-[180px] pt-4 text-white sm:px-7 sm:pb-[196px] sm:pt-5">
        <img :src="blueBackground" alt="Background" class="absolute inset-0 h-full w-full object-cover" />

        <header class="relative z-10 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/15 bg-white/10 px-3 py-2 backdrop-blur-md">
          <RouterLink to="/joueur/dashboard" class="flex items-center gap-2.5">
            <img :src="logoMark" alt="EasySportClub" class="h-10 w-10 rounded-xl bg-white/95 p-2" />
            <span class="text-lg font-bold">EasySportClub</span>
          </RouterLink>

          <nav class="flex flex-wrap items-center gap-2">
            <RouterLink
              v-for="item in liensGlobaux.filter((l) => l.to)"
              :key="item.to"
              :to="item.to"
              class="rounded-full border border-white/25 bg-white/10 px-4 py-1.5 text-[11px] font-semibold text-white/95 transition hover:bg-white/20"
            >
              {{ item.label }}
            </RouterLink>
            <a
              v-for="item in liensGlobaux.filter((l) => l.href)"
              :key="item.href"
              :href="item.href"
              class="rounded-full border border-white/25 bg-white/10 px-4 py-1.5 text-[11px] font-semibold text-white/95 transition hover:bg-white/20"
            >
              {{ item.label }}
            </a>
            <button type="button" class="rounded-full bg-white px-4 py-1.5 text-[11px] font-bold text-[#1f36bf] transition hover:bg-[#eef2ff]" @click="deconnecter">
              Deconnexion
            </button>
          </nav>
        </header>

        <div class="relative z-10 mx-auto mt-10 max-w-4xl text-center sm:mt-14">
          <h1 class="text-3xl font-black leading-[1.16] sm:text-6xl">
            Bienvenue,
            <br class="hidden sm:block" />
            {{ utilisateurResume.nom }}
          </h1>
          <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">
            Votre espace joueur — equipe, evenements, convocations et messagerie en un seul endroit.
          </p>
          <div class="mt-6 flex flex-wrap items-center justify-center gap-2.5">
            <button type="button" class="rounded-full bg-white px-6 py-2 text-sm font-bold text-[#1f36bf] transition hover:bg-[#eef2ff]" @click="afficherModule('evenements')">
              Mes evenements
            </button>
            <button type="button" class="rounded-full border border-white/35 bg-white/8 px-6 py-2 text-sm font-semibold text-white transition hover:bg-white/20" @click="afficherModule('convocations')">
              Mes convocations
            </button>
          </div>
        </div>
      </section>

      <section class="relative -mt-[154px] z-30 min-h-screen pb-0">
        <article class="sticky top-2 z-40 mx-auto w-full max-w-[1220px] rounded-[24px] border border-[#e6ebf8] bg-white text-[#111827] shadow-[0_1px_0_rgba(17,24,39,0.04),0_36px_70px_-54px_rgba(15,23,42,0.55)]">

          <div class="sticky top-0 z-30 flex flex-wrap items-center justify-between gap-3 rounded-t-[24px] border-b border-[#ecf0f9] bg-white/95 px-3 py-3 backdrop-blur-md sm:px-4">
            <div class="flex min-w-0 flex-1 flex-wrap items-center gap-2 text-[11px]">
              <button
                v-for="item in liensFonctionnalites"
                :key="item.key"
                type="button"
                class="group inline-flex items-center gap-2 rounded-full border px-2.5 py-1.5 font-bold transition"
                :class="moduleActif === item.key ? 'border-[#d8e2fb] bg-[#f2f6ff] text-[#1f36bf]' : 'border-transparent text-[#6b7280] hover:border-[#dce4f7] hover:bg-[#f8fbff] hover:text-[#1f2a44]'"
                @click="afficherModule(item.key)"
              >
                {{ item.label }}
              </button>
            </div>

            <div class="flex items-center gap-2">
              <label class="relative hidden sm:block">
                <input
                  v-model="rechercheNavigation"
                  type="text"
                  :placeholder="moduleActif === 'evenements' ? 'Rechercher un evenement' : moduleActif === 'convocations' ? 'Rechercher une convocation' : 'Search'"
                  class="h-8 w-[165px] rounded-full border border-[#dbe2ef] bg-white px-3 py-1 text-xs text-[#1f2a44] outline-none placeholder:text-[#94a3b8]"
                />
              </label>

              <button
                type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff] disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="chargementRafraichissement || chargementEvenements || chargementConvocations"
                @click="actualiserModuleActif"
              >
                <svg class="h-3.5 w-3.5" :class="chargementRafraichissement ? 'animate-spin' : ''" viewBox="0 0 20 20" fill="none">
                  <path d="M16.25 9.25a6.25 6.25 0 1 0-1.72 4.31M16.25 9.25V5.5M16.25 9.25H12.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </button>

              <div class="relative">
                <button
                  type="button"
                  class="relative inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]"
                  @click="notificationOuverte = !notificationOuverte; chargerNotifications()"
                >
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <path d="M18.25 9.4c0-3.4-2.45-5.9-6.25-5.9s-6.25 2.5-6.25 5.9v2.56c0 .72-.26 1.42-.72 1.96L4 15.13h16l-1.03-1.21a3 3 0 0 1-.72-1.96V9.4ZM9.75 18.25a2.45 2.45 0 0 0 4.5 0" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <span v-if="notificationsNonLuesTotal" class="absolute -right-0.5 -top-0.5 grid h-4 min-w-4 place-items-center rounded-full bg-[#ef4444] px-1 text-[9px] font-black text-white">
                    {{ notificationsNonLuesTotal }}
                  </span>
                </button>

                <div v-if="notificationOuverte" class="absolute right-0 top-10 z-50 w-[340px] overflow-hidden rounded-[24px] border border-[#e6edf8] bg-white text-left shadow-[0_22px_60px_-40px_rgba(15,23,42,0.55)]">
                  <div class="flex items-center justify-between border-b border-[#eef2fb] px-4 py-3">
                    <div>
                      <p class="text-sm font-black text-[#111827]">Notifications</p>
                      <p class="text-[11px] font-semibold text-[#64748b]">{{ notificationsNonLuesTotal }} non lue(s)</p>
                    </div>
                    <button type="button" class="rounded-full bg-[#f2f6ff] px-3 py-1 text-[11px] font-black text-[#1f36bf]" @click="chargerNotifications">↻</button>
                  </div>

                  <div class="max-h-[430px] overflow-y-auto p-3">
                    <p v-if="chargementNotifications" class="rounded-2xl bg-[#f8fbff] p-4 text-xs font-bold text-[#64748b]">Chargement...</p>
                    <p v-else-if="!notificationsRecentes.length" class="rounded-2xl bg-[#f8fbff] p-4 text-xs font-bold text-[#64748b]">Aucune notification.</p>

                    <article
                      v-for="notif in notificationsRecentes"
                      v-else
                      :key="notif.id"
                      class="mb-2 cursor-pointer rounded-[20px] border border-[#edf2fb] bg-[#fbfdff] p-3"
                      @click="marquerNotificationLue(notif)"
                    >
                      <div class="flex items-start justify-between gap-3">
                        <div>
                          <p class="text-sm font-black text-[#111827]" :class="notif.est_lue ? 'opacity-60' : ''">{{ notif.titre }}</p>
                          <p class="mt-1 text-xs font-semibold leading-5 text-[#64748b]">{{ notif.contenu }}</p>
                          <p class="mt-2 text-[11px] font-bold text-[#94a3b8]">{{ formatDateHeure(notif.created_at) }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black"
                          :class="notif.statut_action === 'en_attente' ? 'bg-[#fff7ed] text-[#f59e0b]' : notif.statut_action === 'accepte' ? 'bg-[#ecfdf5] text-[#16a34a]' : notif.statut_action === 'refuse' ? 'bg-[#fef2f2] text-[#ef4444]' : 'bg-[#eef2ff] text-[#1f36bf]'"
                        >
                          {{ notif.statut_action || notif.type_notification }}
                        </span>
                      </div>
                    </article>
                  </div>
                </div>
              </div>

              <img v-if="utilisateurResume.image" :src="utilisateurResume.image" :alt="utilisateurResume.nom" class="h-8 w-8 rounded-full object-cover" />
              <span v-else class="block h-8 w-8 rounded-full bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)] ring-1 ring-[#dbe2ef]"></span>
            </div>
          </div>

          <div class="px-3 py-4 sm:px-5 sm:py-5">

            <div v-if="chargement" class="space-y-4">
              <div class="h-44 animate-pulse rounded-3xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
              <div class="h-28 animate-pulse rounded-3xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
              <div class="h-56 animate-pulse rounded-3xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
            </div>

            <template v-else>
              <JoueurDashboardHome
                v-if="moduleActif === 'dashboard'"
                :equipe="equipe"
                :evenements="evenementsEquipeDashboard"
                :convocations="convocationsEquipe"
                :derniere-mise-a-jour="derniereMiseAJour"
                :chargement="false"
                @aller-module="afficherModule"
                @ouvrir-rejoindre-equipe="ouvrirModalRejoindreEquipe"
              />

              <JoueurEvenements
                v-else-if="moduleActif === 'evenements'"
                :evenements="evenementsEquipeModule"
                :equipe="equipe"
                :chargement="chargementEvenements"
                :recherche="rechercheEvenements"
                @update:recherche="rechercheEvenements = $event"
              />

              <JoueurConvocations
                v-else-if="moduleActif === 'convocations'"
                :convocations="convocationsEquipe"
                :equipe="equipe"
                :chargement="chargementConvocations"
                :recherche="rechercheConvocations"
                @update:recherche="rechercheConvocations = $event"
                @repondre="repondreConvocation"
              />

              <JoueurMessagerie
                v-else-if="moduleActif === 'messagerie'"
                :canaux="canaux"
                :messages="messages"
                :canal-selectionne="canalSelectionne"
                :chargement-canaux="chargementCanaux"
                :chargement-messages="chargementMessages"
                :envoi-message="envoiMessage"
                @selectionner-canal="selectionnerCanal"
                @envoyer-message="envoyerMessage"
              />
            </template>
          </div>
        </article>
      </section>
    </div>

    <div
      v-if="modalRejoindreEquipeVisible"
      class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/40 p-4"
      @click.self="fermerModalRejoindreEquipe"
    >
      <div class="w-full max-w-md rounded-[28px] border border-[#e6ebf8] bg-white p-6 shadow-[0_24px_70px_-40px_rgba(15,23,42,0.55)]">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#1f36bf]">Rejoindre une equipe</p>
            <h3 class="mt-2 text-2xl font-black text-[#111827]">Entrer votre code d invitation</h3>
            <p class="mt-2 text-sm font-semibold leading-6 text-[#6b7280]">
              Saisissez le code partage par votre coach ou votre president pour etre rattache a votre equipe.
            </p>
          </div>
          <button
            type="button"
            class="rounded-full border border-[#dbe2ef] px-3 py-1.5 text-xs font-bold text-[#475569] transition hover:bg-[#f8fbff]"
            @click="fermerModalRejoindreEquipe"
          >
            Fermer
          </button>
        </div>

        <div class="mt-6">
          <label class="block">
            <span class="text-[11px] font-black uppercase tracking-[0.18em] text-[#7c8aa5]">Code d invitation</span>
            <input
              v-model="codeInvitationEquipe"
              type="text"
              maxlength="20"
              placeholder="Exemple : AB12CD34"
              class="mt-2 h-12 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold uppercase tracking-[0.12em] text-[#111827] outline-none transition placeholder:normal-case placeholder:tracking-normal placeholder:text-[#94a3b8] focus:border-[#1f36bf] focus:ring-4 focus:ring-[#1f36bf]/10"
              @keyup.enter="rejoindreEquipe"
            />
          </label>

          <p v-if="erreurCodeInvitation" class="mt-3 rounded-2xl border border-[#fecaca] bg-[#fef2f2] px-4 py-3 text-sm font-semibold text-[#b91c1c]">
            {{ erreurCodeInvitation }}
          </p>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
          <button
            type="button"
            class="rounded-full border border-[#dbe2ef] px-5 py-2.5 text-sm font-bold text-[#475569] transition hover:bg-[#f8fbff]"
            @click="fermerModalRejoindreEquipe"
          >
            Annuler
          </button>
          <button
            type="button"
            class="rounded-full bg-[#111827] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#1f2937] disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="chargementRejoindreEquipe"
            @click="rejoindreEquipe"
          >
            {{ chargementRejoindreEquipe ? 'Verification...' : 'Rejoindre' }}
          </button>
        </div>
      </div>
    </div>
  </main>
</template>
