import { beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import PresidentClubsView from './PresidentClubsView.vue'
import { createAppRouter } from '@/router/index.js'
import { authDelete, authGet, authPost } from '@/shared/services/apiClient'

vi.mock('@/shared/services/apiClient', () => ({
  authGet: vi.fn(),
  authPost: vi.fn(),
  authPut: vi.fn(),
  authDelete: vi.fn(),
}))

vi.mock('@/shared/services/toastService', () => ({
  notifyError: vi.fn(),
  notifySuccess: vi.fn(),
}))

const pagination = {
  current_page: 1,
  last_page: 1,
  per_page: 12,
  total: 1,
  from: 1,
  to: 1,
}

const mountView = async () => {
  const router = createAppRouter()
  localStorage.setItem('token_api', 'token-test')
  localStorage.setItem('utilisateur_api', JSON.stringify({ id: 1, prenom: 'Ali', nom: 'President', role: 'president' }))

  await router.push('/president')
  await router.isReady()

  const wrapper = mount(PresidentClubsView, {
    global: {
      plugins: [router],
      stubs: {
        PresidentShellLayout: {
          template: '<div><slot /></div>',
        },
      },
    },
  })

  await flushPromises()
  return wrapper
}

describe('PresidentClubsView', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    window.confirm = vi.fn(() => true)
  })

  it('charge la liste des clubs au montage', async () => {
    authGet.mockResolvedValueOnce({
      data: {
        clubs: [
          {
            id: 1,
            nom: 'Club Atlas',
            ville: 'Casablanca',
            pays: 'Maroc',
            email: 'atlas@test.com',
            telephone: '0600000000',
            description: 'Club test',
            logo_url: null,
          },
        ],
        pagination,
      },
    })

    const wrapper = await mountView()

    expect(authGet).toHaveBeenCalledWith('/president/clubs', { q: '', page: 1, per_page: 12 })
    expect(wrapper.text()).toContain('Club Atlas')
  })

  it('cree un club puis recharge la liste', async () => {
    authGet
      .mockResolvedValueOnce({ data: { clubs: [], pagination: { ...pagination, total: 0, from: 0, to: 0 } } })
      .mockResolvedValueOnce({
        data: {
          clubs: [
            {
              id: 2,
              nom: 'Nouveau Club',
              ville: null,
              pays: null,
              email: null,
              telephone: null,
              description: null,
              logo_url: null,
            },
          ],
          pagination,
        },
      })

    authPost.mockResolvedValueOnce({ message: 'Club cree avec succes.' })

    const wrapper = await mountView()

    wrapper.vm.ouvrirCreation()
    wrapper.vm.formulaire.nom = 'Nouveau Club'

    await wrapper.vm.enregistrerClub()
    await flushPromises()

    expect(authPost).toHaveBeenCalledTimes(1)
    expect(authGet).toHaveBeenCalledTimes(2)
    expect(wrapper.vm.clubs[0].nom).toBe('Nouveau Club')
  })

  it('supprime un club puis recharge la liste', async () => {
    authGet
      .mockResolvedValueOnce({
        data: {
          clubs: [
            {
              id: 1,
              nom: 'Club Atlas',
              ville: null,
              pays: null,
              email: null,
              telephone: null,
              description: null,
              logo_url: null,
            },
          ],
          pagination,
        },
      })
      .mockResolvedValueOnce({ data: { clubs: [], pagination: { ...pagination, total: 0, from: 0, to: 0 } } })

    authDelete.mockResolvedValueOnce({ message: 'Club supprime avec succes.' })

    const wrapper = await mountView()

    await wrapper.vm.supprimerClub({ id: 1, nom: 'Club Atlas' })
    await flushPromises()

    expect(window.confirm).toHaveBeenCalled()
    expect(authDelete).toHaveBeenCalledWith('/president/clubs/1')
    expect(authGet).toHaveBeenCalledTimes(2)
  })
})
