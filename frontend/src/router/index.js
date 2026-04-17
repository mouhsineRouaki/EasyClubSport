import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import PresidentDashboardView from '../views/president/PresidentDashboardView.vue'
import PresidentProfilView from '../views/president/PresidentProfilView.vue'
import PresidentClubsView from '../views/president/PresidentClubsView.vue'
import PresidentEquipesView from '../views/president/PresidentEquipesView.vue'
import PresidentJoueursView from '../views/president/PresidentJoueursView.vue'
import PresidentEvenementsView from '../views/president/PresidentEvenementsView.vue'
import PresidentAnnoncesView from '../views/president/PresidentAnnoncesView.vue'
import PresidentDocumentsView from '../views/president/PresidentDocumentsView.vue'
import PresidentMessagerieView from '../views/president/PresidentMessagerieView.vue'
import CoachDashboardView from '../views/coach/CoachDashboardView.vue'
import CoachProfilView from '../views/coach/CoachProfilView.vue'
import CoachEquipesView from '../views/coach/CoachEquipesView.vue'
import CoachJoueursView from '../views/coach/CoachJoueursView.vue'
import CoachEvenementsView from '../views/coach/CoachEvenementsView.vue'
import CoachMessagerieView from '../views/coach/CoachMessagerieView.vue'

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
    path: '/president/profil',
    name: 'president-profil',
    component: PresidentProfilView,
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/president/clubs',
    name: 'president-clubs',
    component: PresidentClubsView,
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/president/equipes',
    name: 'president-equipes',
    component: PresidentEquipesView,
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/president/joueurs',
    name: 'president-joueurs',
    component: PresidentJoueursView,
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/president/evenements',
    name: 'president-evenements',
    component: PresidentEvenementsView,
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/president/annonces',
    name: 'president-annonces',
    component: PresidentAnnoncesView,
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/president/documents',
    name: 'president-documents',
    component: PresidentDocumentsView,
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/president/messagerie',
    name: 'president-messagerie',
    component: PresidentMessagerieView,
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
    path: '/coach/profil',
    name: 'coach-profil',
    component: CoachProfilView,
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/coach/equipes',
    name: 'coach-equipes',
    component: CoachEquipesView,
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/coach/joueurs',
    name: 'coach-joueurs',
    component: CoachJoueursView,
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/coach/evenements',
    name: 'coach-evenements',
    component: CoachEvenementsView,
    meta: {
      requiresAuth: true,
    },
  },
  {
    path: '/coach/messagerie',
    name: 'coach-messagerie',
    component: CoachMessagerieView,
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
