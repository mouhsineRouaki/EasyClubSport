<script setup>
import { onMounted, ref, watch } from 'vue'
import CoachPlayerCard from '@/roles/coach/joueurs/components/CoachPlayerCard.vue'
import CoachShellLayout from '@/roles/coach/shared/components/CoachShellLayout.vue'
import AppSelectField from '@/shared/components/AppSelectField.vue'
import { useAuthSession } from '@/shared/session/useAuthSession'
import { authGet } from '@/shared/services/apiClient'
import { notifyError } from '@/shared/services/toastService'

const { utilisateur, chargerUtilisateur, deconnecter, gererErreurAuthentification } = useAuthSession()
const chargementEquipes = ref(true)
const chargementJoueurs = ref(false)
const equipes = ref([])
const joueurs = ref([])
const equipeSelectionneeId = ref('')

const chargerEquipes = async () => {
  chargementEquipes.value = true

  try {
    const reponse = await authGet('/coach/equipes')
    equipes.value = reponse?.data?.equipes || []
    equipeSelectionneeId.value = equipes.value[0] ? String(equipes.value[0].id) : ''
  } catch (error) {
    if (!gererErreurAuthentification(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les equipes.')
    }
  } finally {
    chargementEquipes.value = false
  }
}

const chargerJoueurs = async () => {
  if (!equipeSelectionneeId.value) {
    joueurs.value = []
    return
  }

  chargementJoueurs.value = true

  try {
    const reponse = await authGet(`/coach/equipes/${equipeSelectionneeId.value}/joueurs`)
    joueurs.value = reponse?.data?.joueurs || []
  } catch (error) {
    if (!gererErreurAuthentification(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger les joueurs.')
    }
  } finally {
    chargementJoueurs.value = false
  }
}

watch(equipeSelectionneeId, chargerJoueurs)

onMounted(async () => {
  chargerUtilisateur()
  await chargerEquipes()
  await chargerJoueurs()
})
</script>

<template>
  <CoachShellLayout
    title="Joueurs coach"
    subtitle="Suivez les joueurs de chaque equipe que vous encadrez."
    active-tab="joueurs"
    :user="utilisateur"
    @logout="deconnecter"
  >
    <div class="flex flex-wrap items-end gap-3">
      <div class="min-w-[280px]">
        <AppSelectField
          v-model="equipeSelectionneeId"
          label="Equipe"
          :options="equipes"
          placeholder="Choisir une equipe"
          :disabled="chargementEquipes"
          select-class="h-12 w-full rounded-2xl border border-[#dbe3f1] px-4 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]"
        />
      </div>
    </div>

    <div v-if="chargementJoueurs" class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <div v-for="item in 6" :key="item" class="h-40 animate-pulse rounded-[24px] border border-[#edf2ff] bg-[#f8fbff]"></div>
    </div>

    <div v-else class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <CoachPlayerCard v-for="joueur in joueurs" :key="joueur.id" :joueur="joueur" />

      <div v-if="!joueurs.length" class="rounded-[28px] border border-dashed border-[#d7e1fb] bg-[#f8fbff] p-8 text-center text-sm font-semibold text-[#64748b] md:col-span-2 xl:col-span-3">
        Aucun joueur trouve pour cette equipe.
      </div>
    </div>
  </CoachShellLayout>
</template>
