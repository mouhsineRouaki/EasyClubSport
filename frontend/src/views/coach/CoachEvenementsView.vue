<script setup>
import { onMounted, reactive, ref, watch } from 'vue'
import CoachShellLayout from '../../components/coach/CoachShellLayout.vue'
import AppSelectField from '../../components/common/AppSelectField.vue'
import { useAuthSession } from '../../composables/useAuthSession'
import { authDelete, authGet, authPost, authPut } from '../../services/api'
import { notifyError, notifySuccess } from '../../stores/toast'

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
      <article v-for="evenement in evenements" :key="evenement.id" class="rounded-[26px] border border-[#e5ecfb] bg-white p-5">
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">{{ evenement.type }}</p>
            <h2 class="mt-2 text-xl font-black text-[#0f172a]">{{ evenement.titre }}</h2>
          </div>
          <span class="rounded-full bg-[#f8fbff] px-3 py-1 text-[11px] font-black capitalize text-[#2446d8]">{{ evenement.statut }}</span>
        </div>

        <p class="mt-3 text-sm font-semibold text-[#475569]">{{ formatDate(evenement.date_debut) }}</p>
        <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ evenement.lieu || evenement.adversaire_equipe?.nom || evenement.adversaire || '-' }}</p>
        <p class="mt-4 line-clamp-3 text-sm text-[#475569]">{{ evenement.description || 'Aucune description.' }}</p>

        <div class="mt-5 flex gap-2">
          <button type="button" class="rounded-full border border-[#d7e1fb] px-4 py-2 text-xs font-semibold text-[#2446d8]" @click="ouvrirEdition(evenement)">Modifier</button>
          <button type="button" class="rounded-full border border-[#fecdd3] px-4 py-2 text-xs font-semibold text-[#e11d48]" @click="supprimerEvenement(evenement)">Supprimer</button>
        </div>
      </article>

      <div v-if="!evenements.length" class="rounded-[28px] border border-dashed border-[#d7e1fb] bg-[#f8fbff] p-8 text-center text-sm font-semibold text-[#64748b] md:col-span-2 xl:col-span-3">
        Aucun evenement pour cette equipe.
      </div>
    </div>

    <div v-if="modalOuvert" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/35 p-4" @click.self="fermerModal">
      <section class="w-full max-w-2xl rounded-[28px] border border-[#e5ecfb] bg-white p-6 shadow-[0_30px_60px_rgba(15,23,42,0.18)]">
        <div class="flex items-center justify-between gap-3">
          <h2 class="text-2xl font-black text-[#0f172a]">{{ mode === 'edition' ? 'Modifier evenement' : 'Nouvel evenement' }}</h2>
          <button type="button" class="rounded-full border border-[#d7e1fb] px-4 py-2 text-xs font-semibold text-[#2446d8]" @click="fermerModal">Fermer</button>
        </div>

        <form class="mt-6 grid gap-4 md:grid-cols-2" @submit.prevent="enregistrer">
          <label class="md:col-span-2">
            <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Titre</span>
            <input v-model="formulaire.titre" type="text" class="h-12 w-full rounded-2xl border border-[#dbe3f1] px-4 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]" />
            <span v-if="lireErreur('titre')" class="mt-2 block text-xs font-semibold text-[#e11d48]">{{ lireErreur('titre') }}</span>
          </label>

          <label>
            <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Type</span>
            <select v-model="formulaire.type" class="h-12 w-full rounded-2xl border border-[#dbe3f1] px-4 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]">
              <option value="match">Match</option>
              <option value="entrainement">Entrainement</option>
              <option value="reunion">Reunion</option>
            </select>
          </label>

          <label>
            <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Statut</span>
            <select v-model="formulaire.statut" class="h-12 w-full rounded-2xl border border-[#dbe3f1] px-4 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]">
              <option value="planifie">Planifie</option>
              <option value="termine">Termine</option>
              <option value="annule">Annule</option>
            </select>
          </label>

          <label>
            <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Date debut</span>
            <input v-model="formulaire.date_debut" type="datetime-local" class="h-12 w-full rounded-2xl border border-[#dbe3f1] px-4 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]" />
            <span v-if="lireErreur('date_debut')" class="mt-2 block text-xs font-semibold text-[#e11d48]">{{ lireErreur('date_debut') }}</span>
          </label>

          <label>
            <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Date fin</span>
            <input v-model="formulaire.date_fin" type="datetime-local" class="h-12 w-full rounded-2xl border border-[#dbe3f1] px-4 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]" />
          </label>

          <label>
            <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Lieu</span>
            <input v-model="formulaire.lieu" type="text" class="h-12 w-full rounded-2xl border border-[#dbe3f1] px-4 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]" />
          </label>

          <label v-if="formulaire.type === 'match'">
            <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Equipe adverse ID</span>
            <input v-model="formulaire.adversaire_equipe_id" type="number" class="h-12 w-full rounded-2xl border border-[#dbe3f1] px-4 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]" />
            <span v-if="lireErreur('adversaire_equipe_id')" class="mt-2 block text-xs font-semibold text-[#e11d48]">{{ lireErreur('adversaire_equipe_id') }}</span>
          </label>

          <label v-if="formulaire.type === 'match'" class="md:col-span-2">
            <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Adversaire texte</span>
            <input v-model="formulaire.adversaire" type="text" class="h-12 w-full rounded-2xl border border-[#dbe3f1] px-4 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]" />
          </label>

          <label class="md:col-span-2">
            <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Description</span>
            <textarea v-model="formulaire.description" rows="4" class="w-full rounded-2xl border border-[#dbe3f1] px-4 py-3 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]"></textarea>
          </label>

          <div class="md:col-span-2 flex justify-end">
            <button type="submit" class="rounded-full bg-[#0f172a] px-6 py-3 text-sm font-semibold text-white" :disabled="envoi">
              {{ envoi ? 'Enregistrement...' : 'Enregistrer' }}
            </button>
          </div>
        </form>
      </section>
    </div>
  </CoachShellLayout>
</template>
