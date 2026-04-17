import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import PresidentDashboardView from '../views/president/PresidentDashboardView.vue'
import CoachDashboardView from '../views/coach/CoachDashboardView.vue'

const routes = [
  {
    path: '/',
    redirect: '/login',
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterView,
  },
  {
    path: '/president/dashboard',
    name: 'president-dashboard',
    component: PresidentDashboardView,
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/coach/dashboard',
    name: 'coach-dashboard',
    component: CoachDashboardView,
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: {
      template:
        '<main class="min-h-screen grid place-items-center bg-slate-50 p-6"><div class="text-center"><h1 class="text-4xl font-bold text-slate-900">404</h1><p class="mt-2 text-slate-600">Page introuvable.</p><a href="/login" class="mt-5 inline-block rounded-full bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white">Retour a la connexion</a></div></main>',
    },
  },
]

const registerAuthGuard = (router) => {
  router.beforeEach((to) => {
    if (!to.meta.requiresAuth) {
      return true
    }

    const token = localStorage.getItem('token_api')
    if (!token) {
      return '/login'
    }

    return true
  })

  return router
}

export const createAppRouter = () =>
  registerAuthGuard(
    createRouter({
      history: createWebHistory(),
      routes,
    })
  )

const router = createAppRouter()

export default router
