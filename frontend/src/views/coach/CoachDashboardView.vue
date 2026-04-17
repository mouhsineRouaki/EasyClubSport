<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import blueBackground from '../../assets/Background.jpg'
import logoMark from '../../assets/logo-easyclubsport-mark.svg'
import { authGet, authPost, authPut, authDelete } from '../../services/api'
import { notifyError, notifySuccess } from '../../stores/toast'

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
const equipeSelectionnee = ref(null)
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
const evenementSelectionne = ref(null)
const modeEvenements = ref('liste')
const envoiEvenement = ref(false)
const erreursEvenement = ref({})
const rechercheEvenements = ref('')
const debounceEvenements = ref(null)

const formulaireEvenement = reactive({
  titre: '',
  type: 'match',
  date_debut: '',
  date_fin: '',
  lieu: '',
  adversaire: '',
  description: '',
  statut: 'planifie',
})

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
const messageEnvoi = ref('')
const envoiMessage = ref(false)
const rechercheMessagerie = ref('')

// ─── computed ──────────────────────────────────────────────────────────────────
const statistiques = computed(() => dashboard.value?.statistiques || {})
const prochainsEvenements = computed(() => dashboard.value?.prochains_evenements || [])
const evenementsDashboard = computed(() => prochainsEvenements.value.slice(0, 4))
const evenementsCarousel = computed(() => evenementsDashboard.value.length ? [...evenementsDashboard.value, ...evenementsDashboard.value] : [])
const notificationsRecentes = computed(() => notificationsCoach.value.slice(0, 6))
const equipesOptions = computed(() => equipesCoach.value)

const statsCards = computed(() => [
  { label: 'Equipes', value: statistiques.value.equipes_total || 0 },
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

const lienFonctionnelActif = (item) => moduleActif.value === item.key

// ─── helpers visuels ────────────────────────────────────────────────────────────
const formatDate = (date) => {
  if (!date) return '-'
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(new Date(date))
}

const formatDateHeure = (date) => {
  if (!date) return 'Jamais'
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'short', timeStyle: 'medium' }).format(new Date(date))
}

const formatDateTimeInput = (date) => {
  if (!date) return ''
  const v = new Date(date)
  if (Number.isNaN(v.getTime())) return ''
  return new Date(v.getTime() - v.getTimezoneOffset() * 60000).toISOString().slice(0, 16)
}

const imageEvenement = (ev = {}) => ev.image_url || ev.photo_url || ev.image || blueBackground
const backgroundEvenement = (ev = {}) => `linear-gradient(180deg, rgba(7,16,58,0.18), rgba(7,16,58,0.86)), url(${imageEvenement(ev)})`
const logoEquipe = (eq = {}) => eq?.logo_url || eq?.logo || ''
const imageEquipe = (eq = {}) => eq?.logo_url || eq?.logo || blueBackground
const backgroundEquipe = (eq = {}) => `linear-gradient(145deg, rgba(8,18,72,0.86), rgba(36,70,216,0.64)), url(${imageEquipe(eq)})`

const badgeStatutEvenement = (statut) => ({
  planifie: { label: 'Planifie', cls: 'bg-[#eef2ff] text-[#1f36bf]' },
  en_cours: { label: 'En cours', cls: 'bg-[#ecfdf5] text-[#16a34a]' },
  termine: { label: 'Termine', cls: 'bg-[#f1f5f9] text-[#64748b]' },
  annule: { label: 'Annule', cls: 'bg-[#fef2f2] text-[#ef4444]' },
}[statut] || { label: statut, cls: 'bg-[#f8fbff] text-[#64748b]' })

const badgeStatutConvocation = (statut) => ({
  convoque: { label: 'Convoque', cls: 'bg-[#eef2ff] text-[#1f36bf]' },
  confirme: { label: 'Confirme', cls: 'bg-[#ecfdf5] text-[#16a34a]' },
  refuse: { label: 'Refuse', cls: 'bg-[#fef2f2] text-[#ef4444]' },
  en_attente: { label: 'En attente', cls: 'bg-[#fff7ed] text-[#f59e0b]' },
}[statut] || { label: statut, cls: 'bg-[#f8fbff] text-[#64748b]' })

// ─── navigation modules ─────────────────────────────────────────────────────────
const afficherModule = async (key) => {
  moduleActif.value = key
  if (key === 'equipes') await chargerEquipes()
  if (key === 'joueurs') await initialiserJoueurs()
  if (key === 'evenements') { modeEvenements.value = 'liste'; await initialiserEvenements() }
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

const joueursFiltres = computed(() => {
  const q = rechercheJoueurs.value.toLowerCase()
  if (!q) return joueursEquipe.value
  return joueursEquipe.value.filter((j) =>
    [j.nom, j.prenom, j.name, j.email].some((v) => v?.toLowerCase().includes(q))
  )
})

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

const reinitialiserFormulaireEvenement = () => {
  formulaireEvenement.titre = ''
  formulaireEvenement.type = 'match'
  formulaireEvenement.date_debut = ''
  formulaireEvenement.date_fin = ''
  formulaireEvenement.lieu = ''
  formulaireEvenement.adversaire = ''
  formulaireEvenement.description = ''
  formulaireEvenement.statut = 'planifie'
  erreursEvenement.value = {}
}

const remplirFormulaireEvenement = (ev) => {
  formulaireEvenement.titre = ev?.titre || ''
  formulaireEvenement.type = ev?.type || 'match'
  formulaireEvenement.date_debut = formatDateTimeInput(ev?.date_debut)
  formulaireEvenement.date_fin = formatDateTimeInput(ev?.date_fin)
  formulaireEvenement.lieu = ev?.lieu || ''
  formulaireEvenement.adversaire = ev?.adversaire || ''
  formulaireEvenement.description = ev?.description || ''
  formulaireEvenement.statut = ev?.statut || 'planifie'
  erreursEvenement.value = {}
}

const ouvrirCreationEvenement = () => {
  evenementSelectionne.value = null
  reinitialiserFormulaireEvenement()
  modeEvenements.value = 'creation'
}

const ouvrirEditionEvenement = (ev) => {
  evenementSelectionne.value = ev
  remplirFormulaireEvenement(ev)
  modeEvenements.value = 'edition'
}

const retourListeEvenements = () => {
  modeEvenements.value = 'liste'
  erreursEvenement.value = {}
  evenementSelectionne.value = null
}

const creerEvenement = async () => {
  if (!equipeEvenementId.value) { notifyError('Selectionnez une equipe.'); return }
  envoiEvenement.value = true
  erreursEvenement.value = {}
  try {
    const rep = await authPost(`/coach/equipes/${equipeEvenementId.value}/evenements`, {
      titre: formulaireEvenement.titre,
      type: formulaireEvenement.type,
      date_debut: formulaireEvenement.date_debut,
      date_fin: formulaireEvenement.date_fin || null,
      lieu: formulaireEvenement.lieu || null,
      adversaire: formulaireEvenement.type === 'match' ? formulaireEvenement.adversaire || null : null,
      description: formulaireEvenement.description || null,
      statut: formulaireEvenement.statut,
    })
    notifySuccess(rep?.message || 'Evenement cree.')
    await chargerEvenements()
    modeEvenements.value = 'liste'
  } catch (error) {
    erreursEvenement.value = error?.response?.data || {}
    notifyError(error?.response?.message || error.message || 'Impossible de creer l\'evenement.')
  } finally {
    envoiEvenement.value = false
  }
}

const modifierEvenement = async () => {
  if (!equipeEvenementId.value || !evenementSelectionne.value) return
  envoiEvenement.value = true
  erreursEvenement.value = {}
  try {
    const rep = await authPut(`/coach/equipes/${equipeEvenementId.value}/evenements/${evenementSelectionne.value.id}`, {
      titre: formulaireEvenement.titre,
      type: formulaireEvenement.type,
      date_debut: formulaireEvenement.date_debut,
      date_fin: formulaireEvenement.date_fin || null,
      lieu: formulaireEvenement.lieu || null,
      adversaire: formulaireEvenement.type === 'match' ? formulaireEvenement.adversaire || null : null,
      description: formulaireEvenement.description || null,
      statut: formulaireEvenement.statut,
    })
    notifySuccess(rep?.message || 'Evenement modifie.')
    await chargerEvenements()
    modeEvenements.value = 'liste'
  } catch (error) {
    erreursEvenement.value = error?.response?.data || {}
    notifyError(error?.response?.message || error.message || 'Impossible de modifier l\'evenement.')
  } finally {
    envoiEvenement.value = false
  }
}

const supprimerEvenement = async (ev) => {
  if (!window.confirm(`Supprimer l'evenement "${ev.titre}" ?`)) return
  try {
    const rep = await authDelete(`/coach/equipes/${equipeEvenementId.value}/evenements/${ev.id}`)
    notifySuccess(rep?.message || 'Evenement supprime.')
    await chargerEvenements()
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de supprimer l\'evenement.')
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

const modifierStatutConvocation = async (convocation, statut) => {
  try {
    await authPut(`/coach/convocations/${convocation.id}`, { statut })
    convocation.statut = statut
    notifySuccess('Convocation mise a jour.')
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Erreur mise a jour convocation.')
  }
}

const convocationsFiltrees = computed(() => {
  const q = rechercheConvocations.value.toLowerCase()
  if (!q) return convocationsEquipe.value
  return convocationsEquipe.value.filter((c) =>
    [c.utilisateur?.nom, c.utilisateur?.prenom, c.utilisateur?.name, c.evenement?.titre].some((v) => v?.toLowerCase().includes(q))
  )
})

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
  canalSelectionne.value = canal
  chargementMessages.value = true
  try {
    const rep = await authGet(`/coach/canaux/${canal.id}/messages`)
    messages.value = rep?.data?.messages || []
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les messages.')
  } finally {
    chargementMessages.value = false
  }
}

const envoyerMessage = async () => {
  if (!messageEnvoi.value.trim() || !canalSelectionne.value) return
  envoiMessage.value = true
  try {
    const rep = await authPost(`/coach/canaux/${canalSelectionne.value.id}/messages`, { contenu: messageEnvoi.value })
    messages.value.push(rep?.data?.message || { contenu: messageEnvoi.value, created_at: new Date().toISOString() })
    messageEnvoi.value = ''
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible d\'envoyer le message.')
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

watch(rechercheJoueurs, () => {
  clearTimeout(debounceJoueurs.value)
  debounceJoueurs.value = setTimeout(() => {}, 0)
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
})
</script>

<template>
  <main class="min-h-screen bg-[#f4f6fb] font-['Plus_Jakarta_Sans',Inter,sans-serif] text-[#111827]">
    <div class="mx-auto max-w-[1450px] px-2 pb-5 pt-2 sm:px-4 sm:pt-3">

      <!-- ── HERO BANNER ─────────────────────────────────────────────────────── -->
      <section class="relative overflow-hidden rounded-[28px] border border-[#2a43cd] bg-[#2446d8] px-4 pb-[180px] pt-4 text-white sm:px-7 sm:pb-[196px] sm:pt-5">
        <img :src="blueBackground" alt="Background" class="absolute inset-0 h-full w-full object-cover" />

        <header class="relative z-10 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/15 bg-white/10 px-3 py-2 backdrop-blur-md">
          <RouterLink to="/coach/dashboard" class="flex items-center gap-2.5">
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
            Gerez vos equipes
            <br class="hidden sm:block" />
            avec une interface claire
          </h1>
          <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">
            Votre espace coach centralise equipes, evenements, convocations et messagerie en un seul endroit.
          </p>
          <div class="mt-6 flex flex-wrap items-center justify-center gap-2.5">
            <button type="button" class="rounded-full bg-white px-6 py-2 text-sm font-bold text-[#1f36bf] transition hover:bg-[#eef2ff]" @click="afficherModule('equipes')">
              Mes equipes
            </button>
            <button type="button" class="rounded-full border border-white/35 bg-white/8 px-6 py-2 text-sm font-semibold text-white transition hover:bg-white/20" @click="afficherModule('evenements')">
              Voir les evenements
            </button>
          </div>
        </div>
      </section>

      <!-- ── WORKSPACE CARD ──────────────────────────────────────────────────── -->
      <section class="relative -mt-[154px] z-30 min-h-screen pb-0">
        <article class="sticky top-2 z-40 mx-auto w-full max-w-[1220px] rounded-[24px] border border-[#e6ebf8] bg-white text-[#111827] shadow-[0_1px_0_rgba(17,24,39,0.04),0_36px_70px_-54px_rgba(15,23,42,0.55)]">

          <!-- navbar fonctionnalités -->
          <div class="sticky top-0 z-30 flex flex-wrap items-center justify-between gap-3 rounded-t-[24px] border-b border-[#ecf0f9] bg-white/95 px-3 py-3 backdrop-blur-md sm:px-4">
            <div class="flex min-w-0 flex-1 flex-wrap items-center gap-2 text-[11px]">
              <button
                v-for="item in liensFonctionnalites"
                :key="item.key"
                type="button"
                class="group inline-flex items-center gap-2 rounded-full border px-2.5 py-1.5 font-bold transition"
                :class="lienFonctionnelActif(item) ? 'border-[#d8e2fb] bg-[#f2f6ff] text-[#1f36bf]' : 'border-transparent text-[#6b7280] hover:border-[#dce4f7] hover:bg-[#f8fbff] hover:text-[#1f2a44]'"
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
                  :placeholder="moduleActif === 'equipes' ? 'Rechercher une equipe' : moduleActif === 'joueurs' ? 'Rechercher un joueur' : moduleActif === 'evenements' ? 'Rechercher un evenement' : moduleActif === 'convocations' ? 'Rechercher une convocation' : moduleActif === 'messagerie' ? 'Rechercher une conversation' : 'Search'"
                  class="h-8 w-[165px] rounded-full border border-[#dbe2ef] bg-white px-3 py-1 text-xs text-[#1f2a44] outline-none placeholder:text-[#94a3b8]"
                />
              </label>

              <!-- bouton actualiser -->
              <button
                type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff] disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="chargementRafraichissement || chargementEquipes || chargementEvenements || chargementJoueurs"
                @click="actualiserModuleActif"
              >
                <svg class="h-3.5 w-3.5" :class="chargementRafraichissement ? 'animate-spin' : ''" viewBox="0 0 20 20" fill="none">
                  <path d="M16.25 9.25a6.25 6.25 0 1 0-1.72 4.31M16.25 9.25V5.5M16.25 9.25H12.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </button>

              <!-- notifications -->
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

                      <div v-if="notif.action === 'match_invitation' && notif.statut_action === 'en_attente'" class="mt-3 grid grid-cols-2 gap-2">
                        <button type="button" class="rounded-full bg-[#111827] px-3 py-2 text-[11px] font-black text-white transition hover:bg-[#1f36bf] disabled:opacity-60"
                          :disabled="actionNotification === `${notif.id}-accepte`"
                          @click.stop="repondreInvitation(notif, 'accepte')"
                        >Accepter</button>
                        <button type="button" class="rounded-full border border-[#fecaca] bg-white px-3 py-2 text-[11px] font-black text-[#ef4444] transition hover:bg-[#fef2f2] disabled:opacity-60"
                          :disabled="actionNotification === `${notif.id}-refuse`"
                          @click.stop="repondreInvitation(notif, 'refuse')"
                        >Refuser</button>
                      </div>
                    </article>
                  </div>
                </div>
              </div>

              <!-- avatar -->
              <img v-if="utilisateurResume.image" :src="utilisateurResume.image" :alt="utilisateurResume.nom" class="h-8 w-8 rounded-full object-cover" />
              <span v-else class="block h-8 w-8 rounded-full bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)] ring-1 ring-[#dbe2ef]"></span>
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

              <!-- ══ DASHBOARD ══════════════════════════════════════════════════ -->
              <div v-if="moduleActif === 'dashboard'">

                <!-- stats -->
                <section class="mt-6">
                  <div class="text-center">
                    <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Statistiques principales</h3>
                    <p class="mx-auto mt-1 max-w-xl text-xs font-semibold text-[#6b7280]">Etat rapide de votre activite coach.</p>
                    <span class="mt-3 inline-flex rounded-full bg-[#f2f6ff] px-3 py-1 text-[11px] font-bold text-[#1f36bf]">
                      Maj {{ formatDateHeure(derniereMiseAJour) }}
                    </span>
                  </div>

                  <div class="mt-5 flex flex-wrap justify-center gap-2.5">
                    <article
                      v-for="card in statsCards"
                      :key="card.label"
                      class="inline-flex min-w-[150px] items-center justify-between gap-4 rounded-full border border-[#e6edf8] bg-white px-4 py-3 transition hover:border-[#cfdaf2]"
                    >
                      <span class="text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">{{ card.label }}</span>
                      <strong class="text-2xl font-black tracking-[-0.05em] text-[#111827]">{{ card.value }}</strong>
                    </article>
                  </div>
                </section>

                <!-- prochains evenements -->
                <section class="mt-7 rounded-[22px] border border-[#e6edf8] bg-white p-4">
                  <div class="text-center">
                    <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Evenements proches</h3>
                    <p class="mx-auto mt-1 max-w-xl text-xs font-semibold text-[#6b7280]">Les prochains rendez-vous a suivre en priorite.</p>
                    <button type="button" class="mt-3 inline-flex rounded-full border border-[#dbe2ef] px-3 py-1.5 text-xs font-extrabold text-[#1f36bf] transition hover:bg-[#f8fbff]" @click="afficherModule('evenements')">
                      Voir tous
                    </button>
                  </div>

                  <div v-if="evenementsDashboard.length" class="mt-5 overflow-hidden rounded-[30px] bg-[#f7f9ff] p-3 [mask-image:linear-gradient(90deg,transparent,black_8%,black_92%,transparent)]">
                    <div class="dashboard-event-carousel flex w-max gap-4">
                      <article
                        v-for="(ev, i) in evenementsCarousel"
                        :key="`${ev.id}-${i}`"
                        class="relative h-[270px] w-[250px] shrink-0 overflow-hidden rounded-[30px] border border-white/60 bg-cover bg-center p-4 text-white sm:w-[320px]"
                        :style="{ backgroundImage: backgroundEvenement(ev) }"
                      >
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_10%,rgba(255,255,255,0.34),transparent_28%),linear-gradient(180deg,transparent,rgba(0,0,0,0.22))]"></div>
                        <div class="relative z-10 flex h-full flex-col items-center justify-center text-center">
                          <span class="rounded-full border border-white/35 bg-white/18 px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] backdrop-blur-md">
                            {{ formatDate(ev.date_debut) }}
                          </span>
                          <div v-if="ev.type === 'match'" class="mt-4 grid w-full max-w-[230px] grid-cols-[1fr_auto_1fr] items-center gap-2">
                            <img v-if="logoEquipe(ev.equipe)" :src="logoEquipe(ev.equipe)" :alt="ev.equipe?.nom" class="mx-auto h-12 w-12 rounded-2xl object-cover ring-4 ring-white/20" />
                            <span v-else class="mx-auto block h-12 w-12 rounded-2xl bg-white/25 ring-4 ring-white/20"></span>
                            <span class="rounded-full bg-white px-2.5 py-1 text-[9px] font-black text-[#111827]">VS</span>
                            <span class="mx-auto block h-12 w-12 rounded-2xl bg-white/25 ring-4 ring-white/20"></span>
                          </div>
                          <img v-else-if="logoEquipe(ev.equipe)" :src="logoEquipe(ev.equipe)" :alt="ev.equipe?.nom" class="mt-4 h-14 w-14 rounded-2xl object-cover ring-4 ring-white/20" />
                          <h4 class="mt-4 text-3xl font-black leading-tight text-white sm:text-4xl">{{ ev.titre }}</h4>
                          <p class="mt-3 max-w-[220px] text-xs font-semibold leading-5 text-white/78">
                            {{ ev.type === 'match' ? `${ev.equipe?.nom || 'Equipe'} vs ${ev.adversaire || 'Adversaire'}` : ev.equipe?.nom || '' }}
                            <span v-if="ev.lieu"> - {{ ev.lieu }}</span>
                          </p>
                          <button type="button" class="mt-5 rounded-full bg-white px-5 py-2 text-xs font-black text-[#1f36bf] transition hover:bg-[#eef4ff]" @click="afficherModule('evenements')">
                            Ouvrir
                          </button>
                        </div>
                      </article>
                    </div>
                  </div>

                  <p v-else class="mt-4 rounded-2xl border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-4 py-8 text-center text-sm font-semibold text-[#6b7280]">
                    Aucun evenement proche pour le moment.
                  </p>
                </section>

                <!-- mes equipes (apercu) -->
                <section class="mt-7 rounded-[22px] border border-[#e6edf8] bg-white p-4">
                  <div class="text-center">
                    <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Mes equipes</h3>
                    <p class="mx-auto mt-1 max-w-xl text-xs font-semibold text-[#6b7280]">Les equipes que vous encadrez.</p>
                    <button type="button" class="mt-3 inline-flex rounded-full border border-[#dbe2ef] px-3 py-1.5 text-xs font-extrabold text-[#1f36bf] transition hover:bg-[#f8fbff]" @click="afficherModule('equipes')">
                      Gerer equipes
                    </button>
                  </div>

                  <div v-if="chargementEquipes" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div v-for="n in 3" :key="n" class="h-[230px] animate-pulse rounded-[30px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
                  </div>

                  <div v-else-if="equipesCoach.length" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <article
                      v-for="(equipe, idx) in equipesCoach.slice(0, 6)"
                      :key="equipe.id"
                      class="relative min-h-[230px] overflow-hidden rounded-[30px] border border-white/70 bg-cover bg-center p-5 text-white transition hover:-translate-y-1"
                      :class="idx % 2 === 1 ? 'md:translate-y-5' : ''"
                      :style="{ backgroundImage: backgroundEquipe(equipe) }"
                    >
                      <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_10%,rgba(255,255,255,0.32),transparent_26%),linear-gradient(180deg,transparent,rgba(0,0,0,0.18))]"></div>
                      <div class="relative z-10 flex min-h-[190px] flex-col justify-between">
                        <p class="w-max rounded-full border border-white/30 bg-white/14 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] backdrop-blur-md">
                          {{ equipe.categorie || 'Equipe' }}
                        </p>
                        <div>
                          <h4 class="text-3xl font-black leading-tight text-white">{{ equipe.nom }}</h4>
                          <p class="mt-2 text-xs font-semibold leading-5 text-white/76">{{ equipe.club?.nom || 'Club non defini' }}</p>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                          <p class="text-xs font-black text-white/82">{{ equipe.joueurs_total || 0 }} joueurs</p>
                          <button type="button" class="rounded-full bg-white px-4 py-2 text-xs font-black text-[#1f36bf] transition hover:bg-[#eef4ff]" @click="afficherModule('equipes')">
                            Details
                          </button>
                        </div>
                      </div>
                    </article>
                  </div>

                  <p v-else class="mt-4 rounded-2xl border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-4 py-8 text-center text-sm font-semibold text-[#6b7280]">
                    Aucune equipe assignee pour le moment.
                  </p>
                </section>
              </div>

              <!-- ══ EQUIPES ═════════════════════════════════════════════════════ -->
              <section v-else-if="moduleActif === 'equipes'" class="mt-6">
                <div class="mx-auto max-w-3xl text-center">
                  <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Gestion coach</p>
                  <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Mes equipes</h3>
                  <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#6b7280]">Consultez toutes les equipes que vous encadrez.</p>
                  <div class="mx-auto mt-5 max-w-2xl rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-2">
                    <input v-model="rechercheEquipes" type="text" placeholder="Rechercher une equipe..." class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]" />
                  </div>
                </div>

                <div v-if="chargementEquipes" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                  <div v-for="n in 6" :key="n" class="h-[230px] animate-pulse rounded-[30px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
                </div>

                <div v-else-if="equipesCoach.length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                  <article
                    v-for="(equipe, idx) in equipesCoach"
                    :key="equipe.id"
                    class="relative min-h-[230px] overflow-hidden rounded-[30px] border border-white/70 bg-cover bg-center p-5 text-white transition hover:-translate-y-1 cursor-pointer"
                    :class="idx % 2 === 1 ? 'md:translate-y-5' : ''"
                    :style="{ backgroundImage: backgroundEquipe(equipe) }"
                    @click="equipeSelectionnee = equipe"
                  >
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_10%,rgba(255,255,255,0.32),transparent_26%),linear-gradient(180deg,transparent,rgba(0,0,0,0.18))]"></div>
                    <div class="relative z-10 flex min-h-[190px] flex-col justify-between">
                      <div class="flex items-start justify-between">
                        <p class="w-max rounded-full border border-white/30 bg-white/14 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] backdrop-blur-md">
                          {{ equipe.categorie || 'Equipe' }}
                        </p>
                        <span class="rounded-full px-2 py-1 text-[10px] font-black" :class="equipe.statut === 'active' ? 'bg-[#ecfdf5] text-[#16a34a]' : 'bg-[#f1f5f9] text-[#64748b]'">
                          {{ equipe.statut || 'active' }}
                        </span>
                      </div>
                      <div>
                        <h4 class="text-3xl font-black leading-tight text-white">{{ equipe.nom }}</h4>
                        <p class="mt-2 text-xs font-semibold text-white/76">{{ equipe.club?.nom || 'Club non defini' }}</p>
                        <p class="mt-1 text-[11px] font-semibold text-white/60">Code: {{ equipe.code_invitation || '—' }}</p>
                      </div>
                      <div class="flex items-center justify-between">
                        <p class="text-xs font-black text-white/82">{{ equipe.joueurs_total || 0 }} joueurs</p>
                        <button type="button" class="rounded-full bg-white px-4 py-2 text-xs font-black text-[#1f36bf] hover:bg-[#eef4ff]" @click.stop="afficherModule('joueurs'); equipeJoueurId = String(equipe.id)">
                          Voir joueurs
                        </button>
                      </div>
                    </div>
                  </article>
                </div>

                <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
                  <h4 class="text-2xl text-[#111827]">Aucune equipe trouvee</h4>
                  <p class="mt-2 text-sm font-semibold text-[#6b7280]">Vous n'avez pas encore d'equipes assignees.</p>
                </div>
              </section>

              <!-- ══ JOUEURS ═════════════════════════════════════════════════════ -->
              <section v-else-if="moduleActif === 'joueurs'" class="mt-6">
                <div class="mx-auto max-w-3xl text-center">
                  <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Gestion coach</p>
                  <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Joueurs de l'equipe</h3>
                  <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#6b7280]">Choisissez une equipe pour voir ses joueurs.</p>

                  <div class="mx-auto mt-5 max-w-2xl">
                    <select v-model="equipeJoueurId" class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]">
                      <option value="">Choisir une equipe</option>
                      <option v-for="eq in equipesOptions" :key="eq.id" :value="String(eq.id)">{{ eq.nom }}</option>
                    </select>
                  </div>
                </div>

                <div v-if="chargementJoueurs" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                  <div v-for="n in 8" :key="n" class="h-[140px] animate-pulse rounded-[26px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
                </div>

                <div v-else-if="joueursFiltres.length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                  <article
                    v-for="joueur in joueursFiltres"
                    :key="joueur.id"
                    class="rounded-[26px] border border-[#e6edf8] bg-white p-4 transition hover:border-[#cfdaf2] hover:shadow-sm"
                  >
                    <div class="flex items-center gap-3">
                      <img v-if="joueur.photo_url || joueur.photo" :src="joueur.photo_url || joueur.photo" :alt="joueur.nom" class="h-12 w-12 rounded-2xl object-cover" />
                      <span v-else class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff,#dbe7ff_28%,#2446d8_72%)] text-lg font-black text-white">
                        {{ (joueur.prenom || joueur.name || 'J')[0] }}
                      </span>
                      <div class="min-w-0">
                        <p class="truncate text-sm font-black text-[#111827]">
                          {{ [joueur.prenom, joueur.nom].filter(Boolean).join(' ') || joueur.name || 'Joueur' }}
                        </p>
                        <p class="truncate text-[11px] font-semibold text-[#64748b]">{{ joueur.email || '—' }}</p>
                      </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between">
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-black"
                        :class="joueur.statut === 'actif' ? 'bg-[#ecfdf5] text-[#16a34a]' : joueur.statut === 'blesse' ? 'bg-[#fef2f2] text-[#ef4444]' : 'bg-[#f1f5f9] text-[#64748b]'"
                      >
                        {{ joueur.statut || 'actif' }}
                      </span>
                      <span class="text-[11px] font-semibold text-[#94a3b8]">{{ joueur.telephone || '' }}</span>
                    </div>
                  </article>
                </div>

                <div v-else-if="equipeJoueurId" class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
                  <h4 class="text-2xl text-[#111827]">Aucun joueur trouve</h4>
                  <p class="mt-2 text-sm font-semibold text-[#6b7280]">Cette equipe n'a pas encore de joueurs.</p>
                </div>

                <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
                  <p class="text-sm font-semibold text-[#6b7280]">Selectionnez une equipe pour voir ses joueurs.</p>
                </div>
              </section>

              <!-- ══ EVENEMENTS ══════════════════════════════════════════════════ -->
              <section v-else-if="moduleActif === 'evenements'" class="mt-6">

                <!-- liste -->
                <template v-if="modeEvenements === 'liste'">
                  <div class="mx-auto max-w-3xl text-center">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Gestion coach</p>
                    <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Gestion des evenements</h3>
                    <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#6b7280]">Choisissez une equipe, puis creez ou modifiez ses evenements.</p>

                    <div class="mx-auto mt-5 max-w-2xl space-y-2">
                      <select v-model="equipeEvenementId" class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]">
                        <option value="">Choisir une equipe</option>
                        <option v-for="eq in equipesOptions" :key="eq.id" :value="String(eq.id)">{{ eq.nom }}</option>
                      </select>
                    </div>

                    <button
                      type="button"
                      class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white transition hover:bg-[#2446d8]"
                      @click="ouvrirCreationEvenement"
                    >
                      <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" /></svg>
                      Nouvel evenement
                    </button>
                  </div>

                  <div v-if="chargementEvenements" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="n in 6" :key="n" class="h-[270px] animate-pulse rounded-[30px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
                  </div>

                  <div v-else-if="evenementsEquipe.length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                      v-for="ev in evenementsEquipe"
                      :key="ev.id"
                      class="relative min-h-[270px] overflow-hidden rounded-[30px] bg-cover bg-center p-5 text-white"
                      :style="{ backgroundImage: backgroundEvenement(ev) }"
                    >
                      <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_10%,rgba(255,255,255,0.30),transparent_28%),linear-gradient(180deg,transparent,rgba(0,0,0,0.82))]"></div>
                      <div class="relative z-10 flex h-full min-h-[230px] flex-col justify-between">
                        <div class="flex items-start justify-between">
                          <span class="rounded-full border border-white/30 bg-white/14 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] backdrop-blur-md">
                            {{ ev.type }}
                          </span>
                          <span class="rounded-full px-2.5 py-1 text-[10px] font-black" :class="badgeStatutEvenement(ev.statut).cls">
                            {{ badgeStatutEvenement(ev.statut).label }}
                          </span>
                        </div>
                        <div>
                          <p class="text-[11px] font-bold text-white/70">{{ formatDate(ev.date_debut) }}</p>
                          <h4 class="mt-1 text-2xl font-black leading-tight text-white">{{ ev.titre }}</h4>
                          <p v-if="ev.lieu" class="mt-1 text-xs text-white/65">📍 {{ ev.lieu }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                          <button type="button" class="flex-1 rounded-full bg-white py-2 text-xs font-black text-[#1f36bf] hover:bg-[#eef4ff]" @click="ouvrirEditionEvenement(ev)">
                            Modifier
                          </button>
                          <button type="button" class="flex-1 rounded-full border border-white/30 bg-white/10 py-2 text-xs font-black text-white hover:bg-[#fef2f2] hover:text-[#ef4444] hover:border-[#fecaca]" @click="supprimerEvenement(ev)">
                            Supprimer
                          </button>
                        </div>
                      </div>
                    </article>
                  </div>

                  <div v-else-if="equipeEvenementId" class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
                    <h4 class="text-2xl text-[#111827]">Aucun evenement</h4>
                    <p class="mt-2 text-sm font-semibold text-[#6b7280]">Creez le premier evenement pour cette equipe.</p>
                    <button type="button" class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white hover:bg-[#2446d8]" @click="ouvrirCreationEvenement">
                      <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" /></svg>
                      Creer un evenement
                    </button>
                  </div>

                  <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
                    <p class="text-sm font-semibold text-[#6b7280]">Selectionnez une equipe pour voir ses evenements.</p>
                  </div>
                </template>

                <!-- formulaire creation / edition -->
                <template v-else>
                  <div class="mx-auto max-w-2xl">
                    <button type="button" class="mb-5 inline-flex items-center gap-2 text-xs font-black text-[#1f36bf] hover:underline" @click="retourListeEvenements">
                      ← Retour a la liste
                    </button>

                    <div class="rounded-[28px] border border-[#e6edf8] bg-white p-6">
                      <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Gestion coach</p>
                      <h3 class="text-2xl font-black text-[#111827]">
                        {{ modeEvenements === 'creation' ? 'Nouvel evenement' : 'Modifier l\'evenement' }}
                      </h3>

                      <div class="mt-5 space-y-4">
                        <div>
                          <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">Titre *</label>
                          <input v-model="formulaireEvenement.titre" type="text" class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff] focus:bg-white" />
                          <p v-if="erreursEvenement.titre" class="mt-1 text-xs text-red-500">{{ erreursEvenement.titre[0] }}</p>
                        </div>

                        <div>
                          <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">Type *</label>
                          <select v-model="formulaireEvenement.type" class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]">
                            <option value="match">Match</option>
                            <option value="entrainement">Entrainement</option>
                            <option value="reunion">Reunion</option>
                          </select>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                          <div>
                            <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">Date debut *</label>
                            <input v-model="formulaireEvenement.date_debut" type="datetime-local" class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]" />
                          </div>
                          <div>
                            <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">Date fin</label>
                            <input v-model="formulaireEvenement.date_fin" type="datetime-local" class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]" />
                          </div>
                        </div>

                        <div>
                          <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">Lieu</label>
                          <input v-model="formulaireEvenement.lieu" type="text" class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff] focus:bg-white" />
                        </div>

                        <div v-if="formulaireEvenement.type === 'match'">
                          <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">Adversaire</label>
                          <input v-model="formulaireEvenement.adversaire" type="text" class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff] focus:bg-white" />
                        </div>

                        <div>
                          <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">Description</label>
                          <textarea v-model="formulaireEvenement.description" rows="3" class="w-full resize-none rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 py-3 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff] focus:bg-white"></textarea>
                        </div>

                        <div>
                          <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">Statut</label>
                          <select v-model="formulaireEvenement.statut" class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]">
                            <option value="planifie">Planifie</option>
                            <option value="en_cours">En cours</option>
                            <option value="termine">Termine</option>
                            <option value="annule">Annule</option>
                          </select>
                        </div>
                      </div>

                      <div class="mt-6 flex gap-3">
                        <button
                          type="button"
                          class="flex-1 rounded-full bg-[#111827] py-3 text-sm font-black text-white transition hover:bg-[#2446d8] disabled:opacity-60"
                          :disabled="envoiEvenement"
                          @click="modeEvenements === 'creation' ? creerEvenement() : modifierEvenement()"
                        >
                          {{ envoiEvenement ? 'Enregistrement...' : modeEvenements === 'creation' ? 'Creer l\'evenement' : 'Enregistrer les modifications' }}
                        </button>
                        <button type="button" class="rounded-full border border-[#dbe2ef] px-5 py-3 text-sm font-black text-[#6b7280] transition hover:bg-[#f8fbff]" @click="retourListeEvenements">
                          Annuler
                        </button>
                      </div>
                    </div>
                  </div>
                </template>
              </section>

              <!-- ══ CONVOCATIONS ════════════════════════════════════════════════ -->
              <section v-else-if="moduleActif === 'convocations'" class="mt-6">
                <div class="mx-auto max-w-3xl text-center">
                  <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Gestion coach</p>
                  <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Convocations</h3>
                  <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#6b7280]">Consultez et gerez les convocations de vos equipes.</p>

                  <div class="mx-auto mt-5 max-w-2xl space-y-2">
                    <select v-model="equipeConvocationId" class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]">
                      <option value="">Choisir une equipe</option>
                      <option v-for="eq in equipesOptions" :key="eq.id" :value="String(eq.id)">{{ eq.nom }}</option>
                    </select>
                  </div>
                </div>

                <div v-if="chargementConvocations" class="mt-6 space-y-3">
                  <div v-for="n in 5" :key="n" class="h-[72px] animate-pulse rounded-2xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
                </div>

                <div v-else-if="convocationsFiltrees.length" class="mt-6 space-y-3">
                  <article
                    v-for="conv in convocationsFiltrees"
                    :key="conv.id"
                    class="flex items-center justify-between gap-4 rounded-2xl border border-[#e6edf8] bg-white px-4 py-3"
                  >
                    <div class="flex items-center gap-3 min-w-0">
                      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[radial-gradient(circle_at_35%_25%,#ffffff,#dbe7ff_28%,#2446d8_72%)] text-sm font-black text-white">
                        {{ (conv.utilisateur?.prenom || conv.utilisateur?.name || 'J')[0] }}
                      </span>
                      <div class="min-w-0">
                        <p class="truncate text-sm font-black text-[#111827]">
                          {{ [conv.utilisateur?.prenom, conv.utilisateur?.nom].filter(Boolean).join(' ') || conv.utilisateur?.name || 'Joueur' }}
                        </p>
                        <p class="truncate text-[11px] font-semibold text-[#64748b]">{{ conv.evenement?.titre || 'Evenement' }}</p>
                      </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-black" :class="badgeStatutConvocation(conv.statut).cls">
                        {{ badgeStatutConvocation(conv.statut).label }}
                      </span>
                      <select
                        :value="conv.statut"
                        class="h-8 rounded-full border border-[#dbe2ef] bg-white px-2 text-[11px] font-bold text-[#1f2a44] outline-none"
                        @change="modifierStatutConvocation(conv, $event.target.value)"
                      >
                        <option value="convoque">Convoque</option>
                        <option value="confirme">Confirme</option>
                        <option value="refuse">Refuse</option>
                        <option value="en_attente">En attente</option>
                      </select>
                    </div>
                  </article>
                </div>

                <div v-else-if="equipeConvocationId" class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
                  <h4 class="text-2xl text-[#111827]">Aucune convocation</h4>
                  <p class="mt-2 text-sm font-semibold text-[#6b7280]">Creez des evenements et ajoutez des convocations depuis la section Evenements.</p>
                </div>

                <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
                  <p class="text-sm font-semibold text-[#6b7280]">Selectionnez une equipe pour voir ses convocations.</p>
                </div>
              </section>

              <!-- ══ MESSAGERIE ══════════════════════════════════════════════════ -->
              <section v-else-if="moduleActif === 'messagerie'" class="mt-6">
                <div class="mx-auto max-w-3xl text-center">
                  <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Gestion coach</p>
                  <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Messagerie</h3>
                  <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#6b7280]">Communiquez avec vos equipes via les canaux.</p>
                </div>

                <div v-if="chargementCanaux" class="mt-6 grid gap-4 md:grid-cols-[260px_1fr]">
                  <div class="h-[420px] animate-pulse rounded-[24px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
                  <div class="h-[420px] animate-pulse rounded-[24px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
                </div>

                <div v-else-if="canaux.length" class="mt-6 grid gap-4 md:grid-cols-[260px_1fr]">
                  <!-- liste canaux -->
                  <aside class="rounded-[24px] border border-[#e6edf8] bg-white p-3">
                    <p class="mb-3 px-1 text-xs font-black uppercase tracking-[0.15em] text-[#64748b]">Canaux</p>
                    <button
                      v-for="canal in canaux"
                      :key="canal.id"
                      type="button"
                      class="mb-1 flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-left transition"
                      :class="canalSelectionne?.id === canal.id ? 'bg-[#f2f6ff] text-[#1f36bf]' : 'text-[#374151] hover:bg-[#f8fbff]'"
                      @click="selectionnerCanal(canal)"
                    >
                      <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[#2446d8] text-[11px] font-black text-white">
                        {{ (canal.nom || '#')[0].toUpperCase() }}
                      </span>
                      <div class="min-w-0">
                        <p class="truncate text-xs font-black">{{ canal.nom }}</p>
                        <p class="truncate text-[10px] font-semibold text-[#94a3b8]">{{ canal.type_canal }}</p>
                      </div>
                    </button>
                  </aside>

                  <!-- messages -->
                  <div class="flex flex-col rounded-[24px] border border-[#e6edf8] bg-white overflow-hidden" style="min-height: 420px;">
                    <div class="border-b border-[#eef2fb] px-4 py-3">
                      <p class="text-sm font-black text-[#111827]"># {{ canalSelectionne?.nom || 'Canal' }}</p>
                      <p class="text-[11px] font-semibold text-[#64748b]">{{ canalSelectionne?.type_canal || '' }}</p>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4 space-y-3">
                      <p v-if="chargementMessages" class="text-xs font-bold text-[#64748b]">Chargement des messages...</p>
                      <p v-else-if="!messages.length" class="text-xs font-semibold text-[#94a3b8]">Aucun message dans ce canal.</p>

                      <article
                        v-for="msg in messages"
                        :key="msg.id"
                        class="flex items-start gap-3"
                      >
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[radial-gradient(circle_at_35%_25%,#ffffff,#dbe7ff_28%,#2446d8_72%)] text-[11px] font-black text-white">
                          {{ (msg.expediteur?.prenom || msg.expediteur?.name || 'U')[0] }}
                        </span>
                        <div class="min-w-0">
                          <div class="flex items-baseline gap-2">
                            <p class="text-xs font-black text-[#111827]">
                              {{ [msg.expediteur?.prenom, msg.expediteur?.nom].filter(Boolean).join(' ') || msg.expediteur?.name || 'Utilisateur' }}
                            </p>
                            <p class="text-[10px] font-semibold text-[#94a3b8]">{{ formatDateHeure(msg.created_at) }}</p>
                          </div>
                          <p class="mt-0.5 text-sm font-semibold text-[#374151]">{{ msg.contenu }}</p>
                        </div>
                      </article>
                    </div>

                    <div class="border-t border-[#eef2fb] p-3">
                      <div class="flex items-center gap-2">
                        <input
                          v-model="messageEnvoi"
                          type="text"
                          placeholder="Ecrire un message..."
                          class="flex-1 h-10 rounded-full border border-[#dbe2ef] bg-[#f8fbff] px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff] focus:bg-white"
                          @keyup.enter="envoyerMessage"
                        />
                        <button
                          type="button"
                          class="inline-flex h-10 items-center gap-1.5 rounded-full bg-[#111827] px-4 text-xs font-black text-white transition hover:bg-[#2446d8] disabled:opacity-60"
                          :disabled="!messageEnvoi.trim() || envoiMessage"
                          @click="envoyerMessage"
                        >
                          Envoyer
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
                  <h4 class="text-2xl text-[#111827]">Aucun canal disponible</h4>
                  <p class="mt-2 text-sm font-semibold text-[#6b7280]">Les canaux sont crees par le president du club.</p>
                </div>
              </section>

            </template>
          </div>
        </article>
      </section>
    </div>
  </main>
</template>

<style scoped>
.dashboard-event-carousel {
  animation: carousel-scroll 28s linear infinite;
}
.dashboard-event-carousel:hover {
  animation-play-state: paused;
}
@keyframes carousel-scroll {
  from { transform: translateX(0); }
  to { transform: translateX(-50%); }
}
</style>
