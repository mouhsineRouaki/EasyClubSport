<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppCard from '@/shared/components/AppCard.vue'
import AppListState from '@/shared/components/AppListState.vue'
import AppPagination from '@/shared/components/AppPagination.vue'
import PresidentShellLayout from '@/roles/president/shared/components/PresidentShellLayout.vue'
import { authDelete, authGet, authPost, authPut } from '@/shared/services/apiClient'
import { notifyError, notifySuccess } from '@/shared/services/toastService'

const router = useRouter()

const chargementClubs = ref(true)
const chargementEquipes = ref(false)
const envoi = ref(false)
const clubs = ref([])
const equipes = ref([])
const pagination = ref(null)
const selectedClubId = ref('')
const erreurChargement = ref('')
const erreursValidation = ref({})
const succes = ref('')
const afficherFormulaire = ref(false)
const modeEdition = ref(false)
const equipeEditionId = ref(null)
const logoFichier = ref(null)
const logoPreview = ref('')
const utilisateurConnecte = ref(null)
const searchDebounce = ref(null)

const filtres = reactive({
  q: '',
  statut: '',
  page: 1,
  per_page: 12,
})

const formulaire = reactive({
  nom: '',
  categorie: '',
  statut: 'active',
  description: '',
  logo_url: '',
})

const titreFormulaire = computed(() => (modeEdition.value ? 'Modifier l equipe' : 'Nouvelle equipe'))
const logoAffiche = computed(() => logoPreview.value || formulaire.logo_url || '')
const nomLogoSelectionne = computed(() => logoFichier.value?.name || (formulaire.logo_url ? 'Logo actuel charge' : 'Aucun logo selectionne'))
const clubActuel = computed(() => clubs.value.find((club) => String(club.id) === String(selectedClubId.value)) || null)
const utilisateurLayout = computed(() => (utilisateurConnecte.value ? utilisateurConnecte.value : null))
const lireErreur = (champ) => erreursValidation.value?.[champ]?.[0] || ''

const gerer401 = (error) => {
  if (error?.response?.code === 401) {
    localStorage.removeItem('token_api')
    localStorage.removeItem('utilisateur_api')
    router.push('/login')
    return true
  }

  return false
}

const reinitialiserFormulaire = () => {
  formulaire.nom = ''
  formulaire.categorie = ''
  formulaire.statut = 'active'
  formulaire.description = ''
  formulaire.logo_url = ''
  logoFichier.value = null
  logoPreview.value = ''
  erreursValidation.value = {}
}

const chargerClubs = async () => {
  chargementClubs.value = true

  try {
    const reponse = await authGet('/president/clubs', { page: 1, per_page: 100 })
    clubs.value = reponse?.data?.clubs || []

    if (!selectedClubId.value && clubs.value.length) {
      selectedClubId.value = String(clubs.value[0].id)
    }
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les clubs.')
    }
  } finally {
    chargementClubs.value = false
  }
}

const chargerEquipes = async () => {
  if (!selectedClubId.value) {
    equipes.value = []
    pagination.value = null
    return
  }

  chargementEquipes.value = true
  erreurChargement.value = ''

  try {
    const reponse = await authGet(`/president/clubs/${selectedClubId.value}/equipes`, {
      q: filtres.q,
      statut: filtres.statut,
      page: filtres.page,
      per_page: filtres.per_page,
    })

    equipes.value = reponse?.data?.equipes || []
    pagination.value = reponse?.data?.pagination || null
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    erreurChargement.value = error?.response?.message || error.message || 'Impossible de charger les equipes.'
    notifyError(erreurChargement.value)
  } finally {
    chargementEquipes.value = false
  }
}

watch(selectedClubId, async () => {
  filtres.page = 1
  await chargerEquipes()
})

watch(
  () => filtres.statut,
  () => {
    filtres.page = 1
    chargerEquipes()
  }
)

watch(
  () => filtres.q,
  () => {
    if (searchDebounce.value) {
      clearTimeout(searchDebounce.value)
    }

    searchDebounce.value = setTimeout(() => {
      filtres.page = 1
      chargerEquipes()
    }, 350)
  }
)

const choisirLogo = (event) => {
  const fichier = event.target.files?.[0]

  if (!fichier) {
    return
  }

  logoFichier.value = fichier
  logoPreview.value = URL.createObjectURL(fichier)
}

const ouvrirCreation = () => {
  if (!selectedClubId.value) {
    notifyError('Choisissez d abord un club.')
    return
  }

  reinitialiserFormulaire()
  modeEdition.value = false
  equipeEditionId.value = null
  afficherFormulaire.value = true
  succes.value = ''
}

const ouvrirEdition = (equipe) => {
  selectedClubId.value = String(equipe.club_id)
  formulaire.nom = equipe.nom || ''
  formulaire.categorie = equipe.categorie || ''
  formulaire.statut = equipe.statut || 'active'
  formulaire.description = equipe.description || ''
  formulaire.logo_url = equipe.logo_url || ''
  logoFichier.value = null
  logoPreview.value = ''
  erreursValidation.value = {}

  modeEdition.value = true
  equipeEditionId.value = equipe.id
  afficherFormulaire.value = true
  succes.value = ''
}

const fermerFormulaire = () => {
  afficherFormulaire.value = false
  modeEdition.value = false
  equipeEditionId.value = null
  reinitialiserFormulaire()
}

const construireDonneesEquipe = () => {
  const donnees = new FormData()
  donnees.append('nom', formulaire.nom)
  donnees.append('categorie', formulaire.categorie || '')
  donnees.append('statut', formulaire.statut || 'active')
  donnees.append('description', formulaire.description || '')

  if (logoFichier.value) {
    donnees.append('logo', logoFichier.value)
  }

  return donnees
}

const enregistrerEquipe = async () => {
  if (!selectedClubId.value) {
    notifyError('Aucun club selectionne.')
    return
  }

  envoi.value = true
  erreursValidation.value = {}
  succes.value = ''

  try {
    const donnees = construireDonneesEquipe()
    const endpoint = modeEdition.value
      ? `/president/clubs/${selectedClubId.value}/equipes/${equipeEditionId.value}`
      : `/president/clubs/${selectedClubId.value}/equipes`

    const reponse = modeEdition.value ? await authPut(endpoint, donnees) : await authPost(endpoint, donnees)

    succes.value = reponse?.message || (modeEdition.value ? 'Equipe modifiee avec succes.' : 'Equipe creee avec succes.')
    notifySuccess(succes.value)
    fermerFormulaire()
    if (!modeEdition.value) {
      filtres.page = 1
    }
    await chargerEquipes()
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    const reponseErreur = error.response || {}
    erreursValidation.value = reponseErreur.data || {}
  } finally {
    envoi.value = false
  }
}

const supprimerEquipe = async (equipe) => {
  const ok = window.confirm(`Supprimer l equipe "${equipe.nom}" ?`)

  if (!ok) {
    return
  }

  succes.value = ''

  try {
    const reponse = await authDelete(`/president/clubs/${equipe.club_id}/equipes/${equipe.id}`)
    succes.value = reponse?.message || 'Equipe supprimee avec succes.'
    notifySuccess(succes.value)

    if (equipes.value.length === 1 && filtres.page > 1) {
      filtres.page -= 1
    }

    await chargerEquipes()
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de supprimer cette equipe.')
    }
  }
}

const onChangePage = (page) => {
  filtres.page = page
  chargerEquipes()
}

const onChangePerPage = (size) => {
  filtres.per_page = size
  filtres.page = 1
  chargerEquipes()
}

const deconnecter = () => {
  localStorage.removeItem('token_api')
  localStorage.removeItem('utilisateur_api')
  router.push('/login')
}

onMounted(async () => {
  const utilisateurStocke = localStorage.getItem('utilisateur_api')

  if (utilisateurStocke) {
    try {
      utilisateurConnecte.value = JSON.parse(utilisateurStocke)
    } catch {
      utilisateurConnecte.value = null
    }
  }

  await chargerClubs()
  await chargerEquipes()
})
</script>

<template>
  <PresidentShellLayout
    breadcrumb="Equipes"
    title="Gestion des equipes"
    active-section="equipes"
    :utilisateur="utilisateurLayout"
    @logout="deconnecter"
  >
    <AppCard title="Equipes du president" subtitle="Selectionnez un club puis gerez ses equipes.">
      <template #actions>
        <button type="button" class="ecs-btn-primary" @click="ouvrirCreation">+ Nouvelle equipe</button>
      </template>

      <div class="grid gap-3 lg:grid-cols-4">
        <label>
          <span class="text-xs font-bold text-[#64748b]">Club *</span>
          <select v-model="selectedClubId" class="ecs-select" :disabled="chargementClubs">
            <option value="">Choisir un club</option>
            <option v-for="club in clubs" :key="club.id" :value="String(club.id)">{{ club.nom }}</option>
          </select>
        </label>

        <label>
          <span class="text-xs font-bold text-[#64748b]">Statut</span>
          <select v-model="filtres.statut" class="ecs-select" :disabled="!selectedClubId">
            <option value="">Tous</option>
            <option value="active">active</option>
            <option value="inactive">inactive</option>
          </select>
        </label>

        <label class="lg:col-span-2">
          <span class="text-xs font-bold text-[#64748b]">Recherche serveur</span>
          <input v-model="filtres.q" type="text" class="ecs-input" placeholder="Nom, categorie, code invitation..." :disabled="!selectedClubId" />
        </label>
      </div>
    </AppCard>

    <div v-if="succes" class="ecs-note-success">{{ succes }}</div>

    <div
      v-if="afficherFormulaire"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4 backdrop-blur-[2px]"
      @click.self="fermerFormulaire"
    >
      <section class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-2xl border border-[#e7edf8] bg-white p-4 shadow-[0_24px_50px_rgba(15,23,42,0.22)] sm:p-5">
        <div class="flex items-center justify-between gap-3">
          <h3 class="text-base font-bold text-[#1f2a44]">{{ titreFormulaire }}</h3>
          <button class="ecs-btn-secondary text-xs" type="button" @click="fermerFormulaire">Fermer</button>
        </div>

        <form class="mt-4 grid gap-3" @submit.prevent="enregistrerEquipe">
          <label class="rounded-xl border border-dashed border-[#d8e2f1] bg-[#f8fbff] p-3 transition hover:border-[#2563eb] hover:bg-white">
            <span class="block text-xs font-bold text-[#64748b]">Logo de l equipe</span>
            <span class="mt-2 flex flex-wrap items-center justify-between gap-3">
              <span class="min-w-0 flex items-center gap-3">
                <span class="grid h-12 w-12 shrink-0 place-items-center overflow-hidden rounded-xl bg-[linear-gradient(130deg,#0f172a,#334155)] text-xs font-bold text-white">
                  <img v-if="logoAffiche" :src="logoAffiche" alt="Logo equipe" class="h-full w-full object-cover" />
                  <span>EQ</span>
                </span>
                <span class="min-w-0">
                  <span class="block truncate text-xs font-bold text-[#1f2a44]">{{ nomLogoSelectionne }}</span>
                  <span class="mt-0.5 block text-[11px] text-[#64748b]">JPG, JPEG, PNG ou WEBP. Max 2MB.</span>
                </span>
              </span>
              <span class="ecs-btn-secondary text-[11px]">Choisir logo</span>
            </span>
            <input type="file" accept="image/*" class="sr-only" @change="choisirLogo" />
          </label>
          <span v-if="lireErreur('logo')" class="-mt-1 text-xs text-[#e11d48]">{{ lireErreur('logo') }}</span>

          <div class="grid gap-3 md:grid-cols-2">
            <label>
              <span class="text-xs font-bold text-[#64748b]">Nom *</span>
              <input v-model="formulaire.nom" class="ecs-input" type="text" />
              <span v-if="lireErreur('nom')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('nom') }}</span>
            </label>
            <label>
              <span class="text-xs font-bold text-[#64748b]">Categorie</span>
              <input v-model="formulaire.categorie" class="ecs-input" type="text" />
              <span v-if="lireErreur('categorie')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('categorie') }}</span>
            </label>
          </div>

          <div class="grid gap-3 md:grid-cols-2">
            <label>
              <span class="text-xs font-bold text-[#64748b]">Statut</span>
              <select v-model="formulaire.statut" class="ecs-select">
                <option value="active">active</option>
                <option value="inactive">inactive</option>
              </select>
              <span v-if="lireErreur('statut')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('statut') }}</span>
            </label>
            <label class="flex items-end">
              <div class="w-full rounded-xl border border-[#e8edf5] bg-[#f8fbff] px-3 py-2.5">
                <span class="text-xs font-bold text-[#64748b]">Club selectionne</span>
                <p class="mt-1 text-sm font-semibold text-[#1f2a44]">{{ clubActuel?.nom || '-' }}</p>
              </div>
            </label>
          </div>

          <label>
            <span class="text-xs font-bold text-[#64748b]">Description</span>
            <textarea v-model="formulaire.description" rows="3" class="ecs-textarea"></textarea>
            <span v-if="lireErreur('description')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('description') }}</span>
          </label>

          <div class="flex justify-end">
            <button :disabled="envoi" class="ecs-btn-primary" type="submit">
              {{ envoi ? 'Enregistrement...' : modeEdition ? 'Mettre a jour' : 'Creer l equipe' }}
            </button>
          </div>
        </form>
      </section>
    </div>

    <AppCard class="mt-4" title="Liste des equipes">
      <AppListState
        :loading="chargementEquipes"
        :has-data="equipes.length > 0"
        :error-message="erreurChargement"
        :empty-title="selectedClubId ? 'Aucune equipe pour ce club.' : 'Choisissez un club pour afficher ses equipes.'"
        @retry="chargerEquipes"
      >
        <template #empty-action>
          <button v-if="selectedClubId" type="button" class="mt-4 ecs-btn-primary" @click="ouvrirCreation">Creer la premiere equipe</button>
        </template>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <article v-for="equipe in equipes" :key="equipe.id" class="rounded-2xl border border-[#e8edf5] bg-white p-4 transition hover:-translate-y-[1px] hover:shadow-[0_10px_24px_rgba(15,23,42,0.08)]">
            <div class="flex items-start gap-3">
              <span class="grid h-14 w-14 shrink-0 place-items-center overflow-hidden rounded-xl bg-[linear-gradient(130deg,#0f172a,#334155)] text-xs font-bold text-white">
                <img v-if="equipe.logo_url" :src="equipe.logo_url" :alt="equipe.nom" class="h-full w-full object-cover" />
                <span v-else>EQ</span>
              </span>
              <div class="min-w-0">
                <p class="truncate text-base font-bold text-[#1f2a44]">{{ equipe.nom }}</p>
                <p class="mt-1 text-xs text-[#64748b]">Categorie: {{ equipe.categorie || '-' }}</p>
                <p class="mt-1 text-xs capitalize text-[#475569]">Statut: {{ equipe.statut || '-' }}</p>
              </div>
            </div>

            <div class="mt-3 rounded-lg bg-[#f8fbff] px-3 py-2">
              <p class="text-[11px] font-bold uppercase text-[#64748b]">Code invitation</p>
              <p class="mt-1 font-mono text-sm font-bold text-[#1f2a44]">{{ equipe.code_invitation || '-' }}</p>
            </div>

            <p class="mt-3 line-clamp-2 text-xs leading-5 text-[#64748b]">{{ equipe.description || 'Aucune description.' }}</p>

            <div class="mt-4 flex items-center justify-between">
              <span class="text-xs text-[#64748b]">{{ equipe.coach?.name || 'Coach non assigne' }}</span>
              <div class="flex gap-2">
                <button class="ecs-btn-secondary !px-3 !py-1.5 !text-xs" type="button" @click="ouvrirEdition(equipe)">Modifier</button>
                <button class="ecs-btn-danger" type="button" @click="supprimerEquipe(equipe)">Supprimer</button>
              </div>
            </div>
          </article>
        </div>
      </AppListState>

      <AppPagination :pagination="pagination" @change-page="onChangePage" @change-per-page="onChangePerPage" />
    </AppCard>
  </PresidentShellLayout>
</template>
