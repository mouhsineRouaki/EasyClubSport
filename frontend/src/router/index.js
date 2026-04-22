import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import PresidentDashboardView from '../views/president/PresidentDashboardView.vue'
import CoachDashboardView from '../views/coach/CoachDashboardView.vue'
import JoueurDashboardView from '../views/joueur/JoueurDashboardView.vue'

const ROLE_HOME = {
  president: '/president',
  coach: '/coach',
  joueur: '/joueur',
}

const lireUtilisateur = () => {
  const utilisateurStocke = localStorage.getItem('utilisateur_api')

  if (!utilisateurStocke) {
    return null
  }

  try {
    return JSON.parse(utilisateurStocke)
  } catch {
    return null
  }
}

const routeRole = (role) => ROLE_HOME[role] || '/login'

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
    path: '/president',
    name: 'president',
    component: PresidentDashboardView,
    meta: {
      requiresAuth: true,
      role: 'president',
    },
  },
  {
    path: '/president/dashboard',
    redirect: '/president',
  },
  {
    path: '/coach',
    name: 'coach',
    component: CoachDashboardView,
    meta: {
      requiresAuth: true,
      role: 'coach',
    },
  },
  {
    path: '/coach/dashboard',
    redirect: '/coach',
  },
  {
    path: '/joueur',
    name: 'joueur',
    component: JoueurDashboardView,
    meta: {
      requiresAuth: true,
      role: 'joueur',
    },
  },
  {
    path: '/joueur/dashboard',
    redirect: '/joueur',
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

    const utilisateur = lireUtilisateur()
    const roleAttendu = to.meta.role

    if (!utilisateur?.role) {
      return '/login'
    }

    if (roleAttendu && utilisateur.role !== roleAttendu) {
      return routeRole(utilisateur.role)
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
