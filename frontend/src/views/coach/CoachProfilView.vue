<script setup>
import { onMounted } from 'vue'
import CoachShellLayout from '../../components/coach/CoachShellLayout.vue'
import AppProfileManager from '../../components/common/AppProfileManager.vue'
import { useAuthSession } from '../../composables/useAuthSession'

const { utilisateur, chargerUtilisateur, deconnecter } = useAuthSession()

onMounted(() => {
  chargerUtilisateur()
})
</script>

<template>
  <CoachShellLayout
    title="Profil coach"
    subtitle="Mettez a jour vos informations personnelles et votre photo."
    active-tab="profil"
    :user="utilisateur"
    @logout="deconnecter"
  >
    <AppProfileManager :visible="true" role-label="Coach" profile-endpoint="/coach/profil" :show-status="true" />
  </CoachShellLayout>
</template>
