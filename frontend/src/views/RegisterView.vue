<script setup>
import { computed, reactive, ref } from 'vue'
import imageHero from '../assets/hero.png'
import { API_BASE_URL, post } from '../services/api'

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
const erreurGlobale = ref('')
const succes = ref('')
const token = ref('')
const erreursValidation = ref({})

const motDePasseDifferent = computed(() => {
  if (!formulaire.password_confirmation) {
    return false
  }

  return formulaire.password !== formulaire.password_confirmation
})

const resumeInscription = computed(() => ({
  nom: formulaire.nom,
  prenom: formulaire.prenom,
  email: formulaire.email,
  telephone: formulaire.telephone,
  adresse: formulaire.adresse,
  role: formulaire.role,
}))

const peutSoumettre = computed(() => {
  return !motDePasseDifferent.value && !chargement.value
})

const lireErreur = (champ) => {
  return erreursValidation.value?.[champ]?.[0] || ''
}

const reinitialiserMessages = () => {
  erreurGlobale.value = ''
  succes.value = ''
  erreursValidation.value = {}
}

const soumettre = async () => {
  reinitialiserMessages()

  if (motDePasseDifferent.value) {
    erreurGlobale.value = 'Les deux mots de passe ne sont pas identiques.'
    return
  }

  chargement.value = true

  try {
    const reponse = await post('/auth/inscription', { ...formulaire })

    succes.value = reponse.message || 'Compte cree avec succes.'
    token.value = reponse?.data?.token || ''
  } catch (error) {
    const reponseErreur = error.response || {}
    erreurGlobale.value = reponseErreur.message || error.message || 'Une erreur est survenue pendant l inscription.'
    erreursValidation.value = reponseErreur.data || {}
  } finally {
    chargement.value = false
  }
}
</script>

<template>
  <main class="flex min-h-screen items-center justify-center p-4 sm:p-8">
    <section class="grid min-h-[760px] w-full max-w-6xl overflow-hidden rounded-[36px] border border-white/70 bg-white/80 shadow-[0_25px_60px_rgba(15,23,42,0.18)] backdrop-blur-[14px] lg:grid-cols-[1.05fr_0.95fr]">
      <div class="flex flex-col gap-6 bg-[linear-gradient(160deg,#0f172a_0%,#172554_52%,#1d4ed8_100%)] p-6 text-slate-50 sm:p-8">
        <div class="flex items-start gap-4">
          <span class="grid h-14 w-14 place-items-center rounded-[18px] border border-white/20 bg-white/10 font-bold">ES</span>
          <div>
            <p class="m-0 text-sm uppercase tracking-[0.08em] text-white/75">EasyClubSport</p>
            <h1 class="mt-1.5 text-[2.2rem] leading-[1.1] font-semibold">Construire votre club de sport avec une base solide</h1>
          </div>
        </div>

        <img :src="imageHero" alt="Illustration sportive" class="min-h-[360px] w-full flex-1 rounded-[28px] border border-white/15 object-cover" />

        <div class="rounded-[24px] border border-white/10 bg-slate-900/30 px-5 py-4">
          <strong class="block">Etape actuelle</strong>
          <p class="mt-1.5 text-white/80">
            Nous commencons par une page register claire, avec les champs relies a Vue.
          </p>
        </div>
      </div>

      <div class="flex flex-col justify-center gap-6 p-6 sm:p-10">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.08em] text-blue-600">Inscription</p>
          <h2 class="mt-2.5 text-[2.4rem] leading-[1.1] font-semibold text-slate-900">Creer un compte</h2>
          <p class="mt-2.5 text-slate-500">
            Cette vue consomme maintenant l API Laravel pour creer un compte reel.
          </p>
        </div>

        <form class="flex flex-col gap-[18px]" @submit.prevent="soumettre">
          <div class="grid gap-4 md:grid-cols-2">
            <label class="flex flex-col gap-2">
              <span class="text-sm font-semibold text-slate-700">Nom</span>
              <input
                v-model="formulaire.nom"
                type="text"
                placeholder="Entrer votre nom"
                class="w-full rounded-[18px] border border-slate-200 bg-white/95 px-4 py-3.5 outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-600/12"
              />
              <span v-if="lireErreur('nom')" class="text-sm text-red-600">{{ lireErreur('nom') }}</span>
            </label>

            <label class="flex flex-col gap-2">
              <span class="text-sm font-semibold text-slate-700">Prenom</span>
              <input
                v-model="formulaire.prenom"
                type="text"
                placeholder="Entrer votre prenom"
                class="w-full rounded-[18px] border border-slate-200 bg-white/95 px-4 py-3.5 outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-600/12"
              />
              <span v-if="lireErreur('prenom')" class="text-sm text-red-600">{{ lireErreur('prenom') }}</span>
            </label>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <label class="flex flex-col gap-2">
              <span class="text-sm font-semibold text-slate-700">Email</span>
              <input
                v-model="formulaire.email"
                type="email"
                placeholder="exemple@email.com"
                class="w-full rounded-[18px] border border-slate-200 bg-white/95 px-4 py-3.5 outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-600/12"
              />
              <span v-if="lireErreur('email')" class="text-sm text-red-600">{{ lireErreur('email') }}</span>
            </label>

            <label class="flex flex-col gap-2">
              <span class="text-sm font-semibold text-slate-700">Telephone</span>
              <input
                v-model="formulaire.telephone"
                type="text"
                placeholder="06XXXXXXXX"
                class="w-full rounded-[18px] border border-slate-200 bg-white/95 px-4 py-3.5 outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-600/12"
              />
              <span v-if="lireErreur('telephone')" class="text-sm text-red-600">{{ lireErreur('telephone') }}</span>
            </label>
          </div>

          <label class="flex flex-col gap-2">
            <span class="text-sm font-semibold text-slate-700">Adresse</span>
            <input
              v-model="formulaire.adresse"
              type="text"
              placeholder="Ville, quartier, adresse"
              class="w-full rounded-[18px] border border-slate-200 bg-white/95 px-4 py-3.5 outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-600/12"
            />
            <span v-if="lireErreur('adresse')" class="text-sm text-red-600">{{ lireErreur('adresse') }}</span>
          </label>

          <label class="flex flex-col gap-2">
            <span class="text-sm font-semibold text-slate-700">Role</span>
            <select
              v-model="formulaire.role"
              class="w-full rounded-[18px] border border-slate-200 bg-white/95 px-4 py-3.5 outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-600/12"
            >
              <option v-for="role in roles" :key="role.value" :value="role.value">
                {{ role.label }}
              </option>
            </select>
            <span v-if="lireErreur('role')" class="text-sm text-red-600">{{ lireErreur('role') }}</span>
          </label>

          <div class="grid gap-4 md:grid-cols-2">
            <label class="flex flex-col gap-2">
              <span class="text-sm font-semibold text-slate-700">Mot de passe</span>
              <input
                v-model="formulaire.password"
                type="password"
                placeholder="Mot de passe"
                class="w-full rounded-[18px] border border-slate-200 bg-white/95 px-4 py-3.5 outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-600/12"
              />
              <span v-if="lireErreur('password')" class="text-sm text-red-600">{{ lireErreur('password') }}</span>
            </label>

            <label class="flex flex-col gap-2">
              <span class="text-sm font-semibold text-slate-700">Confirmation du mot de passe</span>
              <input
                v-model="formulaire.password_confirmation"
                type="password"
                placeholder="Confirmer le mot de passe"
                class="w-full rounded-[18px] border border-slate-200 bg-white/95 px-4 py-3.5 outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-600/12"
              />
              <span v-if="lireErreur('password_confirmation')" class="text-sm text-red-600">{{ lireErreur('password_confirmation') }}</span>
            </label>
          </div>

          <p v-if="motDePasseDifferent" class="-mt-1 text-sm text-red-600">
            Les deux mots de passe ne sont pas identiques.
          </p>

          <p v-if="erreurGlobale" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ erreurGlobale }}
          </p>

          <div v-if="succes" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <p class="m-0 font-semibold">{{ succes }}</p>
            <p class="mt-2 mb-0 break-all" v-if="token">
              Token recu : <span class="font-mono text-[12px]">{{ token }}</span>
            </p>
          </div>

          <button
            :disabled="!peutSoumettre"
            class="cursor-pointer rounded-full bg-[linear-gradient(135deg,#111827_0%,#1f2937_100%)] px-[18px] py-[15px] font-bold text-white transition hover:opacity-95"
            type="submit"
          >
            {{ chargement ? 'Inscription en cours...' : 'Continuer' }}
          </button>
        </form>

        <section class="rounded-[24px] border border-slate-200 bg-slate-50 p-5">
          <div class="flex items-center justify-between gap-3">
            <h3 class="m-0 text-lg font-semibold text-slate-900">Ce que Vue fait ici</h3>
            <span class="text-sm font-bold text-blue-600">Etape 1</span>
          </div>

          <ul class="my-4 list-disc pl-[18px] text-slate-600">
            <li>Chaque input est lie a `formulaire` avec `v-model`.</li>
            <li>La verification du mot de passe utilise `computed`.</li>
            <li>Le bouton submit declenche `soumettre()` sans recharger la page.</li>
            <li>La fonction `post()` envoie les donnees vers `{{ API_BASE_URL }}/auth/inscription`.</li>
            <li>Les messages d erreur et de succes viennent maintenant de la vraie API.</li>
          </ul>

          <pre class="m-0 overflow-auto rounded-2xl bg-slate-900 p-3.5 text-[13px] text-slate-200">{{ JSON.stringify(resumeInscription, null, 2) }}</pre>
        </section>
      </div>
    </section>
  </main>
</template>
