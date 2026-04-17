<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import CoachShellLayout from '../../components/coach/CoachShellLayout.vue'
import { useStoredUser } from '../../composables/useStoredUser'
import { authGet, authPut } from '../../services/api'
import { notifyError, notifySuccess } from '../../stores/toast'

const router = useRouter()
const { utilisateur, chargerUtilisateur } = useStoredUser()
const chargement = ref(true)
const envoi = ref(false)
const photoFichier = ref(null)
const photoPreview = ref('')
const erreurs = ref({})

const formulaire = reactive({
  nom: '',
  prenom: '',
  email: '',
  telephone: '',
  adresse: '',
})

const gerer401 = (error) => {
  if (error?.response?.code === 401) {
    localStorage.removeItem('token_api')
    localStorage.removeItem('utilisateur_api')
    router.push('/login')
    return true
  }

  return false
}

const remplir = (data) => {
  formulaire.nom = data?.nom || ''
  formulaire.prenom = data?.prenom || ''
  formulaire.email = data?.email || ''
  formulaire.telephone = data?.telephone || ''
  formulaire.adresse = data?.adresse || ''
  photoPreview.value = data?.photo_url || ''
}

const lireErreur = (champ) => erreurs.value?.[champ]?.[0] || ''

const chargerProfil = async () => {
  chargement.value = true

  try {
    const reponse = await authGet('/coach/profil')
    const data = reponse?.data?.utilisateur || null
    if (data) {
      remplir(data)
      utilisateur.value = data
      localStorage.setItem('utilisateur_api', JSON.stringify(data))
    }
  } catch (error) {
    if (!gerer401(error)) {
      notifyError(error?.response?.message || error.message || 'Impossible de charger le profil coach.')
    }
  } finally {
    chargement.value = false
  }
}

const choisirPhoto = (event) => {
  const file = event?.target?.files?.[0]
  if (!file) return
  photoFichier.value = file
  photoPreview.value = URL.createObjectURL(file)
}

const enregistrer = async () => {
  envoi.value = true
  erreurs.value = {}

  try {
    const donnees = new FormData()
    donnees.append('nom', formulaire.nom)
    donnees.append('prenom', formulaire.prenom)
    donnees.append('email', formulaire.email)
    donnees.append('telephone', formulaire.telephone)
    donnees.append('adresse', formulaire.adresse)
    if (photoFichier.value) {
      donnees.append('photo', photoFichier.value)
    }

    const reponse = await authPut('/coach/profil', donnees)
    const data = reponse?.data?.utilisateur || null
    if (data) {
      utilisateur.value = data
      localStorage.setItem('utilisateur_api', JSON.stringify(data))
      remplir(data)
    }
    notifySuccess(reponse?.message || 'Profil coach mis a jour avec succes.')
  } catch (error) {
    if (gerer401(error)) {
      return
    }
    erreurs.value = error?.response?.data || {}
    notifyError(error?.response?.message || error.message || 'Impossible de mettre a jour le profil coach.')
  } finally {
    envoi.value = false
  }
}

const deconnecter = () => {
  localStorage.removeItem('token_api')
  localStorage.removeItem('utilisateur_api')
  router.push('/login')
}

onMounted(async () => {
  chargerUtilisateur()
  await chargerProfil()
})
</script>

<template>
  <CoachShellLayout title="Profil coach" subtitle="Mettez a jour vos informations personnelles et votre photo." active-tab="profil" :user="utilisateur" @logout="deconnecter">
    <div v-if="chargement" class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
      <div class="h-[280px] animate-pulse rounded-[28px] border border-[#edf2ff] bg-[#f8fbff]"></div>
      <div class="h-[420px] animate-pulse rounded-[28px] border border-[#edf2ff] bg-[#f8fbff]"></div>
    </div>

    <div v-else class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
      <section class="rounded-[28px] border border-[#e5ecfb] bg-white p-6 text-center">
        <img v-if="photoPreview" :src="photoPreview" alt="Photo coach" class="mx-auto h-40 w-40 rounded-[28px] object-cover" />
        <div v-else class="mx-auto grid h-40 w-40 place-items-center rounded-[28px] bg-[linear-gradient(135deg,#2446d8_0%,#4c6fff_100%)] text-4xl font-black text-white">
          {{ [formulaire.prenom?.[0], formulaire.nom?.[0]].filter(Boolean).join('').toUpperCase() || 'CH' }}
        </div>
        <h2 class="mt-5 text-2xl font-black text-[#0f172a]">{{ formulaire.prenom }} {{ formulaire.nom }}</h2>
        <p class="mt-1 text-sm font-semibold text-[#64748b]">Coach</p>

        <label class="mt-6 block cursor-pointer rounded-full border border-[#d7e1fb] px-4 py-3 text-sm font-semibold text-[#2446d8] transition hover:bg-[#f6f8ff]">
          Changer la photo
          <input type="file" class="hidden" accept="image/*" @change="choisirPhoto" />
        </label>
      </section>

      <form class="rounded-[28px] border border-[#e5ecfb] bg-white p-6" @submit.prevent="enregistrer">
        <div class="grid gap-4 md:grid-cols-2">
          <label>
            <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Nom</span>
            <input v-model="formulaire.nom" type="text" class="h-12 w-full rounded-2xl border border-[#dbe3f1] px-4 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]" />
            <span v-if="lireErreur('nom')" class="mt-2 block text-xs font-semibold text-[#e11d48]">{{ lireErreur('nom') }}</span>
          </label>

          <label>
            <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Prenom</span>
            <input v-model="formulaire.prenom" type="text" class="h-12 w-full rounded-2xl border border-[#dbe3f1] px-4 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]" />
            <span v-if="lireErreur('prenom')" class="mt-2 block text-xs font-semibold text-[#e11d48]">{{ lireErreur('prenom') }}</span>
          </label>

          <label>
            <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Email</span>
            <input v-model="formulaire.email" type="email" class="h-12 w-full rounded-2xl border border-[#dbe3f1] px-4 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]" />
            <span v-if="lireErreur('email')" class="mt-2 block text-xs font-semibold text-[#e11d48]">{{ lireErreur('email') }}</span>
          </label>

          <label>
            <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Telephone</span>
            <input v-model="formulaire.telephone" type="text" class="h-12 w-full rounded-2xl border border-[#dbe3f1] px-4 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]" />
            <span v-if="lireErreur('telephone')" class="mt-2 block text-xs font-semibold text-[#e11d48]">{{ lireErreur('telephone') }}</span>
          </label>
        </div>

        <label class="mt-4 block">
          <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Adresse</span>
          <textarea v-model="formulaire.adresse" rows="4" class="w-full rounded-2xl border border-[#dbe3f1] px-4 py-3 text-sm font-semibold text-[#0f172a] outline-none focus:border-[#4c6fff]"></textarea>
          <span v-if="lireErreur('adresse')" class="mt-2 block text-xs font-semibold text-[#e11d48]">{{ lireErreur('adresse') }}</span>
        </label>

        <div class="mt-6 flex justify-end">
          <button type="submit" class="rounded-full bg-[#0f172a] px-6 py-3 text-sm font-semibold text-white" :disabled="envoi">
            {{ envoi ? 'Enregistrement...' : 'Enregistrer' }}
          </button>
        </div>
      </form>
    </div>
  </CoachShellLayout>
</template>
