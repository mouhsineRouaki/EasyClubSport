<script setup>
import { computed, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import imageHero from '../assets/hero.png'
import logoEasyClubSport from '../assets/logo-easyclubsport.svg'
import { API_BASE_URL, post } from '../services/api'
import { notifyError } from '../stores/toast'

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

    if (token.value) {
      localStorage.setItem('token_api', token.value)
    }

    if (utilisateur.value) {
      localStorage.setItem('utilisateur_api', JSON.stringify(utilisateur.value))
    }

    if (utilisateur.value?.role === 'president') {
      router.push('/president/dashboard')
    }
  } catch (error) {
    const reponseErreur = error.response || {}
    if (!reponseErreur?.message && error?.message) {
      notifyError(error.message)
    }
    erreursValidation.value = reponseErreur.data || {}
  } finally {
    chargement.value = false
  }
}
</script>

<template>
  <main
    class="flex min-h-screen items-center justify-center bg-[#F2F4FF] bg-[radial-gradient(circle_at_15%_20%,rgba(61,55,241,0.12)_0%,transparent_40%),radial-gradient(circle_at_85%_80%,rgba(245,22,126,0.08)_0%,transparent_40%)] px-4 py-12"
  >
    <section class="grid w-full max-w-5xl overflow-hidden rounded-[20px] shadow-2xl lg:grid-cols-[1fr_1.25fr]">
      <div class="relative flex flex-col justify-between gap-8 overflow-hidden bg-[linear-gradient(145deg,#0A075F_0%,#242565_45%,#3D37F1_100%)] p-8 text-white">
        <div class="pointer-events-none absolute -right-[60px] -top-[60px] h-[220px] w-[220px] rounded-full bg-[rgba(245,22,126,0.18)]"></div>
        <div class="pointer-events-none absolute -bottom-[40px] -left-[40px] h-[160px] w-[160px] rounded-full bg-[rgba(61,55,241,0.25)]"></div>

        <div class="relative z-10 flex flex-col gap-5">
          <div class="flex items-center gap-3">
            <img :src="logoEasyClubSport" alt="Logo EasyClubSport" class="h-12 w-auto" />
          </div>

          <div class="mt-1">
            <h1 class="text-[2rem] leading-tight font-bold text-white">
              Connectez-vous<br />
              <span class="text-[#F5167E]">a votre espace club.</span>
            </h1>
            <p class="mt-3 text-sm leading-relaxed text-white/70">
              Accedez rapidement a vos equipes, vos evenements et vos messages depuis une interface centralisee.
            </p>
          </div>
        </div>

        <div class="relative z-10">
          <img :src="imageHero" alt="Illustration sportive" class="max-h-[240px] w-full rounded-[20px] border border-white/15 object-cover" />
        </div>

        <div class="relative z-10 flex flex-wrap gap-2">
          <span class="inline-block rounded-[100px] border border-white/20 bg-white/10 px-[14px] py-1 text-xs font-medium text-white/85">Dashboard</span>
          <span class="inline-block rounded-[100px] border border-white/20 bg-white/10 px-[14px] py-1 text-xs font-medium text-white/85">Equipes</span>
          <span class="inline-block rounded-[100px] border border-white/20 bg-white/10 px-[14px] py-1 text-xs font-medium text-white/85">Evenements</span>
          <span class="inline-block rounded-[100px] border border-white/20 bg-white/10 px-[14px] py-1 text-xs font-medium text-white/85">Messagerie</span>
        </div>
      </div>

      <div class="flex flex-col justify-center gap-6 bg-white px-8 py-10 sm:px-10">
        <div>
          <p class="m-0 text-[11px] font-bold uppercase tracking-[0.15em] text-[#3D37F1]">Connexion</p>
          <h2 class="m-0 mt-1.5 text-[1.75rem] font-bold text-[#242565]">Acceder a votre compte</h2>
          <p class="mt-1 text-sm text-[#717275]">Entrez vos identifiants pour continuer.</p>
        </div>

        <form class="flex flex-col gap-4" @submit.prevent="soumettre">
          <div class="flex flex-col gap-1.5">
            <label class="text-[11px] font-bold uppercase tracking-[0.1em] text-[#717275]">Email</label>
            <input
              v-model="formulaire.email"
              type="email"
              placeholder="exemple@email.com"
              class="w-full rounded-[10px] border-[1.5px] border-[#E2E4F0] bg-[#F2F4FF] px-4 py-[11px] text-sm text-[#242565] outline-none transition placeholder:text-[#A0A3B1] focus:border-[#3D37F1] focus:bg-white focus:ring-4 focus:ring-[rgba(61,55,241,0.12)]"
            />
            <span v-if="lireErreur('email')" class="text-xs text-[#F5167E]">{{ lireErreur('email') }}</span>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-[11px] font-bold uppercase tracking-[0.1em] text-[#717275]">Mot de passe</label>
            <input
              v-model="formulaire.password"
              type="password"
              placeholder="********"
              class="w-full rounded-[10px] border-[1.5px] border-[#E2E4F0] bg-[#F2F4FF] px-4 py-[11px] text-sm text-[#242565] outline-none transition placeholder:text-[#A0A3B1] focus:border-[#3D37F1] focus:bg-white focus:ring-4 focus:ring-[rgba(61,55,241,0.12)]"
            />
            <span v-if="lireErreur('password')" class="text-xs text-[#F5167E]">{{ lireErreur('password') }}</span>
          </div>

          <div v-if="succes" class="flex flex-col gap-2 rounded-[10px] border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
            <p class="text-sm font-semibold">{{ succes }}</p>
            <p v-if="utilisateur" class="text-xs">Bienvenue {{ utilisateur.prenom || utilisateur.name || 'utilisateur' }}.</p>
            <p v-if="token" class="break-all font-mono text-xs opacity-80">Token : {{ token }}</p>
          </div>

          <button
            :disabled="!peutSoumettre"
            type="submit"
            :class="[
              'mt-1 flex w-full items-center justify-center gap-2 rounded-[100px] py-3.5 text-sm font-bold text-white transition-all',
              peutSoumettre
                ? 'cursor-pointer border-0 bg-[linear-gradient(135deg,#F5167E_0%,#3D37F1_100%)] shadow-[0_8px_24px_rgba(245,22,126,0.3)] hover:-translate-y-px hover:bg-[linear-gradient(135deg,#d4106c_0%,#2d28d4_100%)] hover:shadow-[0_10px_28px_rgba(245,22,126,0.4)]'
                : 'cursor-not-allowed bg-[#E2E4F0] text-[#A0A3B1] shadow-none',
            ]"
          >
            <svg v-if="chargement" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
            </svg>
            {{ chargement ? 'Connexion en cours...' : 'Se connecter' }}
          </button>

          <p class="text-center text-sm text-[#717275]">
            Pas encore de compte ?
            <RouterLink to="/register" class="font-semibold text-[#3D37F1] underline-offset-2 transition hover:text-[#F5167E] hover:underline">Creer un compte</RouterLink>
          </p>

          <p class="text-xs text-[#717275]">
            Endpoint utilise :
            <code class="rounded bg-slate-100 px-1.5 py-0.5">{{ API_BASE_URL }}/auth/connexion</code>
          </p>
        </form>
      </div>
    </section>
  </main>
</template>


