<script setup>
import { onMounted, reactive, ref, watch } from 'vue'
import CoachEventCard from '@/roles/coach/evenements/components/CoachEventCard.vue'
import CoachEventForm from '@/roles/coach/evenements/components/CoachEventForm.vue'
import CoachShellLayout from '@/roles/coach/shared/components/CoachShellLayout.vue'
import AppSelectField from '@/shared/components/AppSelectField.vue'
import AppModalShell from '@/shared/components/ui/AppModalShell.vue'
import { useAuthSession } from '@/shared/session/useAuthSession'
import { authDelete, authGet, authPost, authPut } from '@/shared/services/apiClient'
import { notifyError, notifySuccess } from '@/shared/services/toastService'

const { utilisateur, chargerUtilisateur, deconnecter, gererErreurAuthentification } = useAuthSession()
const chargementEquipes = ref(true)
const chargementEvenements = ref(false)
const envoi = ref(false)
const equipes = ref([])
const evenements = ref([])
const equipeSelectionneeId = ref('')
const modalOuvert = ref(false)
const mode = ref('creation')
const evenementSelectionne = ref(null)
const erreurs = ref({})

const formulaire = reactive({
  titre: '',
  type: 'entrainement',
  date_debut: '',
  date_fin: '',
  lieu: '',
  adversaire: '',
  adversaire_equipe_id: '',
  description: '',
  statut: 'planifie',
})

const formatDate = (value) => {
  if (!value) return '-'

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value))
}

const lireErreur = (champ) => erreurs.value?.[champ]?.[0] || ''

const mettreAJourChamp = (champ, valeur) => {
  formulaire[champ] = valeur
}

const reinitialiserFormulaire = () => {
  formulaire.titre = ''
  formulaire.type = 'entrainement'
  formulaire.date_debut = ''
  formulaire.date_fin = ''
  formulaire.lieu = ''
  formulaire.adversaire = ''
  formulaire.adversaire_equipe_id = ''
  formulaire.description = ''
  formulaire.statut = 'planifie'
  erreurs.value = {}
}

const remplirFormulaire = (evenement) => {
  formulaire.titre = evenement.titre || ''
  formulaire.type = evenement.type || 'entrainement'
  formulaire.date_debut = evenement.date_debut ? String(evenement.date_debut).slice(0, 16) : ''
  formulaire.date_fin = evenement.date_fin ? String(evenement.date_fin).slice(0, 16) : ''
  formulaire.lieu = evenement.lieu || ''
  formulaire.adversaire = evenement.adversaire || ''
  formulaire.adversaire_equipe_id = evenement.adversaire_equipe_id ? String(evenement.adversaire_equipe_id) : ''
  formulaire.description = evenement.description || ''
  formulaire.statut = evenement.statut || 'planifie'
  erreurs.value = {}
}

const chargerEquipes = async () => {
  chargementEquipes.value = true

  try {
    const reponse = await authGet('/coach/equipes')
    equipes.value = reponse?.data?.equipes || []
    equipeSelectionneeId.value = equipes.value[0] ? String(equipes.value[0].id) : ''
  } catch (error) {
    if (!gererErreurAuthentification(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les equipes.')
    }
  } finally {
    chargementEquipes.value = false
  }
}

const chargerEvenements = async () => {
  if (!equipeSelectionneeId.value) {
    evenements.value = []
    return
  }

  chargementEvenements.value = true

  try {
    const reponse = await authGet(`/coach/equipes/${equipeSelectionneeId.value}/evenements`)
    evenements.value = reponse?.data?.evenements || []
  } catch (error) {
    if (!gererErreurAuthentification(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les evenements.')
    }
  } finally {
    chargementEvenements.value = false
  }
}

const ouvrirCreation = () => {
  reinitialiserFormulaire()
  mode.value = 'creation'
  evenementSelectionne.value = null
  modalOuvert.value = true
}

const ouvrirEdition = (evenement) => {
  evenementSelectionne.value = evenement
  remplirFormulaire(evenement)
  mode.value = 'edition'
  modalOuvert.value = true
}

const fermerModal = () => {
  modalOuvert.value = false
  erreurs.value = {}
}

const enregistrer = async () => {
  if (!equipeSelectionneeId.value) {
    notifyError('Choisissez une equipe.')
    return
  }

  envoi.value = true
  erreurs.value = {}

  const payload = {
    titre: formulaire.titre,
    type: formulaire.type,
    date_debut: formulaire.date_debut,
    date_fin: formulaire.date_fin || null,
    lieu: formulaire.lieu || null,
    adversaire: formulaire.type === 'match' ? formulaire.adversaire || null : null,
    adversaire_equipe_id: formulaire.type === 'match' && formulaire.adversaire_equipe_id ? Number(formulaire.adversaire_equipe_id) : null,
    description: formulaire.description || null,
    statut: formulaire.statut,
  }

  try {
    let reponse

    if (mode.value === 'edition' && evenementSelectionne.value) {
      reponse = await authPut(`/coach/equipes/${equipeSelectionneeId.value}/evenements/${evenementSelectionne.value.id}`, payload)
    } else {
      reponse = await authPost(`/coach/equipes/${equipeSelectionneeId.value}/evenements`, payload)
    }

    notifySuccess(reponse?.message || 'Evenement enregistre avec succes.')
    fermerModal()
    await chargerEvenements()
  } catch (error) {
    if (gererErreurAuthentification(error)) {
      return
    }

    erreurs.value = error?.response?.data || {}
    notifyError(error?.response?.message || error.message || 'Impossible d enregistrer cet evenement.')
  } finally {
    envoi.value = false
  }
}

const supprimerEvenement = async (evenement) => {
  if (!window.confirm('Supprimer cet evenement ?')) return

  try {
    const reponse = await authDelete(`/coach/equipes/${equipeSelectionneeId.value}/evenements/${evenement.id}`)
    notifySuccess(reponse?.message || 'Evenement supprime avec succes.')
    await chargerEvenements()
  } catch (error) {
    if (!gererErreurAuthentification(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de supprimer cet evenement.')
    }
  }
}

watch(equipeSelectionneeId, chargerEvenements)

onMounted(async () => {
  chargerUtilisateur()
  await chargerEquipes()
  await chargerEvenements()
})
</script>

<template>
  <CoachShellLayout
    title="Evenements coach"
    subtitle="Planifiez et suivez les activites de vos equipes."
    active-tab="evenements"
    :user="utilisateur"
    @logout="deconnecter"
  >
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div class="min-w-[280px]">
        <AppSelectField
          v-model="equipeSelectionneeId"
          label="Equipe"
          :options="equipes"
          placeholder="Choisir une equipe"
          :disabled="chargementEquipes"
          select-class="h-12 w-full rounded-2xl border border-[#dbe3f1] px-4 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]"
        />
      </div>

      <button type="button" class="rounded-full bg-[#0f172a] px-5 py-3 text-sm font-semibold text-white" @click="ouvrirCreation">
        Nouvel evenement
      </button>
    </div>

    <div v-if="chargementEvenements" class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <div v-for="item in 6" :key="item" class="h-48 animate-pulse rounded-[24px] border border-[#edf2ff] bg-[#f8fbff]"></div>
    </div>

    <div v-else class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <CoachEventCard
        v-for="evenement in evenements"
        :key="evenement.id"
        :evenement="evenement"
        :format-date="formatDate"
        @edit="ouvrirEdition"
        @delete="supprimerEvenement"
      />

      <div v-if="!evenements.length" class="rounded-[28px] border border-dashed border-[#d7e1fb] bg-[#f8fbff] p-8 text-center text-sm font-semibold text-[#64748b] md:col-span-2 xl:col-span-3">
        Aucun evenement pour cette equipe.
      </div>
    </div>

    <AppModalShell v-if="modalOuvert" :title="mode === 'edition' ? 'Modifier evenement' : 'Nouvel evenement'" max-width-class="max-w-2xl" @close="fermerModal">
      <CoachEventForm
        :model-value="formulaire"
        :errors="erreurs"
        :loading="envoi"
        @submit="enregistrer"
        @update-field="mettreAJourChamp"
      />
    </AppModalShell>
  </CoachShellLayout>
</template>
