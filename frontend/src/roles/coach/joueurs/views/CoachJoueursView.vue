<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import CoachJoueurs from '@/roles/coach/joueurs/components/CoachJoueurs.vue'
import CoachPlayerDetailsPanel from '@/roles/coach/joueurs/components/CoachPlayerDetailsPanel.vue'
import CoachPlayerForm from '@/roles/coach/joueurs/components/CoachPlayerForm.vue'
import CoachShellLayout from '@/roles/coach/shared/components/CoachShellLayout.vue'
import AppModalShell from '@/shared/components/ui/AppModalShell.vue'
import { useAuthSession } from '@/shared/session/useAuthSession'
import { notifyError, notifySuccess } from '@/shared/services/toastService'
import {
  createCoachPlayer,
  listCoachPlayers,
  listCoachTeams,
  removeCoachPlayer,
  updateCoachPlayer,
} from '@/roles/coach/joueurs/services/coachPlayersService'

const { utilisateur, chargerUtilisateur, deconnecter } = useAuthSession()

const chargementEquipes = ref(true)
const chargementJoueurs = ref(false)
const enregistrement = ref(false)
const equipes = ref([])
const joueurs = ref([])
const equipeSelectionneeId = ref('')
const recherche = ref('')
const joueurSelectionne = ref(null)
const modalFormulaire = ref(false)
const modeFormulaire = ref('create')
const erreurs = ref({})
const emailInitial = ref('')

const formulaire = reactive({
  nom: '',
  prenom: '',
  email: '',
  telephone: '',
  adresse: '',
  photo: null,
  statut: 'actif',
  numero_joueur: '',
  poste_principal: '',
  poste_secondaire: '',
  pied_fort: '',
  note_globale: '',
  attaque: '',
  defense: '',
  vitesse: '',
  passe: '',
  dribble: '',
  physique: '',
})

const joueursFiltres = computed(() => {
  const q = recherche.value.trim().toLowerCase()

  if (!q) {
    return joueurs.value
  }

  return joueurs.value.filter((joueur) =>
    [
      joueur.nom,
      joueur.prenom,
      joueur.email,
      joueur.poste_principal,
      joueur.numero_joueur ? String(joueur.numero_joueur) : '',
    ].some((value) => value?.toLowerCase().includes(q))
  )
})

const resetForm = () => {
  formulaire.nom = ''
  formulaire.prenom = ''
  formulaire.email = ''
  formulaire.telephone = ''
  formulaire.adresse = ''
  formulaire.photo = null
  formulaire.statut = 'actif'
  formulaire.numero_joueur = ''
  formulaire.poste_principal = ''
  formulaire.poste_secondaire = ''
  formulaire.pied_fort = ''
  formulaire.note_globale = ''
  formulaire.attaque = ''
  formulaire.defense = ''
  formulaire.vitesse = ''
  formulaire.passe = ''
  formulaire.dribble = ''
  formulaire.physique = ''
  erreurs.value = {}
  emailInitial.value = ''
}

const hydrateForm = (joueur) => {
  formulaire.nom = joueur?.nom_famille || ''
  formulaire.prenom = joueur?.prenom || ''
  formulaire.email = joueur?.email || ''
  formulaire.telephone = joueur?.telephone || ''
  formulaire.adresse = joueur?.adresse || ''
  formulaire.photo = null
  formulaire.statut = joueur?.statut || 'actif'
  formulaire.numero_joueur = joueur?.numero_joueur || ''
  formulaire.poste_principal = joueur?.poste_principal || ''
  formulaire.poste_secondaire = joueur?.poste_secondaire || ''
  formulaire.pied_fort = joueur?.pied_fort || ''
  formulaire.note_globale = joueur?.note_globale || ''
  formulaire.attaque = joueur?.attaque || ''
  formulaire.defense = joueur?.defense || ''
  formulaire.vitesse = joueur?.vitesse || ''
  formulaire.passe = joueur?.passe || ''
  formulaire.dribble = joueur?.dribble || ''
  formulaire.physique = joueur?.physique || ''
  erreurs.value = {}
  emailInitial.value = joueur?.email || ''
}

const mettreAJourChamp = (champ, valeur) => {
  formulaire[champ] = valeur
}

const chargerEquipes = async () => {
  chargementEquipes.value = true

  try {
    equipes.value = await listCoachTeams()
    equipeSelectionneeId.value = equipes.value[0] ? String(equipes.value[0].id) : ''
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les equipes.')
  } finally {
    chargementEquipes.value = false
  }
}

const chargerJoueurs = async () => {
  if (!equipeSelectionneeId.value) {
    joueurs.value = []
    joueurSelectionne.value = null
    return
  }

  chargementJoueurs.value = true

  try {
    joueurs.value = await listCoachPlayers(equipeSelectionneeId.value)

    if (joueurSelectionne.value) {
      joueurSelectionne.value = joueurs.value.find((joueur) => String(joueur.id) === String(joueurSelectionne.value.id)) || null
    }
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de charger les joueurs.')
  } finally {
    chargementJoueurs.value = false
  }
}

const ouvrirCreation = () => {
  if (!equipeSelectionneeId.value) {
    notifyError('Selectionnez d abord une equipe.')
    return
  }

  modeFormulaire.value = 'create'
  resetForm()
  modalFormulaire.value = true
}

const ouvrirEdition = (joueur) => {
  modeFormulaire.value = 'edit'
  joueurSelectionne.value = joueur
  hydrateForm(joueur)
  modalFormulaire.value = true
}

const fermerFormulaire = () => {
  modalFormulaire.value = false
  erreurs.value = {}
}

const soumettreFormulaire = async () => {
  if (!equipeSelectionneeId.value) {
    notifyError('Selectionnez une equipe.')
    return
  }

  enregistrement.value = true
  erreurs.value = {}

  try {
    const donnees = { ...formulaire }

    if (modeFormulaire.value === 'edit' && donnees.email === emailInitial.value) {
      delete donnees.email
    }

    const response =
      modeFormulaire.value === 'edit' && joueurSelectionne.value
        ? await updateCoachPlayer(equipeSelectionneeId.value, joueurSelectionne.value.id, donnees)
        : await createCoachPlayer(equipeSelectionneeId.value, donnees)

    notifySuccess(response?.message || 'Joueur enregistre avec succes.')
    fermerFormulaire()
    await chargerJoueurs()

    if (response?.data?.joueur) {
      joueurSelectionne.value = joueurs.value.find((joueur) => String(joueur.id) === String(response.data.joueur.id)) || response.data.joueur
    }
  } catch (error) {
    erreurs.value = error?.response?.data || {}
    notifyError(error?.response?.message || error.message || 'Impossible d enregistrer ce joueur.')
  } finally {
    enregistrement.value = false
  }
}

const retirerJoueur = async (joueur) => {
  if (!equipeSelectionneeId.value) {
    notifyError('Selectionnez une equipe.')
    return
  }

  if (!window.confirm(`Retirer ${joueur.nom || 'ce joueur'} de l equipe ?`)) {
    return
  }

  try {
    const response = await removeCoachPlayer(equipeSelectionneeId.value, joueur.id)
    notifySuccess(response?.message || 'Joueur retire avec succes.')

    if (joueurSelectionne.value && String(joueurSelectionne.value.id) === String(joueur.id)) {
      joueurSelectionne.value = null
    }

    await chargerJoueurs()
  } catch (error) {
    notifyError(error?.response?.message || error.message || 'Impossible de retirer ce joueur.')
  }
}

watch(equipeSelectionneeId, chargerJoueurs)

onMounted(async () => {
  chargerUtilisateur()
  await chargerEquipes()
  await chargerJoueurs()
})
</script>

<template>
  <CoachShellLayout
    title="Joueurs coach"
    subtitle="Gestion complete des joueurs et de leurs cartes football."
    active-tab="joueurs"
    :user="utilisateur"
    @logout="deconnecter"
  >
    <CoachJoueurs
      :equipes="equipes"
      :equipe-id="equipeSelectionneeId"
      :recherche="recherche"
      :chargement-equipes="chargementEquipes"
      :chargement-joueurs="chargementJoueurs"
      :joueurs="joueursFiltres"
      @update:equipe-id="equipeSelectionneeId = $event"
      @update:recherche="recherche = $event"
      @add="ouvrirCreation"
      @select-player="joueurSelectionne = $event"
    />

    <AppModalShell
      v-if="modalFormulaire"
      :title="modeFormulaire === 'edit' ? 'Modifier le joueur' : 'Nouveau joueur'"
      max-width-class="max-w-5xl"
      @close="fermerFormulaire"
    >
      <CoachPlayerForm
        :model-value="formulaire"
        :errors="erreurs"
        :loading="enregistrement"
        :mode="modeFormulaire"
        @submit="soumettreFormulaire"
        @update-field="mettreAJourChamp"
      />
    </AppModalShell>

    <AppModalShell
      v-if="joueurSelectionne"
      title="Carte joueur"
      max-width-class="max-w-6xl"
      @close="joueurSelectionne = null"
    >
      <CoachPlayerDetailsPanel
        :joueur="joueurSelectionne"
        @edit="ouvrirEdition"
        @remove="retirerJoueur"
      />
    </AppModalShell>
  </CoachShellLayout>
</template>
