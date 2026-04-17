import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { API_BASE_URL } from './api'

let echoInstance = null

const lireToken = () => localStorage.getItem('token_api') || ''

const normaliserHost = (host) => {
  if (!host) {
    return 'localhost'
  }

  return String(host).replace(/^https?:\/\//, '').replace(/\/$/, '')
}

const endpointBroadcast = () => `${API_BASE_URL.replace(/\/$/, '')}/broadcasting/auth`

const creerEcho = () => {
  const token = lireToken()

  if (!token) {
    return null
  }

  if (!echoInstance) {
    window.Pusher = Pusher

    const scheme = import.meta.env.VITE_REVERB_SCHEME || 'http'
    const host = normaliserHost(import.meta.env.VITE_REVERB_HOST || 'localhost')
    const port = Number(import.meta.env.VITE_REVERB_PORT || (scheme === 'https' ? 443 : 8080))

    echoInstance = new Echo({
      broadcaster: 'reverb',
      key: import.meta.env.VITE_REVERB_APP_KEY || 'local',
      wsHost: host,
      wsPort: port,
      wssPort: port,
      forceTLS: scheme === 'https',
      enabledTransports: ['ws', 'wss'],
      authEndpoint: endpointBroadcast(),
      auth: {
        headers: {
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
      },
    })
  }

  return echoInstance
}

export const subscribeToTeamMessages = (teamId, callback) => {
  const echo = creerEcho()

  if (!echo || !teamId || typeof callback !== 'function') {
    return () => {}
  }

  const channelName = `equipe.${teamId}.messages`
  echo.private(channelName).listen('.message.envoye', callback)

  return () => {
    echo.leave(channelName)
  }
}

export const disconnectRealtime = () => {
  if (echoInstance) {
    echoInstance.disconnect()
    echoInstance = null
  }
}