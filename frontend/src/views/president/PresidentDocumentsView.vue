<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import PresidentDocumentsSection from '../../components/president/PresidentDocumentsSection.vue'
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
    title="Gestion des documents"
    subtitle="Creation, mise a jour et suivi des documents du president."
    active-section="documents"
    :user="utilisateurConnecte"
    @logout="deconnecter"
  >
    <PresidentDocumentsSection v-model:search-term="recherche" />
  </PresidentShellLayout>
</template>
