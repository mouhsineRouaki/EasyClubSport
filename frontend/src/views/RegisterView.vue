<script setup>
import { computed, reactive, ref } from 'vue'
import imageHero from '../assets/hero.png'
import logoEasyClubSport from '../assets/logo-easyclubsport.svg'
import { API_BASE_URL, post } from '../services/api'
import { notifyError } from '../stores/toast'

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
  } catch (error) {
    const reponseErreur = error.response || {}
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
    <div class="grid w-full max-w-5xl overflow-hidden rounded-[20px] shadow-2xl lg:grid-cols-[1fr_1.25fr]">
      <div class="relative flex flex-col justify-between gap-8 overflow-hidden bg-[linear-gradient(145deg,#0A075F_0%,#242565_45%,#3D37F1_100%)] p-8 text-white">
        <div class="pointer-events-none absolute -right-[60px] -top-[60px] h-[220px] w-[220px] rounded-full bg-[rgba(245,22,126,0.18)]"></div>
        <div class="pointer-events-none absolute -bottom-[40px] -left-[40px] h-[160px] w-[160px] rounded-full bg-[rgba(61,55,241,0.25)]"></div>

        <div class="relative z-10 flex flex-col gap-5">
          <div class="flex items-center gap-3">
            <img :src="logoEasyClubSport" alt="Logo EasyClubSport" class="h-12 w-auto" />
              
          </div>

          <div class="mt-1">
            <h1 class="text-[2rem] leading-tight font-bold text-white">
              Gerez votre club<br />
              <span class="text-[#F5167E]">simplement.</span>
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
            class="max-h-[240px] w-full rounded-[20px] border border-white/15 object-cover"
          />
        </div>

        <div class="relative z-10 flex flex-wrap gap-2">
          <span class="inline-block rounded-[100px] border border-white/20 bg-white/10 px-[14px] py-1 text-xs font-medium text-white/85">Football</span>
          <span class="inline-block rounded-[100px] border border-white/20 bg-white/10 px-[14px] py-1 text-xs font-medium text-white/85">Basketball</span>
          <span class="inline-block rounded-[100px] border border-white/20 bg-white/10 px-[14px] py-1 text-xs font-medium text-white/85">Tennis</span>
          <span class="inline-block rounded-[100px] border border-white/20 bg-white/10 px-[14px] py-1 text-xs font-medium text-white/85">Natation</span>
        </div>
      </div>

      <div class="flex flex-col justify-center gap-6 bg-white px-8 py-10 sm:px-10">
        <div>
          <p class="m-0 text-[11px] font-bold uppercase tracking-[0.15em] text-[#3D37F1]">Inscription</p>
          <h2 class="m-0 mt-1.5 text-[1.75rem] font-bold text-[#242565]">Creer votre compte</h2>
          <p class="mt-1 text-sm text-[#717275]">Remplissez les informations ci-dessous pour rejoindre votre club.</p>
        </div>

        <form class="flex flex-col gap-4" @submit.prevent="soumettre">
          <div class="grid gap-4 sm:grid-cols-2">
            <div class="flex flex-col gap-1.5">
              <label class="text-[11px] font-bold uppercase tracking-[0.1em] text-[#717275]">Nom</label>
              <input
                v-model="formulaire.nom"
                type="text"
                placeholder="Votre nom"
                class="w-full rounded-[10px] border-[1.5px] border-[#E2E4F0] bg-[#F2F4FF] px-4 py-[11px] text-sm text-[#242565] outline-none transition placeholder:text-[#A0A3B1] focus:border-[#3D37F1] focus:bg-white focus:ring-4 focus:ring-[rgba(61,55,241,0.12)]"
              />
              <span v-if="lireErreur('nom')" class="text-xs text-[#F5167E]">{{ lireErreur('nom') }}</span>
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-[11px] font-bold uppercase tracking-[0.1em] text-[#717275]">Prenom</label>
              <input
                v-model="formulaire.prenom"
                type="text"
                placeholder="Votre prenom"
                class="w-full rounded-[10px] border-[1.5px] border-[#E2E4F0] bg-[#F2F4FF] px-4 py-[11px] text-sm text-[#242565] outline-none transition placeholder:text-[#A0A3B1] focus:border-[#3D37F1] focus:bg-white focus:ring-4 focus:ring-[rgba(61,55,241,0.12)]"
              />
              <span v-if="lireErreur('prenom')" class="text-xs text-[#F5167E]">{{ lireErreur('prenom') }}</span>
            </div>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
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
              <label class="text-[11px] font-bold uppercase tracking-[0.1em] text-[#717275]">Telephone</label>
              <input
                v-model="formulaire.telephone"
                type="tel"
                placeholder="06XXXXXXXX"
                class="w-full rounded-[10px] border-[1.5px] border-[#E2E4F0] bg-[#F2F4FF] px-4 py-[11px] text-sm text-[#242565] outline-none transition placeholder:text-[#A0A3B1] focus:border-[#3D37F1] focus:bg-white focus:ring-4 focus:ring-[rgba(61,55,241,0.12)]"
              />
              <span v-if="lireErreur('telephone')" class="text-xs text-[#F5167E]">{{ lireErreur('telephone') }}</span>
            </div>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <div class="flex flex-col gap-1.5">
              <label class="text-[11px] font-bold uppercase tracking-[0.1em] text-[#717275]">Adresse</label>
              <input
                v-model="formulaire.adresse"
                type="text"
                placeholder="Ville, quartier..."
                class="w-full rounded-[10px] border-[1.5px] border-[#E2E4F0] bg-[#F2F4FF] px-4 py-[11px] text-sm text-[#242565] outline-none transition placeholder:text-[#A0A3B1] focus:border-[#3D37F1] focus:bg-white focus:ring-4 focus:ring-[rgba(61,55,241,0.12)]"
              />
              <span v-if="lireErreur('adresse')" class="text-xs text-[#F5167E]">{{ lireErreur('adresse') }}</span>
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-[11px] font-bold uppercase tracking-[0.1em] text-[#717275]">Role</label>
              <select
                v-model="formulaire.role"
                class="w-full rounded-[10px] border-[1.5px] border-[#E2E4F0] bg-[#F2F4FF] px-4 py-[11px] text-sm text-[#242565] outline-none transition focus:border-[#3D37F1] focus:bg-white focus:ring-4 focus:ring-[rgba(61,55,241,0.12)]"
              >
                <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
              </select>
              <span v-if="lireErreur('role')" class="text-xs text-[#F5167E]">{{ lireErreur('role') }}</span>
            </div>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
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
            <div class="flex flex-col gap-1.5">
              <label class="text-[11px] font-bold uppercase tracking-[0.1em] text-[#717275]">Confirmation</label>
              <input
                v-model="formulaire.password_confirmation"
                type="password"
                placeholder="********"
                :class="[
                  'w-full rounded-[10px] border-[1.5px] bg-[#F2F4FF] px-4 py-[11px] text-sm text-[#242565] outline-none transition placeholder:text-[#A0A3B1]',
                  motDePasseDifferent
                    ? 'border-[#F5167E] focus:border-[#F5167E] focus:bg-white focus:ring-4 focus:ring-[rgba(245,22,126,0.12)]'
                    : 'border-[#E2E4F0] focus:border-[#3D37F1] focus:bg-white focus:ring-4 focus:ring-[rgba(61,55,241,0.12)]',
                ]"
              />
              <span v-if="motDePasseDifferent" class="text-xs text-[#F5167E]">Les mots de passe ne correspondent pas.</span>
            </div>
          </div>

          <div v-if="succes" class="flex flex-col gap-2 rounded-[10px] border border-[rgba(7,238,18,0.3)] bg-[#F0FFF4] px-4 py-3 text-[#0A7A12]">
            <div class="flex items-center gap-2">
              <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
              </svg>
              <p class="text-sm font-semibold">{{ succes }}</p>
            </div>
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
            {{ chargement ? 'Inscription en cours...' : 'Creer mon compte' }}
          </button>

          <p class="text-center text-sm text-[#717275]">
            Deja un compte ?
            <RouterLink to="/login" class="font-semibold text-[#3D37F1] underline-offset-2 transition hover:text-[#F5167E] hover:underline">Se connecter</RouterLink>
          </p>
        </form>
      </div>
    </div>
  </main>
</template>
