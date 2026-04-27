import { notifyError } from '@/shared/services/toastService'
import { lireTokenStocke } from '@/shared/session/sessionStorage'

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

async function request(endpoint, options = {}) {
  const { method = 'GET', payload, authentifier = false } = options

  const headers = {
    Accept: 'application/json',
  }

  if (payload !== undefined && !(payload instanceof FormData)) {
    headers['Content-Type'] = 'application/json'
  }

  if (authentifier) {
    const token = lireTokenStocke()
    if (token) {
      headers.Authorization = `Bearer ${token}`
    }
  }

  const response = await fetch(`${API_BASE_URL}${endpoint}`, {
    method,
    headers,
    body: payload !== undefined && payload instanceof FormData ? payload : payload !== undefined ? JSON.stringify(payload) : undefined,
  })

  const body = await lireReponse(response)

  if (!response.ok) {
    const message = body.message || 'Erreur API'
    notifyError(message)

    const error = new Error(message)
    error.response = body
    throw error
  }

  return body
}

function withQuery(endpoint, query = {}) {
  const entries = Object.entries(query || {}).filter(([, value]) => value !== undefined && value !== null && value !== '')

  if (!entries.length) {
    return endpoint
  }

  const searchParams = new URLSearchParams()
  entries.forEach(([key, value]) => {
    searchParams.append(key, String(value))
  })

  const separator = endpoint.includes('?') ? '&' : '?'
  return `${endpoint}${separator}${searchParams.toString()}`
}

export async function post(endpoint, payload) {
  return request(endpoint, {
    method: 'POST',
    payload,
  })
}

export async function authGet(endpoint, query = {}) {
  return request(withQuery(endpoint, query), {
    method: 'GET',
    authentifier: true,
  })
}

export async function authPost(endpoint, payload) {
  return request(endpoint, {
    method: 'POST',
    payload,
    authentifier: true,
  })
}

export async function authPut(endpoint, payload) {
  if (payload instanceof FormData && !payload.has('_method')) {
    payload.append('_method', 'PUT')
  }

  return request(endpoint, {
    method: 'POST',
    payload: payload instanceof FormData ? payload : { ...payload, _method: 'PUT' },
    authentifier: true,
  })
}

export async function authDelete(endpoint) {
  return request(endpoint, {
    method: 'DELETE',
    authentifier: true,
  })
}

export { API_BASE_URL }
