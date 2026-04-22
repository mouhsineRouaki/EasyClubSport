<script setup>
import { ref } from 'vue'
import PresidentAnnouncementsSection from '../../components/president/PresidentAnnouncementsSection.vue'
import PresidentShellLayout from '../../components/president/PresidentShellLayout.vue'
import { useAuthSession } from '../../composables/useAuthSession'

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
