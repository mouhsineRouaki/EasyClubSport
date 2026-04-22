<script setup>
import { ref } from 'vue'
import PresidentDocumentsSection from '@/roles/president/documents/components/PresidentDocumentsSection.vue'
import PresidentShellLayout from '@/roles/president/shared/components/PresidentShellLayout.vue'
import { useAuthSession } from '@/shared/session/useAuthSession'

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
