const TOKEN_STORAGE_KEY = 'token_api'
const USER_STORAGE_KEY = 'utilisateur_api'

export function lireTokenStocke() {
  return localStorage.getItem(TOKEN_STORAGE_KEY) || ''
}

export function sauvegarderToken(token) {
  if (token) {
    localStorage.setItem(TOKEN_STORAGE_KEY, token)
    return
  }

  localStorage.removeItem(TOKEN_STORAGE_KEY)
}

export function lireUtilisateurStocke() {
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

export function sauvegarderUtilisateurStocke(utilisateur) {
  if (utilisateur) {
    localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(utilisateur))
    return
  }

  localStorage.removeItem(USER_STORAGE_KEY)
}

export function effacerSessionStockee() {
  localStorage.removeItem(TOKEN_STORAGE_KEY)
  localStorage.removeItem(USER_STORAGE_KEY)
}
