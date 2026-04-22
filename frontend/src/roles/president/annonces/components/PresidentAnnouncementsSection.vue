<script setup>
import { onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import PresidentAnnouncementCard from './PresidentAnnouncementCard.vue'
import PresidentAnnouncementForm from './PresidentAnnouncementForm.vue'
import { authDelete, authGet, authPost, authPut } from '@/shared/services/apiClient'
import { notifyError, notifySuccess } from '@/shared/services/toastService'

const props = defineProps({
  searchTerm: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['update:searchTerm'])
const router = useRouter()

const chargementClubs = ref(false)
const chargementAnnonces = ref(false)
const envoiAnnonce = ref(false)
const imageAnnonceFichier = ref(null)
const imageAnnoncePreview = ref('')
const clubs = ref([])
const annonces = ref([])
const pagination = ref(null)
const erreurChargement = ref('')
const selectedClubId = ref('')
const annonceSelectionnee = ref(null)
const mode = ref('liste')
const erreursValidation = ref({})
const debounceRecherche = ref(null)

const formulaire = reactive({
  club_id: '',
  titre: '',
  contenu: '',
  image: '',
  est_active: true,
})

const formatDate = (date) => {
  if (!date) {
    return '-'
  }

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(date))
}

const clubFormulaire = () => {
  return clubs.value.find((club) => String(club.id) === String(formulaire.club_id)) || null
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

const reinitialiserFormulaire = () => {
  formulaire.club_id = selectedClubId.value || (clubs.value[0] ? String(clubs.value[0].id) : '')
  formulaire.titre = ''
  formulaire.contenu = ''
  formulaire.image = ''
  formulaire.est_active = true
  imageAnnonceFichier.value = null
  imageAnnoncePreview.value = ''
  erreursValidation.value = {}
}

const remplirFormulaire = (annonce) => {
  formulaire.club_id = String(annonce.club_id || annonce.club?.id || selectedClubId.value || '')
  formulaire.titre = annonce.titre || ''
  formulaire.contenu = annonce.contenu || ''
  formulaire.image = annonce.image || ''
  formulaire.est_active = Boolean(annonce.est_active)
  imageAnnonceFichier.value = null
  imageAnnoncePreview.value = ''
  erreursValidation.value = {}
}

const mettreAJourChamp = (champ, valeur) => {
  formulaire[champ] = valeur
}

const choisirImage = (event) => {
  const fichier = event?.target?.files?.[0]

  if (!fichier) {
    return
  }

  imageAnnonceFichier.value = fichier
  imageAnnoncePreview.value = URL.createObjectURL(fichier)
}

const construireDonneesAnnonce = () => {
  const donnees = new FormData()
  donnees.append('club_id', formulaire.club_id || '')
  donnees.append('titre', formulaire.titre || '')
  donnees.append('contenu', formulaire.contenu || '')
  donnees.append('est_active', formulaire.est_active ? '1' : '0')

  if (imageAnnonceFichier.value) {
    donnees.append('image', imageAnnonceFichier.value)
  }

  return donnees
}

const chargerClubs = async () => {
  chargementClubs.value = true

  try {
    const reponse = await authGet('/president/clubs', {
      page: 1,
      per_page: 50,
    })

    clubs.value = reponse?.data?.clubs || []

    if (!selectedClubId.value && clubs.value.length) {
      selectedClubId.value = String(clubs.value[0].id)
    }

    if (!formulaire.club_id && clubs.value.length) {
      formulaire.club_id = String(clubs.value[0].id)
    }
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    notifyError(error?.response?.message || error.message || 'Impossible de charger les clubs pour les annonces.')
  } finally {
    chargementClubs.value = false
  }
}

const chargerAnnonceDetail = async (id) => {
  try {
    const reponse = await authGet(`/president/annonces/${id}`)
    annonceSelectionnee.value = reponse?.data?.annonce || annonceSelectionnee.value
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    notifyError(error?.response?.message || error.message || 'Impossible de charger le detail de cette annonce.')
  }
}

const chargerAnnonces = async (page = 1) => {
  chargementAnnonces.value = true
  erreurChargement.value = ''

  try {
    const endpoint = selectedClubId.value ? `/president/clubs/${selectedClubId.value}/annonces` : '/president/annonces'
    const reponse = await authGet(endpoint, {
      q: props.searchTerm,
      page,
      per_page: 8,
    })

    annonces.value = reponse?.data?.annonces || []
    pagination.value = reponse?.data?.pagination || null

    if (annonceSelectionnee.value) {
      const annonceFraiche = annonces.value.find((annonce) => annonce.id === annonceSelectionnee.value.id)
      annonceSelectionnee.value = annonceFraiche || annonceSelectionnee.value
    }
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    erreurChargement.value = error?.response?.message || error.message || 'Impossible de charger les annonces.'
    notifyError(erreurChargement.value)
  } finally {
    chargementAnnonces.value = false
  }
}

const refreshCurrent = async (page = pagination.value?.current_page || 1) => {
  await chargerAnnonces(page)

  if (annonceSelectionnee.value?.id) {
    await chargerAnnonceDetail(annonceSelectionnee.value.id)
  }
}

const ouvrirCreation = () => {
  reinitialiserFormulaire()
  mode.value = 'creation'
}

const selectionnerAnnonce = (annonce) => {
  annonceSelectionnee.value = annonce
  mode.value = 'detail'
}

const retourListe = () => {
  mode.value = 'liste'
}

const ouvrirEdition = () => {
  if (!annonceSelectionnee.value) {
    return
  }

  remplirFormulaire(annonceSelectionnee.value)
  mode.value = 'edition'
}

const enregistrerAnnonce = async () => {
  envoiAnnonce.value = true
  erreursValidation.value = {}

  try {
    if (mode.value === 'edition' && annonceSelectionnee.value) {
      const reponse = await authPut(`/president/annonces/${annonceSelectionnee.value.id}`, construireDonneesAnnonce())

      annonceSelectionnee.value = reponse?.data?.annonce || annonceSelectionnee.value
      notifySuccess(reponse?.message || 'Annonce mise a jour avec succes.')
      mode.value = 'detail'
      await chargerAnnonces(pagination.value?.current_page || 1)
      return
    }

    if (!formulaire.club_id) {
      notifyError('Choisissez un club avant de creer une annonce.')
      return
    }

    const reponse = await authPost(`/president/clubs/${formulaire.club_id}/annonces`, construireDonneesAnnonce())

    annonceSelectionnee.value = reponse?.data?.annonce || null
    notifySuccess(reponse?.message || 'Annonce creee avec succes.')
    mode.value = annonceSelectionnee.value ? 'detail' : 'liste'
    await chargerAnnonces(1)
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    erreursValidation.value = error?.response?.data || {}
    notifyError(error?.response?.message || error.message || 'Impossible d enregistrer cette annonce.')
  } finally {
    envoiAnnonce.value = false
  }
}

const supprimerAnnonceSelectionnee = async () => {
  if (!annonceSelectionnee.value) {
    return
  }

  const confirmation = window.confirm(`Supprimer l'annonce \"${annonceSelectionnee.value.titre}\" ?`)

  if (!confirmation) {
    return
  }

  try {
    await authDelete(`/president/annonces/${annonceSelectionnee.value.id}`)
    notifySuccess('Annonce supprimee avec succes.')
    annonceSelectionnee.value = null
    mode.value = 'liste'

    const pageCourante = pagination.value?.current_page || 1
    const pageCible = annonces.value.length === 1 && pageCourante > 1 ? pageCourante - 1 : pageCourante
    await chargerAnnonces(pageCible)
  } catch (error) {
    if (gerer401(error)) {
      return
    }

    notifyError(error?.response?.message || error.message || 'Impossible de supprimer cette annonce.')
  }
}

watch(selectedClubId, async () => {
  if (mode.value === 'creation') {
    formulaire.club_id = selectedClubId.value || (clubs.value[0] ? String(clubs.value[0].id) : '')
  }

  await chargerAnnonces(1)
})

watch(
  () => props.searchTerm,
  () => {
    if (debounceRecherche.value) {
      clearTimeout(debounceRecherche.value)
    }

    debounceRecherche.value = setTimeout(() => {
      chargerAnnonces(1)
    }, 350)
  },
)

onMounted(async () => {
  await chargerClubs()
  await chargerAnnonces(1)
})

onBeforeUnmount(() => {
  if (debounceRecherche.value) {
    clearTimeout(debounceRecherche.value)
  }

  if (imageAnnoncePreview.value) {
    URL.revokeObjectURL(imageAnnoncePreview.value)
  }
})

defineExpose({
  refreshCurrent,
})
</script>

<template>
  <section class="mt-6">
    <template v-if="mode === 'liste'">
      <div class="mx-auto max-w-3xl text-center">
        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Gestion president</p>
        <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Gestion des annonces</h3>
        <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#6b7280]">
          Choisissez un club, recherchez une annonce, puis ouvrez sa fiche pour modifier ou supprimer.
        </p>

        <div class="mx-auto mt-5 max-w-4xl rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-2">
          <div class="grid gap-2 md:grid-cols-[260px_1fr]">
            <select
              v-model="selectedClubId"
              class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]"
              :disabled="chargementClubs"
            >
              <option v-for="club in clubs" :key="club.id" :value="String(club.id)">
                {{ club.nom }}
              </option>
            </select>

            <input
              :value="searchTerm"
              type="text"
              placeholder="Rechercher une annonce..."
              class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
              @input="emit('update:searchTerm', $event.target.value)"
            />
          </div>
        </div>

        <button
          type="button"
          class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white transition hover:bg-[#2446d8]"
          @click="ouvrirCreation"
        >
          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" />
          </svg>
          Nouvelle annonce
        </button>
      </div>

      <div v-if="chargementAnnonces" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <div v-for="n in 8" :key="n" class="h-[245px] animate-pulse rounded-[26px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
      </div>

      <p v-else-if="erreurChargement" class="mt-6 rounded-2xl border border-red-100 bg-red-50 px-4 py-4 text-sm font-semibold text-red-700">
        {{ erreurChargement }}
      </p>

      <template v-else>
        <div v-if="annonces.length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          <PresidentAnnouncementCard
            v-for="annonce in annonces"
            :key="annonce.id"
            :annonce="annonce"
            :active="annonceSelectionnee?.id === annonce.id"
            @select="selectionnerAnnonce"
          />
        </div>

        <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
          <h4 class="text-2xl text-[#111827]">Aucune annonce trouvee</h4>
          <p class="mt-2 text-sm font-semibold text-[#6b7280]">Ajoutez une premiere annonce pour ce club.</p>
          <button
            type="button"
            class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white transition hover:bg-[#2446d8]"
            @click="ouvrirCreation"
          >
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
              <path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" />
            </svg>
            Ajouter la premiere annonce
          </button>
        </div>

        <div v-if="pagination" class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#e6edf8] bg-[#f8fbff] px-4 py-3">
          <p class="text-xs font-bold text-[#6b7280]">Page {{ pagination.current_page || 1 }} / {{ pagination.last_page || 1 }}</p>
          <div class="flex gap-2">
            <button
              type="button"
              class="rounded-full border border-[#dbe2ef] px-4 py-2 text-xs font-black text-[#1f2a44] disabled:opacity-40"
              :disabled="(pagination.current_page || 1) <= 1"
              @click="chargerAnnonces((pagination.current_page || 1) - 1)"
            >
              Precedent
            </button>
            <button
              type="button"
              class="rounded-full bg-[#111827] px-4 py-2 text-xs font-black text-white disabled:opacity-40"
              :disabled="(pagination.current_page || 1) >= (pagination.last_page || 1)"
              @click="chargerAnnonces((pagination.current_page || 1) + 1)"
            >
              Suivant
            </button>
          </div>
        </div>
      </template>
    </template>

    <template v-else-if="mode === 'detail' && annonceSelectionnee">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <button
          type="button"
          class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]"
          aria-label="Retour a la liste"
          title="Retour a la liste"
          @click="retourListe"
        >
          <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M15.25 5.75 9 12l6.25 6.25M9.75 12H20" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>

        <div class="flex gap-2">
          <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]"
            aria-label="Modifier"
            title="Modifier"
            @click="ouvrirEdition"
          >
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="m14.75 5.25 4 4M4.75 19.25l4.45-.9 9.05-9.05a2.83 2.83 0 0 0-4-4L5.2 14.35l-.45 4.9Z" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
          <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#ef4444] text-white transition hover:bg-[#dc2626]"
            aria-label="Supprimer"
            title="Supprimer"
            @click="supprimerAnnonceSelectionnee"
          >
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M9.75 4.75h4.5m-7.5 4h10.5m-9.35 0 .73 9.5a2.25 2.25 0 0 0 2.24 2.08h2.26a2.25 2.25 0 0 0 2.24-2.08l.73-9.5M10.5 11.75v5M13.5 11.75v5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
        </div>
      </div>

      <div class="mt-5 rounded-[30px] bg-[#f3f6fb] p-4">
        <section class="rounded-[22px] bg-white p-5">
          <img
            v-if="annonceSelectionnee.image_url"
            :src="annonceSelectionnee.image_url"
            :alt="annonceSelectionnee.titre"
            class="h-[240px] w-full rounded-[22px] object-cover"
          />
          <div
            v-else
            class="h-[240px] w-full rounded-[22px] bg-[linear-gradient(135deg,#dbe7ff_0%,#eef4ff_45%,#f8fbff_100%)]"
          ></div>

          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#4c6fff]">Annonce</p>
              <h3 class="mt-2 text-3xl font-black text-[#111827]">{{ annonceSelectionnee.titre }}</h3>
              <p class="mt-2 text-sm font-semibold text-[#64748b]">
                {{ annonceSelectionnee.club?.nom || '-' }}
              </p>
            </div>
            <span class="rounded-full px-4 py-2 text-xs font-black uppercase" :class="annonceSelectionnee.est_active ? 'bg-[#ecfdf5] text-[#16a34a]' : 'bg-[#f8fafc] text-[#64748b]'">
              {{ annonceSelectionnee.est_active ? 'Active' : 'Archivee' }}
            </span>
          </div>

          <div class="mt-5 grid gap-4 md:grid-cols-3">
            <section class="rounded-[18px] bg-[#f8fbff] p-4">
              <p class="text-sm font-black text-[#111827]">Auteur</p>
              <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ annonceSelectionnee.auteur?.nom || 'President' }}</p>
            </section>
            <section class="rounded-[18px] bg-[#f8fbff] p-4">
              <p class="text-sm font-black text-[#111827]">Email</p>
              <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ annonceSelectionnee.auteur?.email || '-' }}</p>
            </section>
            <section class="rounded-[18px] bg-[#f8fbff] p-4">
              <p class="text-sm font-black text-[#111827]">Publication</p>
              <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ formatDate(annonceSelectionnee.created_at) }}</p>
            </section>
          </div>

          <section class="mt-5 rounded-[18px] bg-[#f8fbff] p-4">
            <p class="text-sm font-black text-[#111827]">Contenu</p>
            <p class="mt-3 whitespace-pre-line text-sm font-semibold leading-7 text-[#64748b]">
              {{ annonceSelectionnee.contenu || 'Aucun contenu disponible pour cette annonce.' }}
            </p>
          </section>
        </section>
      </div>
    </template>

    <template v-else>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <button
          type="button"
          class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe2ef] bg-white text-[#1f2a44] transition hover:border-[#c7d2ea] hover:bg-[#f8fbff]"
          aria-label="Retour"
          title="Retour"
          @click="mode = annonceSelectionnee ? 'detail' : 'liste'"
        >
          <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M15.25 5.75 9 12l6.25 6.25M9.75 12H20" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
        <div class="text-right">
          <h3 class="text-2xl text-[#111827]">{{ mode === 'edition' ? 'Modifier l annonce' : 'Ajouter une annonce' }}</h3>
          <p class="text-xs font-semibold text-[#64748b]">{{ clubFormulaire()?.nom || 'Choisissez un club' }}</p>
        </div>
      </div>

      <PresidentAnnouncementForm
        :model-value="formulaire"
        :errors="erreursValidation"
        :clubs="clubs"
        :image-preview="imageAnnoncePreview"
        :current-image-url="mode === 'edition' ? annonceSelectionnee?.image_url || '' : ''"
        :image-file-name="imageAnnonceFichier?.name || ''"
        :loading="envoiAnnonce"
        :disable-club="mode === 'edition'"
        :submit-label="mode === 'edition' ? 'Enregistrer les modifications' : 'Creer l annonce'"
        :loading-label="mode === 'edition' ? 'Enregistrement...' : 'Creation...'"
        @choose-image="choisirImage"
        @update-field="mettreAJourChamp"
        @submit="enregistrerAnnonce"
      />
    </template>
  </section>
</template>
