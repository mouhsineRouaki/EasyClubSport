<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AppCard from '@/shared/components/AppCard.vue'
import AppListState from '@/shared/components/AppListState.vue'
import AppPagination from '@/shared/components/AppPagination.vue'
import AppButton from '@/shared/components/ui/AppButton.vue'
import AppStatusMessage from '@/shared/components/ui/AppStatusMessage.vue'
import AppModalShell from '@/shared/components/ui/AppModalShell.vue'
import PresidentClubForm from '@/roles/president/clubs/components/PresidentClubForm.vue'
import PresidentClubListCard from '@/roles/president/clubs/components/PresidentClubListCard.vue'
import PresidentShellLayout from '@/roles/president/shared/components/PresidentShellLayout.vue'
import { authDelete, authGet, authPost, authPut } from '@/shared/services/apiClient'
import { notifyError, notifySuccess } from '@/shared/services/toastService'

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
const totalVilles = computed(() => new Set(clubs.value.map((club) => club.ville).filter(Boolean)).size)
const clubsAvecEmail = computed(() => clubs.value.filter((club) => club.email).length)
const clubsAvecLogo = computed(() => clubs.value.filter((club) => club.logo_url || club.logo).length)
const resumeClubs = computed(() => [
  {
    label: 'Clubs visibles',
    value: pagination.value?.total || clubs.value.length || 0,
    helper: 'Tous les clubs geres dans votre espace.',
  },
  {
    label: 'Villes actives',
    value: totalVilles.value,
    helper: 'Zones deja couvertes par vos clubs.',
  },
  {
    label: 'Emails renseignes',
    value: clubsAvecEmail.value,
    helper: 'Clubs joignables rapidement par email.',
  },
  {
    label: 'Logos charges',
    value: clubsAvecLogo.value,
    helper: 'Cartes avec identite visuelle complete.',
  },
])

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

const mettreAJourChamp = (champ, valeur) => {
  formulaire[champ] = valeur
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
    <section class="grid gap-4 xl:grid-cols-[1.4fr_1fr]">
      <div class="overflow-hidden rounded-[30px] border border-white/60 bg-[linear-gradient(135deg,#0f172a_0%,#183b8c_42%,#2563eb_100%)] p-6 text-white shadow-[0_24px_60px_rgba(15,23,42,0.18)]">
        <p class="text-xs font-black uppercase tracking-[0.24em] text-white/70">Espace president</p>
        <h2 class="mt-3 max-w-xl text-4xl font-black leading-tight sm:text-5xl">
          Une vue plus claire pour gerer tous vos clubs.
        </h2>
        <p class="mt-3 max-w-2xl text-sm font-semibold leading-6 text-white/78">
          Creez, modifiez et suivez vos clubs depuis une seule page avec une recherche simple et des cartes plus visuelles.
        </p>

        <div class="mt-6 flex flex-wrap gap-3">
          <AppButton type="button" size="lg" @click="ouvrirCreation">+ Nouveau club</AppButton>
          <div class="rounded-full border border-white/18 bg-white/10 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-white/82 backdrop-blur-md">
            {{ pagination?.total || clubs.length || 0 }} clubs geres
          </div>
        </div>
      </div>

      <div class="grid gap-3 sm:grid-cols-2">
        <article
          v-for="item in resumeClubs"
          :key="item.label"
          class="rounded-[24px] border border-[#dfe7f5] bg-white p-4 shadow-[0_16px_34px_rgba(15,23,42,0.06)]"
        >
          <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#4c6fff]">{{ item.label }}</p>
          <p class="mt-3 text-3xl font-black text-[#0f172a]">{{ item.value }}</p>
          <p class="mt-2 text-xs font-semibold leading-5 text-[#64748b]">{{ item.helper }}</p>
        </article>
      </div>
    </section>

    <AppCard class="mt-4" title="Explorer les clubs" subtitle="Utilisez les filtres ci-dessous pour retrouver rapidement un club.">
      <template #actions>
        <AppButton type="button" variant="secondary" @click="ouvrirCreation">Creer un club</AppButton>
      </template>

      <div class="grid gap-3 xl:grid-cols-[1.2fr_220px_180px]">
        <label class="rounded-[22px] border border-[#e6edf8] bg-[#f8fbff] p-3">
          <span class="text-xs font-bold text-[#64748b]">Recherche serveur</span>
          <input v-model="filtres.q" type="text" placeholder="Nom, ville, email..." class="ecs-input mt-2" />
        </label>

        <label class="rounded-[22px] border border-[#e6edf8] bg-[#f8fbff] p-3">
          <span class="text-xs font-bold text-[#64748b]">Taille page</span>
          <select v-model.number="filtres.per_page" class="ecs-select mt-2" @change="onChangePerPage(filtres.per_page)">
            <option :value="6">6</option>
            <option :value="12">12</option>
            <option :value="24">24</option>
            <option :value="48">48</option>
          </select>
        </label>

        <div class="rounded-[22px] border border-[#e6edf8] bg-[#f8fbff] p-3">
          <p class="text-xs font-bold text-[#64748b]">Resultat actuel</p>
          <p class="mt-2 text-2xl font-black text-[#0f172a]">{{ clubs.length }}</p>
          <p class="mt-1 text-xs font-semibold text-[#64748b]">club(s) sur cette page</p>
        </div>
      </div>
    </AppCard>

    <AppStatusMessage v-if="succes">{{ succes }}</AppStatusMessage>

    <AppModalShell v-if="afficherFormulaire" :title="titreFormulaire" max-width-class="max-w-4xl" @close="fermerFormulaire">
      <PresidentClubForm
        :model-value="formulaire"
        :errors="erreursValidation"
        :logo-preview="logoPreview"
        :current-logo-url="logoAffiche"
        :logo-file-name="nomLogoSelectionne"
        :loading="envoi"
        :submit-label="modeEdition ? 'Mettre a jour' : 'Creer le club'"
        @submit="enregistrerClub"
        @update-field="mettreAJourChamp"
        @choose-logo="choisirLogo"
      />
    </AppModalShell>

    <AppCard class="mt-4" title="Liste des clubs" subtitle="Chaque card reprend les informations principales du club avec des actions rapides.">
      <AppListState
        :loading="chargement"
        :has-data="clubs.length > 0"
        :error-message="erreurChargement"
        empty-title="Aucun club pour le moment."
        empty-description="Creez votre premier club pour commencer."
        @retry="chargerClubs"
      >
        <template #empty-action>
          <AppButton type="button" class="mt-4" @click="ouvrirCreation">Creer le premier club</AppButton>
        </template>

        <div class="grid gap-5 md:grid-cols-2 2xl:grid-cols-3">
          <PresidentClubListCard
            v-for="club in clubs"
            :key="club.id"
            :club="club"
            @edit="ouvrirEdition"
            @delete="supprimerClub"
          />
        </div>
      </AppListState>

      <AppPagination :pagination="pagination" @change-page="onChangePage" @change-per-page="onChangePerPage" />
    </AppCard>
  </PresidentShellLayout>
</template>
