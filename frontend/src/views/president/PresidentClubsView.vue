<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppCard from '../../components/common/AppCard.vue'
import AppListState from '../../components/common/AppListState.vue'
import AppPagination from '../../components/common/AppPagination.vue'
import PresidentShellLayout from '../../components/president/PresidentShellLayout.vue'
import { authDelete, authGet, authPost, authPut } from '../../services/api'
import { notifyError, notifySuccess } from '../../stores/toast'

const router = useRouter()

const chargement = ref(true)
const envoi = ref(false)
const clubs = ref([])
const pagination = ref(null)
const erreursValidation = ref({})
const succes = ref('')
const erreurChargement = ref('')
const afficherFormulaire = ref(false)
const modeEdition = ref(false)
const clubEditionId = ref(null)
const logoFichier = ref(null)
const logoPreview = ref('')
const utilisateurConnecte = ref(null)
const searchDebounce = ref(null)

const filtres = reactive({
  q: '',
  page: 1,
  per_page: 12,
})

const formulaire = reactive({
  nom: '',
  adresse: '',
  telephone: '',
  email: '',
  description: '',
  ville: '',
  pays: '',
  logo_url: '',
})

const titreFormulaire = computed(() => (modeEdition.value ? 'Modifier le club' : 'Nouveau club'))
const logoAffiche = computed(() => logoPreview.value || formulaire.logo_url || '')
const nomLogoSelectionne = computed(() => logoFichier.value?.name || (formulaire.logo_url ? 'Logo actuel charge' : 'Aucun logo selectionne'))
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
  formulaire.adresse = ''
  formulaire.telephone = ''
  formulaire.email = ''
  formulaire.description = ''
  formulaire.ville = ''
  formulaire.pays = ''
  formulaire.logo_url = ''
  logoFichier.value = null
  logoPreview.value = ''
  erreursValidation.value = {}
}

const chargerClubs = async () => {
  chargement.value = true
  erreurChargement.value = ''

  try {
    const reponse = await authGet('/president/clubs', {
      q: filtres.q,
      page: filtres.page,
      per_page: filtres.per_page,
    })

    clubs.value = reponse?.data?.clubs || []
    pagination.value = reponse?.data?.pagination || null
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    erreurChargement.value = error?.response?.message || error.message || 'Impossible de charger les clubs.'
    notifyError(erreurChargement.value)
  } finally {
    chargement.value = false
  }
}

const choisirLogo = (event) => {
  const fichier = event.target.files?.[0]

  if (!fichier) {
    return
  }

  logoFichier.value = fichier
  logoPreview.value = URL.createObjectURL(fichier)
}

const ouvrirCreation = () => {
  reinitialiserFormulaire()
  modeEdition.value = false
  clubEditionId.value = null
  afficherFormulaire.value = true
  succes.value = ''
}

const ouvrirEdition = (club) => {
  formulaire.nom = club.nom || ''
  formulaire.adresse = club.adresse || ''
  formulaire.telephone = club.telephone || ''
  formulaire.email = club.email || ''
  formulaire.description = club.description || ''
  formulaire.ville = club.ville || ''
  formulaire.pays = club.pays || ''
  formulaire.logo_url = club.logo_url || ''
  logoFichier.value = null
  logoPreview.value = ''
  erreursValidation.value = {}

  modeEdition.value = true
  clubEditionId.value = club.id
  afficherFormulaire.value = true
  succes.value = ''
}

const fermerFormulaire = () => {
  afficherFormulaire.value = false
  modeEdition.value = false
  clubEditionId.value = null
  reinitialiserFormulaire()
}

const construireDonneesClub = () => {
  const donnees = new FormData()
  donnees.append('nom', formulaire.nom)
  donnees.append('adresse', formulaire.adresse || '')
  donnees.append('telephone', formulaire.telephone || '')
  donnees.append('email', formulaire.email || '')
  donnees.append('description', formulaire.description || '')
  donnees.append('ville', formulaire.ville || '')
  donnees.append('pays', formulaire.pays || '')

  if (logoFichier.value) {
    donnees.append('logo', logoFichier.value)
  }

  return donnees
}

const enregistrerClub = async () => {
  envoi.value = true
  erreursValidation.value = {}
  succes.value = ''

  try {
    const donnees = construireDonneesClub()
    const endpoint = modeEdition.value ? `/president/clubs/${clubEditionId.value}` : '/president/clubs'
    const reponse = modeEdition.value ? await authPut(endpoint, donnees) : await authPost(endpoint, donnees)

    succes.value = reponse?.message || (modeEdition.value ? 'Club modifie avec succes.' : 'Club cree avec succes.')
    notifySuccess(succes.value)
    fermerFormulaire()

    if (!modeEdition.value) {
      filtres.page = 1
    }

    await chargerClubs()
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

const supprimerClub = async (club) => {
  const ok = window.confirm(`Supprimer le club "${club.nom}" ?`)

  if (!ok) {
    return
  }

  succes.value = ''

  try {
    const reponse = await authDelete(`/president/clubs/${club.id}`)
    succes.value = reponse?.message || 'Club supprime avec succes.'
    notifySuccess(succes.value)

    if (clubs.value.length === 1 && filtres.page > 1) {
      filtres.page -= 1
    }

    await chargerClubs()
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de supprimer ce club.')
    }
  }
}

const onChangePage = (page) => {
  filtres.page = page
  chargerClubs()
}

const onChangePerPage = (size) => {
  filtres.per_page = size
  filtres.page = 1
  chargerClubs()
}

watch(
  () => filtres.q,
  () => {
    if (searchDebounce.value) {
      clearTimeout(searchDebounce.value)
    }

    searchDebounce.value = setTimeout(() => {
      filtres.page = 1
      chargerClubs()
    }, 350)
  }
)

const deconnecter = () => {
  localStorage.removeItem('token_api')
  localStorage.removeItem('utilisateur_api')
  router.push('/login')
}

onMounted(() => {
  const utilisateurStocke = localStorage.getItem('utilisateur_api')

  if (utilisateurStocke) {
    try {
      utilisateurConnecte.value = JSON.parse(utilisateurStocke)
    } catch {
      utilisateurConnecte.value = null
    }
  }

  chargerClubs()
})
</script>

<template>
  <PresidentShellLayout
    breadcrumb="Clubs"
    title="Gestion des clubs"
    active-section="clubs"
    :utilisateur="utilisateurLayout"
    @logout="deconnecter"
  >
    <AppCard title="Clubs du president" subtitle="Creation, modification et suppression des clubs.">
      <template #actions>
        <button type="button" class="ecs-btn-primary" @click="ouvrirCreation">+ Nouveau club</button>
      </template>

      <div class="grid gap-3 md:grid-cols-[1fr_180px]">
        <label>
          <span class="text-xs font-bold text-[#64748b]">Recherche serveur</span>
          <input v-model="filtres.q" type="text" placeholder="Nom, ville, email..." class="ecs-input" />
        </label>

        <label>
          <span class="text-xs font-bold text-[#64748b]">Taille page</span>
          <select v-model.number="filtres.per_page" class="ecs-select" @change="onChangePerPage(filtres.per_page)">
            <option :value="6">6</option>
            <option :value="12">12</option>
            <option :value="24">24</option>
            <option :value="48">48</option>
          </select>
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

        <form class="mt-4 grid gap-3" @submit.prevent="enregistrerClub">
          <label class="rounded-xl border border-dashed border-[#d8e2f1] bg-[#f8fbff] p-3 transition hover:border-[#2563eb] hover:bg-white">
            <span class="block text-xs font-bold text-[#64748b]">Logo du club</span>
            <span class="mt-2 flex flex-wrap items-center justify-between gap-3">
              <span class="min-w-0 flex items-center gap-3">
                <span class="grid h-12 w-12 shrink-0 place-items-center overflow-hidden rounded-xl bg-[linear-gradient(130deg,#0f172a,#334155)] text-xs font-bold text-white">
                  <img v-if="logoAffiche" :src="logoAffiche" alt="Logo club" class="h-full w-full object-cover" />
                  <span v-else>CL</span>
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
              <span class="text-xs font-bold text-[#64748b]">Email</span>
              <input v-model="formulaire.email" class="ecs-input" type="email" />
              <span v-if="lireErreur('email')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('email') }}</span>
            </label>
          </div>

          <div class="grid gap-3 md:grid-cols-2">
            <label>
              <span class="text-xs font-bold text-[#64748b]">Telephone</span>
              <input v-model="formulaire.telephone" class="ecs-input" type="text" />
              <span v-if="lireErreur('telephone')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('telephone') }}</span>
            </label>
            <label>
              <span class="text-xs font-bold text-[#64748b]">Adresse</span>
              <input v-model="formulaire.adresse" class="ecs-input" type="text" />
              <span v-if="lireErreur('adresse')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('adresse') }}</span>
            </label>
          </div>

          <div class="grid gap-3 md:grid-cols-2">
            <label>
              <span class="text-xs font-bold text-[#64748b]">Ville</span>
              <input v-model="formulaire.ville" class="ecs-input" type="text" />
              <span v-if="lireErreur('ville')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('ville') }}</span>
            </label>
            <label>
              <span class="text-xs font-bold text-[#64748b]">Pays</span>
              <input v-model="formulaire.pays" class="ecs-input" type="text" />
              <span v-if="lireErreur('pays')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('pays') }}</span>
            </label>
          </div>

          <label>
            <span class="text-xs font-bold text-[#64748b]">Description</span>
            <textarea v-model="formulaire.description" rows="3" class="ecs-textarea"></textarea>
            <span v-if="lireErreur('description')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('description') }}</span>
          </label>

          <div class="flex justify-end">
            <button :disabled="envoi" class="ecs-btn-primary" type="submit">
              {{ envoi ? 'Enregistrement...' : modeEdition ? 'Mettre a jour' : 'Creer le club' }}
            </button>
          </div>
        </form>
      </section>
    </div>

    <AppCard class="mt-4" title="Liste des clubs">
      <AppListState
        :loading="chargement"
        :has-data="clubs.length > 0"
        :error-message="erreurChargement"
        empty-title="Aucun club pour le moment."
        empty-description="Creez votre premier club pour commencer."
        @retry="chargerClubs"
      >
        <template #empty-action>
          <button type="button" class="mt-4 ecs-btn-primary" @click="ouvrirCreation">Creer le premier club</button>
        </template>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <article v-for="club in clubs" :key="club.id" class="rounded-2xl border border-[#e8edf5] bg-white p-4 transition hover:-translate-y-[1px] hover:shadow-[0_10px_24px_rgba(15,23,42,0.08)]">
            <div class="flex items-start gap-3">
              <span class="grid h-14 w-14 shrink-0 place-items-center overflow-hidden rounded-xl bg-[linear-gradient(130deg,#0f172a,#334155)] text-xs font-bold text-white">
                <img v-if="club.logo_url" :src="club.logo_url" :alt="club.nom" class="h-full w-full object-cover" />
                <span v-else>CL</span>
              </span>
              <div class="min-w-0">
                <p class="truncate text-base font-bold text-[#1f2a44]">{{ club.nom }}</p>
                <p class="mt-1 text-xs text-[#64748b]">{{ club.ville || '-' }}<span v-if="club.pays">, {{ club.pays }}</span></p>
                <p class="mt-1 truncate text-xs text-[#475569]">{{ club.email || 'email non renseigne' }}</p>
              </div>
            </div>

            <p class="mt-3 line-clamp-2 text-xs leading-5 text-[#64748b]">{{ club.description || 'Aucune description.' }}</p>

            <div class="mt-4 flex items-center justify-between">
              <span class="text-xs text-[#64748b]">{{ club.telephone || '-' }}</span>
              <div class="flex gap-2">
                <button class="ecs-btn-secondary !px-3 !py-1.5 !text-xs" type="button" @click="ouvrirEdition(club)">Modifier</button>
                <button class="ecs-btn-danger" type="button" @click="supprimerClub(club)">Supprimer</button>
              </div>
            </div>
          </article>
        </div>
      </AppListState>

      <AppPagination :pagination="pagination" @change-page="onChangePage" @change-per-page="onChangePerPage" />
    </AppCard>
  </PresidentShellLayout>
</template>
