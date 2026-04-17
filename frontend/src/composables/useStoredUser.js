import { ref } from 'vue'

export const useStoredUser = () => {
  const utilisateur = ref(null)

  const chargerUtilisateur = () => {
    const brut = localStorage.getItem('utilisateur_api')

    if (!brut) {
      utilisateur.value = null
      return utilisateur
    }

    try {
      utilisateur.value = JSON.parse(brut)
    } catch {
      utilisateur.value = null
    }

    return utilisateur
  }

  return {
    utilisateur,
    chargerUtilisateur,
  }
}
