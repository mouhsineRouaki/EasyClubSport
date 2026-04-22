<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import blueBackground from '@/assets/Background.jpg'
import logoMark from '@/assets/logo-easyclubsport-mark.svg'
import { authGet, authPost, authPut, authDelete } from '@/shared/services/apiClient'
import { notifyError, notifySuccess } from '@/shared/services/toastService'
import { subscribeToCanalMessages, subscribeToNotifications, disconnectRealtime } from '@/shared/services/realtimeService'
import AppNotificationsDropdown from '@/shared/components/AppNotificationsDropdown.vue'
import AppProfileManager from '@/shared/components/AppProfileManager.vue'
import AppRoleHeroBanner from '@/shared/components/AppRoleHeroBanner.vue'
import { useAuthSession } from '@/shared/session/useAuthSession'
import CoachDashboardHome from '@/roles/coach/dashboard/components/CoachDashboardHome.vue'
import CoachEquipes from '@/roles/coach/equipes/components/CoachEquipes.vue'
import CoachJoueurs from '@/roles/coach/joueurs/components/CoachJoueurs.vue'
import CoachEvenements from '@/roles/coach/evenements/components/CoachEvenements.vue'
import CoachDisponibilites from '@/roles/coach/disponibilites/components/CoachDisponibilites.vue'
import CoachConvocations from '@/roles/coach/convocations/components/CoachConvocations.vue'
import CoachMessagerie from '@/roles/coach/messagerie/components/CoachMessagerie.vue'

const { utilisateur: utilisateurConnecte, deconnecter, gererErreurAuthentification, sauvegarderUtilisateur } = useAuthSession()

// ─── état global ───────────────────────────────────────────────────────────────
const chargement = ref(true)
const chargementRafraichissement = ref(false)
const moduleActif = ref('dashboard')
const dashboard = ref(null)
const notificationsCoach = ref([])
const notificationsNonLuesTotal = ref(0)
const chargementNotifications = ref(false)
const notificationOuverte = ref(false)
const actionNotification = ref('')
const rafraichissementAuto = ref(true)
const derniereMiseAJour = ref(null)
const intervalRafraichissement = ref(null)
const stopRealtimeNotifications = ref(() => {})

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
const chargementCompositionMatch = ref(false)
const enregistrementCompositionMatch = ref(false)
const compositionMatchEvenement = ref(null)
const chargementFeuilleMatch = ref(false)
const enregistrementFeuilleMatch = ref(false)
const feuilleMatchEvenement = ref(null)
const chargementStatistiquesMatch = ref(false)
const enregistrementStatistiquesMatch = ref(false)
const statistiquesMatchEvenement = ref({ resume: {}, joueurs: [] })

// ─── convocations ──────────────────────────────────────────────────────────────
const chargementConvocations = ref(false)
const convocationsEquipe = ref([])
const equipeConvocationId = ref('')
const evenementConvocationId = ref('')
const rechercheConvocations = ref('')

// disponibilites
const chargementDisponibilites = ref(false)
const disponibilitesEvenement = ref([])
const equipeDisponibiliteId = ref('')
const evenementDisponibiliteId = ref('')
const rechercheDisponibilites = ref('')

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
  { key: 'disponibilites', label: 'Disponibilites' },
  { key: 'convocations', label: 'Convocations' },
  { key: 'messagerie', label: 'Messagerie' },
  { key: 'profil', label: 'Profil' },
]

const liensGlobaux = [
  { label: 'Dashboard', to: '/coach/dashboard' },
  { label: 'About us', href: '#about-easyclubsport' },
  { label: 'Contact us', href: '#contact-support' },
]

const placeholdersRecherche = {
  equipes: 'Rechercher une equipe',
  joueurs: 'Rechercher un joueur',
  evenements: 'Rechercher un evenement',
  disponibilites: 'Rechercher une disponibilite',
  convocations: 'Rechercher une convocation',
  messagerie: 'Rechercher une conversation',
}

const utilisateurResume = computed(() => {
  const u = utilisateurConnecte.value || {}
  return {
    nom: [u.prenom, u.nom].filter(Boolean).join(' ') || u.name || 'Coach',
    email: u.email || '',
    image: u.photo_url || u.photo || '',
  }
})

const mettreAJourProfilCoach = (payload) => {
  const utilisateur = payload?.utilisateur || payload?.data?.utilisateur || null

  if (!utilisateur) return

  utilisateurConnecte.value = utilisateur
  sauvegarderUtilisateur(utilisateur)
}

const champsRechercheParModule = {
  equipes: rechercheEquipes,
  joueurs: rechercheJoueurs,
  evenements: rechercheEvenements,
  disponibilites: rechercheDisponibilites,
  convocations: rechercheConvocations,
  messagerie: rechercheMessagerie,
}

const placeholderRecherche = computed(() => placeholdersRecherche[moduleActif.value] || 'Search')

const rechercheNavigation = computed({
  get() {
    return champsRechercheParModule[moduleActif.value]?.value || ''
  },
  set(value) {
    const champRecherche = champsRechercheParModule[moduleActif.value]

    if (champRecherche) {
      champRecherche.value = value
    }
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
  if (key === 'disponibilites') await initialiserDisponibilites()
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
    else if (moduleActif.value === 'disponibilites') await chargerDisponibilites()
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
    sauvegarderUtilisateur(repProfil?.data?.utilisateur || repProfil?.data || null)
    const equipeDashboardId = repDash?.data?.equipe?.id ? String(repDash.data.equipe.id) : ''
    if (equipeDashboardId) {
      equipeJoueurId.value = equipeDashboardId
      equipeEvenementId.value = equipeDashboardId
      equipeDisponibiliteId.value = equipeDashboardId
      equipeConvocationId.value = equipeDashboardId
    }
    derniereMiseAJour.value = new Date().toISOString()
  } catch (error) {
    if (gererErreurAuthentification(error)) {
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

const basculerNotifications = async () => {
  notificationOuverte.value = !notificationOuverte.value

  if (notificationOuverte.value) {
    await chargerNotifications()
  }
}

const integrerNotification = (notification) => {
  if (!notification?.id) return

  const indexExistant = notificationsCoach.value.findIndex((item) => String(item.id) === String(notification.id))
  if (indexExistant >= 0) {
    const copie = [...notificationsCoach.value]
    copie[indexExistant] = { ...copie[indexExistant], ...notification }
    notificationsCoach.value = copie
  } else {
    notificationsCoach.value = [notification, ...notificationsCoach.value]
  }

  notificationsNonLuesTotal.value = notificationsCoach.value.filter((item) => !item.est_lue).length
}

const marquerToutesNotificationsCommeLues = async () => {
  try {
    await authPut('/coach/notifications/lecture/toutes')
    notificationsCoach.value = notificationsCoach.value.map((notification) => ({
      ...notification,
      est_lue: true,
      date_lecture: notification.date_lecture || new Date().toISOString(),
    }))
    notificationsNonLuesTotal.value = 0
  } catch {
    // silencieux
  }
}

const ouvrirNotificationCoach = async (notification) => {
  await marquerNotificationLue(notification)

  if (notification?.module_cible === 'messagerie') {
    moduleActif.value = 'messagerie'
    await chargerCanaux()
    if (notification.canal_id) {
      const canal = canaux.value.find((item) => String(item.id) === String(notification.canal_id))
      if (canal) {
        await selectionnerCanal(canal)
      }
    }
    return
  }

  if (notification?.module_cible === 'convocations') {
    moduleActif.value = 'convocations'
    await initialiserConvocations()
    return
  }

  if (notification?.module_cible === 'evenements') {
    moduleActif.value = 'evenements'
    if (notification?.evenement?.equipe?.id) {
      equipeEvenementId.value = String(notification.evenement.equipe.id)
    }
    await initialiserEvenements()
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
    if (!equipeDisponibiliteId.value && equipesCoach.value.length) equipeDisponibiliteId.value = String(equipesCoach.value[0].id)
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

const ouvrirDetailEvenement = async (evenement) => {
  if (!evenement) return
  equipeDisponibiliteId.value = String(equipeEvenementId.value || evenement.equipe?.id || '')
  evenementDisponibiliteId.value = String(evenement.id)
  await chargerDisponibilites()
  await chargerCompositionMatch(evenement)
  await chargerFeuilleMatch(evenement)
  await chargerStatistiquesMatch(evenement)
}

const chargerCompositionMatch = async (evenement) => {
  if (!evenement || evenement.type !== 'match') {
    compositionMatchEvenement.value = null
    return
  }

  chargementCompositionMatch.value = true
  try {
    const rep = await authGet(`/coach/equipes/${equipeEvenementId.value}/evenements/${evenement.id}/composition`)
    compositionMatchEvenement.value = rep?.data?.composition || null
  } catch (error) {
    compositionMatchEvenement.value = null
    notifyError(error?.response?.message || error.message || 'Impossible de charger la composition du match.')
  } finally {
    chargementCompositionMatch.value = false
  }
}

const enregistrerCompositionMatch = async ({ evenement, payload }) => {
  enregistrementCompositionMatch.value = true
  try {
    const rep = await authPut(`/coach/equipes/${equipeEvenementId.value}/evenements/${evenement.id}/composition`, payload)
    compositionMatchEvenement.value = rep?.data?.composition || null
    notifySuccess(rep?.message || 'Composition du match enregistree.')
    await chargerEvenements()
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible d enregistrer la composition du match.')
  } finally {
    enregistrementCompositionMatch.value = false
  }
}

const chargerFeuilleMatch = async (evenement) => {
  if (!evenement || evenement.type !== 'match') {
    feuilleMatchEvenement.value = null
    return
  }

  chargementFeuilleMatch.value = true
  try {
    const rep = await authGet(`/coach/equipes/${equipeEvenementId.value}/evenements/${evenement.id}/feuille-match`)
    feuilleMatchEvenement.value = rep?.data?.feuille_match || null
  } catch (error) {
    feuilleMatchEvenement.value = null
    notifyError(error?.response?.message || error.message || 'Impossible de charger la feuille de match.')
  } finally {
    chargementFeuilleMatch.value = false
  }
}

const enregistrerFeuilleMatch = async ({ evenement, payload }) => {
  enregistrementFeuilleMatch.value = true
  try {
    const rep = await authPut(`/coach/equipes/${equipeEvenementId.value}/evenements/${evenement.id}/feuille-match`, payload)
    feuilleMatchEvenement.value = rep?.data?.feuille_match || null
    notifySuccess(rep?.message || 'Feuille de match enregistree.')
    await chargerEvenements()
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible d enregistrer la feuille de match.')
  } finally {
    enregistrementFeuilleMatch.value = false
  }
}

const chargerStatistiquesMatch = async (evenement) => {
  if (!evenement || evenement.type !== 'match') {
    statistiquesMatchEvenement.value = { resume: {}, joueurs: [] }
    return
  }

  chargementStatistiquesMatch.value = true
  try {
    const rep = await authGet(`/coach/equipes/${equipeEvenementId.value}/evenements/${evenement.id}/statistiques-match`)
    statistiquesMatchEvenement.value = rep?.data?.statistiques || { resume: {}, joueurs: [] }
  } catch (error) {
    statistiquesMatchEvenement.value = { resume: {}, joueurs: [] }
    notifyError(error?.response?.message || error.message || 'Impossible de charger les statistiques du match.')
  } finally {
    chargementStatistiquesMatch.value = false
  }
}

const enregistrerStatistiquesMatch = async ({ evenement, payload }) => {
  enregistrementStatistiquesMatch.value = true
  try {
    const rep = await authPut(`/coach/equipes/${equipeEvenementId.value}/evenements/${evenement.id}/statistiques-match`, payload)
    statistiquesMatchEvenement.value = rep?.data?.statistiques || { resume: {}, joueurs: [] }
    notifySuccess(rep?.message || 'Statistiques du match enregistrees.')
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible d enregistrer les statistiques du match.')
  } finally {
    enregistrementStatistiquesMatch.value = false
  }
}

const chargerDisponibilites = async () => {
  if (!equipeDisponibiliteId.value) return
  if (!evenementDisponibiliteId.value) {
    disponibilitesEvenement.value = []
    return
  }

  chargementDisponibilites.value = true
  try {
    const rep = await authGet(`/coach/equipes/${equipeDisponibiliteId.value}/evenements/${evenementDisponibiliteId.value}/disponibilites`)
    disponibilitesEvenement.value = rep?.data?.disponibilites || []
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les disponibilites.')
  } finally {
    chargementDisponibilites.value = false
  }
}

const initialiserDisponibilites = async () => {
  if (!equipesCoach.value.length) await chargerEquipes()

  if (!equipeDisponibiliteId.value && equipesCoach.value.length) {
    equipeDisponibiliteId.value = String(equipesCoach.value[0].id)
  }

  if (!evenementsEquipe.value.length || String(equipeEvenementId.value) !== String(equipeDisponibiliteId.value)) {
    equipeEvenementId.value = equipeDisponibiliteId.value
    await chargerEvenements()
  }

  if (!evenementDisponibiliteId.value && evenementsEquipe.value.length) {
    evenementDisponibiliteId.value = String(evenementsEquipe.value[0].id)
  }

  if (evenementDisponibiliteId.value) {
    await chargerDisponibilites()
  }
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

  if (!equipeConvocationId.value && equipesCoach.value.length) {
    equipeConvocationId.value = String(equipesCoach.value[0].id)
  }

  if (!equipeConvocationId.value) {
    convocationsEquipe.value = []
    evenementConvocationId.value = ''
    return
  }

  if (String(equipeEvenementId.value) !== String(equipeConvocationId.value)) {
    equipeEvenementId.value = equipeConvocationId.value
    await chargerEvenements()
  } else if (!evenementsEquipe.value.length) {
    await chargerEvenements()
  }

  if (!evenementsEquipe.value.some((evenement) => String(evenement.id) === String(evenementConvocationId.value))) {
    evenementConvocationId.value = evenementsEquipe.value[0] ? String(evenementsEquipe.value[0].id) : ''
  }

  await chargerConvocations()
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

const convoquerJoueurDepuisDisponibilite = async ({ evenement, item }) => {
  try {
    const rep = await authPost(`/coach/equipes/${equipeEvenementId.value}/evenements/${evenement.id}/convocations`, {
      utilisateur_ids: [item.utilisateur_id],
      statut: 'convoque',
    })
    notifySuccess(rep?.message || 'Convocation creee avec succes.')
    await chargerDisponibilites()
    if (equipeConvocationId.value !== equipeEvenementId.value) {
      equipeConvocationId.value = equipeEvenementId.value
    }
    evenementConvocationId.value = String(evenement.id)
    await chargerConvocations()
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de creer cette convocation.')
  }
}

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
watch(equipeEvenementId, async () => {
  if (moduleActif.value === 'evenements') {
    await chargerEvenements()
  }

  compositionMatchEvenement.value = null
  feuilleMatchEvenement.value = null
  statistiquesMatchEvenement.value = { resume: {}, joueurs: [] }

  if (String(equipeDisponibiliteId.value) === String(equipeEvenementId.value) && moduleActif.value === 'disponibilites') {
    if (!evenementsEquipe.value.some((evenement) => String(evenement.id) === String(evenementDisponibiliteId.value))) {
      evenementDisponibiliteId.value = evenementsEquipe.value[0] ? String(evenementsEquipe.value[0].id) : ''
    }
  }
})
watch(equipeDisponibiliteId, async () => {
  if (moduleActif.value !== 'disponibilites') return
  equipeEvenementId.value = equipeDisponibiliteId.value
  await chargerEvenements()
  evenementDisponibiliteId.value = evenementsEquipe.value[0] ? String(evenementsEquipe.value[0].id) : ''
  await chargerDisponibilites()
})
watch(evenementDisponibiliteId, () => { if (moduleActif.value === 'disponibilites') chargerDisponibilites() })
watch(equipeConvocationId, async () => {
  if (moduleActif.value !== 'convocations') return

  if (!equipeConvocationId.value) {
    evenementConvocationId.value = ''
    convocationsEquipe.value = []
    return
  }

  equipeEvenementId.value = equipeConvocationId.value
  await chargerEvenements()
  evenementConvocationId.value = evenementsEquipe.value[0] ? String(evenementsEquipe.value[0].id) : ''
  await chargerConvocations()
})

// ─── lifecycle ─────────────────────────────────────────────────────────────────
onMounted(async () => {
  await chargerDashboard()
  await chargerNotifications()
  await chargerEquipes()
  if (utilisateurConnecte.value?.id) {
    stopRealtimeNotifications.value = subscribeToNotifications(utilisateurConnecte.value.id, (notification) => {
      integrerNotification(notification)
    })
  }
  demarrerRafraichissementAuto()
})

onBeforeUnmount(() => {
  arreterRafraichissementAuto()
  clearTimeout(debounceEquipes.value)
  clearTimeout(debounceJoueurs.value)
  clearTimeout(debounceEvenements.value)
  stopRealtimeCoach.value()
  stopRealtimeNotifications.value()
  disconnectRealtime()
})
</script>

<template>
  <main class="min-h-screen bg-[#f4f6fb] font-['Plus_Jakarta_Sans',Inter,sans-serif] text-[#111827]">
    <div class="mx-auto max-w-[1450px] px-2 pb-5 pt-2 sm:px-4 sm:pt-3">

      <!-- ── HERO BANNER ─────────────────────────────────────────────────────── -->
      <AppRoleHeroBanner
        :background-src="blueBackground"
        :logo-src="logoMark"
        home-route="/coach/dashboard"
        :global-links="liensGlobaux"
        @logout="deconnecter"
      >
        <template #title>
          Gerez vos equipes
          <br class="hidden sm:block" />
          avec une interface claire
        </template>

        <template #description>
          Votre espace coach centralise equipes, evenements, convocations et messagerie en un seul endroit.
        </template>

        <template #actions>
          <button
            type="button"
            class="rounded-full bg-white px-6 py-2 text-sm font-bold text-[#1f36bf] transition hover:bg-[#eef2ff]"
            @click="afficherModule('equipes')"
          >
            Mes equipes
          </button>
          <button
            type="button"
            class="rounded-full border border-white/35 bg-white/8 px-6 py-2 text-sm font-semibold text-white transition hover:bg-white/20"
            @click="afficherModule('evenements')"
          >
            Voir les evenements
          </button>
        </template>
      </AppRoleHeroBanner>

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
                <input v-model="rechercheNavigation" type="text" :placeholder="placeholderRecherche"
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
              <AppNotificationsDropdown
                :open="notificationOuverte"
                :loading="chargementNotifications"
                :unread-total="notificationsNonLuesTotal"
                :notifications="notificationsRecentes"
                :action-in-progress="actionNotification"
                :formatter="formatDateHeure"
                empty-text="Aucune notification."
                @toggle="basculerNotifications"
                @refresh="chargerNotifications"
                @mark-all="marquerToutesNotificationsCommeLues"
                @notification-click="ouvrirNotificationCoach"
                @decision="repondreInvitation($event.notification, $event.decision)"
              />

              <button
                type="button"
                class="rounded-full transition hover:scale-[1.03]"
                title="Ouvrir le profil"
                @click="afficherModule('profil')"
              >
                <img v-if="utilisateurResume.image" :src="utilisateurResume.image" :alt="utilisateurResume.nom"
                  class="h-8 w-8 rounded-full object-cover" />
                <span v-else
                  class="block h-8 w-8 rounded-full bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)] ring-1 ring-[#dbe2ef]"></span>
              </button>
            </div>
          </div>
          <div class="px-3 py-4 sm:px-5 sm:py-5">
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
                :disponibilites-evenement="disponibilitesEvenement" :chargement-disponibilites="chargementDisponibilites"
                :composition-match="compositionMatchEvenement" :chargement-composition="chargementCompositionMatch"
                :enregistrement-composition="enregistrementCompositionMatch"
                :feuille-match="feuilleMatchEvenement" :chargement-feuille-match="chargementFeuilleMatch"
                :enregistrement-feuille-match="enregistrementFeuilleMatch"
                :statistiques-match="statistiquesMatchEvenement" :chargement-statistiques-match="chargementStatistiquesMatch"
                :enregistrement-statistiques-match="enregistrementStatistiquesMatch"
                @update:equipe-id="equipeEvenementId = $event" @creer="creerEvenement" @modifier="modifierEvenement"
                @supprimer="supprimerEvenement" @ouvrir-detail="ouvrirDetailEvenement"
                @convoquer-joueur="convoquerJoueurDepuisDisponibilite"
                @enregistrer-composition="enregistrerCompositionMatch"
                @enregistrer-feuille-match="enregistrerFeuilleMatch"
                @enregistrer-statistiques-match="enregistrerStatistiquesMatch" />

              <CoachDisponibilites v-else-if="moduleActif === 'disponibilites'" :disponibilites="disponibilitesEvenement"
                :equipes="equipesOptions" :equipe-id="equipeDisponibiliteId" :evenements="evenementsEquipe"
                :evenement-id="evenementDisponibiliteId" :chargement="chargementDisponibilites"
                :recherche="rechercheDisponibilites" @update:equipe-id="equipeDisponibiliteId = $event"
                @update:evenement-id="evenementDisponibiliteId = $event" @update:recherche="rechercheDisponibilites = $event"
                @aller-convocations="afficherModule('convocations')" />

              <CoachConvocations v-else-if="moduleActif === 'convocations'" :convocations="convocationsEquipe"
                :equipes="equipesOptions" :equipe-id="equipeConvocationId" :evenements="evenementsEquipe"
                :evenement-id="evenementConvocationId" :chargement="chargementConvocations"
                :recherche="rechercheConvocations" @update:equipe-id="equipeConvocationId = $event"
                @update:evenement-id="evenementConvocationId = $event"
                @update:recherche="rechercheConvocations = $event" @modifier-statut="modifierStatutConvocation" />

              <CoachMessagerie v-else-if="moduleActif === 'messagerie'" :canaux="canaux" :messages="messages"
                :canal-selectionne="canalSelectionne" :chargement-canaux="chargementCanaux"
                :chargement-messages="chargementMessages" :envoi-message="envoiMessage"
                @selectionner-canal="selectionnerCanal" @envoyer-message="envoyerMessage" />

              <AppProfileManager
                v-else-if="moduleActif === 'profil'"
                :visible="moduleActif === 'profil'"
                role-label="Coach"
                profile-endpoint="/coach/profil"
                :show-status="true"
                @saved="mettreAJourProfilCoach"
              />
            </template>
          </div>
        </article>
      </section>
    </div>
  </main>
</template>
