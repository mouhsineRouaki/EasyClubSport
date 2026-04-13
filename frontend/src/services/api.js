const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL?.replace(/\/$/, '') || 'http://127.0.0.1:8000/api'

async function lireReponse(response) {
  const contentType = response.headers.get('content-type') || ''

  if (contentType.includes('application/json')) {
    return response.json()
  }

  const text = await response.text()

  return {
    status: false,
    message: text || 'Le serveur a renvoye une reponse non JSON.',
    data: null,
  }
}

export async function post(endpoint, payload) {
  const response = await fetch(`${API_BASE_URL}${endpoint}`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  })

  const body = await lireReponse(response)

  if (!response.ok) {
    const error = new Error(body.message || 'Erreur API')
    error.response = body
    throw error
  }

  return body
}

export { API_BASE_URL }
