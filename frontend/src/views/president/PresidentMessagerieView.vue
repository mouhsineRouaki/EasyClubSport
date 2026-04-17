<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import PresidentMessagingSection from '../../components/president/PresidentMessagingSection.vue'
import PresidentShellLayout from '../../components/president/PresidentShellLayout.vue'

const router = useRouter()
const utilisateurConnecte = ref(null)
const recherche = ref('')

const utilisateurStocke = localStorage.getItem('utilisateur_api')

if (utilisateurStocke) {
  try {
    utilisateurConnecte.value = JSON.parse(utilisateurStocke)
  } catch {
    utilisateurConnecte.value = null
  }
}

const deconnecter = () => {
  localStorage.removeItem('token_api')
  localStorage.removeItem('utilisateur_api')
  router.push('/login')
}
</script>

<template>
  <PresidentShellLayout
    title="Gestion des messages"
    subtitle="Conversations a gauche, fil de discussion a droite, et temps reel par websocket."
    active-section="messagerie"
    :utilisateur="utilisateurConnecte"
    @logout="deconnecter"
  >
    <PresidentMessagingSection v-model:search-term="recherche" />
  </PresidentShellLayout>
</template>