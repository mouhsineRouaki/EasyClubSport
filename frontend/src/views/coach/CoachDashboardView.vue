<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import blueBackground from '../../assets/Background.jpg'
import logoMark from '../../assets/logo-easyclubsport-mark.svg'
import { authGet, authPost, authPut, authDelete } from '../../services/api'
import { notifyError, notifySuccess } from '../../stores/toast'
import { subscribeToCanalMessages, disconnectRealtime } from '../../services/realtime'

import CoachDashboardHome from '../../components/coach/CoachDashboardHome.vue'
import CoachEquipes from '../../components/coach/CoachEquipes.vue'
import CoachJoueurs from '../../components/coach/CoachJoueurs.vue'
import CoachEvenements from '../../components/coach/CoachEvenements.vue'
import CoachConvocations from '../../components/coach/CoachConvocations.vue'
import CoachMessagerie from '../../components/coach/CoachMessagerie.vue'

const router = useRouter()

// ─── état global ───────────────────────────────────────────────────────────────
const chargement = ref(true)
const chargementRafraichissement = ref(false)
const moduleActif = ref('dashboard')
const dashboard = ref(null)
const utilisateurConnecte = ref(null)
const notificationsCoach = ref([])
const notificationsNonLuesTotal = ref(0)
const chargementNotifications = ref(false)
const notificationOuverte = ref(false)
const actionNotification = ref('')
const rafraichissementAuto = ref(true)
const derniereMiseAJour = ref(null)
const intervalRafraichissement = ref(null)

// ─── équipes ───────────────────────────────────────────────────────────────────
const chargementEquipes = ref(false)
const equipesCoach = ref([])
const rechercheEquipes = ref('')
const debounceEquipes = ref(null)

// ─── joueurs ───────────────────────────────────────────────────────────────────
const chargementJoueurs = ref(false)
const joueursEquipe = ref([])
const equipeJoueurId = ref('')
const rechercheJoueurs = ref('')
const debounceJoueurs = ref(null)

// ─── événements ────────────────────────────────────────────────────────────────
const chargementEvenements = ref(false)
const evenementsEquipe = ref([])
const equipeEvenementId = ref('')
const rechercheEvenements = ref('')
const debounceEvenements = ref(null)

// ─── convocations ──────────────────────────────────────────────────────────────
const chargementConvocations = ref(false)
const convocationsEquipe = ref([])
const equipeConvocationId = ref('')
const rechercheConvocations = ref('')

// ─── messagerie ────────────────────────────────────────────────────────────────
const chargementCanaux = ref(false)
const canaux = ref([])
const canalSelectionne = ref(null)
const chargementMessages = ref(false)
const messages = ref([])
const envoiMessage = ref(false)
const rechercheMessagerie = ref('')
const stopRealtimeCoach = ref(() => { })

// ─── computed ──────────────────────────────────────────────────────────────────
const statistiques = computed(() => dashboard.value?.statistiques || {})
const equipeActiveCoach = computed(() => dashboard.value?.equipe || equipesCoach.value[0] || null)
const prochainsEvenements = computed(() => dashboard.value?.prochains_evenements || [])
const evenementsDashboard = computed(() => prochainsEvenements.value.slice(0, 4))
const evenementsCarousel = computed(() => evenementsDashboard.value.length ? [...evenementsDashboard.value, ...evenementsDashboard.value] : [])
const notificationsRecentes = computed(() => notificationsCoach.value.slice(0, 6))
const equipesOptions = computed(() => equipesCoach.value)

const statsCards = computed(() => [
  { label: 'Equipe', value: statistiques.value.equipes_total || 0 },
  { label: 'Joueurs', value: statistiques.value.joueurs_total || 0 },
  { label: 'Evenements', value: statistiques.value.evenements_a_venir || 0 },
  { label: 'Convocations', value: statistiques.value.convocations_en_attente || 0 },
])

const liensFonctionnalites = [
  { key: 'dashboard', label: 'Dashboard' },
  { key: 'equipes', label: 'Equipes' },
  { key: 'joueurs', label: 'Joueurs' },
  { key: 'evenements', label: 'Evenements' },
  { key: 'convocations', label: 'Convocations' },
  { key: 'messagerie', label: 'Messagerie' },
]

const liensGlobaux = [
  { label: 'Dashboard', to: '/coach/dashboard' },
  { label: 'About us', href: '#about-easyclubsport' },
  { label: 'Contact us', href: '#contact-support' },
]

const utilisateurResume = computed(() => {
  const u = utilisateurConnecte.value || {}
  return {
    nom: [u.prenom, u.nom].filter(Boolean).join(' ') || u.name || 'Coach',
    email: u.email || '',
    image: u.photo_url || u.photo || '',
  }
})

const rechercheNavigation = computed({
  get() {
    if (moduleActif.value === 'equipes') return rechercheEquipes.value
    if (moduleActif.value === 'joueurs') return rechercheJoueurs.value
    if (moduleActif.value === 'evenements') return rechercheEvenements.value
    if (moduleActif.value === 'convocations') return rechercheConvocations.value
    if (moduleActif.value === 'messagerie') return rechercheMessagerie.value
    return ''
  },
  set(value) {
    if (moduleActif.value === 'equipes') { rechercheEquipes.value = value; return }
    if (moduleActif.value === 'joueurs') { rechercheJoueurs.value = value; return }
    if (moduleActif.value === 'evenements') { rechercheEvenements.value = value; return }
    if (moduleActif.value === 'convocations') { rechercheConvocations.value = value; return }
    if (moduleActif.value === 'messagerie') { rechercheMessagerie.value = value }
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

// ─── navigation modules ─────────────────────────────────────────────────────────
const afficherModule = async (key) => {
  moduleActif.value = key
  if (key === 'equipes') await chargerEquipes()
  if (key === 'joueurs') await initialiserJoueurs()
  if (key === 'evenements') await initialiserEvenements()
  if (key === 'convocations') await initialiserConvocations()
  if (key === 'messagerie') await chargerCanaux()
}

const actualiserModuleActif = async () => {
  chargementRafraichissement.value = true
  try {
    if (moduleActif.value === 'dashboard') await chargerDashboard()
    else if (moduleActif.value === 'equipes') await chargerEquipes()
    else if (moduleActif.value === 'joueurs') await chargerJoueurs()
    else if (moduleActif.value === 'evenements') await chargerEvenements()
    else if (moduleActif.value === 'convocations') await chargerConvocations()
    else if (moduleActif.value === 'messagerie') await chargerCanaux()
    derniereMiseAJour.value = new Date().toISOString()
  } finally {
    chargementRafraichissement.value = false
  }
}

// ─── chargement dashboard ───────────────────────────────────────────────────────
const chargerDashboard = async () => {
  chargement.value = true
  try {
    const [repDash, repProfil] = await Promise.all([
      authGet('/coach/dashboard'),
      authGet('/auth/moi'),
    ])
    dashboard.value = repDash?.data || null
    utilisateurConnecte.value = repProfil?.data?.utilisateur || repProfil?.data || null
    const equipeDashboardId = repDash?.data?.equipe?.id ? String(repDash.data.equipe.id) : ''
    if (equipeDashboardId) {
      equipeJoueurId.value = equipeDashboardId
      equipeEvenementId.value = equipeDashboardId
      equipeConvocationId.value = equipeDashboardId
    }
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

// ─── notifications ──────────────────────────────────────────────────────────────
const chargerNotifications = async () => {
  chargementNotifications.value = true
  try {
    const rep = await authGet('/coach/notifications')
    notificationsCoach.value = rep?.data?.notifications || []
    notificationsNonLuesTotal.value = notificationsCoach.value.filter((n) => !n.est_lue).length
  } catch {
    // silencieux
  } finally {
    chargementNotifications.value = false
  }
}

const marquerNotificationLue = async (notification) => {
  if (notification.est_lue) return
  try {
    await authPut(`/coach/notifications/${notification.id}/lecture`)
    notification.est_lue = true
    notificationsNonLuesTotal.value = Math.max(0, notificationsNonLuesTotal.value - 1)
  } catch { /* silencieux */ }
}

const repondreInvitation = async (notification, reponse) => {
  actionNotification.value = `${notification.id}-${reponse}`
  try {
    await authPost(`/coach/evenements/${notification.evenement_id}/invitation/${reponse === 'accepte' ? 'acceptation' : 'refus'}`)
    notifySuccess(reponse === 'accepte' ? 'Invitation acceptee.' : 'Invitation refusee.')
    notification.statut_action = reponse
    await chargerNotifications()
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Erreur lors de la reponse.')
  } finally {
    actionNotification.value = ''
  }
}

// ─── equipes ────────────────────────────────────────────────────────────────────
const chargerEquipes = async () => {
  chargementEquipes.value = true
  try {
    const rep = await authGet('/coach/equipes')
    equipesCoach.value = rep?.data?.equipes || []
    if (!equipeJoueurId.value && equipesCoach.value.length) equipeJoueurId.value = String(equipesCoach.value[0].id)
    if (!equipeEvenementId.value && equipesCoach.value.length) equipeEvenementId.value = String(equipesCoach.value[0].id)
    if (!equipeConvocationId.value && equipesCoach.value.length) equipeConvocationId.value = String(equipesCoach.value[0].id)
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les equipes.')
  } finally {
    chargementEquipes.value = false
  }
}

// ─── joueurs ────────────────────────────────────────────────────────────────────
const chargerJoueurs = async () => {
  if (!equipeJoueurId.value) return
  chargementJoueurs.value = true
  try {
    const rep = await authGet(`/coach/equipes/${equipeJoueurId.value}/joueurs`)
    joueursEquipe.value = rep?.data?.joueurs || []
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les joueurs.')
  } finally {
    chargementJoueurs.value = false
  }
}

const initialiserJoueurs = async () => {
  if (!equipesCoach.value.length) await chargerEquipes()
  if (equipeJoueurId.value) await chargerJoueurs()
}

// ─── événements ────────────────────────────────────────────────────────────────
const chargerEvenements = async () => {
  if (!equipeEvenementId.value) return
  chargementEvenements.value = true
  try {
    const rep = await authGet(`/coach/equipes/${equipeEvenementId.value}/evenements`, { q: rechercheEvenements.value })
    evenementsEquipe.value = rep?.data?.evenements || []
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les evenements.')
  } finally {
    chargementEvenements.value = false
  }
}

const initialiserEvenements = async () => {
  if (!equipesCoach.value.length) await chargerEquipes()
  if (equipeEvenementId.value) await chargerEvenements()
}

const creerEvenement = async ({ payload, onErreur }) => {
  if (!equipeEvenementId.value) { notifyError('Selectionnez une equipe.'); return }
  try {
    const rep = await authPost(`/coach/equipes/${equipeEvenementId.value}/evenements`, payload)
    notifySuccess(rep?.message || 'Evenement cree.')
    await chargerEvenements()
  } catch (error) {
    onErreur?.(error?.response?.data || {})
    notifyError(error?.response?.message || error.message || "Impossible de creer l'evenement.")
    throw error
  }
}

const modifierEvenement = async ({ id, payload, onErreur }) => {
  if (!equipeEvenementId.value) return
  try {
    const rep = await authPut(`/coach/equipes/${equipeEvenementId.value}/evenements/${id}`, payload)
    notifySuccess(rep?.message || 'Evenement modifie.')
    await chargerEvenements()
  } catch (error) {
    onErreur?.(error?.response?.data || {})
    notifyError(error?.response?.message || error.message || "Impossible de modifier l'evenement.")
    throw error
  }
}

const supprimerEvenement = async (ev) => {
  if (!window.confirm(`Supprimer l'evenement "${ev.titre}" ?`)) return
  try {
    const rep = await authDelete(`/coach/equipes/${equipeEvenementId.value}/evenements/${ev.id}`)
    notifySuccess(rep?.message || 'Evenement supprime.')
    await chargerEvenements()
  } catch (error) {
    notifyError(error?.response?.message || error.message || "Impossible de supprimer l'evenement.")
  }
}

// ─── convocations ──────────────────────────────────────────────────────────────
const chargerConvocations = async () => {
  if (!equipeConvocationId.value) return
  chargementConvocations.value = true
  try {
    const rep = await authGet(`/coach/equipes/${equipeConvocationId.value}/convocations`)
    convocationsEquipe.value = rep?.data?.convocations || []
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les convocations.')
  } finally {
    chargementConvocations.value = false
  }
}

const initialiserConvocations = async () => {
  if (!equipesCoach.value.length) await chargerEquipes()
  if (equipeConvocationId.value) await chargerConvocations()
}

const modifierStatutConvocation = async ({ convocation, statut }) => {
  try {
    await authPut(`/coach/convocations/${convocation.id}`, { statut })
    convocation.statut = statut
    notifySuccess('Convocation mise a jour.')
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Erreur mise a jour convocation.')
  }
}

// ─── messagerie ────────────────────────────────────────────────────────────────
const chargerCanaux = async () => {
  chargementCanaux.value = true
  try {
    const rep = await authGet('/coach/canaux')
    canaux.value = rep?.data?.canaux || []
    if (!canalSelectionne.value && canaux.value.length) {
      await selectionnerCanal(canaux.value[0])
    }
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les canaux.')
  } finally {
    chargementCanaux.value = false
  }
}

const selectionnerCanal = async (canal) => {
  stopRealtimeCoach.value()
  canalSelectionne.value = canal
  chargementMessages.value = true
  try {
    const rep = await authGet(`/coach/canaux/${canal.id}/messages`)
    messages.value = (rep?.data?.messages || []).map(normaliserMessage).filter(Boolean)
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les messages.')
  } finally {
    chargementMessages.value = false
  }

  const canalId = canal.id
  if (!canalId) return

  stopRealtimeCoach.value = subscribeToCanalMessages(canalId, (payload) => {
    const msg = payload?.message || payload
    pousserMessage(msg)
  })
}

const envoyerMessage = async (contenu) => {
  if (!canalSelectionne.value) return
  envoiMessage.value = true
  try {
    const rep = await authPost(`/coach/canaux/${canalSelectionne.value.id}/messages`, { contenu })
    pousserMessage(rep?.data?.message)
  } catch (error) {
    notifyError(error?.response?.message || error.message || "Impossible d'envoyer le message.")
  } finally {
    envoiMessage.value = false
  }
}

// ─── rafraichissement auto ──────────────────────────────────────────────────────
const demarrerRafraichissementAuto = () => {
  intervalRafraichissement.value = setInterval(async () => {
    if (!rafraichissementAuto.value) return
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

// ─── watchers ──────────────────────────────────────────────────────────────────
watch(rechercheEquipes, () => {
  clearTimeout(debounceEquipes.value)
  debounceEquipes.value = setTimeout(chargerEquipes, 350)
})

watch(rechercheEvenements, () => {
  clearTimeout(debounceEvenements.value)
  debounceEvenements.value = setTimeout(() => {
    if (moduleActif.value === 'evenements') chargerEvenements()
  }, 350)
})

watch(equipeJoueurId, () => { if (moduleActif.value === 'joueurs') chargerJoueurs() })
watch(equipeEvenementId, () => { if (moduleActif.value === 'evenements') chargerEvenements() })
watch(equipeConvocationId, () => { if (moduleActif.value === 'convocations') chargerConvocations() })

// ─── lifecycle ─────────────────────────────────────────────────────────────────
onMounted(async () => {
  await chargerDashboard()
  await chargerNotifications()
  await chargerEquipes()
  demarrerRafraichissementAuto()
})

onBeforeUnmount(() => {
  arreterRafraichissementAuto()
  clearTimeout(debounceEquipes.value)
  clearTimeout(debounceJoueurs.value)
  clearTimeout(debounceEvenements.value)
  stopRealtimeCoach.value()
  disconnectRealtime()
})
</script>

<template>
  <main class="min-h-screen bg-[#f4f6fb] font-['Plus_Jakarta_Sans',Inter,sans-serif] text-[#111827]">
    <div class="mx-auto max-w-[1450px] px-2 pb-5 pt-2 sm:px-4 sm:pt-3">

      <!-- ── HERO BANNER ─────────────────────────────────────────────────────── -->
      <section
        class="relative overflow-hidden rounded-[28px] border border-[#2a43cd] bg-[#2446d8] px-4 pb-[180px] pt-4 text-white sm:px-7 sm:pb-[196px] sm:pt-5">
        <img :src="blueBackground" alt="Background" class="absolute inset-0 h-full w-full object-cover" />

        <header
          class="relative z-10 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/15 bg-white/10 px-3 py-2 backdrop-blur-md">
          <RouterLink to="/coach/dashboard" class="flex items-center gap-2.5">
            <img :src="logoMark" alt="EasySportClub" class="h-10 w-10 rounded-xl bg-white/95 p-2" />
            <span class="text-lg font-bold">EasySportClub</span>
          </RouterLink>

          <nav class="flex flex-wrap items-center gap-2">
            <RouterLink v-for="item in liensGlobaux.filter((l) => l.to)" :key="item.to" :to="item.to"
              class="rounded-full border border-white/25 bg-white/10 px-4 py-1.5 text-[11px] font-semibold text-white/95 transition hover:bg-white/20">
              {{ item.label }}
            </RouterLink>
            <a v-for="item in liensGlobaux.filter((l) => l.href)" :key="item.href" :href="item.href"
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
          <h1 class="text-3xl font-black leading-[1.16] sm:text-6xl">
            Gerez vos equipes
            <br class="hidden sm:block" />
            avec une interface claire
          </h1>
          <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">
            Votre espace coach centralise equipes, evenements, convocations et messagerie en un seul endroit.
          </p>
          <div class="mt-6 flex flex-wrap items-center justify-center gap-2.5">
            <button type="button"
              class="rounded-full bg-white px-6 py-2 text-sm font-bold text-[#1f36bf] transition hover:bg-[#eef2ff]"
              @click="afficherModule('equipes')">
              Mes equipes
            </button>
            <button type="button"
              class="rounded-full border border-white/35 bg-white/8 px-6 py-2 text-sm font-semibold text-white transition hover:bg-white/20"
              @click="afficherModule('evenements')">
              Voir les evenements
            </button>
          </div>
        </div>
      </section>

      <!-- ── WORKSPACE CARD ──────────────────────────────────────────────────── -->
      <section class="relative -mt-[154px] z-30 min-h-screen pb-0">
        <article
          class="sticky top-2 z-40 mx-auto w-full max-w-[1220px] rounded-[24px] border border-[#e6ebf8] bg-white text-[#111827] shadow-[0_1px_0_rgba(17,24,39,0.04),0_36px_70px_-54px_rgba(15,23,42,0.55)]">

          <!-- navbar fonctionnalités -->
          <div
            class="sticky top-0 z-30 flex flex-wrap items-center justify-between gap-3 rounded-t-[24px] border-b border-[#ecf0f9] bg-white/95 px-3 py-3 backdrop-blur-md sm:px-4">
            <div class="flex min-w-0 flex-1 flex-wrap items-center gap-2 text-[11px]">
              <button v-for="item in liensFonctionnalites" :key="item.key" type="button"
                class="group inline-flex items-center gap-2 rounded-full border px-2.5 py-1.5 font-bold transition"
                :class="moduleActif === item.key ? 'border-[#d8e2fb] bg-[#f2f6ff] text-[#1f36bf]' : 'border-transparent text-[#6b7280] hover:border-[#dce4f7] hover:bg-[#f8fbff] hover:text-[#1f2a44]'"
                @click="afficherModule(item.key)">
                {{ item.label }}
              </button>
            </div>

            <div class="flex items-center gap-2">
              <label class="relative hidden sm:block">
                <input v-model="rechercheNavigation" type="text"
                  :placeholder="moduleActif === 'equipes' ? 'Rechercher une equipe' : moduleActif === 'joueurs' ? 'Rechercher un joueur' : moduleActif === 'evenements' ? 'Rechercher un evenement' : moduleActif === 'convocations' ? 'Rechercher une convocation' : moduleActif === 'messagerie' ? 'Rechercher une conversation' : 'Search'"
                  class="h-8 w-[165px] rounded-full border border-[#dbe2ef] bg-white px-3 py-1 text-xs text-[#1f2a44] outline-none placeholder:text-[#94a3b8]" />
              </label>

              <!-- bouton actualiser -->
              <button type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff] disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="chargementRafraichissement || chargementEquipes || chargementEvenements || chargementJoueurs"
                @click="actualiserModuleActif">
                <svg class="h-3.5 w-3.5" :class="chargementRafraichissement ? 'animate-spin' : ''" viewBox="0 0 20 20"
                  fill="none">
                  <path d="M16.25 9.25a6.25 6.25 0 1 0-1.72 4.31M16.25 9.25V5.5M16.25 9.25H12.5" stroke="currentColor"
                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </button>

              <!-- notifications -->
              <div class="relative">
                <button type="button"
                  class="relative inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]"
                  @click="notificationOuverte = !notificationOuverte; chargerNotifications()">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <path
                      d="M18.25 9.4c0-3.4-2.45-5.9-6.25-5.9s-6.25 2.5-6.25 5.9v2.56c0 .72-.26 1.42-.72 1.96L4 15.13h16l-1.03-1.21a3 3 0 0 1-.72-1.96V9.4ZM9.75 18.25a2.45 2.45 0 0 0 4.5 0"
                      stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <span v-if="notificationsNonLuesTotal"
                    class="absolute -right-0.5 -top-0.5 grid h-4 min-w-4 place-items-center rounded-full bg-[#ef4444] px-1 text-[9px] font-black text-white">
                    {{ notificationsNonLuesTotal }}
                  </span>
                </button>

                <div v-if="notificationOuverte"
                  class="absolute right-0 top-10 z-50 w-[340px] overflow-hidden rounded-[24px] border border-[#e6edf8] bg-white text-left shadow-[0_22px_60px_-40px_rgba(15,23,42,0.55)]">
                  <div class="flex items-center justify-between border-b border-[#eef2fb] px-4 py-3">
                    <div>
                      <p class="text-sm font-black text-[#111827]">Notifications</p>
                      <p class="text-[11px] font-semibold text-[#64748b]">{{ notificationsNonLuesTotal }} non lue(s)</p>
                    </div>
                    <button type="button"
                      class="rounded-full bg-[#f2f6ff] px-3 py-1 text-[11px] font-black text-[#1f36bf]"
                      @click="chargerNotifications">↻</button>
                  </div>

                  <div class="max-h-[430px] overflow-y-auto p-3">
                    <p v-if="chargementNotifications"
                      class="rounded-2xl bg-[#f8fbff] p-4 text-xs font-bold text-[#64748b]">Chargement...</p>
                    <p v-else-if="!notificationsRecentes.length"
                      class="rounded-2xl bg-[#f8fbff] p-4 text-xs font-bold text-[#64748b]">Aucune notification.</p>

                    <article v-for="notif in notificationsRecentes" v-else :key="notif.id"
                      class="mb-2 cursor-pointer rounded-[20px] border border-[#edf2fb] bg-[#fbfdff] p-3"
                      @click="marquerNotificationLue(notif)">
                      <div class="flex items-start justify-between gap-3">
                        <div>
                          <p class="text-sm font-black text-[#111827]" :class="notif.est_lue ? 'opacity-60' : ''">{{
                            notif.titre }}</p>
                          <p class="mt-1 text-xs font-semibold leading-5 text-[#64748b]">{{ notif.contenu }}</p>
                          <p class="mt-2 text-[11px] font-bold text-[#94a3b8]">{{ formatDateHeure(notif.created_at) }}
                          </p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black"
                          :class="notif.statut_action === 'en_attente' ? 'bg-[#fff7ed] text-[#f59e0b]' : notif.statut_action === 'accepte' ? 'bg-[#ecfdf5] text-[#16a34a]' : notif.statut_action === 'refuse' ? 'bg-[#fef2f2] text-[#ef4444]' : 'bg-[#eef2ff] text-[#1f36bf]'">
                          {{ notif.statut_action || notif.type_notification }}
                        </span>
                      </div>

                      <div v-if="notif.action === 'match_invitation' && notif.statut_action === 'en_attente'"
                        class="mt-3 grid grid-cols-2 gap-2">
                        <button type="button"
                          class="rounded-full bg-[#111827] px-3 py-2 text-[11px] font-black text-white transition hover:bg-[#1f36bf] disabled:opacity-60"
                          :disabled="actionNotification === `${notif.id}-accepte`"
                          @click.stop="repondreInvitation(notif, 'accepte')">Accepter</button>
                        <button type="button"
                          class="rounded-full border border-[#fecaca] bg-white px-3 py-2 text-[11px] font-black text-[#ef4444] transition hover:bg-[#fef2f2] disabled:opacity-60"
                          :disabled="actionNotification === `${notif.id}-refuse`"
                          @click.stop="repondreInvitation(notif, 'refuse')">Refuser</button>
                      </div>
                    </article>
                  </div>
                </div>
              </div>

              <!-- avatar -->
              <img v-if="utilisateurResume.image" :src="utilisateurResume.image" :alt="utilisateurResume.nom"
                class="h-8 w-8 rounded-full object-cover" />
              <span v-else
                class="block h-8 w-8 rounded-full bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)] ring-1 ring-[#dbe2ef]"></span>
            </div>
          </div>

          <!-- ── CONTENU MODULES ──────────────────────────────────────────────── -->
          <div class="px-3 py-4 sm:px-5 sm:py-5">

            <!-- skeleton global -->
            <div v-if="chargement" class="space-y-4">
              <div class="h-28 animate-pulse rounded-3xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
              <div class="h-56 animate-pulse rounded-3xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
              <div class="h-44 animate-pulse rounded-3xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
            </div>

            <template v-else>
              <CoachDashboardHome v-if="moduleActif === 'dashboard'" :stats-cards="statsCards"
                :evenements-dashboard="evenementsDashboard" :evenements-carousel="evenementsCarousel"
                :equipe-active="equipeActiveCoach" :chargement-equipes="chargementEquipes"
                :derniere-mise-a-jour="derniereMiseAJour" @aller-module="afficherModule" />

              <CoachEquipes v-else-if="moduleActif === 'equipes'" :equipes="equipesCoach"
                :chargement="chargementEquipes" :recherche="rechercheEquipes"
                @update:recherche="rechercheEquipes = $event"
                @voir-joueurs="(id) => { afficherModule('joueurs'); equipeJoueurId = String(id) }" />

              <CoachJoueurs v-else-if="moduleActif === 'joueurs'" :joueurs="joueursEquipe" :equipes="equipesOptions"
                :equipe-id="equipeJoueurId" :chargement="chargementJoueurs" :recherche="rechercheJoueurs"
                @update:equipe-id="equipeJoueurId = $event" @update:recherche="rechercheJoueurs = $event" />

              <CoachEvenements v-else-if="moduleActif === 'evenements'" :evenements="evenementsEquipe"
                :equipes="equipesOptions" :equipe-id="equipeEvenementId" :chargement="chargementEvenements"
                @update:equipe-id="equipeEvenementId = $event" @creer="creerEvenement" @modifier="modifierEvenement"
                @supprimer="supprimerEvenement" />

              <CoachConvocations v-else-if="moduleActif === 'convocations'" :convocations="convocationsEquipe"
                :equipes="equipesOptions" :equipe-id="equipeConvocationId" :chargement="chargementConvocations"
                :recherche="rechercheConvocations" @update:equipe-id="equipeConvocationId = $event"
                @update:recherche="rechercheConvocations = $event" @modifier-statut="modifierStatutConvocation" />

              <CoachMessagerie v-else-if="moduleActif === 'messagerie'" :canaux="canaux" :messages="messages"
                :canal-selectionne="canalSelectionne" :chargement-canaux="chargementCanaux"
                :chargement-messages="chargementMessages" :envoi-message="envoiMessage"
                @selectionner-canal="selectionnerCanal" @envoyer-message="envoyerMessage" />
            </template>
          </div>
        </article>
      </section>
    </div>
  </main>
</template>
