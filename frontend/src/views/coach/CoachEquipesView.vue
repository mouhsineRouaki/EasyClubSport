<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import CoachShellLayout from '../../components/coach/CoachShellLayout.vue'
import { useStoredUser } from '../../composables/useStoredUser'
import { authGet } from '../../services/api'
import { notifyError } from '../../stores/toast'

const router = useRouter()
const { utilisateur, chargerUtilisateur } = useStoredUser()
const chargement = ref(true)
const equipes = ref([])
const equipeSelectionnee = ref(null)

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
  chargement.value = true
  try {
    const reponse = await authGet('/coach/equipes')
    equipes.value = reponse?.data?.equipes || []
    equipeSelectionnee.value = equipes.value[0] || null
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les equipes du coach.')
    }
  } finally {
    chargement.value = false
  }
}

const deconnecter = () => {
  localStorage.removeItem('token_api')
  localStorage.removeItem('utilisateur_api')
  router.push('/login')
}

onMounted(async () => {
  chargerUtilisateur()
  await chargerEquipes()
})
</script>

<template>
  <CoachShellLayout title="Equipes coach" subtitle="Consultez vos equipes, leur club et leur effectif actuel." active-tab="equipes" :user="utilisateur" @logout="deconnecter">
    <div v-if="chargement" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <div v-for="item in 3" :key="item" class="h-56 animate-pulse rounded-[28px] border border-[#edf2ff] bg-[#f8fbff]"></div>
    </div>

    <div v-else class="grid gap-6 xl:grid-cols-[1fr_390px]">
      <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <button
          v-for="equipe in equipes"
          :key="equipe.id"
          type="button"
          class="overflow-hidden rounded-[28px] border text-left transition"
          :class="equipeSelectionnee?.id === equipe.id ? 'border-[#4c6fff] bg-[#f7f9ff]' : 'border-[#e5ecfb] bg-white hover:border-[#cdd8ff]'"
          @click="equipeSelectionnee = equipe"
        >
          <div class="h-40 w-full bg-[linear-gradient(135deg,#2446d8_0%,#4c6fff_100%)]">
            <img v-if="equipe.logo_url" :src="equipe.logo_url" :alt="equipe.nom" class="h-full w-full object-cover" />
          </div>
          <div class="p-5">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">{{ equipe.categorie || 'categorie' }}</p>
            <h2 class="mt-2 text-2xl font-black text-[#0f172a]">{{ equipe.nom }}</h2>
            <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ equipe.club?.nom || '-' }} · {{ equipe.club?.ville || '-' }}</p>
            <div class="mt-4 flex items-center justify-between text-sm font-semibold text-[#334155]">
              <span>{{ equipe.joueurs_total || 0 }} joueurs</span>
              <span class="capitalize">{{ equipe.statut || '-' }}</span>
            </div>
          </div>
        </button>

        <div v-if="!equipes.length" class="rounded-[28px] border border-dashed border-[#d7e1fb] bg-[#f8fbff] p-8 text-center text-sm font-semibold text-[#64748b] md:col-span-2 xl:col-span-3">
          Aucune equipe affectee a ce coach.
        </div>
      </section>

      <aside class="rounded-[28px] border border-[#e5ecfb] bg-white p-6">
        <div v-if="equipeSelectionnee">
          <p class="text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Detail equipe</p>
          <h2 class="mt-2 text-3xl font-black text-[#0f172a]">{{ equipeSelectionnee.nom }}</h2>
          <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ equipeSelectionnee.club?.nom || '-' }}</p>

          <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
            <div class="rounded-[22px] border border-[#edf2ff] bg-[#f8fbff] p-4">
              <p class="text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Categorie</p>
              <p class="mt-2 text-lg font-black text-[#0f172a]">{{ equipeSelectionnee.categorie || '-' }}</p>
            </div>
            <div class="rounded-[22px] border border-[#edf2ff] bg-[#f8fbff] p-4">
              <p class="text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Joueurs</p>
              <p class="mt-2 text-lg font-black text-[#0f172a]">{{ equipeSelectionnee.joueurs_total || 0 }}</p>
            </div>
            <div class="rounded-[22px] border border-[#edf2ff] bg-[#f8fbff] p-4">
              <p class="text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Statut</p>
              <p class="mt-2 text-lg font-black capitalize text-[#0f172a]">{{ equipeSelectionnee.statut || '-' }}</p>
            </div>
            <div class="rounded-[22px] border border-[#edf2ff] bg-[#f8fbff] p-4">
              <p class="text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Description</p>
              <p class="mt-2 text-sm font-semibold text-[#475569]">{{ equipeSelectionnee.description || 'Aucune description.' }}</p>
            </div>
          </div>
        </div>

        <div v-else class="text-center text-sm font-semibold text-[#64748b]">Choisissez une equipe pour voir ses details.</div>
      </aside>
    </div>
  </CoachShellLayout>
</template>
