<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppCard from '@/shared/components/AppCard.vue'
import AppListState from '@/shared/components/AppListState.vue'
import AppPagination from '@/shared/components/AppPagination.vue'
import PresidentShellLayout from '@/roles/president/shared/components/PresidentShellLayout.vue'
import { authDelete, authGet, authPost } from '@/shared/services/apiClient'
import { notifyError, notifySuccess } from '@/shared/services/toastService'

const router = useRouter()

const chargementClubs = ref(true)
const chargementEquipes = ref(false)
const chargementJoueurs = ref(false)
const envoi = ref(false)
const clubs = ref([])
const equipes = ref([])
const joueurs = ref([])
const pagination = ref(null)
const selectedClubId = ref('')
const selectedEquipeId = ref('')
const utilisateurConnecte = ref(null)
const searchDebounce = ref(null)
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

const clubActuel = computed(() => clubs.value.find((club) => String(club.id) === String(selectedClubId.value)) || null)
const equipeActuelle = computed(() => equipes.value.find((equipe) => String(equipe.id) === String(selectedEquipeId.value)) || null)
const utilisateurLayout = computed(() => (utilisateurConnecte.value ? utilisateurConnecte.value : null))
const titreListe = computed(() => (selectedEquipeId.value ? 'Liste des joueurs' : 'Selectionnez une equipe'))
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
  erreursValidation.value = {}
  afficherFormulaire.value = true
}

const fermerFormulaire = () => {
  afficherFormulaire.value = false
  formulaireAjout.utilisateur_id = ''
  erreursValidation.value = {}
}

const ajouterJoueur = async () => {
  if (!selectedClubId.value || !selectedEquipeId.value) {
    notifyError('Selection de club/equipe invalide.')
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
        <button type="button" class="ecs-btn-primary" @click="ouvrirFormulaire">+ Ajouter joueur</button>
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

    <div v-if="succes" class="ecs-note-success">{{ succes }}</div>

    <div
      v-if="afficherFormulaire"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4 backdrop-blur-[2px]"
      @click.self="fermerFormulaire"
    >
      <section class="w-full max-w-xl rounded-2xl border border-[#e7edf8] bg-white p-4 shadow-[0_24px_50px_rgba(15,23,42,0.22)] sm:p-5">
        <div class="flex items-center justify-between gap-3">
          <h3 class="text-base font-bold text-[#1f2a44]">Ajouter un joueur a l equipe</h3>
          <button class="ecs-btn-secondary text-xs" type="button" @click="fermerFormulaire">Fermer</button>
        </div>

        <form class="mt-4 grid gap-3" @submit.prevent="ajouterJoueur">
          <label>
            <span class="text-xs font-bold text-[#64748b]">Utilisateur ID *</span>
            <input
              v-model="formulaireAjout.utilisateur_id"
              type="number"
              min="1"
              placeholder="Ex: 17"
              class="ecs-input"
            />
            <span v-if="lireErreur('utilisateur_id')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('utilisateur_id') }}</span>
          </label>

          <div class="flex justify-end">
            <button :disabled="envoi" class="ecs-btn-primary" type="submit">
              {{ envoi ? 'Ajout...' : 'Ajouter joueur' }}
            </button>
          </div>
        </form>
      </section>
    </div>

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
          <button v-if="selectedEquipeId" type="button" class="mt-4 ecs-btn-primary" @click="ouvrirFormulaire">Ajouter le premier joueur</button>
        </template>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <article v-for="joueur in joueurs" :key="joueur.id" class="rounded-2xl border border-[#e8edf5] bg-white p-4 transition hover:-translate-y-[1px] hover:shadow-[0_10px_24px_rgba(15,23,42,0.08)]">
            <div class="flex items-start gap-3">
              <span class="grid h-14 w-14 shrink-0 place-items-center overflow-hidden rounded-xl bg-[linear-gradient(130deg,#0f172a,#334155)] text-xs font-bold text-white">
                <img v-if="joueur.photo_url" :src="joueur.photo_url" :alt="joueur.name || joueur.nom" class="h-full w-full object-cover" />
                <span v-else>JR</span>
              </span>
              <div class="min-w-0">
                <p class="truncate text-base font-bold text-[#1f2a44]">{{ `${joueur.prenom || ''} ${joueur.nom || ''}`.trim() || joueur.name || 'Joueur' }}</p>
                <p class="mt-1 truncate text-xs text-[#64748b]">{{ joueur.email || 'email non renseigne' }}</p>
                <p class="mt-1 text-xs text-[#475569]">{{ joueur.telephone || '-' }}</p>
              </div>
            </div>

            <div class="mt-3 rounded-lg bg-[#f8fbff] px-3 py-2">
              <p class="text-[11px] font-bold uppercase text-[#64748b]">Date d affectation</p>
              <p class="mt-1 text-sm font-semibold text-[#1f2a44]">{{ joueur.date_affectation || '-' }}</p>
            </div>

            <div class="mt-4 flex items-center justify-end">
              <button class="ecs-btn-danger" type="button" @click="retirerJoueur(joueur)">Retirer</button>
            </div>
          </article>
        </div>
      </AppListState>

      <AppPagination :pagination="pagination" @change-page="onChangePage" @change-per-page="onChangePerPage" />
    </AppCard>
  </PresidentShellLayout>
</template>