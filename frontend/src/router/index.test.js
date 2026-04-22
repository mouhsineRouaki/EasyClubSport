import { beforeEach, describe, expect, it } from 'vitest'
import { createAppRouter } from './index'

const initRouter = async (path) => {
  const router = createAppRouter()
  await router.push(path)
  await router.isReady()
  return router
}

describe('Router Guard', () => {
  beforeEach(() => {
    localStorage.removeItem('token_api')
    localStorage.removeItem('utilisateur_api')
  })

  it('redirige vers /login si route protegee sans token', async () => {
    const router = await initRouter('/president')

    expect(router.currentRoute.value.fullPath).toBe('/login')
  })

  it('autorise la route du role connecte', async () => {
    localStorage.setItem('token_api', 'token-test')
    localStorage.setItem('utilisateur_api', JSON.stringify({ role: 'president' }))

    const router = await initRouter('/president')

    expect(router.currentRoute.value.fullPath).toBe('/president')
  })

  it("redirige vers l'espace du role si l'utilisateur force une autre URL", async () => {
    localStorage.setItem('token_api', 'token-test')
    localStorage.setItem('utilisateur_api', JSON.stringify({ role: 'president' }))

    const router = await initRouter('/coach')

    expect(router.currentRoute.value.fullPath).toBe('/president')
  })
})
