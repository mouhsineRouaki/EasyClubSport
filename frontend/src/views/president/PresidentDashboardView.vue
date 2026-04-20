<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import blueBackground from '../../assets/Background.jpg'
import logoMark from '../../assets/logo-easyclubsport-mark.svg'
import PresidentClubCard from '../../components/president/PresidentClubCard.vue'
import PresidentClubForm from '../../components/president/PresidentClubForm.vue'
import PresidentEventCard from '../../components/president/PresidentEventCard.vue'
import PresidentEventForm from '../../components/president/PresidentEventForm.vue'
import PresidentAnnouncementsSection from '../../components/president/PresidentAnnouncementsSection.vue'
import PresidentDocumentsSection from '../../components/president/PresidentDocumentsSection.vue'
import PresidentMessagingSection from '../../components/president/PresidentMessagingSection.vue'
import PresidentPlayerCard from '../../components/president/PresidentPlayerCard.vue'
import PresidentTeamCard from '../../components/president/PresidentTeamCard.vue'
import PresidentTeamForm from '../../components/president/PresidentTeamForm.vue'
import MatchCompositionSection from '../../components/common/MatchCompositionSection.vue'
import MatchSheetSection from '../../components/common/MatchSheetSection.vue'
import MatchStatisticsSection from '../../components/common/MatchStatisticsSection.vue'
import AppNotificationsDropdown from '../../components/common/AppNotificationsDropdown.vue'
import { authDelete, authGet, authPost, authPut } from '../../services/api'
import { notifyError, notifySuccess } from '../../stores/toast'
import { subscribeToNotifications, disconnectRealtime } from '../../services/realtime'

const router = useRouter()

const chargement = ref(true)
const chargementRafraichissement = ref(false)
const moduleActif = ref('dashboard')
const dashboard = ref(null)
const utilisateurConnecte = ref(null)
const notificationsPresident = ref([])
const notificationsNonLuesTotal = ref(0)
const chargementNotifications = ref(false)
const notificationOuverte = ref(false)
const actionNotification = ref('')
const rafraichissementAuto = ref(true)
const derniereMiseAJour = ref(null)
const intervalRafraichissement = ref(null)
const stopRealtimeNotifications = ref(() => {})
const chargementClubs = ref(false)
const clubsGestion = ref([])
const paginationClubs = ref(null)
const erreurClubs = ref('')
const rechercheClubs = ref('')
const clubSelectionne = ref(null)
const equipeSelectionnee = ref(null)
const modeClubs = ref('liste')
const envoiClub = ref(false)
const erreursClub = ref({})
const logoClubFichier = ref(null)
const logoClubPreview = ref('')
const debounceClubs = ref(null)
const chargementClubsOptions = ref(false)
const clubsOptions = ref([])
const chargementEquipes = ref(false)
const equipesGestion = ref([])
const paginationEquipes = ref(null)
const erreurEquipes = ref('')
const rechercheEquipes = ref('')
const clubEquipeId = ref('')
const equipeGestionSelectionnee = ref(null)
const modeEquipes = ref('liste')
const envoiEquipe = ref(false)
const erreursEquipe = ref({})
const logoEquipeFichier = ref(null)
const logoEquipePreview = ref('')
const debounceEquipes = ref(null)
const chargementJoueurs = ref(false)
const joueursGestion = ref([])
const paginationJoueurs = ref(null)
const erreurJoueurs = ref('')
const rechercheJoueurs = ref('')
const clubJoueurId = ref('')
const equipeJoueurId = ref('')
const equipesJoueurOptions = ref([])
const joueurSelectionne = ref(null)
const modeJoueurs = ref('liste')
const envoiJoueur = ref(false)
const utilisateurIdAjout = ref('')
const erreursJoueur = ref({})
const debounceJoueurs = ref(null)
const chargementEvenements = ref(false)
const evenementsGestion = ref([])
const paginationEvenements = ref(null)
const erreurEvenements = ref('')
const rechercheEvenements = ref('')
const clubEvenementId = ref('')
const equipeEvenementId = ref('')
const equipesEvenementOptions = ref([])
const chargementEquipesAdversairesEvenements = ref(false)
const equipesAdversairesEvenementOptions = ref([])
const dateDebutEvenements = ref('')
const dateFinEvenements = ref('')
const evenementSelectionne = ref(null)
const modeEvenements = ref('liste')
const envoiEvenement = ref(false)
const erreursEvenement = ref({})
const chargementCompositionMatch = ref(false)
const compositionMatchEvenement = ref(null)
const chargementFeuilleMatch = ref(false)
const feuilleMatchEvenement = ref(null)
const chargementStatistiquesMatch = ref(false)
const statistiquesMatchEvenement = ref({ resume: {}, joueurs: [] })
const debounceEvenements = ref(null)
const debounceAdversairesEvenements = ref(null)
const rechercheAnnonces = ref('')
const annoncesSectionRef = ref(null)
const rechercheDocuments = ref('')
const documentsSectionRef = ref(null)
const rechercheMessagerie = ref('')
const messagerieSectionRef = ref(null)

const formulaireClub = reactive({
  nom: '',
  adresse: '',
  telephone: '',
  email: '',
  description: '',
  ville: '',
  pays: '',
})

const formulaireEquipe = reactive({
  nom: '',
  categorie: '',
  statut: 'active',
  description: '',
})

const formulaireEvenement = reactive({
  titre: '',
  type: 'match',
  date_debut: '',
  date_fin: '',
  lieu: '',
  adversaire: '',
  adversaire_equipe_id: '',
  description: '',
  statut: 'planifie',
})

const statistiques = computed(() => dashboard.value?.statistiques || {})
const equipesRecentes = computed(() => dashboard.value?.equipes_recentes || [])
const prochainsEvenements = computed(() => dashboard.value?.prochains_evenements || [])

const liensGlobaux = [
  { label: 'Dashboard', to: '/president/dashboard' },
  { label: 'About us', href: '#about-easyclubsport' },
  { label: 'Contact us', href: '#contact-support' },
]

const liensFonctionnalites = [
  { key: 'dashboard', label: 'Dashboard' },
  { key: 'clubs', label: 'Clubs' },
  { key: 'equipes', label: 'Equipes' },
  { key: 'joueurs', label: 'Joueurs' },
  { key: 'evenements', label: 'Evenements' },
  { key: 'annonces', label: 'Annonces' },
  { key: 'documents', label: 'Documents' },
  { key: 'messagerie', label: 'Messagerie' },
]

const statsCards = computed(() => [
  {
    label: 'Joueurs',
    value: statistiques.value.joueurs_total || 0,
  },
  {
    label: 'Clubs',
    value: statistiques.value.clubs_total || 0,
  },
  {
    label: 'Equipes',
    value: statistiques.value.equipes_total || 0,
  },
  {
    label: 'Coachs',
    value: statistiques.value.coachs_total || 0,
  },
  {
    label: 'Evenements',
    value: statistiques.value.evenements_a_venir_total || 0,
  },
])

const evenementsDashboard = computed(() => prochainsEvenements.value.slice(0, 4))
const evenementsCarousel = computed(() => (evenementsDashboard.value.length ? [...evenementsDashboard.value, ...evenementsDashboard.value] : []))
const cartesEquipesRecentes = computed(() => equipesRecentes.value.slice(0, 6))
const notificationsRecentes = computed(() => notificationsPresident.value.slice(0, 6))
const rechercheNavigation = computed({
  get() {
    if (moduleActif.value === 'equipes') {
      return rechercheEquipes.value
    }

    if (moduleActif.value === 'joueurs') {
      return rechercheJoueurs.value
    }

    if (moduleActif.value === 'evenements') {
      return rechercheEvenements.value
    }

    if (moduleActif.value === 'annonces') {
      return rechercheAnnonces.value
    }

    if (moduleActif.value === 'documents') {
      return rechercheDocuments.value
    }

    if (moduleActif.value === 'messagerie') {
      return rechercheMessagerie.value
    }

    return rechercheClubs.value
  },
  set(value) {
    if (moduleActif.value === 'equipes') {
      rechercheEquipes.value = value
      return
    }

    if (moduleActif.value === 'joueurs') {
      rechercheJoueurs.value = value
      return
    }

    if (moduleActif.value === 'evenements') {
      rechercheEvenements.value = value
      return
    }

    if (moduleActif.value === 'annonces') {
      rechercheAnnonces.value = value
      return
    }

    if (moduleActif.value === 'documents') {
      rechercheDocuments.value = value
      return
    }

    if (moduleActif.value === 'messagerie') {
      rechercheMessagerie.value = value
      return
    }

    if (moduleActif.value === 'clubs') {
      rechercheClubs.value = value
    }
  },
})
const equipesClubSelectionne = computed(() => clubSelectionne.value?.equipes || [])
const clubEquipeSelectionne = computed(() => {
  return clubsOptions.value.find((club) => String(club.id) === String(clubEquipeId.value)) || null
})
const clubJoueurSelectionne = computed(() => {
  return clubsOptions.value.find((club) => String(club.id) === String(clubJoueurId.value)) || null
})
const equipeJoueurSelectionnee = computed(() => {
  return equipesJoueurOptions.value.find((equipe) => String(equipe.id) === String(equipeJoueurId.value)) || null
})
const clubEvenementSelectionne = computed(() => {
  return clubsOptions.value.find((club) => String(club.id) === String(clubEvenementId.value)) || null
})
const equipeEvenementSelectionnee = computed(() => {
  return equipesEvenementOptions.value.find((equipe) => String(equipe.id) === String(equipeEvenementId.value)) || null
})
const equipesAdversairesDisponibles = computed(() => {
  return equipesAdversairesEvenementOptions.value.filter((equipe) => String(equipe.id) !== String(equipeEvenementId.value))
})
const equipeAdversaireEvenementSelectionnee = computed(() => {
  return equipesAdversairesEvenementOptions.value.find((equipe) => String(equipe.id) === String(formulaireEvenement.adversaire_equipe_id)) || null
})
const nomJoueurSelectionne = computed(() => {
  if (!joueurSelectionne.value) {
    return 'Joueur'
  }

  return [joueurSelectionne.value.prenom, joueurSelectionne.value.nom].filter(Boolean).join(' ') || joueurSelectionne.value.name || 'Joueur'
})
const joueursClubSelectionne = computed(() => {
  const joueurs = []
  const joueursDejaAjoutes = new Set()

  equipesClubSelectionne.value.forEach((equipe) => {
    ;(equipe.joueurs || []).forEach((joueur) => {
      const cle = joueur.id ? `joueur-${joueur.id}` : `${joueur.email || joueur.nom || 'joueur'}-${equipe.id}`

      if (joueursDejaAjoutes.has(cle)) {
        return
      }

      joueursDejaAjoutes.add(cle)
      joueurs.push({
        ...joueur,
        equipe_id: equipe.id,
        equipe_nom: equipe.nom,
      })
    })
  })

  return joueurs
})
const evenementsPassesClubSelectionne = computed(() => {
  const evenements = []
  const evenementsDejaAjoutes = new Set()

  equipesClubSelectionne.value.forEach((equipe) => {
    ;(equipe.evenements_passes || []).forEach((evenement) => {
      const cle = evenement.id ? `evenement-${evenement.id}` : `${evenement.titre || 'evenement'}-${evenement.date_debut || ''}-${equipe.id}`

      if (evenementsDejaAjoutes.has(cle)) {
        return
      }

      evenementsDejaAjoutes.add(cle)
      evenements.push({
        ...evenement,
        equipe_id: equipe.id,
        equipe_nom: equipe.nom,
      })
    })
  })

  return evenements.sort((a, b) => new Date(b.date_debut || 0) - new Date(a.date_debut || 0))
})

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

const formatDateTimeInput = (date) => {
  if (!date) {
    return ''
  }

  const valeur = new Date(date)

  if (Number.isNaN(valeur.getTime())) {
    return ''
  }

  const timezoneOffset = valeur.getTimezoneOffset() * 60000
  return new Date(valeur.getTime() - timezoneOffset).toISOString().slice(0, 16)
}

const libelleInvitation = (statut) => ({
  en_attente: 'Invitation en attente',
  accepte: 'Invitation acceptee',
  refuse: 'Invitation refusee',
  sans_invitation: 'Sans invitation',
}[statut] || 'Invitation')

const imageEvenement = (evenement = {}) => {
  return evenement.image_url || evenement.photo_url || evenement.media_url || evenement.image || blueBackground
}

const backgroundEvenement = (evenement = {}) => {
  return `linear-gradient(180deg, rgba(7, 16, 58, 0.18), rgba(7, 16, 58, 0.86)), url(${imageEvenement(evenement)})`
}

const imageEquipe = (equipe = {}) => {
  return equipe.image_url || equipe.logo_url || equipe.photo_url || equipe.club?.logo_url || blueBackground
}

const logoEquipe = (equipe = {}) => {
  return equipe?.logo_url || equipe?.logo || equipe?.club?.logo_url || ''
}

const backgroundEquipe = (equipe = {}) => {
  return `linear-gradient(145deg, rgba(8, 18, 72, 0.86), rgba(36, 70, 216, 0.64)), url(${imageEquipe(equipe)})`
}

const imageClub = (club = {}) => club.logo_url || club.logo || blueBackground

const backgroundClub = (club = {}) => {
  return `linear-gradient(180deg, rgba(7, 16, 58, 0.08), rgba(7, 16, 58, 0.62)), url(${imageClub(club)})`
}

const lireErreurClub = (champ) => erreursClub.value?.[champ]?.[0] || ''

const reinitialiserFormulaireClub = () => {
  formulaireClub.nom = ''
  formulaireClub.adresse = ''
  formulaireClub.telephone = ''
  formulaireClub.email = ''
  formulaireClub.description = ''
  formulaireClub.ville = ''
  formulaireClub.pays = ''
  erreursClub.value = {}
  logoClubFichier.value = null
  logoClubPreview.value = ''
}

const remplirFormulaireClub = (club) => {
  formulaireClub.nom = club?.nom || ''
  formulaireClub.adresse = club?.adresse || ''
  formulaireClub.telephone = club?.telephone || ''
  formulaireClub.email = club?.email || ''
  formulaireClub.description = club?.description || ''
  formulaireClub.ville = club?.ville || ''
  formulaireClub.pays = club?.pays || ''
  erreursClub.value = {}
  logoClubFichier.value = null
  logoClubPreview.value = ''
}

const reinitialiserFormulaireEquipe = () => {
  formulaireEquipe.nom = ''
  formulaireEquipe.categorie = ''
  formulaireEquipe.statut = 'active'
  formulaireEquipe.description = ''
  erreursEquipe.value = {}
  logoEquipeFichier.value = null
  logoEquipePreview.value = ''
}

const remplirFormulaireEquipe = (equipe) => {
  formulaireEquipe.nom = equipe?.nom || ''
  formulaireEquipe.categorie = equipe?.categorie || ''
  formulaireEquipe.statut = equipe?.statut || 'active'
  formulaireEquipe.description = equipe?.description || ''
  erreursEquipe.value = {}
  logoEquipeFichier.value = null
  logoEquipePreview.value = ''
}

const reinitialiserFormulaireEvenement = () => {
  formulaireEvenement.titre = ''
  formulaireEvenement.type = 'match'
  formulaireEvenement.date_debut = ''
  formulaireEvenement.date_fin = ''
  formulaireEvenement.lieu = ''
  formulaireEvenement.adversaire = ''
  formulaireEvenement.adversaire_equipe_id = ''
  formulaireEvenement.description = ''
  formulaireEvenement.statut = 'planifie'
  erreursEvenement.value = {}
}

const remplirFormulaireEvenement = (evenement) => {
  formulaireEvenement.titre = evenement?.titre || ''
  formulaireEvenement.type = evenement?.type || 'match'
  formulaireEvenement.date_debut = formatDateTimeInput(evenement?.date_debut)
  formulaireEvenement.date_fin = formatDateTimeInput(evenement?.date_fin)
  formulaireEvenement.lieu = evenement?.lieu || ''
  formulaireEvenement.adversaire = evenement?.adversaire || ''
  formulaireEvenement.adversaire_equipe_id = evenement?.adversaire_equipe_id ? String(evenement.adversaire_equipe_id) : ''
  formulaireEvenement.description = evenement?.description || ''
  formulaireEvenement.statut = evenement?.statut || 'planifie'
  erreursEvenement.value = {}
}

const mettreAJourChampClub = (champ, valeur) => {
  formulaireClub[champ] = valeur
}

const mettreAJourChampEquipe = (champ, valeur) => {
  formulaireEquipe[champ] = valeur
}

const mettreAJourChampEvenement = (champ, valeur) => {
  formulaireEvenement[champ] = valeur

  if (champ === 'type' && valeur !== 'match') {
    formulaireEvenement.adversaire = ''
    formulaireEvenement.adversaire_equipe_id = ''
  }
}

const choisirLogoClub = (event) => {
  const fichier = event.target.files?.[0]

  if (!fichier) {
    return
  }

  logoClubFichier.value = fichier
  logoClubPreview.value = URL.createObjectURL(fichier)
}

const choisirLogoEquipe = (event) => {
  const fichier = event.target.files?.[0]

  if (!fichier) {
    return
  }

  logoEquipeFichier.value = fichier
  logoEquipePreview.value = URL.createObjectURL(fichier)
}

const construireDonneesClub = () => {
  const donnees = new FormData()
  donnees.append('nom', formulaireClub.nom)
  donnees.append('adresse', formulaireClub.adresse || '')
  donnees.append('telephone', formulaireClub.telephone || '')
  donnees.append('email', formulaireClub.email || '')
  donnees.append('description', formulaireClub.description || '')
  donnees.append('ville', formulaireClub.ville || '')
  donnees.append('pays', formulaireClub.pays || '')

  if (logoClubFichier.value) {
    donnees.append('logo', logoClubFichier.value)
  }

  return donnees
}

const construireDonneesEquipe = () => {
  const donnees = new FormData()
  donnees.append('nom', formulaireEquipe.nom)
  donnees.append('categorie', formulaireEquipe.categorie || '')
  donnees.append('statut', formulaireEquipe.statut || 'active')
  donnees.append('description', formulaireEquipe.description || '')

  if (logoEquipeFichier.value) {
    donnees.append('logo', logoEquipeFichier.value)
  }

  return donnees
}

const construireDonneesEvenement = () => ({
  titre: formulaireEvenement.titre,
  type: formulaireEvenement.type,
  date_debut: formulaireEvenement.date_debut,
  date_fin: formulaireEvenement.date_fin || null,
  lieu: formulaireEvenement.lieu || null,
  adversaire: formulaireEvenement.type === 'match' ? equipeAdversaireEvenementSelectionnee.value?.nom || formulaireEvenement.adversaire || null : null,
  adversaire_equipe_id: formulaireEvenement.type === 'match' ? formulaireEvenement.adversaire_equipe_id || null : null,
  description: formulaireEvenement.description || null,
  statut: formulaireEvenement.statut || 'planifie',
})

const afficherModule = (key) => {
  moduleActif.value = key

  if (key === 'clubs') {
    modeClubs.value = 'liste'
  }

  if (key === 'equipes') {
    modeEquipes.value = 'liste'
    initialiserGestionEquipes()
  }

  if (key === 'joueurs') {
    modeJoueurs.value = 'liste'
    initialiserGestionJoueurs()
  }

  if (key === 'evenements') {
    modeEvenements.value = 'liste'
    initialiserGestionEvenements()
  }

  if (key === 'clubs' && !clubsGestion.value.length) {
    chargerClubsGestion()
  }
}

const chargerClubsGestion = async (page = 1) => {
  chargementClubs.value = true
  erreurClubs.value = ''

  try {
    const reponse = await authGet('/president/clubs', {
      q: rechercheClubs.value,
      page,
      per_page: 9,
    })

    clubsGestion.value = reponse?.data?.clubs || []
    paginationClubs.value = reponse?.data?.pagination || null

    if (clubSelectionne.value) {
      clubSelectionne.value = clubsGestion.value.find((club) => club.id === clubSelectionne.value.id) || null
      equipeSelectionnee.value = clubSelectionne.value?.equipes?.find((equipe) => equipe.id === equipeSelectionnee.value?.id)
        || clubSelectionne.value?.equipes?.[0]
        || null
    }
  } catch (error) {
    if (error?.response?.code === 401) {
      localStorage.removeItem('token_api')
      localStorage.removeItem('utilisateur_api')
      router.push('/login')
      return
    }

    erreurClubs.value = error?.response?.message || error.message || 'Impossible de charger les clubs.'
    notifyError(erreurClubs.value)
  } finally {
    chargementClubs.value = false
  }
}

const chargerClubsOptions = async () => {
  chargementClubsOptions.value = true

  try {
    const reponse = await authGet('/president/clubs', {
      page: 1,
      per_page: 50,
    })

    clubsOptions.value = reponse?.data?.clubs || []

    if (!clubEquipeId.value && clubsOptions.value.length) {
      clubEquipeId.value = String(clubsOptions.value[0].id)
    }
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les clubs pour les equipes.')
  } finally {
    chargementClubsOptions.value = false
  }
}

const chargerEquipesGestion = async (page = 1) => {
  if (!clubEquipeId.value) {
    equipesGestion.value = []
    paginationEquipes.value = null
    return
  }

  chargementEquipes.value = true
  erreurEquipes.value = ''

  try {
    const reponse = await authGet(`/president/clubs/${clubEquipeId.value}/equipes`, {
      q: rechercheEquipes.value,
      page,
      per_page: 12,
    })

    equipesGestion.value = reponse?.data?.equipes || []
    paginationEquipes.value = reponse?.data?.pagination || null

    if (equipeGestionSelectionnee.value) {
      equipeGestionSelectionnee.value = equipesGestion.value.find((equipe) => equipe.id === equipeGestionSelectionnee.value.id) || null
    }
  } catch (error) {
    erreurEquipes.value = error?.response?.message || error.message || 'Impossible de charger les equipes.'
    notifyError(erreurEquipes.value)
  } finally {
    chargementEquipes.value = false
  }
}

const initialiserGestionEquipes = async () => {
  if (!clubsOptions.value.length) {
    await chargerClubsOptions()
  }

  if (clubEquipeId.value) {
    await chargerEquipesGestion(1)
  }
}

const chargerEquipesJoueurOptions = async () => {
  equipesJoueurOptions.value = []
  equipeJoueurId.value = ''

  if (!clubJoueurId.value) {
    return
  }

  try {
    const reponse = await authGet(`/president/clubs/${clubJoueurId.value}/equipes`, {
      page: 1,
      per_page: 50,
    })

    equipesJoueurOptions.value = reponse?.data?.equipes || []

    if (equipesJoueurOptions.value.length) {
      equipeJoueurId.value = String(equipesJoueurOptions.value[0].id)
    }
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les equipes pour les joueurs.')
  }
}

const chargerJoueursGestion = async (page = 1) => {
  if (!clubJoueurId.value || !equipeJoueurId.value) {
    joueursGestion.value = []
    paginationJoueurs.value = null
    return
  }

  chargementJoueurs.value = true
  erreurJoueurs.value = ''

  try {
    const reponse = await authGet(`/president/clubs/${clubJoueurId.value}/equipes/${equipeJoueurId.value}/joueurs`, {
      q: rechercheJoueurs.value,
      page,
      per_page: 12,
    })

    joueursGestion.value = reponse?.data?.joueurs || []
    paginationJoueurs.value = reponse?.data?.pagination || null

    if (joueurSelectionne.value) {
      joueurSelectionne.value = joueursGestion.value.find((joueur) => joueur.id === joueurSelectionne.value.id) || null
    }
  } catch (error) {
    erreurJoueurs.value = error?.response?.message || error.message || 'Impossible de charger les joueurs.'
    notifyError(erreurJoueurs.value)
  } finally {
    chargementJoueurs.value = false
  }
}

const initialiserGestionJoueurs = async () => {
  if (!clubsOptions.value.length) {
    await chargerClubsOptions()
  }

  if (!clubJoueurId.value && clubsOptions.value.length) {
    clubJoueurId.value = String(clubsOptions.value[0].id)
  }

  if (clubJoueurId.value && !equipesJoueurOptions.value.length) {
    await chargerEquipesJoueurOptions()
  }

  if (clubJoueurId.value && equipeJoueurId.value) {
    await chargerJoueursGestion(1)
  }
}

const chargerEquipesEvenementOptions = async () => {
  equipesEvenementOptions.value = []
  equipeEvenementId.value = ''

  if (!clubEvenementId.value) {
    return
  }

  try {
    const reponse = await authGet(`/president/clubs/${clubEvenementId.value}/equipes`, {
      page: 1,
      per_page: 50,
    })

    equipesEvenementOptions.value = reponse?.data?.equipes || []

    if (equipesEvenementOptions.value.length) {
      equipeEvenementId.value = String(equipesEvenementOptions.value[0].id)
    }
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les equipes pour les evenements.')
  }
}

const chargerEquipesAdversairesEvenementOptions = async (q = '') => {
  chargementEquipesAdversairesEvenements.value = true

  try {
    const reponse = await authGet('/president/equipes/adversaires', {
      q,
      page: 1,
      per_page: 100,
      exclude_equipe_id: equipeEvenementId.value,
    })

    equipesAdversairesEvenementOptions.value = reponse?.data?.equipes || []
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les equipes adversaires.')
  } finally {
    chargementEquipesAdversairesEvenements.value = false
  }
}

const rechercherEquipesAdversairesEvenementOptions = (terme) => {
  if (debounceAdversairesEvenements.value) {
    clearTimeout(debounceAdversairesEvenements.value)
  }

  debounceAdversairesEvenements.value = setTimeout(() => {
    chargerEquipesAdversairesEvenementOptions(terme)
  }, 300)
}

const chargerEvenementsGestion = async (page = 1) => {
  if (!clubEvenementId.value || !equipeEvenementId.value) {
    evenementsGestion.value = []
    paginationEvenements.value = null
    return
  }

  chargementEvenements.value = true
  erreurEvenements.value = ''

  try {
    const reponse = await authGet(`/president/clubs/${clubEvenementId.value}/equipes/${equipeEvenementId.value}/evenements`, {
      q: rechercheEvenements.value,
      date_debut: dateDebutEvenements.value,
      date_fin: dateFinEvenements.value,
      page,
      per_page: 12,
    })

    evenementsGestion.value = reponse?.data?.evenements || []
    paginationEvenements.value = reponse?.data?.pagination || null

    if (evenementSelectionne.value) {
      evenementSelectionne.value = evenementsGestion.value.find((evenement) => evenement.id === evenementSelectionne.value.id) || null
    }
  } catch (error) {
    erreurEvenements.value = error?.response?.message || error.message || 'Impossible de charger les evenements.'
    notifyError(erreurEvenements.value)
  } finally {
    chargementEvenements.value = false
  }
}

const initialiserGestionEvenements = async () => {
  if (!clubsOptions.value.length) {
    await chargerClubsOptions()
  }

  if (!equipesAdversairesEvenementOptions.value.length) {
    await chargerEquipesAdversairesEvenementOptions()
  }

  if (!clubEvenementId.value && clubsOptions.value.length) {
    clubEvenementId.value = String(clubsOptions.value[0].id)
  }

  if (clubEvenementId.value && !equipesEvenementOptions.value.length) {
    await chargerEquipesEvenementOptions()
  }

  if (clubEvenementId.value && equipeEvenementId.value) {
    await chargerEvenementsGestion(1)
  }
}

const selectionnerClub = (club) => {
  clubSelectionne.value = club
  equipeSelectionnee.value = club?.equipes?.[0] || null
  modeClubs.value = 'detail'
}

const retourListeClubs = () => {
  modeClubs.value = 'liste'
  erreursClub.value = {}
  equipeSelectionnee.value = null
}

const selectionnerEquipeClub = (equipe) => {
  equipeSelectionnee.value = equipe
}

const ouvrirEditionClub = () => {
  if (!clubSelectionne.value) {
    return
  }

  remplirFormulaireClub(clubSelectionne.value)
  modeClubs.value = 'edition'
}

const ouvrirCreationClub = () => {
  clubSelectionne.value = null
  equipeSelectionnee.value = null
  reinitialiserFormulaireClub()
  modeClubs.value = 'creation'
}

const creerClub = async () => {
  envoiClub.value = true
  erreursClub.value = {}

  try {
    const reponse = await authPost('/president/clubs', construireDonneesClub())
    notifySuccess(reponse?.message || 'Club cree avec succes.')

    const clubCree = reponse?.data?.club
    await chargerClubsGestion(1)

    if (clubCree?.id) {
      clubSelectionne.value = clubsGestion.value.find((club) => club.id === clubCree.id) || clubCree
      equipeSelectionnee.value = clubSelectionne.value?.equipes?.[0] || null
      modeClubs.value = 'detail'
      return
    }

    reinitialiserFormulaireClub()
    modeClubs.value = 'liste'
  } catch (error) {
    erreursClub.value = error?.response?.data || {}
    notifyError(error?.response?.message || error.message || 'Impossible de creer ce club.')
  } finally {
    envoiClub.value = false
  }
}

const enregistrerClubSelectionne = async () => {
  if (!clubSelectionne.value) {
    return
  }

  envoiClub.value = true
  erreursClub.value = {}

  try {
    const reponse = await authPut(`/president/clubs/${clubSelectionne.value.id}`, construireDonneesClub())
    notifySuccess(reponse?.message || 'Club modifie avec succes.')

    const clubMisAJour = reponse?.data?.club
    if (clubMisAJour) {
      clubSelectionne.value = { ...clubSelectionne.value, ...clubMisAJour }
    }

    await chargerClubsGestion(paginationClubs.value?.current_page || 1)
    modeClubs.value = 'detail'
  } catch (error) {
    erreursClub.value = error?.response?.data || {}
    notifyError(error?.response?.message || error.message || 'Impossible de modifier ce club.')
  } finally {
    envoiClub.value = false
  }
}

const supprimerClubSelectionne = async () => {
  if (!clubSelectionne.value) {
    return
  }

  const ok = window.confirm(`Supprimer le club "${clubSelectionne.value.nom}" ?`)

  if (!ok) {
    return
  }

  try {
    const reponse = await authDelete(`/president/clubs/${clubSelectionne.value.id}`)
    notifySuccess(reponse?.message || 'Club supprime avec succes.')
    clubSelectionne.value = null
    modeClubs.value = 'liste'
    await chargerClubsGestion(paginationClubs.value?.current_page || 1)
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de supprimer ce club.')
  }
}

const changerClubEquipe = async () => {
  modeEquipes.value = 'liste'
  equipeGestionSelectionnee.value = null
  await chargerEquipesGestion(1)
}

const selectionnerEquipeGestion = (equipe) => {
  equipeGestionSelectionnee.value = equipe
  modeEquipes.value = 'detail'
}

const retourListeEquipes = () => {
  modeEquipes.value = 'liste'
  erreursEquipe.value = {}
}

const ouvrirCreationEquipe = () => {
  if (!clubEquipeId.value) {
    notifyError('Selectionnez un club avant de creer une equipe.')
    return
  }

  equipeGestionSelectionnee.value = null
  reinitialiserFormulaireEquipe()
  modeEquipes.value = 'creation'
}

const ouvrirEditionEquipe = () => {
  if (!equipeGestionSelectionnee.value) {
    return
  }

  remplirFormulaireEquipe(equipeGestionSelectionnee.value)
  modeEquipes.value = 'edition'
}

const creerEquipe = async () => {
  if (!clubEquipeId.value) {
    notifyError('Selectionnez un club avant de creer une equipe.')
    return
  }

  envoiEquipe.value = true
  erreursEquipe.value = {}

  try {
    const reponse = await authPost(`/president/clubs/${clubEquipeId.value}/equipes`, construireDonneesEquipe())
    notifySuccess(reponse?.message || 'Equipe creee avec succes.')

    const equipeCreee = reponse?.data?.equipe
    await chargerEquipesGestion(1)

    if (equipeCreee?.id) {
      equipeGestionSelectionnee.value = equipesGestion.value.find((equipe) => equipe.id === equipeCreee.id) || equipeCreee
      modeEquipes.value = 'detail'
      return
    }

    reinitialiserFormulaireEquipe()
    modeEquipes.value = 'liste'
  } catch (error) {
    erreursEquipe.value = error?.response?.data || {}
    notifyError(error?.response?.message || error.message || 'Impossible de creer cette equipe.')
  } finally {
    envoiEquipe.value = false
  }
}

const enregistrerEquipeSelectionnee = async () => {
  if (!clubEquipeId.value || !equipeGestionSelectionnee.value) {
    return
  }

  envoiEquipe.value = true
  erreursEquipe.value = {}

  try {
    const reponse = await authPut(`/president/clubs/${clubEquipeId.value}/equipes/${equipeGestionSelectionnee.value.id}`, construireDonneesEquipe())
    notifySuccess(reponse?.message || 'Equipe modifiee avec succes.')

    const equipeMiseAJour = reponse?.data?.equipe
    if (equipeMiseAJour) {
      equipeGestionSelectionnee.value = { ...equipeGestionSelectionnee.value, ...equipeMiseAJour }
    }

    await chargerEquipesGestion(paginationEquipes.value?.current_page || 1)
    modeEquipes.value = 'detail'
  } catch (error) {
    erreursEquipe.value = error?.response?.data || {}
    notifyError(error?.response?.message || error.message || 'Impossible de modifier cette equipe.')
  } finally {
    envoiEquipe.value = false
  }
}

const supprimerEquipeSelectionnee = async () => {
  if (!clubEquipeId.value || !equipeGestionSelectionnee.value) {
    return
  }

  const ok = window.confirm(`Supprimer l equipe "${equipeGestionSelectionnee.value.nom}" ?`)

  if (!ok) {
    return
  }

  try {
    const reponse = await authDelete(`/president/clubs/${clubEquipeId.value}/equipes/${equipeGestionSelectionnee.value.id}`)
    notifySuccess(reponse?.message || 'Equipe supprimee avec succes.')
    equipeGestionSelectionnee.value = null
    modeEquipes.value = 'liste'
    await chargerEquipesGestion(paginationEquipes.value?.current_page || 1)
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de supprimer cette equipe.')
  }
}

const changerClubJoueur = async () => {
  modeJoueurs.value = 'liste'
  joueurSelectionne.value = null
  await chargerEquipesJoueurOptions()
  await chargerJoueursGestion(1)
}

const changerEquipeJoueur = async () => {
  modeJoueurs.value = 'liste'
  joueurSelectionne.value = null
  await chargerJoueursGestion(1)
}

const selectionnerJoueur = (joueur) => {
  joueurSelectionne.value = joueur
  modeJoueurs.value = 'detail'
}

const retourListeJoueurs = () => {
  modeJoueurs.value = 'liste'
  erreursJoueur.value = {}
}

const ouvrirAjoutJoueur = () => {
  if (!clubJoueurId.value || !equipeJoueurId.value) {
    notifyError('Selectionnez un club et une equipe avant d ajouter un joueur.')
    return
  }

  utilisateurIdAjout.value = ''
  erreursJoueur.value = {}
  modeJoueurs.value = 'ajout'
}

const ajouterJoueurEquipe = async () => {
  if (!clubJoueurId.value || !equipeJoueurId.value) {
    notifyError('Selectionnez un club et une equipe avant d ajouter un joueur.')
    return
  }

  envoiJoueur.value = true
  erreursJoueur.value = {}

  try {
    const reponse = await authPost(`/president/clubs/${clubJoueurId.value}/equipes/${equipeJoueurId.value}/joueurs`, {
      utilisateur_id: utilisateurIdAjout.value,
    })

    notifySuccess(reponse?.message || 'Joueur ajoute avec succes.')
    utilisateurIdAjout.value = ''
    modeJoueurs.value = 'liste'
    await chargerJoueursGestion(1)
  } catch (error) {
    erreursJoueur.value = error?.response?.data || {}
    notifyError(error?.response?.message || error.message || 'Impossible d ajouter ce joueur.')
  } finally {
    envoiJoueur.value = false
  }
}

const retirerJoueurSelectionne = async () => {
  if (!clubJoueurId.value || !equipeJoueurId.value || !joueurSelectionne.value) {
    return
  }

  const ok = window.confirm(`Retirer "${nomJoueurSelectionne.value}" de cette equipe ?`)

  if (!ok) {
    return
  }

  try {
    const reponse = await authDelete(`/president/clubs/${clubJoueurId.value}/equipes/${equipeJoueurId.value}/joueurs/${joueurSelectionne.value.id}`)
    notifySuccess(reponse?.message || 'Joueur retire avec succes.')
    joueurSelectionne.value = null
    modeJoueurs.value = 'liste'
    await chargerJoueursGestion(paginationJoueurs.value?.current_page || 1)
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de retirer ce joueur.')
  }
}

const changerClubEvenement = async () => {
  modeEvenements.value = 'liste'
  evenementSelectionne.value = null
  formulaireEvenement.adversaire_equipe_id = ''
  await chargerEquipesEvenementOptions()
  await chargerEvenementsGestion(1)
}

const changerEquipeEvenement = async () => {
  modeEvenements.value = 'liste'
  evenementSelectionne.value = null
  if (String(formulaireEvenement.adversaire_equipe_id) === String(equipeEvenementId.value)) {
    formulaireEvenement.adversaire_equipe_id = ''
  }
  await chargerEquipesAdversairesEvenementOptions()
  await chargerEvenementsGestion(1)
}

const chargerCompositionMatchEvenement = async (evenement) => {
  if (!evenement || evenement.type !== 'match') {
    compositionMatchEvenement.value = null
    return
  }

  chargementCompositionMatch.value = true
  try {
    const rep = await authGet(`/president/clubs/${clubEvenementId.value}/equipes/${equipeEvenementId.value}/evenements/${evenement.id}/composition`)
    compositionMatchEvenement.value = rep?.data?.composition || null
  } catch (error) {
    compositionMatchEvenement.value = null
    notifyError(error?.response?.message || error.message || 'Impossible de charger la composition du match.')
  } finally {
    chargementCompositionMatch.value = false
  }
}

const chargerFeuilleMatchEvenement = async (evenement) => {
  if (!evenement || evenement.type !== 'match') {
    feuilleMatchEvenement.value = null
    return
  }

  chargementFeuilleMatch.value = true
  try {
    const rep = await authGet(`/president/clubs/${clubEvenementId.value}/equipes/${equipeEvenementId.value}/evenements/${evenement.id}/feuille-match`)
    feuilleMatchEvenement.value = rep?.data?.feuille_match || null
  } catch (error) {
    feuilleMatchEvenement.value = null
    notifyError(error?.response?.message || error.message || 'Impossible de charger la feuille de match.')
  } finally {
    chargementFeuilleMatch.value = false
  }
}

const chargerStatistiquesMatchEvenement = async (evenement) => {
  if (!evenement || evenement.type !== 'match') {
    statistiquesMatchEvenement.value = { resume: {}, joueurs: [] }
    return
  }

  chargementStatistiquesMatch.value = true
  try {
    const rep = await authGet(`/president/clubs/${clubEvenementId.value}/equipes/${equipeEvenementId.value}/evenements/${evenement.id}/statistiques`)
    statistiquesMatchEvenement.value = rep?.data?.statistiques || { resume: {}, joueurs: [] }
  } catch (error) {
    statistiquesMatchEvenement.value = { resume: {}, joueurs: [] }
    notifyError(error?.response?.message || error.message || 'Impossible de charger les statistiques du match.')
  } finally {
    chargementStatistiquesMatch.value = false
  }
}

const selectionnerEvenement = async (evenement) => {
  evenementSelectionne.value = evenement
  modeEvenements.value = 'detail'
  await chargerCompositionMatchEvenement(evenement)
  await chargerFeuilleMatchEvenement(evenement)
  await chargerStatistiquesMatchEvenement(evenement)
}

const retourListeEvenements = () => {
  modeEvenements.value = 'liste'
  erreursEvenement.value = {}
  compositionMatchEvenement.value = null
  feuilleMatchEvenement.value = null
  statistiquesMatchEvenement.value = { resume: {}, joueurs: [] }
}

const ouvrirCreationEvenement = () => {
  if (!clubEvenementId.value || !equipeEvenementId.value) {
    notifyError('Selectionnez un club et une equipe avant de creer un evenement.')
    return
  }

  evenementSelectionne.value = null
  reinitialiserFormulaireEvenement()
  modeEvenements.value = 'creation'
}

const ouvrirEditionEvenement = () => {
  if (!evenementSelectionne.value) {
    return
  }

  remplirFormulaireEvenement(evenementSelectionne.value)
  modeEvenements.value = 'edition'
}

const creerEvenement = async () => {
  if (!clubEvenementId.value || !equipeEvenementId.value) {
    notifyError('Selectionnez un club et une equipe avant de creer un evenement.')
    return
  }

  envoiEvenement.value = true
  erreursEvenement.value = {}

  try {
    const reponse = await authPost(`/president/clubs/${clubEvenementId.value}/equipes/${equipeEvenementId.value}/evenements`, construireDonneesEvenement())
    notifySuccess(reponse?.message || 'Evenement cree avec succes.')

    const evenementCree = reponse?.data?.evenement
    await chargerNotificationsPresident()
    await chargerEvenementsGestion(1)

    if (evenementCree?.id) {
      evenementSelectionne.value = evenementsGestion.value.find((evenement) => evenement.id === evenementCree.id) || evenementCree
      modeEvenements.value = 'detail'
      return
    }

    reinitialiserFormulaireEvenement()
    modeEvenements.value = 'liste'
  } catch (error) {
    erreursEvenement.value = error?.response?.data || {}
    notifyError(error?.response?.message || error.message || 'Impossible de creer cet evenement.')
  } finally {
    envoiEvenement.value = false
  }
}

const enregistrerEvenementSelectionne = async () => {
  if (!clubEvenementId.value || !equipeEvenementId.value || !evenementSelectionne.value) {
    return
  }

  envoiEvenement.value = true
  erreursEvenement.value = {}

  try {
    const reponse = await authPut(`/president/clubs/${clubEvenementId.value}/equipes/${equipeEvenementId.value}/evenements/${evenementSelectionne.value.id}`, construireDonneesEvenement())
    notifySuccess(reponse?.message || 'Evenement modifie avec succes.')

    const evenementMisAJour = reponse?.data?.evenement
    if (evenementMisAJour) {
      evenementSelectionne.value = { ...evenementSelectionne.value, ...evenementMisAJour }
    }

    await chargerNotificationsPresident()
    await chargerEvenementsGestion(paginationEvenements.value?.current_page || 1)
    modeEvenements.value = 'detail'
  } catch (error) {
    erreursEvenement.value = error?.response?.data || {}
    notifyError(error?.response?.message || error.message || 'Impossible de modifier cet evenement.')
  } finally {
    envoiEvenement.value = false
  }
}

const supprimerEvenementSelectionne = async () => {
  if (!clubEvenementId.value || !equipeEvenementId.value || !evenementSelectionne.value) {
    return
  }

  const ok = window.confirm(`Supprimer l evenement "${evenementSelectionne.value.titre}" ?`)

  if (!ok) {
    return
  }

  try {
    const reponse = await authDelete(`/president/clubs/${clubEvenementId.value}/equipes/${equipeEvenementId.value}/evenements/${evenementSelectionne.value.id}`)
    notifySuccess(reponse?.message || 'Evenement supprime avec succes.')
    evenementSelectionne.value = null
    modeEvenements.value = 'liste'
    await chargerEvenementsGestion(paginationEvenements.value?.current_page || 1)
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de supprimer cet evenement.')
  }
}

const chargerNotificationsPresident = async () => {
  chargementNotifications.value = true

  try {
    const reponse = await authGet('/president/notifications')
    notificationsPresident.value = reponse?.data?.notifications || []
    notificationsNonLuesTotal.value = reponse?.data?.notifications_non_lues_total || 0
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les notifications.')
  } finally {
    chargementNotifications.value = false
  }
}

const repondreInvitationNotification = async (notification, decision) => {
  const evenementId = notification?.evenement_id

  if (!evenementId) {
    return
  }

  actionNotification.value = `${notification.id}-${decision}`

  try {
    const endpoint = decision === 'accepte'
      ? `/president/evenements/${evenementId}/invitation/acceptation`
      : `/president/evenements/${evenementId}/invitation/refus`
    const reponse = await authPost(endpoint, {})

    notifySuccess(reponse?.message || 'Invitation traitee avec succes.')
    await chargerNotificationsPresident()
    await recupererDashboard()

    if (moduleActif.value === 'evenements') {
      await chargerEvenementsGestion(paginationEvenements.value?.current_page || 1)
    }
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de traiter cette invitation.')
  } finally {
    actionNotification.value = ''
  }
}

const basculerNotifications = async () => {
  notificationOuverte.value = !notificationOuverte.value

  if (notificationOuverte.value) {
    await chargerNotificationsPresident()
  }
}

const integrerNotification = (notification) => {
  if (!notification?.id) {
    return
  }

  const indexExistant = notificationsPresident.value.findIndex((item) => String(item.id) === String(notification.id))

  if (indexExistant >= 0) {
    const copie = [...notificationsPresident.value]
    copie[indexExistant] = { ...copie[indexExistant], ...notification }
    notificationsPresident.value = copie
  } else {
    notificationsPresident.value = [notification, ...notificationsPresident.value]
  }

  notificationsNonLuesTotal.value = notificationsPresident.value.filter((item) => !item.est_lue).length
}

const marquerNotificationPresidentCommeLue = async (notification) => {
  if (!notification || notification.est_lue) {
    return
  }

  try {
    await authPut(`/president/notifications/${notification.id}/lecture`)
    notification.est_lue = true
    notificationsNonLuesTotal.value = Math.max(0, notificationsNonLuesTotal.value - 1)
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de marquer cette notification comme lue.')
  }
}

const marquerToutesNotificationsPresidentCommeLues = async () => {
  try {
    await authPut('/president/notifications/lecture/toutes')
    notificationsPresident.value = notificationsPresident.value.map((notification) => ({
      ...notification,
      est_lue: true,
      date_lecture: notification.date_lecture || new Date().toISOString(),
    }))
    notificationsNonLuesTotal.value = 0
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de marquer toutes les notifications comme lues.')
  }
}

const ouvrirNotificationPresident = async (notification) => {
  await marquerNotificationPresidentCommeLue(notification)

  if (notification?.module_cible === 'messagerie') {
    moduleActif.value = 'messagerie'
    return
  }

  if (notification?.module_cible === 'evenements' && notification?.evenement) {
    moduleActif.value = 'evenements'

    const clubId = notification.evenement.equipe?.club?.id
    const equipeId = notification.evenement.equipe?.id

    if (clubId) {
      clubEvenementId.value = String(clubId)
      await chargerEquipesEvenementOptions(clubEvenementId.value)
    }

    if (equipeId) {
      equipeEvenementId.value = String(equipeId)
    }

    await chargerEvenementsGestion(1)
  }
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

const actualiserModuleActif = () => {
  if (moduleActif.value === 'clubs') {
    chargerClubsGestion(paginationClubs.value?.current_page || 1)
    return
  }

  if (moduleActif.value === 'equipes') {
    chargerEquipesGestion(paginationEquipes.value?.current_page || 1)
    return
  }

  if (moduleActif.value === 'joueurs') {
    chargerJoueursGestion(paginationJoueurs.value?.current_page || 1)
    return
  }

  if (moduleActif.value === 'evenements') {
    chargerEvenementsGestion(paginationEvenements.value?.current_page || 1)
    return
  }

  if (moduleActif.value === 'annonces') {
    annoncesSectionRef.value?.refreshCurrent?.()
    return
  }

  if (moduleActif.value === 'documents') {
    documentsSectionRef.value?.refreshCurrent?.()
    return
  }

  if (moduleActif.value === 'messagerie') {
    messagerieSectionRef.value?.refreshCurrent?.()
    return
  }

  rafraichirMaintenant()
}

const deconnecter = () => {
  localStorage.removeItem('token_api')
  localStorage.removeItem('utilisateur_api')
  router.push('/login')
}

const lienFonctionnelActif = (item) => moduleActif.value === item.key

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
  chargerNotificationsPresident()
  if (utilisateurConnecte.value?.id) {
    stopRealtimeNotifications.value = subscribeToNotifications(utilisateurConnecte.value.id, (notification) => {
      integrerNotification(notification)
    })
  }
  demarrerRafraichissementAuto()
})

watch(rafraichissementAuto, () => {
  demarrerRafraichissementAuto()
})

watch(rechercheClubs, () => {
  if (debounceClubs.value) {
    clearTimeout(debounceClubs.value)
  }

  debounceClubs.value = setTimeout(() => {
    if (moduleActif.value === 'clubs') {
      chargerClubsGestion(1)
    }
  }, 350)
})

watch(rechercheEquipes, () => {
  if (debounceEquipes.value) {
    clearTimeout(debounceEquipes.value)
  }

  debounceEquipes.value = setTimeout(() => {
    if (moduleActif.value === 'equipes') {
      chargerEquipesGestion(1)
    }
  }, 350)
})

watch(rechercheJoueurs, () => {
  if (debounceJoueurs.value) {
    clearTimeout(debounceJoueurs.value)
  }

  debounceJoueurs.value = setTimeout(() => {
    if (moduleActif.value === 'joueurs') {
      chargerJoueursGestion(1)
    }
  }, 350)
})

watch([rechercheEvenements, dateDebutEvenements, dateFinEvenements], () => {
  if (debounceEvenements.value) {
    clearTimeout(debounceEvenements.value)
  }

  debounceEvenements.value = setTimeout(() => {
    if (moduleActif.value === 'evenements') {
      chargerEvenementsGestion(1)
    }
  }, 350)
})

onBeforeUnmount(() => {
  arreterRafraichissementAuto()

  if (debounceClubs.value) {
    clearTimeout(debounceClubs.value)
  }

  if (debounceEquipes.value) {
    clearTimeout(debounceEquipes.value)
  }

  if (debounceJoueurs.value) {
    clearTimeout(debounceJoueurs.value)
  }

  if (debounceEvenements.value) {
    clearTimeout(debounceEvenements.value)
  }

  if (debounceAdversairesEvenements.value) {
    clearTimeout(debounceAdversairesEvenements.value)
  }

  stopRealtimeNotifications.value?.()
  disconnectRealtime()
})
</script>

<template>
  <main class="min-h-screen bg-[#f4f6fb] font-['Plus_Jakarta_Sans',Inter,sans-serif] text-[#111827]">
    <div class="mx-auto max-w-[1450px] px-2 pb-5 pt-2 sm:px-4 sm:pt-3">
      <section class="relative overflow-hidden rounded-[28px] border border-[#2a43cd] bg-[#2446d8] px-4 pb-[180px] pt-4 text-white sm:px-7 sm:pb-[196px] sm:pt-5">
        <img :src="blueBackground" alt="Blue shell background" class="absolute inset-0 h-full w-full object-cover" />

        <header class="relative z-10 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/15 bg-white/10 px-3 py-2 backdrop-blur-md">
          <RouterLink to="/president/dashboard" class="flex items-center gap-2.5">
            <img :src="logoMark" alt="EasySportClub" class="h-10 w-10 rounded-xl bg-white/95 p-2" />
            <span class="text-lg font-bold">EasySportClub</span>
          </RouterLink>

          <nav class="flex flex-wrap items-center gap-2">
            <RouterLink
              v-for="item in liensGlobaux.filter((lien) => lien.to)"
              :key="item.to"
              :to="item.to"
              class="rounded-full border border-white/25 bg-white/10 px-4 py-1.5 text-[11px] font-semibold text-white/95 transition hover:bg-white/20"
            >
              {{ item.label }}
            </RouterLink>
            <a
              v-for="item in liensGlobaux.filter((lien) => lien.href)"
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
          <h1 class="text-3xl font-black leading-[1.16] tracking-normal sm:text-6xl">
            Pilotez votre club sportif
            <br class="hidden sm:block" />
            avec une interface claire
          </h1>
          <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">
            Le bleu garde l'identite du produit. Le grand espace blanc devient votre zone de travail pour toutes les fonctionnalites president.
          </p>

          <div class="mt-6 flex flex-wrap items-center justify-center gap-2.5">
            <button type="button" class="rounded-full bg-white px-6 py-2 text-sm font-bold text-[#1f36bf] transition hover:bg-[#eef2ff]" @click="afficherModule('clubs')">Gerer les clubs</button>
            <button type="button" class="rounded-full border border-white/35 bg-white/8 px-6 py-2 text-sm font-semibold text-white transition hover:bg-white/20" @click="afficherModule('evenements')">Voir les evenements</button>
          </div>
        </div>
      </section>

      <section class="relative -mt-[154px] z-30 min-h-screen pb-0">
        <article
          class="sticky top-2 z-40 mx-auto w-full max-w-[1220px] rounded-[24px] border border-[#e6ebf8] bg-white text-[#111827] shadow-[0_1px_0_rgba(17,24,39,0.04),0_36px_70px_-54px_rgba(15,23,42,0.55)]"
        >
          <div>
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
                    :placeholder="moduleActif === 'clubs' ? 'Rechercher un club' : moduleActif === 'equipes' ? 'Rechercher une equipe' : moduleActif === 'joueurs' ? 'Rechercher un joueur' : moduleActif === 'evenements' ? 'Rechercher un evenement' : moduleActif === 'annonces' ? 'Rechercher une annonce' : moduleActif === 'documents' ? 'Rechercher un document' : moduleActif === 'messagerie' ? 'Rechercher une conversation' : 'Search'"
                    class="h-8 w-[165px] rounded-full border border-[#dbe2ef] bg-white px-3 py-1 text-xs text-[#1f2a44] outline-none placeholder:text-[#94a3b8]"
                  />
                </label>
                <button
                  type="button"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff] disabled:cursor-not-allowed disabled:opacity-60"
                  :disabled="chargementRafraichissement || chargementClubs || chargementEquipes || chargementJoueurs || chargementEvenements"
                  aria-label="Actualiser"
                  title="Actualiser"
                  @click="actualiserModuleActif"
                >
                  <svg class="h-3.5 w-3.5" :class="chargementRafraichissement || chargementClubs || chargementEquipes || chargementJoueurs || chargementEvenements ? 'animate-spin' : ''" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M16.25 9.25a6.25 6.25 0 1 0-1.72 4.31M16.25 9.25V5.5M16.25 9.25H12.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </button>
                <AppNotificationsDropdown
                  :open="notificationOuverte"
                  :loading="chargementNotifications"
                  :unread-total="notificationsNonLuesTotal"
                  :notifications="notificationsRecentes"
                  :action-in-progress="actionNotification"
                  :formatter="formatDateHeure"
                  @toggle="basculerNotifications"
                  @refresh="chargerNotificationsPresident"
                  @mark-all="marquerToutesNotificationsPresidentCommeLues"
                  @notification-click="ouvrirNotificationPresident"
                  @decision="repondreInvitationNotification($event.notification, $event.decision)"
                />
                <img
                  v-if="utilisateurResume.image"
                  :src="utilisateurResume.image"
                  :alt="utilisateurResume.nom"
                  class="h-8 w-8 rounded-full object-cover"
                />
                <span v-else class="block h-8 w-8 rounded-full bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)] ring-1 ring-[#dbe2ef]"></span>
              </div>
            </div>

            <div class="px-3 py-4 sm:px-5 sm:py-5">
              <div v-if="chargement" class="space-y-4">
                <div class="h-28 animate-pulse rounded-3xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
                <div class="h-56 animate-pulse rounded-3xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
                <div class="h-44 animate-pulse rounded-3xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
              </div>

              <template v-else>
                <div v-if="moduleActif === 'dashboard'">
                <section class="mt-6">
                  <div class="text-center">
                    <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Statistiques principales</h3>
                    <p class="mx-auto mt-1 max-w-xl text-xs font-semibold text-[#6b7280]">Etat rapide de l'organisation sportive.</p>
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

                <section class="mt-7 rounded-[22px] border border-[#e6edf8] bg-white p-4">
                  <div class="text-center">
                    <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Evenements proches</h3>
                    <p class="mx-auto mt-1 max-w-xl text-xs font-semibold text-[#6b7280]">Les prochains rendez-vous a suivre en priorite.</p>
                    <RouterLink to="/president/evenements" class="mt-3 inline-flex rounded-full border border-[#dbe2ef] px-3 py-1.5 text-xs font-extrabold text-[#1f36bf] transition hover:bg-[#f8fbff]">
                      Voir tous
                    </RouterLink>
                  </div>

                  <div v-if="evenementsDashboard.length" class="mt-5 overflow-hidden rounded-[30px] bg-[#f7f9ff] p-3 [mask-image:linear-gradient(90deg,transparent,black_8%,black_92%,transparent)]">
                    <div class="dashboard-event-carousel flex w-max gap-4">
                      <article
                        v-for="(evenement, index) in evenementsCarousel"
                        :key="`${evenement.id}-${index}`"
                        class="relative h-[270px] w-[250px] shrink-0 overflow-hidden rounded-[30px] border border-white/60 bg-cover bg-center p-4 text-white sm:w-[320px]"
                        :style="{ backgroundImage: backgroundEvenement(evenement) }"
                      >
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_10%,rgba(255,255,255,0.34),transparent_28%),linear-gradient(180deg,transparent,rgba(0,0,0,0.22))]"></div>
                        <div class="relative z-10 flex h-full flex-col items-center justify-center text-center">
                          <span class="rounded-full border border-white/35 bg-white/18 px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] text-white backdrop-blur-md">
                            {{ formatDate(evenement.date_debut) }}
                          </span>
                          <div v-if="evenement.type === 'match'" class="mt-4 grid w-full max-w-[230px] grid-cols-[1fr_auto_1fr] items-center gap-2">
                            <img v-if="logoEquipe(evenement.equipe)" :src="logoEquipe(evenement.equipe)" :alt="evenement.equipe?.nom || 'Equipe'" class="mx-auto h-12 w-12 rounded-2xl object-cover ring-4 ring-white/20" />
                            <span v-else class="mx-auto block h-12 w-12 rounded-2xl bg-white/25 ring-4 ring-white/20"></span>
                            <span class="rounded-full bg-white px-2.5 py-1 text-[9px] font-black text-[#111827]">VS</span>
                            <img v-if="logoEquipe(evenement.adversaire_equipe)" :src="logoEquipe(evenement.adversaire_equipe)" :alt="evenement.adversaire_equipe?.nom || 'Adversaire'" class="mx-auto h-12 w-12 rounded-2xl object-cover ring-4 ring-white/20" />
                            <span v-else class="mx-auto block h-12 w-12 rounded-2xl bg-white/25 ring-4 ring-white/20"></span>
                          </div>
                          <img v-else-if="logoEquipe(evenement.equipe)" :src="logoEquipe(evenement.equipe)" :alt="evenement.equipe?.nom || 'Equipe'" class="mt-4 h-14 w-14 rounded-2xl object-cover ring-4 ring-white/20" />
                          <h4 class="mt-4 text-3xl font-black leading-tight tracking-normal text-white sm:text-4xl">
                            {{ evenement.titre }}
                          </h4>
                          <p class="mt-3 max-w-[220px] text-xs font-semibold leading-5 text-white/78">
                            {{ evenement.type === 'match' ? `${evenement.equipe?.nom || 'Equipe'} vs ${evenement.adversaire_equipe?.nom || evenement.adversaire || 'Adversaire'}` : evenement.equipe?.nom || 'Equipe non definie' }}
                            <span v-if="evenement.lieu"> - {{ evenement.lieu }}</span>
                          </p>
                          <RouterLink to="/president/evenements" class="mt-5 rounded-full bg-white px-5 py-2 text-xs font-black text-[#1f36bf] transition hover:bg-[#eef4ff]">
                            Ouvrir
                          </RouterLink>
                        </div>
                      </article>
                    </div>
                  </div>

                  <p v-else class="mt-4 rounded-2xl border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-4 py-8 text-center text-sm font-semibold text-[#6b7280]">
                    Aucun evenement proche pour le moment.
                  </p>
                </section>

                <section class="mt-7 rounded-[22px] border border-[#e6edf8] bg-white p-4">
                  <div class="text-center">
                    <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Equipes recentes</h3>
                    <p class="mx-auto mt-1 max-w-xl text-xs font-semibold text-[#6b7280]">Les dernieres equipes a suivre dans votre espace president.</p>
                    <RouterLink to="/president/equipes" class="mt-3 inline-flex rounded-full border border-[#dbe2ef] px-3 py-1.5 text-xs font-extrabold text-[#1f36bf] transition hover:bg-[#f8fbff]">
                      Gerer equipes
                    </RouterLink>
                  </div>

                  <div v-if="cartesEquipesRecentes.length" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <article
                      v-for="(equipe, index) in cartesEquipesRecentes"
                      :key="equipe.id"
                      class="relative min-h-[230px] overflow-hidden rounded-[30px] border border-white/70 bg-cover bg-center p-5 text-white transition hover:-translate-y-1"
                      :class="index % 2 === 1 ? 'md:translate-y-5' : ''"
                      :style="{ backgroundImage: backgroundEquipe(equipe) }"
                    >
                      <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_10%,rgba(255,255,255,0.32),transparent_26%),linear-gradient(180deg,transparent,rgba(0,0,0,0.18))]"></div>
                      <div class="relative z-10 flex min-h-[190px] flex-col justify-between">
                        <p class="w-max rounded-full border border-white/30 bg-white/14 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-white backdrop-blur-md">
                          {{ equipe.categorie || 'Equipe' }}
                        </p>

                        <div>
                          <h4 class="text-3xl font-black leading-tight tracking-normal text-white">
                            {{ equipe.nom }}
                          </h4>
                          <p class="mt-2 text-xs font-semibold leading-5 text-white/76">
                            {{ equipe.club?.nom || 'Club non defini' }}
                          </p>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                          <p class="text-xs font-black text-white/82">{{ equipe.joueurs_total || 0 }} joueurs</p>
                          <RouterLink to="/president/equipes" class="rounded-full bg-white px-4 py-2 text-xs font-black text-[#1f36bf] transition hover:bg-[#eef4ff]">
                            Details
                          </RouterLink>
                        </div>
                      </div>
                    </article>
                  </div>

                  <p v-else class="mt-4 rounded-2xl border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-4 py-8 text-center text-sm font-semibold text-[#6b7280]">
                    Aucune equipe recente disponible.
                  </p>
                </section>
                </div>

                <section v-else-if="moduleActif === 'clubs'" class="mt-6">
                  <template v-if="modeClubs === 'liste'">
                    <div class="mx-auto max-w-3xl text-center">
                      <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Gestion president</p>
                      <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Gestion des clubs</h3>
                      <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#6b7280]">
                        Recherchez un club, ouvrez sa carte, puis modifiez ou supprimez depuis sa page detail.
                      </p>

                      <div class="mx-auto mt-5 max-w-2xl rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-2">
                        <input
                          v-model="rechercheClubs"
                          type="text"
                          placeholder="Rechercher un club..."
                          class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
                        />
                      </div>

                      <button
                        type="button"
                        class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white transition hover:bg-[#2446d8]"
                        @click="ouvrirCreationClub"
                      >
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                          <path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" />
                        </svg>
                        Nouveau club
                      </button>
                    </div>

                    <div v-if="chargementClubs" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                      <div v-for="n in 8" :key="n" class="h-[245px] animate-pulse rounded-[26px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
                    </div>

                    <p v-else-if="erreurClubs" class="mt-6 rounded-2xl border border-red-100 bg-red-50 px-4 py-4 text-sm font-semibold text-red-700">
                      {{ erreurClubs }}
                    </p>

                    <template v-else>
                      <div v-if="clubsGestion.length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <PresidentClubCard
                          v-for="club in clubsGestion"
                          :key="club.id"
                          :club="club"
                          :fallback-image="blueBackground"
                          @select="selectionnerClub"
                        />
                      </div>

                      <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
                        <h4 class="text-2xl text-[#111827]">Aucun club trouve</h4>
                        <p class="mt-2 text-sm font-semibold text-[#6b7280]">Essayez de changer la recherche ou creez un premier club depuis la page clubs complete.</p>
                        <button
                          type="button"
                          class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white transition hover:bg-[#2446d8]"
                          @click="ouvrirCreationClub"
                        >
                          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" />
                          </svg>
                          Ajouter le premier club
                        </button>
                      </div>

                      <div v-if="paginationClubs" class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#e6edf8] bg-[#f8fbff] px-4 py-3">
                        <p class="text-xs font-bold text-[#6b7280]">Page {{ paginationClubs.current_page || 1 }} / {{ paginationClubs.last_page || 1 }}</p>
                        <div class="flex gap-2">
                          <button
                            type="button"
                            class="rounded-full border border-[#dbe2ef] px-4 py-2 text-xs font-black text-[#1f2a44] disabled:opacity-40"
                            :disabled="(paginationClubs.current_page || 1) <= 1"
                            @click="chargerClubsGestion((paginationClubs.current_page || 1) - 1)"
                          >
                            Precedent
                          </button>
                          <button
                            type="button"
                            class="rounded-full bg-[#111827] px-4 py-2 text-xs font-black text-white disabled:opacity-40"
                            :disabled="(paginationClubs.current_page || 1) >= (paginationClubs.last_page || 1)"
                            @click="chargerClubsGestion((paginationClubs.current_page || 1) + 1)"
                          >
                            Suivant
                          </button>
                        </div>
                      </div>
                    </template>
                  </template>

                  <template v-else-if="modeClubs === 'detail' && clubSelectionne">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                      <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]"
                        aria-label="Retour a la liste"
                        title="Retour a la liste"
                        @click="retourListeClubs"
                      >
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                          <path d="M15.25 5.75 9 12l6.25 6.25M9.75 12H20" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                      </button>
                      <div class="flex gap-2">
                        <button
                          type="button"
                          class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]"
                          aria-label="Modifier le club"
                          title="Modifier le club"
                          @click="ouvrirEditionClub"
                        >
                          <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m14.25 6.25 3.5 3.5M4.75 19.25l3.48-.66c.38-.07.73-.26 1-.53l9.56-9.56a2.47 2.47 0 0 0-3.5-3.5L5.73 14.56c-.27.27-.46.62-.53 1l-.45 3.69Z" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                        </button>
                        <button
                          type="button"
                          class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#ef4444] text-white transition hover:bg-[#dc2626]"
                          aria-label="Supprimer le club"
                          title="Supprimer le club"
                          @click="supprimerClubSelectionne"
                        >
                          <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M9.75 4.75h4.5m-7.5 4h10.5m-9.35 0 .73 9.5a2.25 2.25 0 0 0 2.24 2.08h2.26a2.25 2.25 0 0 0 2.24-2.08l.73-9.5M10.5 11.75v5M13.5 11.75v5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                        </button>
                      </div>
                    </div>

                    <div class="mt-5 rounded-[30px] bg-[#f3f6fb] p-4">
                      <div class="relative min-h-[230px] overflow-hidden rounded-[28px] bg-cover bg-center" :style="{ backgroundImage: backgroundClub(clubSelectionne) }">
                        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(9,18,64,0.08),rgba(9,18,64,0.42))]"></div>
                        <div class="relative z-10 p-5 text-white">
                          <p class="w-max rounded-full bg-white/90 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-[#2446d8]">Club profile</p>
                        </div>
                      </div>

                      <section class="relative z-10 mx-3 -mt-12 rounded-[20px] border border-white/80 bg-white/95 p-4 backdrop-blur-md">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                          <div class="flex items-center gap-4">
                            <img v-if="clubSelectionne.logo_url" :src="clubSelectionne.logo_url" :alt="clubSelectionne.nom" class="h-20 w-20 rounded-2xl object-cover ring-4 ring-white" />
                            <span v-else class="block h-20 w-20 rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)] ring-4 ring-white"></span>
                            <div>
                              <h3 class="text-3xl leading-tight text-[#111827] sm:text-4xl">{{ clubSelectionne.nom }}</h3>
                              <p class="mt-1 text-sm font-semibold text-[#64748b]">{{ clubSelectionne.ville || '-' }}<span v-if="clubSelectionne.pays">, {{ clubSelectionne.pays }}</span></p>
                            </div>
                          </div>

                          <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-[#eef4ff] px-4 py-2 text-xs font-black text-[#2446d8]">{{ clubSelectionne.equipes_total || 0 }} equipes</span>
                            <span class="rounded-full bg-[#ecfdf5] px-4 py-2 text-xs font-black text-[#16a34a]">{{ clubSelectionne.joueurs_total || 0 }} joueurs</span>
                            <span class="rounded-full bg-[#fff7ed] px-4 py-2 text-xs font-black text-[#ea580c]">{{ clubSelectionne.coachs_total || 0 }} coachs</span>
                          </div>
                        </div>
                      </section>

                      <div class="mt-5 grid gap-4 lg:grid-cols-[0.92fr_1.08fr_0.9fr]">
                        <section class="rounded-[22px] bg-white p-4">
                          <p class="text-sm font-black text-[#111827]">President</p>
                          <div class="mt-4 flex items-center gap-3">
                            <img
                              v-if="clubSelectionne.president?.photo_url"
                              :src="clubSelectionne.president.photo_url"
                              :alt="clubSelectionne.president.nom"
                              class="h-14 w-14 rounded-2xl object-cover"
                            />
                            <span v-else class="block h-14 w-14 rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)]"></span>
                            <div>
                              <p class="text-sm font-black text-[#111827]">{{ clubSelectionne.president?.nom || utilisateurResume.nom }}</p>
                              <p class="text-xs font-semibold text-[#64748b]">{{ clubSelectionne.president?.email || utilisateurResume.email }}</p>
                              <p class="text-xs font-semibold text-[#94a3b8]">{{ clubSelectionne.president?.telephone || '-' }}</p>
                            </div>
                          </div>

                          <div class="mt-5 border-t border-[#eef2f7] pt-4">
                            <p class="text-sm font-black text-[#111827]">Informations club</p>
                            <div class="mt-3 space-y-2 text-xs font-semibold text-[#64748b]">
                              <p>Email : {{ clubSelectionne.email || '-' }}</p>
                              <p>Telephone : {{ clubSelectionne.telephone || '-' }}</p>
                              <p>Adresse : {{ clubSelectionne.adresse || '-' }}</p>
                            </div>
                          </div>
                        </section>

                        <section class="rounded-[22px] bg-white p-4">
                          <div class="flex items-start justify-between gap-3">
                            <div>
                              <p class="text-sm font-black text-[#111827]">Club information</p>
                              <p class="mt-2 text-sm font-semibold leading-6 text-[#64748b]">{{ clubSelectionne.description || 'Aucune description disponible pour ce club.' }}</p>
                            </div>
                            <button type="button" class="text-xs font-black text-[#2446d8]" @click="ouvrirEditionClub">Edit</button>
                          </div>

                          <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div class="rounded-2xl bg-[#f8fbff] p-3">
                              <p class="text-2xl font-black text-[#111827]">{{ clubSelectionne.equipes_total || 0 }}</p>
                              <p class="text-[10px] font-black uppercase text-[#6b7280]">Equipes</p>
                            </div>
                            <div class="rounded-2xl bg-[#f8fbff] p-3">
                              <p class="text-2xl font-black text-[#111827]">{{ clubSelectionne.joueurs_total || 0 }}</p>
                              <p class="text-[10px] font-black uppercase text-[#6b7280]">Joueurs</p>
                            </div>
                            <div class="rounded-2xl bg-[#f8fbff] p-3">
                              <p class="text-2xl font-black text-[#111827]">{{ clubSelectionne.coachs_total || 0 }}</p>
                              <p class="text-[10px] font-black uppercase text-[#6b7280]">Coachs</p>
                            </div>
                            <div class="rounded-2xl bg-[#f8fbff] p-3">
                              <p class="text-lg font-black text-[#111827]">{{ clubSelectionne.ville || '-' }}</p>
                              <p class="text-[10px] font-black uppercase text-[#6b7280]">Ville</p>
                            </div>
                          </div>
                        </section>

                        <section class="rounded-[22px] bg-white p-4">
                          <p class="text-sm font-black text-[#111827]">Coach principal</p>
                          <div v-if="clubSelectionne.coach_principal" class="mt-4 flex items-center gap-3">
                            <img
                              v-if="clubSelectionne.coach_principal.photo_url"
                              :src="clubSelectionne.coach_principal.photo_url"
                              :alt="clubSelectionne.coach_principal.nom"
                              class="h-14 w-14 rounded-2xl object-cover"
                            />
                            <span v-else class="block h-14 w-14 rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)]"></span>
                            <div>
                              <p class="text-sm font-black text-[#111827]">{{ clubSelectionne.coach_principal.nom || 'Coach non defini' }}</p>
                              <p class="text-xs font-semibold text-[#64748b]">{{ clubSelectionne.coach_principal.email || '-' }}</p>
                            </div>
                          </div>
                          <p v-else class="mt-4 rounded-2xl border border-dashed border-[#cfdaf2] px-3 py-4 text-center text-xs font-semibold text-[#6b7280]">Aucun coach principal.</p>
                        </section>
                      </div>

                      <section class="mt-5 rounded-[22px] bg-white p-4">
                        <div>
                          <p class="text-sm font-black text-[#111827]">Equipes</p>
                          <p class="mt-1 text-xs font-semibold text-[#64748b]">Cliquez sur une carte pour la selectionner. Les joueurs et evenements affiches plus bas concernent tout le club.</p>
                        </div>

                        <div v-if="equipesClubSelectionne.length" class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                          <PresidentTeamCard
                            v-for="equipe in equipesClubSelectionne"
                            :key="equipe.id"
                            :equipe="equipe"
                            :active="equipeSelectionnee?.id === equipe.id"
                            :fallback-image="blueBackground"
                            @select="selectionnerEquipeClub"
                          />
                        </div>
                        <p v-else class="mt-4 rounded-2xl border border-dashed border-[#cfdaf2] px-3 py-6 text-center text-sm font-semibold text-[#6b7280]">Aucune equipe liee a ce club.</p>
                      </section>

                      <section class="mt-5 grid gap-4 lg:grid-cols-2">
                        <section class="rounded-[22px] bg-white p-4">
                          <div class="flex items-start justify-between gap-3">
                            <div>
                              <p class="text-sm font-black text-[#111827]">Joueurs du club</p>
                              <p class="mt-1 text-xs font-semibold text-[#64748b]">Tous les joueurs regroupes depuis toutes les equipes.</p>
                            </div>
                            <span class="rounded-full bg-[#ecfdf5] px-3 py-1 text-[10px] font-black text-[#16a34a]">{{ joueursClubSelectionne.length }} joueurs</span>
                          </div>

                          <div v-if="joueursClubSelectionne.length" class="mt-4 grid gap-2 sm:grid-cols-2">
                            <div v-for="joueur in joueursClubSelectionne" :key="joueur.id || `${joueur.email}-${joueur.equipe_id}`" class="flex items-center gap-3 rounded-2xl bg-[#f8fbff] p-3">
                              <img v-if="joueur.photo_url" :src="joueur.photo_url" :alt="joueur.nom" class="h-10 w-10 rounded-xl object-cover" />
                              <span v-else class="block h-10 w-10 rounded-xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)]"></span>
                              <div class="min-w-0">
                                <p class="truncate text-sm font-black text-[#111827]">{{ joueur.nom || 'Joueur' }}</p>
                                <p class="truncate text-xs font-semibold text-[#6b7280]">{{ joueur.equipe_nom || joueur.role_equipe || joueur.email || '-' }}</p>
                              </div>
                            </div>
                          </div>
                          <p v-else class="mt-3 rounded-2xl border border-dashed border-[#cfdaf2] px-3 py-6 text-center text-xs font-semibold text-[#6b7280]">Aucun joueur dans ce club.</p>
                        </section>

                        <section class="rounded-[22px] bg-white p-4">
                          <div class="flex items-start justify-between gap-3">
                            <div>
                              <p class="text-sm font-black text-[#111827]">Evenements passes du club</p>
                              <p class="mt-1 text-xs font-semibold text-[#64748b]">Historique regroupe de toutes les equipes du club.</p>
                            </div>
                            <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-[10px] font-black text-[#2446d8]">{{ evenementsPassesClubSelectionne.length }} passes</span>
                          </div>

                          <div v-if="evenementsPassesClubSelectionne.length" class="mt-4 space-y-2">
                            <div v-for="evenement in evenementsPassesClubSelectionne" :key="evenement.id || `${evenement.titre}-${evenement.equipe_id}`" class="rounded-2xl bg-[#f8fbff] p-3">
                              <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                  <p class="text-sm font-black text-[#111827]">{{ evenement.titre }}</p>
                                  <p class="mt-1 text-xs font-semibold text-[#6b7280]">{{ formatDate(evenement.date_debut) }} - {{ evenement.lieu || 'Lieu non defini' }}</p>
                                </div>
                                <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-black text-[#2446d8]">{{ evenement.equipe_nom || 'Equipe' }}</span>
                              </div>
                              <div v-if="evenement.type === 'match'" class="mt-2 flex items-center gap-2 text-xs font-semibold text-[#94a3b8]">
                                <img v-if="logoEquipe(evenement.adversaire_equipe)" :src="logoEquipe(evenement.adversaire_equipe)" :alt="evenement.adversaire_equipe?.nom || 'Adversaire'" class="h-6 w-6 rounded-lg object-cover" />
                                <span>Adversaire : {{ evenement.adversaire_equipe?.nom || evenement.adversaire || '-' }}</span>
                              </div>
                            </div>
                          </div>
                          <p v-else class="mt-3 rounded-2xl border border-dashed border-[#cfdaf2] px-3 py-6 text-center text-xs font-semibold text-[#6b7280]">Aucun evenement passe dans ce club.</p>
                        </section>
                      </section>
                    </div>
                  </template>

                  <template v-else-if="modeClubs === 'creation'">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                      <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]"
                        aria-label="Retour a la liste"
                        title="Retour a la liste"
                        @click="modeClubs = 'liste'"
                      >
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                          <path d="M15.25 5.75 9 12l6.25 6.25M9.75 12H20" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                      </button>
                      <h3 class="text-2xl text-[#111827]">Ajouter un nouveau club</h3>
                    </div>

                    <PresidentClubForm
                      :model-value="formulaireClub"
                      :errors="erreursClub"
                      :logo-preview="logoClubPreview"
                      :logo-file-name="logoClubFichier?.name || ''"
                      :loading="envoiClub"
                      submit-label="Creer le club"
                      loading-label="Creation..."
                      @update-field="mettreAJourChampClub"
                      @choose-logo="choisirLogoClub"
                      @submit="creerClub"
                    />
                  </template>

                  <template v-else-if="modeClubs === 'edition' && clubSelectionne">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                      <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[#dbe2ef] px-4 py-2 text-xs font-black text-[#1f2a44] transition hover:bg-[#f8fbff]" @click="modeClubs = 'detail'">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                          <path d="M12.5 4.5 7 10l5.5 5.5M8 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Annuler
                      </button>
                      <h3 class="text-2xl text-[#111827]">Modifier le club</h3>
                    </div>

                    <PresidentClubForm
                      :model-value="formulaireClub"
                      :errors="erreursClub"
                      :logo-preview="logoClubPreview"
                      :current-logo-url="clubSelectionne.logo_url || ''"
                      :logo-file-name="logoClubFichier?.name || ''"
                      :loading="envoiClub"
                      submit-label="Enregistrer les modifications"
                      loading-label="Enregistrement..."
                      @update-field="mettreAJourChampClub"
                      @choose-logo="choisirLogoClub"
                      @submit="enregistrerClubSelectionne"
                    />
                  </template>
                </section>

                <section v-else-if="moduleActif === 'equipes'" class="mt-6">
                  <template v-if="modeEquipes === 'liste'">
                    <div class="mx-auto max-w-3xl text-center">
                      <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Gestion president</p>
                      <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Gestion des equipes</h3>
                      <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#6b7280]">
                        Choisissez un club, recherchez une equipe, puis ouvrez sa fiche pour modifier ou supprimer.
                      </p>

                      <div class="mx-auto mt-5 grid max-w-3xl gap-2 rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-2 md:grid-cols-[220px_1fr]">
                        <select
                          v-model="clubEquipeId"
                          class="h-11 rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]"
                          @change="changerClubEquipe"
                        >
                          <option value="">Selectionner un club</option>
                          <option v-for="club in clubsOptions" :key="club.id" :value="String(club.id)">
                            {{ club.nom }}
                          </option>
                        </select>
                        <input
                          v-model="rechercheEquipes"
                          type="text"
                          placeholder="Rechercher une equipe..."
                          class="h-11 rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
                        />
                      </div>

                      <button
                        type="button"
                        class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white transition hover:bg-[#2446d8] disabled:opacity-50"
                        :disabled="!clubEquipeId"
                        @click="ouvrirCreationEquipe"
                      >
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                          <path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" />
                        </svg>
                        Nouvelle equipe
                      </button>
                    </div>

                    <div v-if="chargementClubsOptions || chargementEquipes" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                      <div v-for="n in 8" :key="n" class="h-[220px] animate-pulse rounded-[24px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
                    </div>

                    <p v-else-if="!clubEquipeId" class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center text-sm font-semibold text-[#6b7280]">
                      Selectionnez un club pour afficher ses equipes.
                    </p>

                    <p v-else-if="erreurEquipes" class="mt-6 rounded-2xl border border-red-100 bg-red-50 px-4 py-4 text-sm font-semibold text-red-700">
                      {{ erreurEquipes }}
                    </p>

                    <template v-else>
                      <div v-if="equipesGestion.length" class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <PresidentTeamCard
                          v-for="equipe in equipesGestion"
                          :key="equipe.id"
                          :equipe="equipe"
                          :active="equipeGestionSelectionnee?.id === equipe.id"
                          :fallback-image="clubEquipeSelectionne?.logo_url || blueBackground"
                          @select="selectionnerEquipeGestion"
                        />
                      </div>

                      <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
                        <h4 class="text-2xl text-[#111827]">Aucune equipe trouvee</h4>
                        <p class="mt-2 text-sm font-semibold text-[#6b7280]">Ajoutez une premiere equipe pour ce club.</p>
                        <button
                          type="button"
                          class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white transition hover:bg-[#2446d8]"
                          @click="ouvrirCreationEquipe"
                        >
                          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" />
                          </svg>
                          Ajouter la premiere equipe
                        </button>
                      </div>

                      <div v-if="paginationEquipes" class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#e6edf8] bg-[#f8fbff] px-4 py-3">
                        <p class="text-xs font-bold text-[#6b7280]">Page {{ paginationEquipes.current_page || 1 }} / {{ paginationEquipes.last_page || 1 }}</p>
                        <div class="flex gap-2">
                          <button
                            type="button"
                            class="rounded-full border border-[#dbe2ef] px-4 py-2 text-xs font-black text-[#1f2a44] disabled:opacity-40"
                            :disabled="(paginationEquipes.current_page || 1) <= 1"
                            @click="chargerEquipesGestion((paginationEquipes.current_page || 1) - 1)"
                          >
                            Precedent
                          </button>
                          <button
                            type="button"
                            class="rounded-full bg-[#111827] px-4 py-2 text-xs font-black text-white disabled:opacity-40"
                            :disabled="(paginationEquipes.current_page || 1) >= (paginationEquipes.last_page || 1)"
                            @click="chargerEquipesGestion((paginationEquipes.current_page || 1) + 1)"
                          >
                            Suivant
                          </button>
                        </div>
                      </div>
                    </template>
                  </template>

                  <template v-else-if="modeEquipes === 'detail' && equipeGestionSelectionnee">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                      <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]"
                        aria-label="Retour a la liste"
                        title="Retour a la liste"
                        @click="retourListeEquipes"
                      >
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                          <path d="M15.25 5.75 9 12l6.25 6.25M9.75 12H20" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                      </button>
                      <div class="flex gap-2">
                        <button
                          type="button"
                          class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]"
                          aria-label="Modifier l equipe"
                          title="Modifier l equipe"
                          @click="ouvrirEditionEquipe"
                        >
                          <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m14.25 6.25 3.5 3.5M4.75 19.25l3.48-.66c.38-.07.73-.26 1-.53l9.56-9.56a2.47 2.47 0 0 0-3.5-3.5L5.73 14.56c-.27.27-.46.62-.53 1l-.45 3.69Z" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                        </button>
                        <button
                          type="button"
                          class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#ef4444] text-white transition hover:bg-[#dc2626]"
                          aria-label="Supprimer l equipe"
                          title="Supprimer l equipe"
                          @click="supprimerEquipeSelectionnee"
                        >
                          <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M9.75 4.75h4.5m-7.5 4h10.5m-9.35 0 .73 9.5a2.25 2.25 0 0 0 2.24 2.08h2.26a2.25 2.25 0 0 0 2.24-2.08l.73-9.5M10.5 11.75v5M13.5 11.75v5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                        </button>
                      </div>
                    </div>

                    <div class="mt-5 rounded-[30px] bg-[#f3f6fb] p-4">
                      <div class="relative min-h-[230px] overflow-hidden rounded-[28px] bg-cover bg-center" :style="{ backgroundImage: backgroundEquipe(equipeGestionSelectionnee) }">
                        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(9,18,64,0.12),rgba(9,18,64,0.58))]"></div>
                        <div class="relative z-10 p-5 text-white">
                          <p class="w-max rounded-full bg-white/90 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-[#2446d8]">Equipe profile</p>
                        </div>
                      </div>

                      <section class="relative z-10 mx-3 -mt-12 rounded-[20px] border border-white/80 bg-white/95 p-4 backdrop-blur-md">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                          <div class="flex items-center gap-4">
                            <img v-if="equipeGestionSelectionnee.logo_url" :src="equipeGestionSelectionnee.logo_url" :alt="equipeGestionSelectionnee.nom" class="h-20 w-20 rounded-2xl object-cover ring-4 ring-white" />
                            <span v-else class="block h-20 w-20 rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)] ring-4 ring-white"></span>
                            <div>
                              <h3 class="text-3xl leading-tight text-[#111827] sm:text-4xl">{{ equipeGestionSelectionnee.nom }}</h3>
                              <p class="mt-1 text-sm font-semibold text-[#64748b]">{{ clubEquipeSelectionne?.nom || 'Club non defini' }} - {{ equipeGestionSelectionnee.categorie || 'Categorie non definie' }}</p>
                            </div>
                          </div>

                          <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-[#eef4ff] px-4 py-2 text-xs font-black text-[#2446d8]">{{ equipeGestionSelectionnee.joueurs_total || 0 }} joueurs</span>
                            <span class="rounded-full bg-[#ecfdf5] px-4 py-2 text-xs font-black text-[#16a34a]">{{ equipeGestionSelectionnee.statut || 'active' }}</span>
                            <span class="rounded-full bg-[#fff7ed] px-4 py-2 text-xs font-black text-[#ea580c]">{{ equipeGestionSelectionnee.code_invitation || 'Code non defini' }}</span>
                          </div>
                        </div>
                      </section>

                      <div class="mt-5 grid gap-4 lg:grid-cols-3">
                        <section class="rounded-[22px] bg-white p-4">
                          <p class="text-sm font-black text-[#111827]">Coach</p>
                          <div v-if="equipeGestionSelectionnee.coach" class="mt-4">
                            <p class="text-sm font-black text-[#111827]">{{ [equipeGestionSelectionnee.coach.prenom, equipeGestionSelectionnee.coach.nom].filter(Boolean).join(' ') || equipeGestionSelectionnee.coach.name || 'Coach' }}</p>
                            <p class="mt-1 text-xs font-semibold text-[#64748b]">{{ equipeGestionSelectionnee.coach.email || '-' }}</p>
                          </div>
                          <p v-else class="mt-4 rounded-2xl border border-dashed border-[#cfdaf2] px-3 py-4 text-center text-xs font-semibold text-[#6b7280]">Aucun coach affecte.</p>
                        </section>

                        <section class="rounded-[22px] bg-white p-4 lg:col-span-2">
                          <p class="text-sm font-black text-[#111827]">Description</p>
                          <p class="mt-2 text-sm font-semibold leading-6 text-[#64748b]">{{ equipeGestionSelectionnee.description || 'Aucune description disponible pour cette equipe.' }}</p>
                        </section>
                      </div>
                    </div>
                  </template>

                  <template v-else-if="modeEquipes === 'creation'">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                      <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]"
                        aria-label="Retour a la liste"
                        title="Retour a la liste"
                        @click="modeEquipes = 'liste'"
                      >
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                          <path d="M15.25 5.75 9 12l6.25 6.25M9.75 12H20" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                      </button>
                      <h3 class="text-2xl text-[#111827]">Ajouter une nouvelle equipe</h3>
                    </div>

                    <PresidentTeamForm
                      :model-value="formulaireEquipe"
                      :errors="erreursEquipe"
                      :logo-preview="logoEquipePreview"
                      :logo-file-name="logoEquipeFichier?.name || ''"
                      :loading="envoiEquipe"
                      submit-label="Creer l equipe"
                      loading-label="Creation..."
                      @update-field="mettreAJourChampEquipe"
                      @choose-logo="choisirLogoEquipe"
                      @submit="creerEquipe"
                    />
                  </template>

                  <template v-else-if="modeEquipes === 'edition' && equipeGestionSelectionnee">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                      <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[#dbe2ef] px-4 py-2 text-xs font-black text-[#1f2a44] transition hover:bg-[#f8fbff]" @click="modeEquipes = 'detail'">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                          <path d="M12.5 4.5 7 10l5.5 5.5M8 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Annuler
                      </button>
                      <h3 class="text-2xl text-[#111827]">Modifier l equipe</h3>
                    </div>

                    <PresidentTeamForm
                      :model-value="formulaireEquipe"
                      :errors="erreursEquipe"
                      :logo-preview="logoEquipePreview"
                      :current-logo-url="equipeGestionSelectionnee.logo_url || ''"
                      :logo-file-name="logoEquipeFichier?.name || ''"
                      :loading="envoiEquipe"
                      submit-label="Enregistrer les modifications"
                      loading-label="Enregistrement..."
                      @update-field="mettreAJourChampEquipe"
                      @choose-logo="choisirLogoEquipe"
                      @submit="enregistrerEquipeSelectionnee"
                    />
                  </template>
                </section>

                <section v-else-if="moduleActif === 'joueurs'" class="mt-6">
                  <template v-if="modeJoueurs === 'liste'">
                    <div class="mx-auto max-w-3xl text-center">
                      <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Gestion president</p>
                      <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Gestion des joueurs</h3>
                      <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#6b7280]">
                        Choisissez un club et une equipe, recherchez un joueur, puis ouvrez sa fiche ou retirez-le de l equipe.
                      </p>

                      <div class="mx-auto mt-5 grid max-w-4xl gap-2 rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-2 lg:grid-cols-[210px_210px_1fr]">
                        <select v-model="clubJoueurId" class="h-11 rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]" @change="changerClubJoueur">
                          <option value="">Selectionner un club</option>
                          <option v-for="club in clubsOptions" :key="club.id" :value="String(club.id)">{{ club.nom }}</option>
                        </select>
                        <select v-model="equipeJoueurId" class="h-11 rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]" @change="changerEquipeJoueur">
                          <option value="">Selectionner une equipe</option>
                          <option v-for="equipe in equipesJoueurOptions" :key="equipe.id" :value="String(equipe.id)">{{ equipe.nom }}</option>
                        </select>
                        <input v-model="rechercheJoueurs" type="text" placeholder="Rechercher un joueur..." class="h-11 rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]" />
                      </div>

                      <button type="button" class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white transition hover:bg-[#2446d8] disabled:opacity-50" :disabled="!clubJoueurId || !equipeJoueurId" @click="ouvrirAjoutJoueur">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                          <path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" />
                        </svg>
                        Ajouter joueur
                      </button>
                    </div>

                    <div v-if="chargementClubsOptions || chargementJoueurs" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                      <div v-for="n in 8" :key="n" class="h-[210px] animate-pulse rounded-[26px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
                    </div>

                    <p v-else-if="!clubJoueurId || !equipeJoueurId" class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center text-sm font-semibold text-[#6b7280]">
                      Selectionnez un club et une equipe pour afficher les joueurs.
                    </p>

                    <p v-else-if="erreurJoueurs" class="mt-6 rounded-2xl border border-red-100 bg-red-50 px-4 py-4 text-sm font-semibold text-red-700">
                      {{ erreurJoueurs }}
                    </p>

                    <template v-else>
                      <div v-if="joueursGestion.length" class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <PresidentPlayerCard v-for="joueur in joueursGestion" :key="joueur.id" :joueur="joueur" :active="joueurSelectionne?.id === joueur.id" @select="selectionnerJoueur" />
                      </div>

                      <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
                        <h4 class="text-2xl text-[#111827]">Aucun joueur trouve</h4>
                        <p class="mt-2 text-sm font-semibold text-[#6b7280]">Ajoutez un joueur existant a cette equipe avec son id utilisateur.</p>
                        <button type="button" class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white transition hover:bg-[#2446d8]" @click="ouvrirAjoutJoueur">
                          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" />
                          </svg>
                          Ajouter un joueur
                        </button>
                      </div>

                      <div v-if="paginationJoueurs" class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#e6edf8] bg-[#f8fbff] px-4 py-3">
                        <p class="text-xs font-bold text-[#6b7280]">Page {{ paginationJoueurs.current_page || 1 }} / {{ paginationJoueurs.last_page || 1 }}</p>
                        <div class="flex gap-2">
                          <button type="button" class="rounded-full border border-[#dbe2ef] px-4 py-2 text-xs font-black text-[#1f2a44] disabled:opacity-40" :disabled="(paginationJoueurs.current_page || 1) <= 1" @click="chargerJoueursGestion((paginationJoueurs.current_page || 1) - 1)">Precedent</button>
                          <button type="button" class="rounded-full bg-[#111827] px-4 py-2 text-xs font-black text-white disabled:opacity-40" :disabled="(paginationJoueurs.current_page || 1) >= (paginationJoueurs.last_page || 1)" @click="chargerJoueursGestion((paginationJoueurs.current_page || 1) + 1)">Suivant</button>
                        </div>
                      </div>
                    </template>
                  </template>

                  <template v-else-if="modeJoueurs === 'detail' && joueurSelectionne">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                      <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]" aria-label="Retour a la liste" title="Retour a la liste" @click="retourListeJoueurs">
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                          <path d="M15.25 5.75 9 12l6.25 6.25M9.75 12H20" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                      </button>
                      <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#ef4444] text-white transition hover:bg-[#dc2626]" aria-label="Retirer le joueur" title="Retirer le joueur" @click="retirerJoueurSelectionne">
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                          <path d="M9.75 4.75h4.5m-7.5 4h10.5m-9.35 0 .73 9.5a2.25 2.25 0 0 0 2.24 2.08h2.26a2.25 2.25 0 0 0 2.24-2.08l.73-9.5M10.5 11.75v5M13.5 11.75v5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                      </button>
                    </div>

                    <div class="mt-5 rounded-[30px] bg-[#f3f6fb] p-4">
                      <section class="rounded-[24px] bg-white p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                          <div class="flex items-center gap-4">
                            <img v-if="joueurSelectionne.photo_url" :src="joueurSelectionne.photo_url" :alt="nomJoueurSelectionne" class="h-20 w-20 rounded-2xl object-cover" />
                            <span v-else class="block h-20 w-20 rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)]"></span>
                            <div>
                              <h3 class="text-3xl leading-tight text-[#111827] sm:text-4xl">{{ nomJoueurSelectionne }}</h3>
                              <p class="mt-1 text-sm font-semibold text-[#64748b]">{{ equipeJoueurSelectionnee?.nom || 'Equipe non definie' }} - {{ clubJoueurSelectionne?.nom || 'Club non defini' }}</p>
                            </div>
                          </div>
                          <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-[#eef4ff] px-4 py-2 text-xs font-black text-[#2446d8]">{{ joueurSelectionne.role_equipe || 'joueur' }}</span>
                            <span class="rounded-full bg-[#ecfdf5] px-4 py-2 text-xs font-black text-[#16a34a]">{{ joueurSelectionne.statut || 'actif' }}</span>
                            <span class="rounded-full bg-[#fff7ed] px-4 py-2 text-xs font-black text-[#ea580c]">{{ joueurSelectionne.date_affectation || '-' }}</span>
                          </div>
                        </div>
                      </section>

                      <div class="mt-5 grid gap-4 lg:grid-cols-3">
                        <section class="rounded-[22px] bg-white p-4">
                          <p class="text-sm font-black text-[#111827]">Contact</p>
                          <div class="mt-3 space-y-2 text-xs font-semibold text-[#64748b]">
                            <p>Email : {{ joueurSelectionne.email || '-' }}</p>
                            <p>Telephone : {{ joueurSelectionne.telephone || '-' }}</p>
                            <p>Adresse : {{ joueurSelectionne.adresse || '-' }}</p>
                          </div>
                        </section>
                        <section class="rounded-[22px] bg-white p-4 lg:col-span-2">
                          <p class="text-sm font-black text-[#111827]">Informations sportives</p>
                          <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl bg-[#f8fbff] p-3">
                              <p class="text-lg font-black text-[#111827]">{{ clubJoueurSelectionne?.nom || '-' }}</p>
                              <p class="text-[10px] font-black uppercase text-[#6b7280]">Club</p>
                            </div>
                            <div class="rounded-2xl bg-[#f8fbff] p-3">
                              <p class="text-lg font-black text-[#111827]">{{ equipeJoueurSelectionnee?.nom || '-' }}</p>
                              <p class="text-[10px] font-black uppercase text-[#6b7280]">Equipe</p>
                            </div>
                            <div class="rounded-2xl bg-[#f8fbff] p-3">
                              <p class="text-lg font-black text-[#111827]">{{ joueurSelectionne.role || '-' }}</p>
                              <p class="text-[10px] font-black uppercase text-[#6b7280]">Role compte</p>
                            </div>
                          </div>
                        </section>
                      </div>
                    </div>
                  </template>

                  <template v-else-if="modeJoueurs === 'ajout'">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                      <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]" aria-label="Retour a la liste" title="Retour a la liste" @click="modeJoueurs = 'liste'">
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                          <path d="M15.25 5.75 9 12l6.25 6.25M9.75 12H20" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                      </button>
                      <h3 class="text-2xl text-[#111827]">Ajouter un joueur a l equipe</h3>
                    </div>

                    <form class="mt-5 grid gap-4 rounded-[32px] border border-[#e6edf8] bg-[#f8fbff] p-5" @submit.prevent="ajouterJoueurEquipe">
                      <div class="rounded-3xl bg-white p-4">
                        <p class="text-sm font-black text-[#111827]">{{ clubJoueurSelectionne?.nom || 'Club' }} - {{ equipeJoueurSelectionnee?.nom || 'Equipe' }}</p>
                        <p class="mt-1 text-xs font-semibold text-[#64748b]">Le backend actuel ajoute un joueur avec son identifiant utilisateur.</p>
                      </div>
                      <label>
                        <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">ID utilisateur joueur</span>
                        <input v-model="utilisateurIdAjout" type="number" min="1" class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold outline-none focus:border-[#4c6fff]" />
                        <span v-if="erreursJoueur?.utilisateur_id?.[0]" class="mt-1 block text-xs font-semibold text-red-600">{{ erreursJoueur.utilisateur_id[0] }}</span>
                      </label>
                      <div class="flex justify-end">
                        <button :disabled="envoiJoueur" type="submit" class="rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white transition hover:bg-[#1f36bf] disabled:opacity-60">
                          {{ envoiJoueur ? 'Ajout...' : 'Ajouter le joueur' }}
                        </button>
                      </div>
                    </form>
                  </template>
                </section>

                <section v-else-if="moduleActif === 'evenements'" class="mt-6">
                  <template v-if="modeEvenements === 'liste'">
                    <div class="mx-auto max-w-3xl text-center">
                      <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Gestion president</p>
                      <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Gestion des evenements</h3>
                      <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#6b7280]">
                        Choisissez un club et une equipe, filtrez par date, puis ouvrez une carte pour modifier ou supprimer l evenement.
                      </p>

                      <div class="mx-auto mt-5 grid max-w-5xl gap-2 rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-2 lg:grid-cols-[190px_190px_1fr_160px_160px]">
                        <select v-model="clubEvenementId" class="h-11 rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]" @change="changerClubEvenement">
                          <option value="">Selectionner un club</option>
                          <option v-for="club in clubsOptions" :key="club.id" :value="String(club.id)">{{ club.nom }}</option>
                        </select>
                        <select v-model="equipeEvenementId" class="h-11 rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]" @change="changerEquipeEvenement">
                          <option value="">Selectionner une equipe</option>
                          <option v-for="equipe in equipesEvenementOptions" :key="equipe.id" :value="String(equipe.id)">{{ equipe.nom }}</option>
                        </select>
                        <input v-model="rechercheEvenements" type="text" placeholder="Rechercher un evenement..." class="h-11 rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]" />
                        <input v-model="dateDebutEvenements" type="date" class="h-11 rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]" title="Date debut" />
                        <input v-model="dateFinEvenements" type="date" class="h-11 rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]" title="Date fin" />
                      </div>

                      <button type="button" class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white transition hover:bg-[#2446d8] disabled:opacity-50" :disabled="!clubEvenementId || !equipeEvenementId" @click="ouvrirCreationEvenement">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                          <path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" />
                        </svg>
                        Nouvel evenement
                      </button>
                    </div>

                    <div v-if="chargementClubsOptions || chargementEquipesAdversairesEvenements || chargementEvenements" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                      <div v-for="n in 8" :key="n" class="h-[230px] animate-pulse rounded-[26px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
                    </div>

                    <p v-else-if="!clubEvenementId || !equipeEvenementId" class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center text-sm font-semibold text-[#6b7280]">
                      Selectionnez un club et une equipe pour afficher les evenements.
                    </p>

                    <p v-else-if="erreurEvenements" class="mt-6 rounded-2xl border border-red-100 bg-red-50 px-4 py-4 text-sm font-semibold text-red-700">
                      {{ erreurEvenements }}
                    </p>

                    <template v-else>
                      <div v-if="evenementsGestion.length" class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <PresidentEventCard v-for="evenement in evenementsGestion" :key="evenement.id" :evenement="evenement" :active="evenementSelectionne?.id === evenement.id" @select="selectionnerEvenement" />
                      </div>

                      <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
                        <h4 class="text-2xl text-[#111827]">Aucun evenement trouve</h4>
                        <p class="mt-2 text-sm font-semibold text-[#6b7280]">Changez la recherche, les dates, ou creez un nouvel evenement pour cette equipe.</p>
                        <button type="button" class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white transition hover:bg-[#2446d8]" @click="ouvrirCreationEvenement">
                          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" />
                          </svg>
                          Creer un evenement
                        </button>
                      </div>

                      <div v-if="paginationEvenements" class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#e6edf8] bg-[#f8fbff] px-4 py-3">
                        <p class="text-xs font-bold text-[#6b7280]">Page {{ paginationEvenements.current_page || 1 }} / {{ paginationEvenements.last_page || 1 }}</p>
                        <div class="flex gap-2">
                          <button type="button" class="rounded-full border border-[#dbe2ef] px-4 py-2 text-xs font-black text-[#1f2a44] disabled:opacity-40" :disabled="(paginationEvenements.current_page || 1) <= 1" @click="chargerEvenementsGestion((paginationEvenements.current_page || 1) - 1)">Precedent</button>
                          <button type="button" class="rounded-full bg-[#111827] px-4 py-2 text-xs font-black text-white disabled:opacity-40" :disabled="(paginationEvenements.current_page || 1) >= (paginationEvenements.last_page || 1)" @click="chargerEvenementsGestion((paginationEvenements.current_page || 1) + 1)">Suivant</button>
                        </div>
                      </div>
                    </template>
                  </template>

                  <template v-else-if="modeEvenements === 'detail' && evenementSelectionne">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                      <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]" aria-label="Retour a la liste" title="Retour a la liste" @click="retourListeEvenements">
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                          <path d="M15.25 5.75 9 12l6.25 6.25M9.75 12H20" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                      </button>
                      <div class="flex gap-2">
                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]" aria-label="Modifier" title="Modifier" @click="ouvrirEditionEvenement">
                          <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m14.75 5.25 4 4M4.75 19.25l4.45-.9 9.05-9.05a2.83 2.83 0 0 0-4-4L5.2 14.35l-.45 4.9Z" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                        </button>
                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#ef4444] text-white transition hover:bg-[#dc2626]" aria-label="Supprimer" title="Supprimer" @click="supprimerEvenementSelectionne">
                          <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M9.75 4.75h4.5m-7.5 4h10.5m-9.35 0 .73 9.5a2.25 2.25 0 0 0 2.24 2.08h2.26a2.25 2.25 0 0 0 2.24-2.08l.73-9.5M10.5 11.75v5M13.5 11.75v5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                        </button>
                      </div>
                    </div>

                    <div class="mt-5 rounded-[30px] bg-[#f3f6fb] p-4">
                      <section class="overflow-hidden rounded-[24px] bg-white">
                        <div class="min-h-[260px] bg-cover bg-center p-5 text-white" :style="{ backgroundImage: backgroundEvenement(evenementSelectionne) }">
                          <div class="flex min-h-[220px] flex-col justify-between">
                            <div class="flex flex-wrap gap-2">
                              <span class="rounded-full bg-white/90 px-4 py-2 text-xs font-black capitalize text-[#2446d8]">{{ evenementSelectionne.type || 'evenement' }}</span>
                              <span class="rounded-full bg-white/90 px-4 py-2 text-xs font-black capitalize text-[#111827]">{{ evenementSelectionne.statut || 'planifie' }}</span>
                              <span v-if="evenementSelectionne.type === 'match'" class="rounded-full bg-white/90 px-4 py-2 text-xs font-black text-[#f59e0b]">{{ libelleInvitation(evenementSelectionne.statut_invitation_adversaire) }}</span>
                            </div>
                            <div>
                              <div v-if="evenementSelectionne.type === 'match'" class="mb-5 grid max-w-2xl grid-cols-[1fr_auto_1fr] items-center gap-3 rounded-[26px] bg-white/14 p-4 backdrop-blur-md">
                                <div class="text-center">
                                  <img v-if="logoEquipe(evenementSelectionne.equipe || equipeEvenementSelectionnee)" :src="logoEquipe(evenementSelectionne.equipe || equipeEvenementSelectionnee)" :alt="evenementSelectionne.equipe?.nom || equipeEvenementSelectionnee?.nom || 'Equipe'" class="mx-auto h-16 w-16 rounded-[22px] object-cover ring-4 ring-white/25" />
                                  <span v-else class="mx-auto block h-16 w-16 rounded-[22px] bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)] ring-4 ring-white/25"></span>
                                  <p class="mt-2 truncate text-sm font-black text-white">{{ evenementSelectionne.equipe?.nom || equipeEvenementSelectionnee?.nom || 'Equipe' }}</p>
                                </div>
                                <span class="rounded-full bg-white px-4 py-2 text-xs font-black text-[#111827]">VS</span>
                                <div class="text-center">
                                  <img v-if="logoEquipe(evenementSelectionne.adversaire_equipe)" :src="logoEquipe(evenementSelectionne.adversaire_equipe)" :alt="evenementSelectionne.adversaire_equipe?.nom || 'Adversaire'" class="mx-auto h-16 w-16 rounded-[22px] object-cover ring-4 ring-white/25" />
                                  <span v-else class="mx-auto block h-16 w-16 rounded-[22px] bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#e2e8f0_38%,#94a3b8_90%)] ring-4 ring-white/25"></span>
                                  <p class="mt-2 truncate text-sm font-black text-white">{{ evenementSelectionne.adversaire_equipe?.nom || evenementSelectionne.adversaire || 'Adversaire' }}</p>
                                </div>
                              </div>

                              <div v-else class="mb-5 flex max-w-xl items-center gap-4 rounded-[26px] bg-white/14 p-4 backdrop-blur-md">
                                <img v-if="logoEquipe(evenementSelectionne.equipe || equipeEvenementSelectionnee)" :src="logoEquipe(evenementSelectionne.equipe || equipeEvenementSelectionnee)" :alt="evenementSelectionne.equipe?.nom || equipeEvenementSelectionnee?.nom || 'Equipe'" class="h-16 w-16 rounded-[22px] object-cover ring-4 ring-white/25" />
                                <span v-else class="block h-16 w-16 rounded-[22px] bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)] ring-4 ring-white/25"></span>
                                <div>
                                  <p class="text-xl font-black text-white">{{ evenementSelectionne.type === 'entrainement' ? 'Entrainement' : 'Reunion' }}</p>
                                  <p class="text-sm font-semibold text-white/78">{{ evenementSelectionne.equipe?.nom || equipeEvenementSelectionnee?.nom || 'Equipe' }}</p>
                                </div>
                              </div>

                              <h3 class="max-w-3xl text-4xl leading-tight text-white sm:text-5xl">{{ evenementSelectionne.titre }}</h3>
                              <p class="mt-2 max-w-2xl text-sm font-semibold text-white/80">
                                {{ clubEvenementSelectionne?.nom || evenementSelectionne.club?.nom || 'Club' }} - {{ equipeEvenementSelectionnee?.nom || evenementSelectionne.equipe?.nom || 'Equipe' }}
                              </p>
                            </div>
                          </div>
                        </div>
                      </section>

                      <div class="mt-5 grid gap-4 lg:grid-cols-4">
                        <section class="rounded-[22px] bg-white p-4">
                          <p class="text-sm font-black text-[#111827]">Date debut</p>
                          <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ formatDateHeure(evenementSelectionne.date_debut) }}</p>
                        </section>
                        <section class="rounded-[22px] bg-white p-4">
                          <p class="text-sm font-black text-[#111827]">Date fin</p>
                          <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ evenementSelectionne.date_fin ? formatDateHeure(evenementSelectionne.date_fin) : 'Non definie' }}</p>
                        </section>
                        <section class="rounded-[22px] bg-white p-4">
                          <p class="text-sm font-black text-[#111827]">Lieu</p>
                          <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ evenementSelectionne.lieu || '-' }}</p>
                        </section>
                        <section class="rounded-[22px] bg-white p-4">
                          <p class="text-sm font-black text-[#111827]">{{ evenementSelectionne.type === 'match' ? 'Equipe adversaire' : 'Type' }}</p>
                          <div v-if="evenementSelectionne.type === 'match'" class="mt-3 flex items-center gap-3">
                            <img v-if="logoEquipe(evenementSelectionne.adversaire_equipe)" :src="logoEquipe(evenementSelectionne.adversaire_equipe)" :alt="evenementSelectionne.adversaire_equipe?.nom || 'Adversaire'" class="h-10 w-10 rounded-xl object-cover" />
                            <span v-else class="block h-10 w-10 rounded-xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#e2e8f0_38%,#94a3b8_90%)]"></span>
                            <div class="min-w-0">
                              <p class="truncate text-sm font-black text-[#111827]">{{ evenementSelectionne.adversaire_equipe?.nom || evenementSelectionne.adversaire || '-' }}</p>
                              <p class="truncate text-xs font-semibold text-[#64748b]">{{ evenementSelectionne.adversaire_equipe?.club?.nom || 'Equipe de la plateforme' }}</p>
                            </div>
                          </div>
                          <p v-else class="mt-2 text-sm font-semibold text-[#64748b]">{{ evenementSelectionne.type === 'entrainement' ? 'Entrainement' : 'Reunion' }}</p>
                        </section>
                      </div>

                      <div class="mt-5 grid gap-4 lg:grid-cols-3">
                        <section class="rounded-[22px] bg-white p-4">
                          <p class="text-sm font-black text-[#111827]">Club</p>
                          <p class="mt-2 text-xl font-black text-[#111827]">{{ clubEvenementSelectionne?.nom || evenementSelectionne.club?.nom || '-' }}</p>
                          <p class="mt-1 text-xs font-semibold text-[#64748b]">{{ clubEvenementSelectionne?.ville || evenementSelectionne.club?.ville || 'Ville non definie' }}</p>
                        </section>
                        <section class="rounded-[22px] bg-white p-4">
                          <p class="text-sm font-black text-[#111827]">Equipe</p>
                          <p class="mt-2 text-xl font-black text-[#111827]">{{ equipeEvenementSelectionnee?.nom || evenementSelectionne.equipe?.nom || '-' }}</p>
                          <p class="mt-1 text-xs font-semibold text-[#64748b]">{{ equipeEvenementSelectionnee?.categorie || evenementSelectionne.equipe?.categorie || 'Categorie non definie' }}</p>
                        </section>
                        <section class="rounded-[22px] bg-white p-4">
                          <p class="text-sm font-black text-[#111827]">Statut</p>
                          <p class="mt-2 text-xl font-black capitalize text-[#111827]">{{ evenementSelectionne.statut || 'planifie' }}</p>
                          <p class="mt-1 text-xs font-semibold text-[#64748b]">Suivi administratif de l evenement.</p>
                        </section>
                      </div>

                      <section class="mt-5 rounded-[22px] bg-white p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                          <div>
                            <p class="text-sm font-black text-[#111827]">Disponibilites joueurs</p>
                            <p class="mt-1 text-xs font-semibold text-[#64748b]">Vue rapide des reponses avant les convocations.</p>
                          </div>
                          <span class="rounded-full bg-[#f2f6ff] px-3 py-1 text-[11px] font-black text-[#1f36bf]">
                            {{ evenementSelectionne.disponibilites?.total_reponses || 0 }} reponse(s)
                          </span>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                          <article class="rounded-[20px] border border-[#e6edf8] bg-[#f8fbff] px-4 py-3 text-center">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-[#7c8aa5]">Presents</p>
                            <p class="mt-2 text-2xl font-black text-[#16a34a]">{{ evenementSelectionne.disponibilites?.present_total || 0 }}</p>
                          </article>
                          <article class="rounded-[20px] border border-[#e6edf8] bg-[#f8fbff] px-4 py-3 text-center">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-[#7c8aa5]">Absents</p>
                            <p class="mt-2 text-2xl font-black text-[#ef4444]">{{ evenementSelectionne.disponibilites?.absent_total || 0 }}</p>
                          </article>
                          <article class="rounded-[20px] border border-[#e6edf8] bg-[#f8fbff] px-4 py-3 text-center">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-[#7c8aa5]">Incertains</p>
                            <p class="mt-2 text-2xl font-black text-[#f59e0b]">{{ evenementSelectionne.disponibilites?.incertain_total || 0 }}</p>
                          </article>
                          <article class="rounded-[20px] border border-[#e6edf8] bg-[#f8fbff] px-4 py-3 text-center">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-[#7c8aa5]">Sans reponse</p>
                            <p class="mt-2 text-2xl font-black text-[#1f36bf]">
                              {{ Math.max(0, (equipeSelectionnee?.joueurs?.length || 0) - (evenementSelectionne.disponibilites?.total_reponses || 0)) }}
                            </p>
                          </article>
                        </div>
                      </section>

                      <MatchCompositionSection
                        v-if="evenementSelectionne.type === 'match'"
                        :composition="compositionMatchEvenement"
                        :chargement="chargementCompositionMatch"
                        titre="Composition du match"
                        description="Lecture seule de la composition preparee pour cette rencontre."
                      />

                      <MatchSheetSection
                        v-if="evenementSelectionne.type === 'match'"
                        :feuille-match="feuilleMatchEvenement"
                        :chargement="chargementFeuilleMatch"
                        titre="Feuille de match"
                        description="Lecture seule du score et du resume de la rencontre."
                      />

                      <MatchStatisticsSection
                        v-if="evenementSelectionne.type === 'match'"
                        :statistiques="statistiquesMatchEvenement"
                        :chargement="chargementStatistiquesMatch"
                        titre="Statistiques du match"
                        description="Lecture seule des statistiques individuelles de la rencontre."
                      />

                      <section class="mt-5 rounded-[22px] bg-white p-4">
                        <p class="text-sm font-black text-[#111827]">Description</p>
                        <p class="mt-2 text-sm font-semibold leading-6 text-[#64748b]">{{ evenementSelectionne.description || 'Aucune description disponible pour cet evenement.' }}</p>
                      </section>
                    </div>
                  </template>

                  <template v-else-if="modeEvenements === 'creation'">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                      <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]" aria-label="Retour a la liste" title="Retour a la liste" @click="modeEvenements = 'liste'">
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                          <path d="M15.25 5.75 9 12l6.25 6.25M9.75 12H20" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                      </button>
                      <div class="text-right">
                        <h3 class="text-2xl text-[#111827]">Ajouter un evenement</h3>
                        <p class="text-xs font-semibold text-[#64748b]">{{ clubEvenementSelectionne?.nom || 'Club' }} - {{ equipeEvenementSelectionnee?.nom || 'Equipe' }}</p>
                      </div>
                    </div>

                    <PresidentEventForm
                      :model-value="formulaireEvenement"
                      :errors="erreursEvenement"
                      :loading="envoiEvenement"
                      :adversaire-options="equipesAdversairesDisponibles"
                      :equipe-locale="equipeEvenementSelectionnee"
                      submit-label="Creer l evenement"
                      loading-label="Creation..."
                      @update-field="mettreAJourChampEvenement"
                      @search-adversaire="rechercherEquipesAdversairesEvenementOptions"
                      @submit="creerEvenement"
                    />
                  </template>

                  <template v-else-if="modeEvenements === 'edition' && evenementSelectionne">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                      <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[#dbe2ef] px-4 py-2 text-xs font-black text-[#1f2a44] transition hover:bg-[#f8fbff]" @click="modeEvenements = 'detail'">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                          <path d="M12.5 4.5 7 10l5.5 5.5M8 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Annuler
                      </button>
                      <h3 class="text-2xl text-[#111827]">Modifier l evenement</h3>
                    </div>

                    <PresidentEventForm
                      :model-value="formulaireEvenement"
                      :errors="erreursEvenement"
                      :loading="envoiEvenement"
                      :adversaire-options="equipesAdversairesDisponibles"
                      :equipe-locale="equipeEvenementSelectionnee"
                      submit-label="Enregistrer les modifications"
                      loading-label="Enregistrement..."
                      @update-field="mettreAJourChampEvenement"
                      @search-adversaire="rechercherEquipesAdversairesEvenementOptions"
                      @submit="enregistrerEvenementSelectionne"
                    />
                  </template>
                </section>

                <PresidentAnnouncementsSection
                  v-else-if="moduleActif === 'annonces'"
                  ref="annoncesSectionRef"
                  v-model:search-term="rechercheAnnonces"
                  class="mt-6"
                />

                <PresidentDocumentsSection
                  v-else-if="moduleActif === 'documents'"
                  ref="documentsSectionRef"
                  v-model:search-term="rechercheDocuments"
                  class="mt-6"
                />

                <PresidentMessagingSection
                  v-else-if="moduleActif === 'messagerie'"
                  ref="messagerieSectionRef"
                  v-model:search-term="rechercheMessagerie"
                  class="mt-6"
                />

                <section v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-14 text-center">
                  <h3 class="text-3xl text-[#111827]">{{ liensFonctionnalites.find((item) => item.key === moduleActif)?.label }}</h3>
                  <p class="mx-auto mt-3 max-w-xl text-sm font-semibold text-[#6b7280]">
                    Cette fonctionnalite changera aussi le contenu dans le meme conteneur blanc. Le module Clubs est le premier module complet avec cartes et details.
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
              Le conteneur blanc reste l'espace principal pour les clubs, equipes, joueurs, evenements, documents, annonces et messages.
            </p>
          </section>

          <section id="contact-support" class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-md">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-white/60">Contact us</p>
            <h2 class="mt-2 text-lg font-extrabold text-white">Besoin d'aide sur la plateforme ?</h2>
            <p class="mt-2 text-sm leading-6 text-white/70">
              Utilisez la messagerie ou le profil president pour gerer les informations et suivre les prochaines integrations.
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


