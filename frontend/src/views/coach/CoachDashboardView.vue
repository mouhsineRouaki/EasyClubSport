<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import CoachShellLayout from '../../components/coach/CoachShellLayout.vue'
import { useStoredUser } from '../../composables/useStoredUser'
import { authGet } from '../../services/api'
import { notifyError } from '../../stores/toast'

const router = useRouter()
const { utilisateur, chargerUtilisateur } = useStoredUser()
const chargement = ref(true)
const dashboard = ref(null)

const stats = computed(() => dashboard.value?.statistiques || {})
const prochainEvenement = computed(() => dashboard.value?.prochain_evenement || null)
const evenementsRecents = computed(() => dashboard.value?.evenements_recents || [])
const canauxRecents = computed(() => dashboard.value?.canaux_recents || [])

const gerer401 = (error) => {
  if (error?.response?.code === 401) {
    localStorage.removeItem('token_api')
    localStorage.removeItem('utilisateur_api')
    router.push('/login')
    return true
  }

  return false
}

const formatDate = (value) => {
  if (!value) return '-'

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value))
}

const chargerDashboard = async () => {
  chargement.value = true

  try {
    const reponse = await authGet('/coach/dashboard')
    dashboard.value = reponse?.data || null
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger le dashboard coach.')
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
  await chargerDashboard()
})
</script>

<template>
  <CoachShellLayout title="Dashboard coach" subtitle="Vue rapide sur vos equipes, vos evenements et vos canaux actifs." active-tab="dashboard" :user="utilisateur" @logout="deconnecter">
    <div v-if="chargement" class="grid gap-4 lg:grid-cols-4">
      <div v-for="item in 4" :key="item" class="h-28 animate-pulse rounded-[24px] border border-[#edf2ff] bg-[#f8fbff]"></div>
    </div>

    <div v-else class="space-y-6">
      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[24px] border border-[#e5ecfb] bg-[#f8fbff] p-5">
          <p class="text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Equipes</p>
          <p class="mt-3 text-4xl font-black text-[#0f172a]">{{ stats.equipes_total || 0 }}</p>
        </article>
        <article class="rounded-[24px] border border-[#e5ecfb] bg-[#f8fbff] p-5">
          <p class="text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Joueurs</p>
          <p class="mt-3 text-4xl font-black text-[#0f172a]">{{ stats.joueurs_total || 0 }}</p>
        </article>
        <article class="rounded-[24px] border border-[#e5ecfb] bg-[#f8fbff] p-5">
          <p class="text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Evenements</p>
          <p class="mt-3 text-4xl font-black text-[#0f172a]">{{ stats.evenements_a_venir_total || 0 }}</p>
        </article>
        <article class="rounded-[24px] border border-[#e5ecfb] bg-[#f8fbff] p-5">
          <p class="text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Convocations</p>
          <p class="mt-3 text-4xl font-black text-[#0f172a]">{{ stats.convocations_en_attente_total || 0 }}</p>
        </article>
      </div>

      <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <section class="rounded-[28px] border border-[#e5ecfb] bg-white p-6">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Prochain rendez-vous</p>
              <h2 class="mt-2 text-2xl font-black text-[#0f172a]">Agenda prioritaire</h2>
            </div>
          </div>

          <div v-if="prochainEvenement" class="mt-5 rounded-[24px] bg-[linear-gradient(135deg,#2446d8_0%,#4c6fff_100%)] p-6 text-white">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-white/70">{{ prochainEvenement.type }}</p>
            <h3 class="mt-3 text-3xl font-black">{{ prochainEvenement.titre }}</h3>
            <p class="mt-2 text-sm font-semibold text-white/80">{{ prochainEvenement.equipe?.nom || '-' }} · {{ prochainEvenement.adversaire || prochainEvenement.lieu || '-' }}</p>
            <p class="mt-5 text-sm font-semibold">{{ formatDate(prochainEvenement.date_debut) }}</p>
          </div>
          <div v-else class="mt-5 rounded-[24px] border border-dashed border-[#d7e1fb] bg-[#f8fbff] p-8 text-center text-sm font-semibold text-[#64748b]">
            Aucun evenement programme.
          </div>
        </section>

        <section class="rounded-[28px] border border-[#e5ecfb] bg-white p-6">
          <p class="text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Canaux actifs</p>
          <h2 class="mt-2 text-2xl font-black text-[#0f172a]">Messagerie recente</h2>

          <div class="mt-5 space-y-3">
            <article v-for="canal in canauxRecents" :key="canal.id" class="rounded-[22px] border border-[#edf2ff] bg-[#f8fbff] p-4">
              <p class="text-sm font-black text-[#0f172a]">{{ canal.nom }}</p>
              <p class="mt-1 text-xs font-semibold text-[#64748b]">{{ canal.type_canal }}</p>
            </article>

            <div v-if="!canauxRecents.length" class="rounded-[22px] border border-dashed border-[#d7e1fb] bg-[#f8fbff] p-6 text-center text-sm font-semibold text-[#64748b]">
              Aucun canal recent.
            </div>
          </div>
        </section>
      </div>

      <section class="rounded-[28px] border border-[#e5ecfb] bg-white p-6">
        <p class="text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Evenements recents</p>
        <h2 class="mt-2 text-2xl font-black text-[#0f172a]">Historique proche</h2>

        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <article v-for="evenement in evenementsRecents" :key="evenement.id" class="rounded-[24px] border border-[#edf2ff] bg-[#f8fbff] p-5">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">{{ evenement.statut }}</p>
            <h3 class="mt-3 text-xl font-black text-[#0f172a]">{{ evenement.titre }}</h3>
            <p class="mt-3 text-sm font-semibold text-[#475569]">{{ formatDate(evenement.date_debut) }}</p>
          </article>

          <div v-if="!evenementsRecents.length" class="rounded-[24px] border border-dashed border-[#d7e1fb] bg-[#f8fbff] p-6 text-center text-sm font-semibold text-[#64748b] md:col-span-2 xl:col-span-3">
            Aucun evenement recent.
          </div>
        </div>
      </section>
    </div>
  </CoachShellLayout>
</template>
