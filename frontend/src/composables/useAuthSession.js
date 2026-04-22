import { ref } from 'vue'
import { useRouter } from 'vue-router'

const TOKEN_STORAGE_KEY = 'token_api'
const USER_STORAGE_KEY = 'utilisateur_api'

export const lireUtilisateurStocke = () => {
  const utilisateurStocke = localStorage.getItem(USER_STORAGE_KEY)

  if (!utilisateurStocke) {
    return null
  }

  try {
    return JSON.parse(utilisateurStocke)
  } catch {
    return null
  }
}

const effacerSessionLocale = () => {
  localStorage.removeItem(TOKEN_STORAGE_KEY)
  localStorage.removeItem(USER_STORAGE_KEY)
}

export const useAuthSession = () => {
  const router = useRouter()
  const utilisateur = ref(lireUtilisateurStocke())

  const chargerUtilisateur = () => {
    utilisateur.value = lireUtilisateurStocke()
    return utilisateur
  }

  const sauvegarderUtilisateur = (nouvelUtilisateur) => {
    utilisateur.value = nouvelUtilisateur || null

    if (nouvelUtilisateur) {
      localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(nouvelUtilisateur))
      return
    }

    localStorage.removeItem(USER_STORAGE_KEY)
  }

  const deconnecter = () => {
    effacerSessionLocale()
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
