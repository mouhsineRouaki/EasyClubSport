<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppCard from '@/shared/components/AppCard.vue'
import AppListState from '@/shared/components/AppListState.vue'
import AppPagination from '@/shared/components/AppPagination.vue'
import AppButton from '@/shared/components/ui/AppButton.vue'
import AppStatusMessage from '@/shared/components/ui/AppStatusMessage.vue'
import AppModalShell from '@/shared/components/ui/AppModalShell.vue'
import PresidentPlayerAssignForm from '@/roles/president/joueurs/components/PresidentPlayerAssignForm.vue'
import PresidentPlayerListCard from '@/roles/president/joueurs/components/PresidentPlayerListCard.vue'
import PresidentShellLayout from '@/roles/president/shared/components/PresidentShellLayout.vue'
import { authDelete, authGet, authPost } from '@/shared/services/apiClient'
import { notifyError, notifySuccess } from '@/shared/services/toastService'

const router = useRouter()

const chargementClubs = ref(true)
const chargementEquipes = ref(false)
const chargementJoueurs = ref(false)
const chargementJoueursDisponibles = ref(false)
const envoi = ref(false)
const clubs = ref([])
const equipes = ref([])
const joueurs = ref([])
const joueursDisponibles = ref([])
const pagination = ref(null)
const paginationJoueursDisponibles = ref(null)
const selectedClubId = ref('')
const selectedEquipeId = ref('')
const utilisateurConnecte = ref(null)
const searchDebounce = ref(null)
const searchAjoutDebounce = ref(null)
const erreurChargement = ref('')
const succes = ref('')
const afficherFormulaire = ref(false)
const erreursValidation = ref({})

const filtres = reactive({
  q: '',
  page: 1,
  per_page: 12,
})

const formulaireAjout = reactive({
  utilisateur_id: '',
})

const filtresAjout = reactive({
  q: '',
  page: 1,
  per_page: 6,
})

const clubActuel = computed(() => clubs.value.find((club) => String(club.id) === String(selectedClubId.value)) || null)
const equipeActuelle = computed(() => equipes.value.find((equipe) => String(equipe.id) === String(selectedEquipeId.value)) || null)
const utilisateurLayout = computed(() => (utilisateurConnecte.value ? utilisateurConnecte.value : null))
const titreListe = computed(() => (selectedEquipeId.value ? 'Liste des joueurs' : 'Selectionnez une equipe'))
const lireErreur = (champ) => erreursValidation.value?.[champ]?.[0] || ''

const mettreAJourChamp = (champ, valeur) => {
  formulaireAjout[champ] = valeur
}

const gerer401 = (error) => {
  if (error?.response?.code === 401) {
    localStorage.removeItem('token_api')
    localStorage.removeItem('utilisateur_api')
    router.push('/login')
    return true
  }

  return false
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
    selectedEquipeId.value = ''
    return
  }

  chargementEquipes.value = true

  try {
    const reponse = await authGet(`/president/clubs/${selectedClubId.value}/equipes`, { page: 1, per_page: 100 })
    equipes.value = reponse?.data?.equipes || []

    if (equipes.value.length) {
      const equipeExiste = equipes.value.some((equipe) => String(equipe.id) === String(selectedEquipeId.value))
      if (!equipeExiste) {
        selectedEquipeId.value = String(equipes.value[0].id)
      }
    } else {
      selectedEquipeId.value = ''
    }
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les equipes.')
    }
  } finally {
    chargementEquipes.value = false
  }
}

const chargerJoueurs = async () => {
  if (!selectedClubId.value || !selectedEquipeId.value) {
    joueurs.value = []
    pagination.value = null
    return
  }

  chargementJoueurs.value = true
  erreurChargement.value = ''

  try {
    const reponse = await authGet(`/president/clubs/${selectedClubId.value}/equipes/${selectedEquipeId.value}/joueurs`, {
      q: filtres.q,
      page: filtres.page,
      per_page: filtres.per_page,
    })

    joueurs.value = reponse?.data?.joueurs || []
    pagination.value = reponse?.data?.pagination || null
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    erreurChargement.value = error?.response?.message || error.message || 'Impossible de charger les joueurs.'
    notifyError(erreurChargement.value)
  } finally {
    chargementJoueurs.value = false
  }
}

watch(selectedClubId, async () => {
  filtres.page = 1
  await chargerEquipes()
  await chargerJoueurs()
})

watch(selectedEquipeId, async () => {
  filtres.page = 1
  await chargerJoueurs()
})

watch(
  () => filtres.q,
  () => {
    if (searchDebounce.value) {
      clearTimeout(searchDebounce.value)
    }

    searchDebounce.value = setTimeout(() => {
      filtres.page = 1
      chargerJoueurs()
    }, 350)
  }
)

const ouvrirFormulaire = () => {
  if (!selectedClubId.value || !selectedEquipeId.value) {
    notifyError('Selectionnez d abord un club et une equipe.')
    return
  }

  formulaireAjout.utilisateur_id = ''
  filtresAjout.q = ''
  filtresAjout.page = 1
  filtresAjout.per_page = 6
  joueursDisponibles.value = []
  paginationJoueursDisponibles.value = null
  erreursValidation.value = {}
  afficherFormulaire.value = true
  chargerJoueursDisponibles()
}

const fermerFormulaire = () => {
  afficherFormulaire.value = false
  formulaireAjout.utilisateur_id = ''
  filtresAjout.q = ''
  filtresAjout.page = 1
  erreursValidation.value = {}
}

const chargerJoueursDisponibles = async () => {
  if (!selectedClubId.value || !selectedEquipeId.value || !afficherFormulaire.value) {
    joueursDisponibles.value = []
    paginationJoueursDisponibles.value = null
    return
  }

  chargementJoueursDisponibles.value = true

  try {
    const reponse = await authGet(`/president/clubs/${selectedClubId.value}/equipes/${selectedEquipeId.value}/joueurs-disponibles`, {
      q: filtresAjout.q,
      page: filtresAjout.page,
      per_page: filtresAjout.per_page,
    })

    joueursDisponibles.value = reponse?.data?.joueurs || []
    paginationJoueursDisponibles.value = reponse?.data?.pagination || null
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les joueurs disponibles.')
    }
  } finally {
    chargementJoueursDisponibles.value = false
  }
}

watch(
  () => filtresAjout.q,
  () => {
    if (!afficherFormulaire.value) {
      return
    }

    if (searchAjoutDebounce.value) {
      clearTimeout(searchAjoutDebounce.value)
    }

    searchAjoutDebounce.value = setTimeout(() => {
      filtresAjout.page = 1
      chargerJoueursDisponibles()
    }, 350)
  }
)

const ajouterJoueur = async () => {
  if (!selectedClubId.value || !selectedEquipeId.value) {
    notifyError('Selection de club/equipe invalide.')
    return
  }

  if (!formulaireAjout.utilisateur_id) {
    erreursValidation.value = {
      utilisateur_id: ['Selectionnez un joueur dans la liste.'],
    }
    return
  }

  envoi.value = true
  erreursValidation.value = {}

  try {
    const reponse = await authPost(`/president/clubs/${selectedClubId.value}/equipes/${selectedEquipeId.value}/joueurs`, {
      utilisateur_id: Number(formulaireAjout.utilisateur_id),
    })

    succes.value = reponse?.message || 'Joueur ajoute a l equipe.'
    notifySuccess(succes.value)
    fermerFormulaire()
    filtres.page = 1
    await chargerJoueurs()
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

const retirerJoueur = async (joueur) => {
  if (!selectedClubId.value || !selectedEquipeId.value) {
    notifyError('Selection de club/equipe invalide.')
    return
  }

  const ok = window.confirm(`Retirer ${joueur.prenom || ''} ${joueur.nom || ''} de l equipe ?`)
  if (!ok) {
    return
  }

  try {
    const reponse = await authDelete(`/president/clubs/${selectedClubId.value}/equipes/${selectedEquipeId.value}/joueurs/${joueur.id}`)
    succes.value = reponse?.message || 'Joueur retire de l equipe.'
    notifySuccess(succes.value)

    if (joueurs.value.length === 1 && filtres.page > 1) {
      filtres.page -= 1
    }

    await chargerJoueurs()
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de retirer ce joueur.')
    }
  }
}

const onChangePage = (page) => {
  filtres.page = page
  chargerJoueurs()
}

const onChangePerPage = (size) => {
  filtres.per_page = size
  filtres.page = 1
  chargerJoueurs()
}

const onChangePageAjout = (page) => {
  filtresAjout.page = page
  chargerJoueursDisponibles()
}

const onChangePerPageAjout = (size) => {
  filtresAjout.per_page = size
  filtresAjout.page = 1
  chargerJoueursDisponibles()
}

const mettreAJourRechercheAjout = (valeur) => {
  filtresAjout.q = valeur
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
  await chargerJoueurs()
})
</script>

<template>
  <PresidentShellLayout
    breadcrumb="Joueurs"
    title="Gestion des joueurs"
    active-section="joueurs"
    :utilisateur="utilisateurLayout"
    @logout="deconnecter"
  >
    <AppCard title="Joueurs par equipe" subtitle="Selectionnez un club puis une equipe pour gerer les joueurs.">
      <template #actions>
        <AppButton type="button" @click="ouvrirFormulaire">+ Ajouter joueur</AppButton>
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
          <span class="text-xs font-bold text-[#64748b]">Equipe *</span>
          <select v-model="selectedEquipeId" class="ecs-select" :disabled="chargementEquipes || !selectedClubId">
            <option value="">Choisir une equipe</option>
            <option v-for="equipe in equipes" :key="equipe.id" :value="String(equipe.id)">{{ equipe.nom }}</option>
          </select>
        </label>

        <label class="lg:col-span-2">
          <span class="text-xs font-bold text-[#64748b]">Recherche serveur</span>
          <input
            v-model="filtres.q"
            type="text"
            class="ecs-input"
            placeholder="Nom, prenom, email, telephone..."
            :disabled="!selectedEquipeId"
          />
        </label>
      </div>

      <p class="mt-3 text-xs text-[#64748b]">
        Club:
        <span class="font-semibold text-[#1f2a44]">{{ clubActuel?.nom || '-' }}</span>
        -
        Equipe:
        <span class="font-semibold text-[#1f2a44]">{{ equipeActuelle?.nom || '-' }}</span>
      </p>
    </AppCard>

    <AppStatusMessage v-if="succes">{{ succes }}</AppStatusMessage>

    <AppModalShell v-if="afficherFormulaire" title="Ajouter un joueur a l equipe" max-width-class="max-w-xl" @close="fermerFormulaire">
      <PresidentPlayerAssignForm
        :model-value="formulaireAjout"
        :errors="erreursValidation"
        :loading="envoi"
        :players="joueursDisponibles"
        :players-loading="chargementJoueursDisponibles"
        :players-pagination="paginationJoueursDisponibles"
        :search="filtresAjout.q"
        @submit="ajouterJoueur"
        @update-field="mettreAJourChamp"
        @update-search="mettreAJourRechercheAjout"
        @change-page="onChangePageAjout"
        @change-per-page="onChangePerPageAjout"
      />
    </AppModalShell>

    <AppCard class="mt-4" :title="titreListe">
      <AppListState
        :loading="chargementJoueurs"
        :has-data="joueurs.length > 0"
        :error-message="erreurChargement"
        :empty-title="selectedEquipeId ? 'Aucun joueur dans cette equipe.' : 'Choisissez une equipe pour afficher ses joueurs.'"
        empty-description="Ajoutez des joueurs pour commencer a construire l effectif."
        @retry="chargerJoueurs"
      >
        <template #empty-action>
          <AppButton v-if="selectedEquipeId" type="button" class="mt-4" @click="ouvrirFormulaire">Ajouter le premier joueur</AppButton>
        </template>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <PresidentPlayerListCard
            v-for="joueur in joueurs"
            :key="joueur.id"
            :joueur="joueur"
            @remove="retirerJoueur"
          />
        </div>
      </AppListState>

      <AppPagination :pagination="pagination" @change-page="onChangePage" @change-per-page="onChangePerPage" />
    </AppCard>
  </PresidentShellLayout>
</template>
