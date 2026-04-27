<script setup>
import { ref } from 'vue'
import PresidentAnnouncementsSection from '@/roles/president/annonces/components/PresidentAnnouncementsSection.vue'
import PresidentShellLayout from '@/roles/president/shared/components/PresidentShellLayout.vue'
import { useAuthSession } from '@/shared/session/useAuthSession'

const { utilisateur, deconnecter } = useAuthSession()
const recherche = ref('')
</script>

<template>
  <PresidentShellLayout
    title="Gestion des annonces"
    subtitle="Creation, mise a jour et suivi des annonces du president."
    active-section="annonces"
    :utilisateur="utilisateur"
    @logout="deconnecter"
  >
    <PresidentAnnouncementsSection v-model:search-term="recherche" />
  </PresidentShellLayout>
</template>
