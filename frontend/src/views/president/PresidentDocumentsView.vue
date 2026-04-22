<script setup>
import { ref } from 'vue'
import PresidentDocumentsSection from '../../components/president/PresidentDocumentsSection.vue'
import PresidentShellLayout from '../../components/president/PresidentShellLayout.vue'
import { useAuthSession } from '../../composables/useAuthSession'

const { utilisateur, deconnecter } = useAuthSession()
const recherche = ref('')
</script>

<template>
  <PresidentShellLayout
    title="Gestion des documents"
    subtitle="Creation, mise a jour et suivi des documents du president."
    active-section="documents"
    :utilisateur="utilisateur"
    @logout="deconnecter"
  >
    <PresidentDocumentsSection v-model:search-term="recherche" />
  </PresidentShellLayout>
</template>
