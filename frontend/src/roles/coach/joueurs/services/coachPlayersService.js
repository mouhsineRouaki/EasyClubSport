import { authDelete, authGet, authPost, authPut } from '@/shared/services/apiClient'

const appendField = (formData, key, value) => {
  if (value === undefined || value === null || value === '') {
    return
  }

  formData.append(key, value)
}

export const createPlayerPayload = (payload = {}) => {
  const formData = new FormData()

  appendField(formData, 'nom', payload.nom)
  appendField(formData, 'prenom', payload.prenom)
  appendField(formData, 'email', payload.email)
  appendField(formData, 'telephone', payload.telephone)
  appendField(formData, 'adresse', payload.adresse)
  appendField(formData, 'statut', payload.statut)
  appendField(formData, 'numero_joueur', payload.numero_joueur)
  appendField(formData, 'poste_principal', payload.poste_principal)
  appendField(formData, 'poste_secondaire', payload.poste_secondaire)
  appendField(formData, 'pied_fort', payload.pied_fort)
  appendField(formData, 'note_globale', payload.note_globale)
  appendField(formData, 'attaque', payload.attaque)
  appendField(formData, 'defense', payload.defense)
  appendField(formData, 'vitesse', payload.vitesse)
  appendField(formData, 'passe', payload.passe)
  appendField(formData, 'dribble', payload.dribble)
  appendField(formData, 'physique', payload.physique)

  if (payload.photo instanceof File) {
    formData.append('photo', payload.photo)
  }

  return formData
}

export const listCoachTeams = async () => {
  const response = await authGet('/coach/equipes')
  return response?.data?.equipes || []
}

export const listCoachPlayers = async (equipeId) => {
  const response = await authGet(`/coach/equipes/${equipeId}/joueurs`)
  return response?.data?.joueurs || []
}

export const createCoachPlayer = async (equipeId, payload) => {
  return authPost(`/coach/equipes/${equipeId}/joueurs`, createPlayerPayload(payload))
}

export const updateCoachPlayer = async (equipeId, joueurId, payload) => {
  return authPut(`/coach/equipes/${equipeId}/joueurs/${joueurId}`, createPlayerPayload(payload))
}

export const removeCoachPlayer = async (equipeId, joueurId) => {
  return authDelete(`/coach/equipes/${equipeId}/joueurs/${joueurId}`)
}
