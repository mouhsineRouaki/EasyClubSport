import { reactive } from 'vue'

const toasts = reactive([])
let seed = 0

export function pushToast(message, options = {}) {
  if (!message) {
    return
  }

  const id = ++seed
  const type = options.type || 'error'
  const duration = options.duration ?? 5000

  toasts.push({
    id,
    message,
    type,
  })

  if (duration > 0) {
    setTimeout(() => {
      removeToast(id)
    }, duration)
  }
}

export function removeToast(id) {
  const index = toasts.findIndex((toast) => toast.id === id)

  if (index >= 0) {
    toasts.splice(index, 1)
  }
}

export function notifyError(message, duration = 5000) {
  pushToast(message, { type: 'error', duration })
}

export function notifySuccess(message, duration = 5000) {
  pushToast(message, { type: 'success', duration })
}

export function useToasts() {
  return {
    toasts,
    removeToast,
  }
}
