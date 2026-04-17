import { describe, expect, it } from 'vitest'
import { createAppRouter } from './index'

const initRouter = async (path) => {
  const router = createAppRouter()
  await router.push(path)
  await router.isReady()
  return router
}

describe('Router Guard', () => {
  it('redirige vers /login si route protegee sans token', async () => {
    localStorage.removeItem('token_api')

    const router = await initRouter('/president/dashboard')

    expect(router.currentRoute.value.fullPath).toBe('/login')
  })

  it('autorise route protegee avec token', async () => {
    localStorage.setItem('token_api', 'token-test')

    const router = await initRouter('/president/dashboard')

    expect(router.currentRoute.value.fullPath).toBe('/president/dashboard')
  })
})