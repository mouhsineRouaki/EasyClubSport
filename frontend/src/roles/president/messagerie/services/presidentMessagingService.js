import { authDelete, authGet, authPost } from '@/shared/services/apiClient'

const appendField = (formData, key, value) => {
  if (value === undefined || value === null || value === '') {
    return
  }

  formData.append(key, value)
}

export const createConversationPayload = (payload = {}) => {
  const formData = new FormData()

  appendField(formData, 'type_canal', payload.type_canal || 'prive')
  appendField(formData, 'nom', payload.nom)
  appendField(formData, 'description', payload.description)

  ;(payload.utilisateur_ids || []).forEach((id) => {
    formData.append('utilisateur_ids[]', id)
  })

  if (payload.image instanceof File) {
    formData.append('image', payload.image)
  }

  return formData
}

export const createPresidentConversation = async (clubId, equipeId, payload) => {
  return authPost(
    `/president/clubs/${clubId}/equipes/${equipeId}/canaux`,
    createConversationPayload(payload)
  )
}

export const fetchConversationParticipants = async (canalId) => {
  const response = await authGet(`/president/canaux/${canalId}/participants`)
  return response?.data?.participants || []
}

export const removeConversationParticipant = async (canalId, participantId) => {
  return authDelete(`/president/canaux/${canalId}/participants/${participantId}`)
}
