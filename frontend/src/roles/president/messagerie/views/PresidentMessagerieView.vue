<script setup>
import { ref } from 'vue'
import PresidentMessagingSection from '@/roles/president/messagerie/components/PresidentMessagingSection.vue'
import PresidentShellLayout from '@/roles/president/shared/components/PresidentShellLayout.vue'
import { useAuthSession } from '@/shared/session/useAuthSession'

const { utilisateur, deconnecter } = useAuthSession()
const recherche = ref('')
</script>

<template>
  <PresidentShellLayout
    title="Gestion des messages"
    subtitle="Conversations a gauche, fil de discussion a droite, et temps reel par websocket."
    active-section="messagerie"
    :utilisateur="utilisateur"
    @logout="deconnecter"
  >
    <PresidentMessagingSection v-model:search-term="recherche" />
  </PresidentShellLayout>
</template>
