import { ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  effacerSessionStockee,
  lireUtilisateurStocke,
  sauvegarderUtilisateurStocke,
} from '@/shared/session/sessionStorage'

export { lireUtilisateurStocke } from '@/shared/session/sessionStorage'

export const useAuthSession = () => {
  const router = useRouter()
  const utilisateur = ref(lireUtilisateurStocke())

  const chargerUtilisateur = () => {
    utilisateur.value = lireUtilisateurStocke()
    return utilisateur
  }

  const sauvegarderUtilisateur = (nouvelUtilisateur) => {
    utilisateur.value = nouvelUtilisateur || null

    sauvegarderUtilisateurStocke(nouvelUtilisateur)
  }

  const deconnecter = () => {
    effacerSessionStockee()
    utilisateur.value = null
    router.push('/login')
  }

  const gererErreurAuthentification = (error) => {
    if (error?.response?.code === 401 || error?.status === 401) {
      deconnecter()
      return true
    }

    return false
  }

  return {
    utilisateur,
    chargerUtilisateur,
    sauvegarderUtilisateur,
    deconnecter,
    gererErreurAuthentification,
  }
}
