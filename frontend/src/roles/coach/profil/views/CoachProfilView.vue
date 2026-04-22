<script setup>
import { onMounted } from 'vue'
import CoachShellLayout from '@/roles/coach/shared/components/CoachShellLayout.vue'
import AppProfileManager from '@/shared/components/AppProfileManager.vue'
import { useAuthSession } from '@/shared/session/useAuthSession'

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
