<script setup>
import { ref } from 'vue'
import PresidentMessagingSection from '../../components/president/PresidentMessagingSection.vue'
import PresidentShellLayout from '../../components/president/PresidentShellLayout.vue'
import { useAuthSession } from '../../composables/useAuthSession'

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
