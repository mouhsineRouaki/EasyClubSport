<script setup>
import { computed, onMounted, ref } from 'vue'
import CoachShellLayout from '@/roles/coach/shared/components/CoachShellLayout.vue'
import CoachEquipeCard from '@/roles/coach/equipes/components/CoachEquipeCard.vue'
import AppDetailsTable from '@/shared/components/details/AppDetailsTable.vue'
import { useAuthSession } from '@/shared/session/useAuthSession'
import { authGet } from '@/shared/services/apiClient'
import { notifyError } from '@/shared/services/toastService'

const { utilisateur, chargerUtilisateur, deconnecter, gererErreurAuthentification } = useAuthSession()
const chargement = ref(true)
const equipes = ref([])
const equipeSelectionnee = ref(null)

const detailsEquipe = computed(() => {
  if (!equipeSelectionnee.value) {
    return []
  }

  return [
    { label: 'Categorie', value: equipeSelectionnee.value.categorie || '-' },
    { label: 'Joueurs', value: equipeSelectionnee.value.joueurs_total || 0 },
    { label: 'Statut', value: equipeSelectionnee.value.statut || '-' },
    { label: 'Club', value: equipeSelectionnee.value.club?.nom || '-' },
    { label: 'Ville', value: equipeSelectionnee.value.club?.ville || '-' },
    { label: 'Description', value: equipeSelectionnee.value.description || 'Aucune description.' },
  ]
})

const chargerEquipes = async () => {
  chargement.value = true

  try {
    const reponse = await authGet('/coach/equipes')
    equipes.value = reponse?.data?.equipes || []
    equipeSelectionnee.value = equipes.value[0] || null
  } catch (error) {
    if (!gererErreurAuthentification(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les equipes du coach.')
    }
  } finally {
    chargement.value = false
  }
}

onMounted(async () => {
  chargerUtilisateur()
  await chargerEquipes()
})
</script>

<template>
  <CoachShellLayout
    title="Equipes coach"
    subtitle="Consultez vos equipes, leur club et leur effectif actuel."
    active-tab="equipes"
    :user="utilisateur"
    @logout="deconnecter"
  >
    <div v-if="chargement" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <div v-for="item in 3" :key="item" class="h-56 animate-pulse rounded-[28px] border border-[#edf2ff] bg-[#f8fbff]"></div>
    </div>

    <div v-else class="grid gap-6 xl:grid-cols-[1fr_390px]">
      <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <CoachEquipeCard
          v-for="equipe in equipes"
          :key="equipe.id"
          :equipe="equipe"
          :active="equipeSelectionnee?.id === equipe.id"
          selectable
          action-label="Selectionner"
          @select="equipeSelectionnee = $event"
          @show-players="equipeSelectionnee = equipes.find((item) => String(item.id) === String($event)) || equipeSelectionnee"
        />

        <div v-if="!equipes.length" class="rounded-[28px] border border-dashed border-[#d7e1fb] bg-[#f8fbff] p-8 text-center text-sm font-semibold text-[#64748b] md:col-span-2 xl:col-span-3">
          Aucune equipe affectee a ce coach.
        </div>
      </section>

      <aside class="rounded-[28px] border border-[#e5ecfb] bg-white p-6">
        <div v-if="equipeSelectionnee">
          <p class="text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Detail equipe</p>
          <h2 class="mt-2 text-3xl font-black text-[#0f172a]">{{ equipeSelectionnee.nom }}</h2>
          <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ equipeSelectionnee.club?.nom || '-' }}</p>

          <div class="mt-6">
            <AppDetailsTable :items="detailsEquipe" :columns="1" />
          </div>
        </div>

        <div v-else class="text-center text-sm font-semibold text-[#64748b]">Choisissez une equipe pour voir ses details.</div>
      </aside>
    </div>
  </CoachShellLayout>
</template>
