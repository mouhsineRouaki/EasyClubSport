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
  if (!formulaire.password_confirmation) return false
  return formulaire.password !== formulaire.password_confirmation
})

const resumeInscription = computed(() => {
  const fullName = `${formulaire.prenom} ${formulaire.nom}`.trim()
  return fullName || 'Nouveau membre'
})

const peutSoumettre = computed(() => !motDePasseDifferent.value && !chargement.value)

const lireErreur = (champ) => erreursValidation.value?.[champ]?.[0] || ''

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
    erreurGlobale.value = reponseErreur.message || error.message || "Une erreur est survenue pendant l'inscription."
    erreursValidation.value = reponseErreur.data || {}
  } finally {
    chargement.value = false
  }
}
</script>

<template>
  <main
    class="min-h-screen bg-[radial-gradient(circle_at_12%_10%,rgba(245,22,126,0.14),transparent_36%),radial-gradient(circle_at_88%_18%,rgba(61,55,241,0.16),transparent_38%),linear-gradient(180deg,#F2F4FF_0%,#EEF1FF_100%)] px-4 py-8 md:px-8 md:py-12"
  >
    <section
      class="mx-auto grid w-full max-w-7xl overflow-hidden rounded-[28px] border border-[#D8DDF8] bg-white shadow-[0_24px_80px_rgba(10,7,95,0.18)] lg:grid-cols-[0.95fr_1.05fr]"
    >
      <aside class="relative flex flex-col gap-6 overflow-hidden bg-[linear-gradient(155deg,#0A075F_0%,#242565_52%,#3D37F1_100%)] p-6 text-white sm:p-8">
        <div class="pointer-events-none absolute -right-14 -top-14 h-52 w-52 rounded-full bg-[#F5167E]/20 blur-sm"></div>
        <div class="pointer-events-none absolute -bottom-16 -left-12 h-44 w-44 rounded-full bg-[#3D37F1]/40 blur-sm"></div>

        <div class="relative z-10 flex items-start gap-3">
          <span
            class="grid h-12 w-12 place-items-center rounded-2xl border border-white/30 bg-white/10 text-sm font-extrabold tracking-[0.08em]"
          >
            ES
          </span>
          <div>
            <p class="m-0 text-xs font-bold uppercase tracking-[0.16em] text-white/70">EasyClubSport</p>
            <h1 class="mt-2 text-[1.95rem] leading-[1.1] font-bold sm:text-[2.2rem]">
              Inscription premium pour clubs et equipes
            </h1>
          </div>
        </div>

        <div class="relative z-10 overflow-hidden rounded-3xl border border-white/15">
          <img :src="imageHero" alt="Univers sportif" class="h-[220px] w-full object-cover sm:h-[280px]" />
          <div class="absolute inset-x-3 bottom-3 rounded-xl border border-white/20 bg-[#0A075F]/60 p-3 backdrop-blur-sm">
            <p class="text-xs font-bold uppercase tracking-[0.08em] text-[#FFD1E8]">Direction visuelle</p>
            <p class="mt-1 text-sm text-white/85">
              Palette evenementielle forte, contraste net et hierearchie claire.
            </p>
          </div>
        </div>

        <div class="relative z-10 grid gap-3 sm:grid-cols-2">
          <article class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3">
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-white/70">Role</p>
            <p class="mt-1 text-base font-semibold capitalize">{{ formulaire.role }}</p>
          </article>
          <article class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3">
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-white/70">Profil</p>
            <p class="mt-1 text-base font-semibold">{{ resumeInscription }}</p>
          </article>
        </div>
      </aside>

      <section class="bg-[linear-gradient(180deg,#FFFFFF_0%,#FCFCFF_100%)] p-6 sm:p-8 lg:p-10">
        <header class="mb-6">
          <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#3D37F1]">Inscription</p>
          <h2 class="mt-2 text-[2rem] leading-[1.1] font-bold text-[#242565] sm:text-[2.35rem]">Creer votre compte</h2>
          <p class="mt-2 text-sm text-[#717275]">Chaque section est separee pour offrir une experience plus claire.</p>
        </header>

        <form class="space-y-4" @submit.prevent="soumettre">
          <article class="rounded-2xl border border-[#E4E8FA] bg-white p-4 sm:p-5">
            <h3 class="mb-4 text-[11px] font-bold uppercase tracking-[0.14em] text-[#5C65A4]">1. Identite</h3>

            <div class="grid gap-4 sm:grid-cols-2">
              <label class="block space-y-2">
                <span class="text-xs font-semibold uppercase tracking-[0.08em] text-[#717275]">Nom</span>
                <input
                  v-model="formulaire.nom"
                  type="text"
                  placeholder="Entrer votre nom"
                  class="w-full rounded-xl border border-[#DCE1F4] bg-[#F2F4FF] px-4 py-3 text-sm text-[#242565] outline-none transition focus:border-[#3D37F1] focus:bg-white focus:ring-4 focus:ring-[#3D37F1]/15"
                />
                <small v-if="lireErreur('nom')" class="text-xs text-[#F5167E]">{{ lireErreur('nom') }}</small>
              </label>

              <label class="block space-y-2">
                <span class="text-xs font-semibold uppercase tracking-[0.08em] text-[#717275]">Prenom</span>
                <input
                  v-model="formulaire.prenom"
                  type="text"
                  placeholder="Entrer votre prenom"
                  class="w-full rounded-xl border border-[#DCE1F4] bg-[#F2F4FF] px-4 py-3 text-sm text-[#242565] outline-none transition focus:border-[#3D37F1] focus:bg-white focus:ring-4 focus:ring-[#3D37F1]/15"
                />
                <small v-if="lireErreur('prenom')" class="text-xs text-[#F5167E]">{{ lireErreur('prenom') }}</small>
              </label>
            </div>

            <label class="mt-4 block space-y-2">
              <span class="text-xs font-semibold uppercase tracking-[0.08em] text-[#717275]">Role</span>
              <select
                v-model="formulaire.role"
                class="w-full rounded-xl border border-[#DCE1F4] bg-[#F2F4FF] px-4 py-3 text-sm text-[#242565] outline-none transition focus:border-[#3D37F1] focus:bg-white focus:ring-4 focus:ring-[#3D37F1]/15"
              >
                <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
              </select>
              <small v-if="lireErreur('role')" class="text-xs text-[#F5167E]">{{ lireErreur('role') }}</small>
            </label>
          </article>

          <article class="rounded-2xl border border-[#E4E8FA] bg-white p-4 sm:p-5">
            <h3 class="mb-4 text-[11px] font-bold uppercase tracking-[0.14em] text-[#5C65A4]">2. Contact</h3>

            <div class="grid gap-4 sm:grid-cols-2">
              <label class="block space-y-2">
                <span class="text-xs font-semibold uppercase tracking-[0.08em] text-[#717275]">Email</span>
                <input
                  v-model="formulaire.email"
                  type="email"
                  placeholder="exemple@email.com"
                  class="w-full rounded-xl border border-[#DCE1F4] bg-[#F2F4FF] px-4 py-3 text-sm text-[#242565] outline-none transition focus:border-[#3D37F1] focus:bg-white focus:ring-4 focus:ring-[#3D37F1]/15"
                />
                <small v-if="lireErreur('email')" class="text-xs text-[#F5167E]">{{ lireErreur('email') }}</small>
              </label>

              <label class="block space-y-2">
                <span class="text-xs font-semibold uppercase tracking-[0.08em] text-[#717275]">Telephone</span>
                <input
                  v-model="formulaire.telephone"
                  type="text"
                  placeholder="06XXXXXXXX"
                  class="w-full rounded-xl border border-[#DCE1F4] bg-[#F2F4FF] px-4 py-3 text-sm text-[#242565] outline-none transition focus:border-[#3D37F1] focus:bg-white focus:ring-4 focus:ring-[#3D37F1]/15"
                />
                <small v-if="lireErreur('telephone')" class="text-xs text-[#F5167E]">{{ lireErreur('telephone') }}</small>
              </label>
            </div>

            <label class="mt-4 block space-y-2">
              <span class="text-xs font-semibold uppercase tracking-[0.08em] text-[#717275]">Adresse</span>
              <input
                v-model="formulaire.adresse"
                type="text"
                placeholder="Ville, quartier, adresse"
                class="w-full rounded-xl border border-[#DCE1F4] bg-[#F2F4FF] px-4 py-3 text-sm text-[#242565] outline-none transition focus:border-[#3D37F1] focus:bg-white focus:ring-4 focus:ring-[#3D37F1]/15"
              />
              <small v-if="lireErreur('adresse')" class="text-xs text-[#F5167E]">{{ lireErreur('adresse') }}</small>
            </label>
          </article>

          <article class="rounded-2xl border border-dashed border-[#D4DAF5] bg-white p-4 sm:p-5">
            <h3 class="mb-4 text-[11px] font-bold uppercase tracking-[0.14em] text-[#5C65A4]">3. Securite</h3>

            <div class="grid gap-4 sm:grid-cols-2">
              <label class="block space-y-2">
                <span class="text-xs font-semibold uppercase tracking-[0.08em] text-[#717275]">Mot de passe</span>
                <input
                  v-model="formulaire.password"
                  type="password"
                  placeholder="Mot de passe"
                  class="w-full rounded-xl border border-[#DCE1F4] bg-[#F2F4FF] px-4 py-3 text-sm text-[#242565] outline-none transition focus:border-[#3D37F1] focus:bg-white focus:ring-4 focus:ring-[#3D37F1]/15"
                />
                <small v-if="lireErreur('password')" class="text-xs text-[#F5167E]">{{ lireErreur('password') }}</small>
              </label>

              <label class="block space-y-2">
                <span class="text-xs font-semibold uppercase tracking-[0.08em] text-[#717275]">Confirmation</span>
                <input
                  v-model="formulaire.password_confirmation"
                  type="password"
                  placeholder="Confirmer le mot de passe"
                  :class="[
                    'w-full rounded-xl border bg-[#F2F4FF] px-4 py-3 text-sm text-[#242565] outline-none transition focus:bg-white focus:ring-4',
                    motDePasseDifferent
                      ? 'border-[#F5167E] focus:border-[#F5167E] focus:ring-[#F5167E]/15'
                      : 'border-[#DCE1F4] focus:border-[#3D37F1] focus:ring-[#3D37F1]/15',
                  ]"
                />
                <small v-if="lireErreur('password_confirmation')" class="text-xs text-[#F5167E]">
                  {{ lireErreur('password_confirmation') }}
                </small>
              </label>
            </div>

            <p v-if="motDePasseDifferent" class="mt-3 text-xs font-semibold text-[#F5167E]">
              Les deux mots de passe ne sont pas identiques.
            </p>
          </article>

          <p
            v-if="erreurGlobale"
            class="rounded-xl border border-[#FFD0E6] bg-[#FFF3F9] px-4 py-3 text-sm text-[#A80A58]"
          >
            {{ erreurGlobale }}
          </p>

          <div
            v-if="succes"
            class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
          >
            <p class="font-semibold">{{ succes }}</p>
            <p v-if="token" class="mt-1 break-all font-mono text-xs opacity-80">Token : {{ token }}</p>
          </div>

          <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
            <button
              :disabled="!peutSoumettre"
              type="submit"
              :class="[
                'rounded-full px-5 py-3 text-sm font-bold text-white transition',
                peutSoumettre
                  ? 'bg-[linear-gradient(135deg,#F5167E_0%,#3D37F1_100%)] shadow-[0_12px_28px_rgba(245,22,126,0.28)] hover:-translate-y-[1px]'
                  : 'cursor-not-allowed bg-[#C9CDE4]',
              ]"
            >
              {{ chargement ? 'Inscription en cours...' : 'Creer mon compte' }}
            </button>

            <button
              type="button"
              class="rounded-full border border-[#D5DAF4] bg-white px-5 py-3 text-sm font-semibold text-[#242565] transition hover:border-[#B9C1EB]"
            >
              J'ai deja un compte
            </button>
          </div>

          <p class="text-xs text-[#717275]">
            Endpoint utilise :
            <code class="rounded-md bg-[#EEF1FF] px-1.5 py-0.5 text-[#242565]">{{ API_BASE_URL }}/auth/inscription</code>
          </p>
        </form>
      </section>
    </section>
  </main>
</template>
