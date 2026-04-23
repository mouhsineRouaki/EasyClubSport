<script setup>
import { computed, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import imageHero from '@/assets/hero.png'
import logoEasyClubSport from '@/assets/logo-easyclubsport.svg'
import AppButton from '@/shared/components/ui/AppButton.vue'
import AppField from '@/shared/components/ui/AppField.vue'
import AppStatusMessage from '@/shared/components/ui/AppStatusMessage.vue'
import { post } from '@/shared/services/apiClient'
import { sauvegarderToken, sauvegarderUtilisateurStocke } from '@/shared/session/sessionStorage'
import { notifyError } from '@/shared/services/toastService'

const router = useRouter()

const roles = [
  { label: 'President', value: 'president' },
  { label: 'Coach', value: 'coach' },
  { label: 'Joueur', value: 'joueur' },
]

const formulaire = reactive({
  nom: '',
  prenom: '',
  email: '',
  telephone: '',
  adresse: '',
  password: '',
  password_confirmation: '',
  role: 'joueur',
})

const chargement = ref(false)
const succes = ref('')
const token = ref('')
const utilisateur = ref(null)
const erreursValidation = ref({})

const motDePasseDifferent = computed(() => {
  if (!formulaire.password_confirmation) return false
  return formulaire.password !== formulaire.password_confirmation
})

const peutSoumettre = computed(() => !motDePasseDifferent.value && !chargement.value)

const lireErreur = (champ) => erreursValidation.value?.[champ]?.[0] || ''

const reinitialiserMessages = () => {
  succes.value = ''
  erreursValidation.value = {}
}

const soumettre = async () => {
  reinitialiserMessages()
  if (motDePasseDifferent.value) {
    notifyError('Les deux mots de passe ne sont pas identiques.')
    return
  }
  chargement.value = true
  try {
    const reponse = await post('/auth/inscription', { ...formulaire })
    succes.value = reponse.message || 'Compte cree avec succes.'
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
    erreursValidation.value = reponseErreur.data || {}
  } finally {
    chargement.value = false
  }
}
</script>

<template>
  <main class="ecs-auth-shell">
    <div class="ecs-auth-grid">
      <div class="ecs-auth-hero">
        <div class="pointer-events-none absolute -right-[60px] -top-[60px] h-[220px] w-[220px] rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-[40px] -left-[40px] h-[160px] w-[160px] rounded-full bg-white/10"></div>

        <div class="relative z-10 flex flex-col gap-5">
          <div class="flex items-center gap-3">
            <img :src="logoEasyClubSport" alt="Logo EasyClubSport" class="h-12 w-auto" />
              
          </div>

          <div class="mt-1">
            <h1 class="text-[2rem] leading-tight font-bold text-white">
              Gerez votre club<br />
              <span class="text-[#ccfbf1]">simplement.</span>
            </h1>
            <p class="mt-3 text-sm leading-relaxed text-white/70">
              Rejoignez des centaines de clubs qui font confiance a EasyClubSport pour organiser leurs equipes et leurs membres.
            </p>
          </div>
        </div>

        <div class="relative z-10">
          <img
            :src="imageHero"
            alt="Illustration sportive"
            class="max-h-[240px] w-full rounded-[28px] border border-white/15 object-cover"
          />
        </div>

        <div class="relative z-10 flex flex-wrap gap-2">
          <span class="ecs-chip border-white/15 bg-white/10 text-white">Football</span>
          <span class="ecs-chip border-white/15 bg-white/10 text-white">Basketball</span>
          <span class="ecs-chip border-white/15 bg-white/10 text-white">Tennis</span>
          <span class="ecs-chip border-white/15 bg-white/10 text-white">Natation</span>
        </div>
      </div>

      <div class="ecs-auth-form">
        <div>
          <p class="ecs-kicker">Inscription</p>
          <h2 class="m-0 mt-1.5 text-[1.75rem] font-bold text-[#172554]">Creer votre compte</h2>
          <p class="mt-1 text-sm text-[#64748b]">Remplissez les informations ci-dessous pour rejoindre votre club.</p>
        </div>

        <form class="flex flex-col gap-4" @submit.prevent="soumettre">
          <div class="grid gap-4 sm:grid-cols-2">
            <AppField label="Nom" :error="lireErreur('nom')">
              <input
                v-model="formulaire.nom"
                type="text"
                placeholder="Votre nom"
                class="ecs-input"
              />
            </AppField>
            <AppField label="Prenom" :error="lireErreur('prenom')">
              <input
                v-model="formulaire.prenom"
                type="text"
                placeholder="Votre prenom"
                class="ecs-input"
              />
            </AppField>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <AppField label="Email" :error="lireErreur('email')">
              <input
                v-model="formulaire.email"
                type="email"
                placeholder="exemple@email.com"
                class="ecs-input"
              />
            </AppField>
            <AppField label="Telephone" :error="lireErreur('telephone')">
              <input
                v-model="formulaire.telephone"
                type="tel"
                placeholder="06XXXXXXXX"
                class="ecs-input"
              />
            </AppField>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <AppField label="Adresse" :error="lireErreur('adresse')">
              <input
                v-model="formulaire.adresse"
                type="text"
                placeholder="Ville, quartier..."
                class="ecs-input"
              />
            </AppField>
            <AppField label="Role" :error="lireErreur('role')">
              <select
                v-model="formulaire.role"
                class="ecs-select"
              >
                <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
              </select>
            </AppField>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <AppField label="Mot de passe" :error="lireErreur('password')">
              <input
                v-model="formulaire.password"
                type="password"
                placeholder="********"
                class="ecs-input"
              />
            </AppField>
            <AppField
              label="Confirmation"
              :error="motDePasseDifferent ? 'Les mots de passe ne correspondent pas.' : ''"
            >
              <input
                v-model="formulaire.password_confirmation"
                type="password"
                placeholder="********"
                :class="[
                  'ecs-input',
                  motDePasseDifferent ? 'border-[#e11d48] focus:border-[#e11d48] focus:ring-[#e11d48]/10' : '',
                ]"
              />
            </AppField>
          </div>

          <AppStatusMessage v-if="succes" type="success">
            <div class="flex items-center gap-2">
              <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
              </svg>
              <p class="text-sm font-semibold">{{ succes }}</p>
            </div>
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
            {{ chargement ? 'Inscription en cours...' : 'Creer mon compte' }}
          </AppButton>

          <p class="text-center text-sm text-[#64748b]">
            Deja un compte ?
            <RouterLink to="/login" class="font-semibold text-[#1d4ed8] underline-offset-2 transition hover:text-[#14b8a6] hover:underline">Se connecter</RouterLink>
          </p>
        </form>
      </div>
    </div>
  </main>
</template>


