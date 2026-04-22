import { ref } from 'vue'
import { lireUtilisateurStocke } from './useAuthSession'

export const useStoredUser = () => {
  const utilisateur = ref(null)

  const chargerUtilisateur = () => {
    utilisateur.value = lireUtilisateurStocke()
    return utilisateur
  }

  return {
    utilisateur,
    chargerUtilisateur,
  }
}
