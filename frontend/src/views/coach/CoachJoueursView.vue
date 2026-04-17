<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import CoachShellLayout from '../../components/coach/CoachShellLayout.vue'
import { useStoredUser } from '../../composables/useStoredUser'
import { authGet } from '../../services/api'
import { notifyError } from '../../stores/toast'

const router = useRouter()
const { utilisateur, chargerUtilisateur } = useStoredUser()
const chargementEquipes = ref(true)
const chargementJoueurs = ref(false)
const equipes = ref([])
const joueurs = ref([])
const selectedEquipeId = ref('')

const gerer401 = (error) => {
  if (error?.response?.code === 401) {
    localStorage.removeItem('token_api')
    localStorage.removeItem('utilisateur_api')
    router.push('/login')
    return true
  }
  return false
}

const chargerEquipes = async () => {
  chargementEquipes.value = true
  try {
    const reponse = await authGet('/coach/equipes')
    equipes.value = reponse?.data?.equipes || []
    selectedEquipeId.value = equipes.value[0] ? String(equipes.value[0].id) : ''
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les equipes.')
    }
  } finally {
    chargementEquipes.value = false
  }
}

const chargerJoueurs = async () => {
  if (!selectedEquipeId.value) {
    joueurs.value = []
    return
  }

  chargementJoueurs.value = true
  try {
    const reponse = await authGet(`/coach/equipes/${selectedEquipeId.value}/joueurs`)
    joueurs.value = reponse?.data?.joueurs || []
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les joueurs.')
    }
  } finally {
    chargementJoueurs.value = false
  }
}

watch(selectedEquipeId, chargerJoueurs)

const deconnecter = () => {
  localStorage.removeItem('token_api')
  localStorage.removeItem('utilisateur_api')
  router.push('/login')
}

onMounted(async () => {
  chargerUtilisateur()
  await chargerEquipes()
  await chargerJoueurs()
})
</script>

<template>
  <CoachShellLayout title="Joueurs coach" subtitle="Suivez les joueurs de chaque equipe que vous encadrez." active-tab="joueurs" :user="utilisateur" @logout="deconnecter">
    <div class="flex flex-wrap items-center gap-3">
      <select v-model="selectedEquipeId" class="h-12 min-w-[280px] rounded-2xl border border-[#dbe3f1] px-4 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]" :disabled="chargementEquipes">
        <option value="">Choisir une equipe</option>
        <option v-for="equipe in equipes" :key="equipe.id" :value="String(equipe.id)">{{ equipe.nom }}</option>
      </select>
    </div>

    <div v-if="chargementJoueurs" class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <div v-for="item in 6" :key="item" class="h-40 animate-pulse rounded-[24px] border border-[#edf2ff] bg-[#f8fbff]"></div>
    </div>

    <div v-else class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <article v-for="joueur in joueurs" :key="joueur.id" class="rounded-[26px] border border-[#e5ecfb] bg-white p-5">
        <div class="flex items-center gap-4">
          <img v-if="joueur.photo_url" :src="joueur.photo_url" :alt="joueur.nom" class="h-16 w-16 rounded-2xl object-cover" />
          <div v-else class="grid h-16 w-16 place-items-center rounded-2xl bg-[linear-gradient(135deg,#2446d8_0%,#4c6fff_100%)] text-sm font-black text-white">
            {{ joueur.nom?.slice(0,2).toUpperCase() }}
          </div>
          <div>
            <h2 class="text-lg font-black text-[#0f172a]">{{ joueur.nom }}</h2>
            <p class="mt-1 text-sm font-semibold text-[#64748b]">{{ joueur.email || '-' }}</p>
          </div>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2">
          <div class="rounded-[18px] bg-[#f8fbff] p-3">
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#64748b]">Telephone</p>
            <p class="mt-2 text-sm font-semibold text-[#0f172a]">{{ joueur.telephone || '-' }}</p>
          </div>
          <div class="rounded-[18px] bg-[#f8fbff] p-3">
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#64748b]">Statut</p>
            <p class="mt-2 text-sm font-semibold text-[#0f172a]">{{ joueur.statut || '-' }}</p>
          </div>
        </div>
      </article>

      <div v-if="!joueurs.length" class="rounded-[28px] border border-dashed border-[#d7e1fb] bg-[#f8fbff] p-8 text-center text-sm font-semibold text-[#64748b] md:col-span-2 xl:col-span-3">
        Aucun joueur trouve pour cette equipe.
      </div>
    </div>
  </CoachShellLayout>
</template>
