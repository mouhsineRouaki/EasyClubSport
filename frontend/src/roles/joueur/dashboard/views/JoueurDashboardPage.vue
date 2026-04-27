<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import blueBackground from '@/assets/Background.jpg'
import logoMark from '@/assets/logo-easyclubsport-mark.svg'
import AppProfileManager from '@/shared/components/AppProfileManager.vue'
import AppRoleHeroBanner from '@/shared/components/AppRoleHeroBanner.vue'
import AppRoleWorkspacePanel from '@/shared/components/AppRoleWorkspacePanel.vue'
import JoueurAnnonces from '@/roles/joueur/annonces/components/JoueurAnnonces.vue'
import JoueurConvocations from '@/roles/joueur/convocations/components/JoueurConvocations.vue'
import JoueurDashboardHome from '@/roles/joueur/dashboard/components/JoueurDashboardHome.vue'
import JoueurEvenements from '@/roles/joueur/evenements/components/JoueurEvenements.vue'
import JoueurMessagerie from '@/roles/joueur/messagerie/components/JoueurMessagerie.vue'
import { useAuthSession } from '@/shared/session/useAuthSession'
import { authGet, authPost, authPut } from '@/shared/services/apiClient'
import { disconnectRealtime, subscribeToCanalMessages, subscribeToNotifications } from '@/shared/services/realtimeService'
import { notifyError, notifySuccess } from '@/shared/services/toastService'
 
const { utilisateur: utilisateurConnecte, deconnecter, gererErreurAuthentification, sauvegarderUtilisateur } = useAuthSession()

const liensFonctionnalites = [
  { key: 'dashboard', label: 'Dashboard' },
  { key: 'evenements', label: 'Evenements' },
  { key: 'annonces', label: 'Annonces' },
  { key: 'convocations', label: 'Convocations' },
  { key: 'messagerie', label: 'Messagerie' },
  { key: 'profil', label: 'Profil' },
]

const liensGlobaux = [
  { label: 'Dashboard', to: '/joueur/dashboard' },
  { label: 'About us', href: '#about-easyclubsport' },
  { label: 'Contact us', href: '#contact-support' },
]

const placeholdersRecherche = {
  evenements: 'Rechercher un evenement',
  annonces: 'Rechercher une annonce',
  convocations: 'Rechercher une convocation',
}

const creerStatistiquesMatchVides = () => ({
  resume: {},
  joueurs: [],
})

// Etat general
const chargement = ref(true)
const chargementRafraichissement = ref(false)
const moduleActif = ref('dashboard')
const derniereMiseAJour = ref(null)
const intervalRafraichissement = ref(null)

// Utilisateur et notifications
const notificationsJoueur = ref([])
const notificationsNonLuesTotal = ref(0)
const chargementNotifications = ref(false)
const notificationOuverte = ref(false)
const stopRealtimeNotifications = ref(() => {})

// Donnees du dashboard
const equipe = ref(null)
const evenementsDashboard = ref([])
const evenementsModule = ref([])
const convocationsEquipe = ref([])

// Evenements et convocations
const chargementEvenements = ref(false)
const chargementAnnonces = ref(false)
const chargementConvocations = ref(false)
const rechercheEvenements = ref('')
const rechercheAnnonces = ref('')
const rechercheConvocations = ref('')
const debounceEvenements = ref(null)
const debounceAnnonces = ref(null)
const annoncesModule = ref([])
const paginationAnnonces = ref(null)

// Details d'un match
const chargementCompositionMatch = ref(false)
const compositionMatchEvenement = ref(null)
const chargementFeuilleMatch = ref(false)
const feuilleMatchEvenement = ref(null)
const chargementStatistiquesMatch = ref(false)
const statistiquesMatchEvenement = ref(creerStatistiquesMatchVides())

// Messagerie
const chargementCanaux = ref(false)
const canaux = ref([])
const canalSelectionne = ref(null)
const chargementMessages = ref(false)
const messages = ref([])
const envoiMessage = ref(false)
const stopRealtimeJoueur = ref(() => {})

// Rejoindre une equipe
const modalRejoindreEquipeVisible = ref(false)
const codeInvitationEquipe = ref('')
const chargementRejoindreEquipe = ref(false)
const erreurCodeInvitation = ref('')

const champsRechercheParModule = {
  evenements: rechercheEvenements,
  annonces: rechercheAnnonces,
  convocations: rechercheConvocations,
}

const notificationsRecentes = computed(() => notificationsJoueur.value.slice(0, 6))
const notificationsDropdownProps = computed(() => ({
  open: notificationOuverte.value,
  loading: chargementNotifications.value,
  unreadTotal: notificationsNonLuesTotal.value,
  notifications: notificationsRecentes.value,
  formatter: formatDateHeure,
  emptyText: 'Aucune notification.',
}))

const placeholderRecherche = computed(() => {
  return placeholdersRecherche[moduleActif.value] || 'Search'
})

const utilisateurResume = computed(() => {
  const utilisateur = utilisateurConnecte.value || {}

  return {
    nom: [utilisateur.prenom, utilisateur.nom].filter(Boolean).join(' ') || utilisateur.name || 'Joueur',
    email: utilisateur.email || '',
    image: utilisateur.photo_url || utilisateur.photo || '',
  }
})

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

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'short',
    timeStyle: 'medium',
  }).format(new Date(date))
}

const reinitialiserDetailsMatch = () => {
  compositionMatchEvenement.value = null
  feuilleMatchEvenement.value = null
  statistiquesMatchEvenement.value = creerStatistiquesMatchVides()
}

const mettreAJourTotalNotifications = () => {
  notificationsNonLuesTotal.value = notificationsJoueur.value.filter((notification) => !notification.est_lue).length
}

const mettreAJourProfilJoueur = (payload) => {
  const utilisateur = payload?.utilisateur || payload?.data?.utilisateur || null
  const equipeUtilisateur = payload?.equipe || payload?.data?.equipe || null

  if (utilisateur) {
    utilisateurConnecte.value = utilisateur
    sauvegarderUtilisateur(utilisateur)
  }

  if (equipeUtilisateur) {
    equipe.value = equipeUtilisateur
  }
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
          nom:
            [utilisateurConnecte.value.prenom, utilisateurConnecte.value.nom].filter(Boolean).join(' ') ||
            utilisateurConnecte.value.name ||
            'Utilisateur',
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

const chargerDashboard = async () => {
  chargement.value = true

  try {
    const [reponseDashboard, reponseProfil] = await Promise.all([
      authGet('/joueur/dashboard'),
      authGet('/auth/moi'),
    ])

    equipe.value = reponseDashboard?.data?.equipe || null
    evenementsDashboard.value = reponseDashboard?.data?.evenements || []
    convocationsEquipe.value = reponseDashboard?.data?.convocations || []
    sauvegarderUtilisateur(reponseProfil?.data?.utilisateur || reponseProfil?.data || null)
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

const chargerNotifications = async () => {
  chargementNotifications.value = true

  try {
    const reponse = await authGet('/joueur/notifications')
    notificationsJoueur.value = reponse?.data?.notifications || []
    mettreAJourTotalNotifications()
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
  } catch {
    // silencieux
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

  const indexExistant = notificationsJoueur.value.findIndex((item) => String(item.id) === String(notification.id))

  if (indexExistant >= 0) {
    const copie = [...notificationsJoueur.value]
    copie[indexExistant] = { ...copie[indexExistant], ...notification }
    notificationsJoueur.value = copie
  } else {
    notificationsJoueur.value = [notification, ...notificationsJoueur.value]
  }

  mettreAJourTotalNotifications()
}

const marquerToutesNotificationsCommeLues = async () => {
  try {
    await authPut('/joueur/notifications/lecture/toutes')
    notificationsJoueur.value = notificationsJoueur.value.map((notification) => ({
      ...notification,
      est_lue: true,
      date_lecture: notification.date_lecture || new Date().toISOString(),
    }))
    notificationsNonLuesTotal.value = 0
  } catch {
    // silencieux
  }
}

const chargerEvenements = async () => {
  chargementEvenements.value = true

  try {
    const reponse = await authGet('/joueur/evenements', { q: rechercheEvenements.value })
    evenementsModule.value = reponse?.data?.evenements || []
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les evenements.')
  } finally {
    chargementEvenements.value = false
  }
}

const chargerAnnonces = async (page = 1) => {
  chargementAnnonces.value = true

  try {
    const reponse = await authGet('/joueur/annonces', {
      q: rechercheAnnonces.value,
      page,
      per_page: 8,
    })

    annoncesModule.value = reponse?.data?.annonces || []
    paginationAnnonces.value = reponse?.data?.pagination || null
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les annonces.')
  } finally {
    chargementAnnonces.value = false
  }
}

const chargerConvocations = async () => {
  chargementConvocations.value = true

  try {
    const reponse = await authGet('/joueur/convocations')
    convocationsEquipe.value = reponse?.data?.convocations || []
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les convocations.')
  } finally {
    chargementConvocations.value = false
  }
}

const chargerCompositionMatch = async (evenement) => {
  if (evenement?.type !== 'match') {
    compositionMatchEvenement.value = null
    return
  }

  chargementCompositionMatch.value = true

  try {
    const reponse = await authGet(`/joueur/evenements/${evenement.id}/composition`)
    compositionMatchEvenement.value = reponse?.data?.composition || null
  } catch (error) {
    compositionMatchEvenement.value = null
    notifyError(error?.response?.message || error.message || 'Impossible de charger la composition du match.')
  } finally {
    chargementCompositionMatch.value = false
  }
}

const chargerFeuilleMatch = async (evenement) => {
  if (evenement?.type !== 'match') {
    feuilleMatchEvenement.value = null
    return
  }

  chargementFeuilleMatch.value = true

  try {
    const reponse = await authGet(`/joueur/evenements/${evenement.id}/feuille-match`)
    feuilleMatchEvenement.value = reponse?.data?.feuille_match || null
  } catch (error) {
    feuilleMatchEvenement.value = null
    notifyError(error?.response?.message || error.message || 'Impossible de charger la feuille de match.')
  } finally {
    chargementFeuilleMatch.value = false
  }
}

const chargerStatistiquesMatch = async (evenement) => {
  if (evenement?.type !== 'match') {
    statistiquesMatchEvenement.value = creerStatistiquesMatchVides()
    return
  }

  chargementStatistiquesMatch.value = true

  try {
    const reponse = await authGet(`/joueur/evenements/${evenement.id}/statistiques-match`)
    statistiquesMatchEvenement.value = reponse?.data?.statistiques || creerStatistiquesMatchVides()
  } catch (error) {
    statistiquesMatchEvenement.value = creerStatistiquesMatchVides()
    notifyError(error?.response?.message || error.message || 'Impossible de charger les statistiques du match.')
  } finally {
    chargementStatistiquesMatch.value = false
  }
}

const ouvrirDetailEvenement = async (evenement) => {
  reinitialiserDetailsMatch()

  if (evenement?.type !== 'match') {
    return
  }

  await chargerCompositionMatch(evenement)
  await chargerFeuilleMatch(evenement)
  await chargerStatistiquesMatch(evenement)
}

const appliquerDisponibiliteDansListe = (liste, evenementId, disponibilite) => {
  return (liste || []).map((evenement) => {
    if (String(evenement.id) !== String(evenementId)) {
      return evenement
    }

    return {
      ...evenement,
      disponibilite: {
        id: disponibilite.id,
        reponse: disponibilite.reponse,
        commentaire: disponibilite.commentaire,
        date_reponse: disponibilite.date_reponse,
      },
    }
  })
}

const repondreDisponibilite = async ({ evenement, reponse, commentaire }) => {
  try {
    const reponseApi = await authPut(`/joueur/evenements/${evenement.id}/disponibilite`, {
      reponse,
      commentaire,
    })

    const disponibilite = reponseApi?.data?.disponibilite

    if (disponibilite) {
      evenementsModule.value = appliquerDisponibiliteDansListe(evenementsModule.value, evenement.id, disponibilite)
      evenementsDashboard.value = appliquerDisponibiliteDansListe(evenementsDashboard.value, evenement.id, disponibilite)
    }

    notifySuccess('Disponibilite enregistree avec succes.')
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible d enregistrer la disponibilite.')
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
    const reponse = await authGet('/joueur/canaux')
    canaux.value = reponse?.data?.canaux || []

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
  stopRealtimeJoueur.value()
  canalSelectionne.value = canal
  chargementMessages.value = true

  try {
    const reponse = await authGet(`/joueur/canaux/${canal.id}/messages`)
    messages.value = (reponse?.data?.messages || []).map(normaliserMessage).filter(Boolean)
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les messages.')
  } finally {
    chargementMessages.value = false
  }

  if (!canal.id) return

  stopRealtimeJoueur.value = subscribeToCanalMessages(canal.id, (payload) => {
    pousserMessage(payload?.message || payload)
  })
}

const envoyerMessage = async (contenu) => {
  if (!canalSelectionne.value) return

  envoiMessage.value = true

  try {
    const reponse = await authPost(`/joueur/canaux/${canalSelectionne.value.id}/messages`, { contenu })
    pousserMessage(reponse?.data?.message)
  } catch (error) {
    notifyError(error?.response?.message || error.message || "Impossible d'envoyer le message.")
  } finally {
    envoiMessage.value = false
  }
}

const reinitialiserModalRejoindreEquipe = () => {
  erreurCodeInvitation.value = ''
  codeInvitationEquipe.value = ''
}

const ouvrirModalRejoindreEquipe = () => {
  reinitialiserModalRejoindreEquipe()
  modalRejoindreEquipeVisible.value = true
}

const fermerModalRejoindreEquipe = () => {
  if (chargementRejoindreEquipe.value) return

  modalRejoindreEquipeVisible.value = false
  reinitialiserModalRejoindreEquipe()
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

const chargerModule = async (moduleKey, options = {}) => {
  const { forcerRafraichissement = false } = options

  switch (moduleKey) {
    case 'dashboard':
      if (forcerRafraichissement) {
        await chargerDashboard()
      }
      break
    case 'evenements':
      if (!equipe.value) {
        await chargerDashboard()
      }
      await chargerEvenements()
      break
    case 'annonces':
      if (!equipe.value) {
        await chargerDashboard()
      }
      await chargerAnnonces()
      break
    case 'convocations':
      if (!equipe.value) {
        await chargerDashboard()
      }
      await chargerConvocations()
      break
    case 'messagerie':
      await chargerCanaux()
      break
    default:
      break
  }
}

const afficherModule = async (moduleKey) => {
  moduleActif.value = moduleKey
  await chargerModule(moduleKey)
}

const actualiserModuleActif = async () => {
  chargementRafraichissement.value = true

  try {
    await chargerModule(moduleActif.value, { forcerRafraichissement: true })
    derniereMiseAJour.value = new Date().toISOString()
  } finally {
    chargementRafraichissement.value = false
  }
}

const ouvrirNotificationJoueur = async (notification) => {
  await marquerNotificationLue(notification)

  switch (notification?.module_cible) {
    case 'messagerie': {
      moduleActif.value = 'messagerie'
      await chargerModule('messagerie')

      if (!notification.canal_id) {
        return
      }

      const canal = canaux.value.find((item) => String(item.id) === String(notification.canal_id))

      if (canal) {
        await selectionnerCanal(canal)
      }
      break
    }
    case 'convocations':
      moduleActif.value = 'convocations'
      await chargerModule('convocations')
      break
    case 'evenements':
      moduleActif.value = 'evenements'
      await chargerModule('evenements')
      break
    case 'annonces':
      moduleActif.value = 'annonces'
      await chargerModule('annonces')
      break
    default:
      break
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
  if (!intervalRafraichissement.value) return

  clearInterval(intervalRafraichissement.value)
  intervalRafraichissement.value = null
}

watch(rechercheEvenements, () => {
  clearTimeout(debounceEvenements.value)

  debounceEvenements.value = setTimeout(() => {
    if (moduleActif.value === 'evenements') {
      chargerEvenements()
    }
  }, 350)
})

watch(rechercheAnnonces, () => {
  clearTimeout(debounceAnnonces.value)

  debounceAnnonces.value = setTimeout(() => {
    if (moduleActif.value === 'annonces') {
      chargerAnnonces(1)
    }
  }, 350)
})

onMounted(async () => {
  await chargerDashboard()
  await chargerNotifications()

  if (utilisateurConnecte.value?.id) {
    stopRealtimeNotifications.value = subscribeToNotifications(utilisateurConnecte.value.id, (notification) => {
      integrerNotification(notification)
    })
  }

  demarrerRafraichissementAuto()
})

onBeforeUnmount(() => {
  arreterRafraichissementAuto()
  clearTimeout(debounceEvenements.value)
  clearTimeout(debounceAnnonces.value)
  stopRealtimeJoueur.value()
  stopRealtimeNotifications.value()
  disconnectRealtime()
})
</script>

<template>
  <main class="min-h-screen bg-[#f4f6fb] font-['Plus_Jakarta_Sans',Inter,sans-serif] text-[#111827]">
    <div class="mx-auto max-w-[1450px] px-2 pb-5 pt-2 sm:px-4 sm:pt-3">
      <AppRoleHeroBanner
        :background-src="blueBackground"
        :logo-src="logoMark"
        home-route="/joueur/dashboard"
        :global-links="liensGlobaux"
        @logout="deconnecter"
      >
        <template #title>
          Bienvenue,
          <br class="hidden sm:block" />
          {{ utilisateurResume.nom }}
        </template>

        <template #description>
          Votre espace joueur - equipe, evenements, convocations et messagerie en un seul endroit.
        </template>

        <template #actions>
          <button
            type="button"
            class="rounded-full bg-white px-6 py-2 text-sm font-bold text-[#1f36bf] transition hover:bg-[#eef2ff]"
            @click="afficherModule('evenements')"
          >
            Mes evenements
          </button>
          <button
            type="button"
            class="rounded-full border border-white/35 bg-white/8 px-6 py-2 text-sm font-semibold text-white transition hover:bg-white/20"
            @click="afficherModule('convocations')"
          >
            Mes convocations
          </button>
        </template>
      </AppRoleHeroBanner>

      <AppRoleWorkspacePanel
        :feature-links="liensFonctionnalites"
        :active-module="moduleActif"
        :search-value="rechercheNavigation"
        :search-placeholder="placeholderRecherche"
        :refresh-disabled="chargementRafraichissement || chargementEvenements || chargementAnnonces || chargementConvocations"
        :refresh-loading="chargementRafraichissement"
        :user="utilisateurResume"
        :notifications-props="notificationsDropdownProps"
        @update:search-value="rechercheNavigation = $event"
        @select-module="afficherModule"
        @refresh="actualiserModuleActif"
        @open-profile="afficherModule('profil')"
        @toggle-notifications="basculerNotifications"
        @refresh-notifications="chargerNotifications"
        @mark-all-notifications="marquerToutesNotificationsCommeLues"
        @notification-click="ouvrirNotificationJoueur"
      >
        <div v-if="chargement" class="space-y-4">
          <div class="h-44 animate-pulse rounded-3xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
          <div class="h-28 animate-pulse rounded-3xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
          <div class="h-56 animate-pulse rounded-3xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
        </div>

        <template v-else>
          <JoueurDashboardHome
            v-if="moduleActif === 'dashboard'"
            :equipe="equipe"
            :evenements="evenementsDashboard"
            :convocations="convocationsEquipe"
            :derniere-mise-a-jour="derniereMiseAJour"
            :chargement="false"
            @aller-module="afficherModule"
            @ouvrir-rejoindre-equipe="ouvrirModalRejoindreEquipe"
          />

          <JoueurEvenements
            v-else-if="moduleActif === 'evenements'"
            :evenements="evenementsModule"
            :equipe="equipe"
            :chargement="chargementEvenements"
            :recherche="rechercheEvenements"
            :composition-match="compositionMatchEvenement"
            :chargement-composition="chargementCompositionMatch"
            :feuille-match="feuilleMatchEvenement"
            :chargement-feuille-match="chargementFeuilleMatch"
            :statistiques-match="statistiquesMatchEvenement"
            :chargement-statistiques-match="chargementStatistiquesMatch"
            @update:recherche="rechercheEvenements = $event"
            @repondre-disponibilite="repondreDisponibilite"
            @ouvrir-detail="ouvrirDetailEvenement"
          />

          <JoueurAnnonces
            v-else-if="moduleActif === 'annonces'"
            :annonces="annoncesModule"
            :equipe="equipe"
            :chargement="chargementAnnonces"
            :recherche="rechercheAnnonces"
            :pagination="paginationAnnonces"
            @update:recherche="rechercheAnnonces = $event"
            @change-page="chargerAnnonces"
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

          <AppProfileManager
            v-else-if="moduleActif === 'profil'"
            :visible="true"
            role-label="Joueur"
            profile-endpoint="/joueur/profil"
            :show-status="true"
            :equipe="equipe"
            @saved="mettreAJourProfilJoueur"
          />
        </template>
      </AppRoleWorkspacePanel>
    </div>

    <div
      v-if="modalRejoindreEquipeVisible"
      class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/40 p-4"
      @click.self="fermerModalRejoindreEquipe"
    >
      <div
        class="w-full max-w-md rounded-[28px] border border-[#e6ebf8] bg-white p-6 shadow-[0_24px_70px_-40px_rgba(15,23,42,0.55)]"
      >
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

          <p
            v-if="erreurCodeInvitation"
            class="mt-3 rounded-2xl border border-[#fecaca] bg-[#fef2f2] px-4 py-3 text-sm font-semibold text-[#b91c1c]"
          >
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
