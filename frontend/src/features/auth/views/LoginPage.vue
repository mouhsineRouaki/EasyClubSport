<script setup>
import { computed, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import imageHero from '@/assets/hero.png'
import logoEasyClubSport from '@/assets/logo-easyclubsport.svg'
import AppButton from '@/shared/components/ui/AppButton.vue'
import AppField from '@/shared/components/ui/AppField.vue'
import AppStatusMessage from '@/shared/components/ui/AppStatusMessage.vue'
import { API_BASE_URL, post } from '@/shared/services/apiClient'
import { sauvegarderToken, sauvegarderUtilisateurStocke } from '@/shared/session/sessionStorage'
import { notifyError } from '@/shared/services/toastService'

const router = useRouter()

const formulaire = reactive({
  email: '',
  password: '',
})

const chargement = ref(false)
const succes = ref('')
const token = ref('')
const utilisateur = ref(null)
const erreursValidation = ref({})

const peutSoumettre = computed(() => {
  return formulaire.email.trim() !== '' && formulaire.password.trim() !== '' && !chargement.value
})

const lireErreur = (champ) => erreursValidation.value?.[champ]?.[0] || ''

const extraireErreursValidation = (reponseErreur) => {
  if (!reponseErreur) {
    return {}
  }

  if (reponseErreur.errors && typeof reponseErreur.errors === 'object') {
    return reponseErreur.errors
  }

  if (reponseErreur.data && typeof reponseErreur.data === 'object') {
    return reponseErreur.data
  }

  return {}
}

const reinitialiserMessages = () => {
  succes.value = ''
  erreursValidation.value = {}
}

const soumettre = async () => {
  reinitialiserMessages()
  chargement.value = true

  try {
    const reponse = await post('/auth/connexion', { ...formulaire })
    succes.value = reponse.message || 'Connexion reussie.'
    token.value = reponse?.data?.token || ''
    utilisateur.value = reponse?.data?.utilisateur || null

    sauvegarderToken(token.value)
    sauvegarderUtilisateurStocke(utilisateur.value)

    if (utilisateur.value?.role === 'president') {
      router.push('/president')
    } else if (utilisateur.value?.role === 'coach') {
      router.push('/coach')
    } else if (utilisateur.value?.role === 'joueur') {
      router.push('/joueur')
    } else {
      router.push('/')
    }
  } catch (error) {
    const reponseErreur = error.response || {}
    if (!reponseErreur?.message && error?.message) {
      notifyError(error.message)
    }
    erreursValidation.value = extraireErreursValidation(reponseErreur)
  } finally {
    chargement.value = false
  }
}
</script>

<template>
  <main class="ecs-auth-shell">
    <section class="ecs-auth-grid">
      <div class="ecs-auth-hero">
        <div class="pointer-events-none absolute -right-[60px] -top-[60px] h-[220px] w-[220px] rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-[40px] -left-[40px] h-[160px] w-[160px] rounded-full bg-white/10"></div>

        <div class="relative z-10 flex flex-col gap-5">
          <div class="flex items-center gap-3">
            <img :src="logoEasyClubSport" alt="Logo EasyClubSport" class="h-12 w-auto" />
          </div>

          <div class="mt-1">
            <h1 class="text-[2rem] leading-tight font-bold text-white">
              Connectez-vous<br />
              <span class="text-[#ccfbf1]">a votre espace club.</span>
            </h1>
            <p class="mt-3 text-sm leading-relaxed text-white/70">
              Accedez rapidement a vos equipes, vos evenements et vos messages depuis une interface centralisee.
            </p>
          </div>
        </div>

        <div class="relative z-10">
          <img :src="imageHero" alt="Illustration sportive" class="max-h-[240px] w-full rounded-[28px] border border-white/15 object-cover" />
        </div>

        <div class="relative z-10 flex flex-wrap gap-2">
          <span class="ecs-chip bg-white/10 text-white border-white/15">Dashboard</span>
          <span class="ecs-chip bg-white/10 text-white border-white/15">Equipes</span>
          <span class="ecs-chip bg-white/10 text-white border-white/15">Evenements</span>
          <span class="ecs-chip bg-white/10 text-white border-white/15">Messagerie</span>
        </div>
      </div>

      <div class="ecs-auth-form">
        <div>
          <p class="ecs-kicker">Connexion</p>
          <h2 class="mt-1.5 text-[1.75rem] font-bold text-[#172554]">Acceder a votre compte</h2>
          <p class="mt-1 text-sm text-[#64748b]">Entrez vos identifiants pour continuer.</p>
        </div>

        <form class="flex flex-col gap-4" @submit.prevent="soumettre">
          <AppField label="Email" :error="lireErreur('email')">
            <input
              v-model="formulaire.email"
              type="email"
              placeholder="exemple@email.com"
              class="ecs-input"
            />
          </AppField>

          <AppField label="Mot de passe" :error="lireErreur('password')">
            <input
              v-model="formulaire.password"
              type="password"
              placeholder="********"
              class="ecs-input"
            />
          </AppField>

          <AppStatusMessage v-if="succes" type="success">
            <p class="text-sm font-semibold">{{ succes }}</p>
            <p v-if="utilisateur" class="text-xs">Bienvenue {{ utilisateur.prenom || utilisateur.name || 'utilisateur' }}.</p>
            <p v-if="token" class="break-all font-mono text-xs opacity-80">Token : {{ token }}</p>
          </AppStatusMessage>

          <AppButton
            :disabled="!peutSoumettre"
            type="submit"
            size="lg"
            block
          >
            <svg v-if="chargement" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
            </svg>
            {{ chargement ? 'Connexion en cours...' : 'Se connecter' }}
          </AppButton>

          <p class="text-center text-sm text-[#64748b]">
            Pas encore de compte ?
            <RouterLink to="/register" class="font-semibold text-[#1d4ed8] underline-offset-2 transition hover:text-[#14b8a6] hover:underline">Creer un compte</RouterLink>
          </p>

          <p class="text-xs text-[#64748b]">
            Endpoint utilise :
            <code class="rounded-full bg-slate-100 px-2 py-0.5">{{ API_BASE_URL }}/auth/connexion</code>
          </p>
        </form>
      </div>
    </section>
  </main>
</template>


