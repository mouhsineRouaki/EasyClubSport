<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppCard from '../../components/common/AppCard.vue'
import PresidentShellLayout from '../../components/president/PresidentShellLayout.vue'
import { authGet, authPut } from '../../services/api'
import { notifyError, notifySuccess } from '../../stores/toast'

const router = useRouter()

const chargement = ref(true)
const sauvegarde = ref(false)
const succes = ref('')
const erreursValidation = ref({})
const photoPreview = ref('')
const photoFichier = ref(null)
const versionPhoto = ref(Date.now())

const formulaire = reactive({
  nom: '',
  prenom: '',
  email: '',
  telephone: '',
  adresse: '',
  role: '',
  statut: '',
  photo_url: '',
})

const parametresCompte = [
  'Recevoir les alertes des nouvelles inscriptions',
  'Recevoir les reponses des coachs',
  'Notifier quand un document est ajoute',
]

const parametresApplication = [
  'Activer les rappels des evenements',
  'Recevoir le resume mensuel du club',
  'Synchroniser les annonces importantes',
]

const conversations = [
  { nom: 'Coach principal', message: 'Planning entrainement a valider.', couleur: '#fbbf24' },
  { nom: 'Responsable joueurs', message: 'Deux nouveaux joueurs a confirmer.', couleur: '#ef4444' },
  { nom: 'Documents club', message: 'Un nouveau document interne a ete ajoute.', couleur: '#334155' },
  { nom: 'Equipe U19', message: 'Match amical propose samedi.', couleur: '#0ea5e9' },
]

const modules = [
  { titre: 'Gestion des equipes', description: 'Creer et suivre les equipes du club.', image: 'linear-gradient(135deg,#0f172a,#1e293b)' },
  { titre: 'Evenements sportifs', description: 'Planifier matchs, reunions et stages.', image: 'linear-gradient(135deg,#1d4ed8,#0ea5e9)' },
  { titre: 'Documents du club', description: 'Centraliser les fichiers et pieces partagees.', image: 'linear-gradient(135deg,#14532d,#16a34a)' },
]

const nomComplet = computed(() => [formulaire.prenom, formulaire.nom].filter(Boolean).join(' ') || formulaire.email || 'President')

const imageProfil = computed(() => {
  const source = photoPreview.value || formulaire.photo_url || ''

  if (!source || source.startsWith('blob:')) {
    return source
  }

  const separateur = source.includes('?') ? '&' : '?'
  return `${source}${separateur}v=${versionPhoto.value}`
})

const initiales = computed(() => ((formulaire.prenom?.[0] || '') + (formulaire.nom?.[0] || '')).toUpperCase() || 'PR')

const nomPhotoSelectionnee = computed(() => {
  return photoFichier.value?.name || (formulaire.photo_url ? 'Photo actuelle chargee' : 'Aucune photo selectionnee')
})

const utilisateurLayout = computed(() => ({
  nom: formulaire.nom,
  prenom: formulaire.prenom,
  email: formulaire.email,
  role: formulaire.role,
  photo_url: imageProfil.value,
}))

const lireErreur = (champ) => erreursValidation.value?.[champ]?.[0] || ''

const chargerProfil = async () => {
  chargement.value = true

  try {
    const reponse = await authGet('/president/profil')
    const utilisateur = reponse?.data?.utilisateur || {}

    formulaire.nom = utilisateur.nom || ''
    formulaire.prenom = utilisateur.prenom || ''
    formulaire.email = utilisateur.email || ''
    formulaire.telephone = utilisateur.telephone || ''
    formulaire.adresse = utilisateur.adresse || ''
    formulaire.role = utilisateur.role || ''
    formulaire.statut = utilisateur.statut || ''
    formulaire.photo_url = utilisateur.photo_url || ''
    versionPhoto.value = Date.now()

    localStorage.setItem('utilisateur_api', JSON.stringify(utilisateur))
  } catch (error) {
    if (error?.response?.code === 401) {
      localStorage.removeItem('token_api')
      localStorage.removeItem('utilisateur_api')
      router.push('/login')
      return
    }

    notifyError(error?.response?.message || error.message || 'Impossible de charger le profil.')
  } finally {
    chargement.value = false
  }
}

const choisirPhoto = (event) => {
  const fichier = event.target.files?.[0]

  if (!fichier) {
    return
  }

  photoFichier.value = fichier
  photoPreview.value = URL.createObjectURL(fichier)
}

const enregistrerProfil = async () => {
  sauvegarde.value = true
  succes.value = ''
  erreursValidation.value = {}

  const donnees = new FormData()
  donnees.append('nom', formulaire.nom)
  donnees.append('prenom', formulaire.prenom)
  donnees.append('email', formulaire.email)
  donnees.append('telephone', formulaire.telephone || '')
  donnees.append('adresse', formulaire.adresse || '')

  if (photoFichier.value) {
    donnees.append('photo', photoFichier.value)
  }

  try {
    const reponse = await authPut('/president/profil', donnees)
    const utilisateur = reponse?.data?.utilisateur || {}

    formulaire.nom = utilisateur.nom || formulaire.nom
    formulaire.prenom = utilisateur.prenom || formulaire.prenom
    formulaire.email = utilisateur.email || formulaire.email
    formulaire.telephone = utilisateur.telephone || formulaire.telephone
    formulaire.adresse = utilisateur.adresse || formulaire.adresse
    formulaire.role = utilisateur.role || formulaire.role
    formulaire.statut = utilisateur.statut || formulaire.statut
    formulaire.photo_url = utilisateur.photo_url || formulaire.photo_url
    versionPhoto.value = Date.now()

    photoFichier.value = null
    photoPreview.value = ''
    succes.value = reponse.message || 'Profil modifie avec succes.'
    notifySuccess(succes.value)
    localStorage.setItem('utilisateur_api', JSON.stringify(utilisateur))
  } catch (error) {
    const reponseErreur = error.response || {}
    erreursValidation.value = reponseErreur.data || {}
    if (!Object.keys(erreursValidation.value).length) {
      notifyError(reponseErreur.message || error.message || 'Impossible de mettre a jour le profil.')
    }
  } finally {
    sauvegarde.value = false
  }
}

const deconnecter = () => {
  localStorage.removeItem('token_api')
  localStorage.removeItem('utilisateur_api')
  router.push('/login')
}

onMounted(() => {
  chargerProfil()
})
</script>

<template>
  <PresidentShellLayout
    breadcrumb="Profil"
    title="Profil president"
    active-section="profil"
    :utilisateur="utilisateurLayout"
    @logout="deconnecter"
  >
    <AppCard title="Identite president" subtitle="Informations du compte connecte.">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-center gap-4">
          <span class="grid h-18 w-18 place-items-center overflow-hidden rounded-2xl bg-[linear-gradient(130deg,#0f172a,#334155)] text-lg font-bold text-white">
            <img v-if="imageProfil" :src="imageProfil" :alt="nomComplet" class="h-full w-full object-cover" />
            <span v-else>{{ initiales }}</span>
          </span>
          <div>
            <p class="text-lg font-bold text-[#1f2a44]">{{ nomComplet }}</p>
            <p class="mt-1 text-sm capitalize text-[#64748b]">{{ formulaire.role || 'president' }} / {{ formulaire.statut || 'actif' }}</p>
            <p class="mt-1 text-xs text-[#475569]">{{ formulaire.email || '-' }}</p>
          </div>
        </div>

        <label class="rounded-xl border border-dashed border-[#d8e2f1] bg-[#f8fbff] p-3 transition hover:border-[#2563eb] hover:bg-white">
          <span class="block text-xs font-bold text-[#64748b]">Photo de profil</span>
          <span class="mt-2 block max-w-[220px] truncate text-xs text-[#475569]">{{ nomPhotoSelectionnee }}</span>
          <span class="mt-2 inline-block ecs-btn-secondary text-[11px]">Changer la photo</span>
          <input type="file" accept="image/*" class="sr-only" @change="choisirPhoto" />
        </label>
      </div>
    </AppCard>

    <div v-if="succes" class="ecs-note-success">{{ succes }}</div>

    <section class="mt-4 grid gap-4 xl:grid-cols-[1.2fr_0.9fr_0.9fr]">
      <AppCard title="Informations du profil" subtitle="Ces donnees sont synchronisees avec l API.">
        <div v-if="chargement" class="grid gap-3">
          <div v-for="n in 6" :key="n" class="h-10 animate-pulse rounded-xl bg-[#f1f5f9]"></div>
        </div>

        <form v-else class="grid gap-3" @submit.prevent="enregistrerProfil">
          <div class="grid gap-3 sm:grid-cols-2">
            <label>
              <span class="text-xs font-bold text-[#64748b]">Prenom</span>
              <input v-model="formulaire.prenom" class="ecs-input" type="text" />
              <span v-if="lireErreur('prenom')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('prenom') }}</span>
            </label>
            <label>
              <span class="text-xs font-bold text-[#64748b]">Nom</span>
              <input v-model="formulaire.nom" class="ecs-input" type="text" />
              <span v-if="lireErreur('nom')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('nom') }}</span>
            </label>
          </div>

          <label>
            <span class="text-xs font-bold text-[#64748b]">Email</span>
            <input v-model="formulaire.email" class="ecs-input" type="email" />
            <span v-if="lireErreur('email')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('email') }}</span>
          </label>

          <label>
            <span class="text-xs font-bold text-[#64748b]">Telephone</span>
            <input v-model="formulaire.telephone" class="ecs-input" type="tel" />
            <span v-if="lireErreur('telephone')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('telephone') }}</span>
          </label>

          <label>
            <span class="text-xs font-bold text-[#64748b]">Adresse</span>
            <input v-model="formulaire.adresse" class="ecs-input" type="text" />
            <span v-if="lireErreur('adresse')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('adresse') }}</span>
          </label>

          <div class="mt-1 flex flex-wrap items-center justify-between gap-3">
            <p class="text-xs text-[#64748b]">Role: <span class="font-semibold capitalize text-[#1f2a44]">{{ formulaire.role || 'president' }}</span></p>
            <button :disabled="sauvegarde" class="ecs-btn-primary" type="submit">
              {{ sauvegarde ? 'Enregistrement...' : 'Enregistrer' }}
            </button>
          </div>
        </form>
      </AppCard>

      <AppCard title="Parametres" subtitle="Preferences du compte president.">
        <p class="text-xs font-bold uppercase text-[#64748b]">Compte</p>
        <div class="mt-3 space-y-3">
          <label v-for="(parametre, index) in parametresCompte" :key="parametre" class="flex items-center gap-3 text-xs text-[#475569]">
            <span :class="['relative h-4 w-8 rounded-full transition', index === 1 ? 'bg-[#d1d5db]' : 'bg-[#334155]']">
              <span :class="['absolute top-0.5 h-3 w-3 rounded-full bg-white transition', index === 1 ? 'left-0.5' : 'left-[18px]']"></span>
            </span>
            {{ parametre }}
          </label>
        </div>

        <p class="mt-6 text-xs font-bold uppercase text-[#64748b]">Application</p>
        <div class="mt-3 space-y-3">
          <label v-for="(parametre, index) in parametresApplication" :key="parametre" class="flex items-center gap-3 text-xs text-[#475569]">
            <span :class="['relative h-4 w-8 rounded-full transition', index === 1 ? 'bg-[#d1d5db]' : 'bg-[#334155]']">
              <span :class="['absolute top-0.5 h-3 w-3 rounded-full bg-white transition', index === 1 ? 'left-0.5' : 'left-[18px]']"></span>
            </span>
            {{ parametre }}
          </label>
        </div>
      </AppCard>

      <AppCard title="Messages recents" subtitle="Echanges internes du club.">
        <div class="space-y-3">
          <div v-for="conversation in conversations" :key="conversation.nom" class="flex items-center gap-3">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-xs font-bold text-white" :style="{ background: conversation.couleur }">
              {{ conversation.nom.slice(0, 2).toUpperCase() }}
            </span>
            <div class="min-w-0 flex-1">
              <p class="truncate text-xs font-bold text-[#1f2a44]">{{ conversation.nom }}</p>
              <p class="truncate text-xs text-[#64748b]">{{ conversation.message }}</p>
            </div>
            <button class="text-[11px] font-bold text-[#2563eb]" type="button">Ouvrir</button>
          </div>
        </div>
      </AppCard>
    </section>

    <AppCard class="mt-4" title="Modules president" subtitle="Acces rapide aux axes de gestion.">
      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article v-for="module in modules" :key="module.titre" class="overflow-hidden rounded-xl border border-[#e8edf5] bg-white">
          <div class="h-24" :style="{ background: module.image }"></div>
          <div class="p-4">
            <p class="text-sm font-bold text-[#1f2a44]">{{ module.titre }}</p>
            <p class="mt-1 text-xs text-[#64748b]">{{ module.description }}</p>
          </div>
        </article>

        <button class="grid min-h-[156px] place-items-center rounded-xl border border-dashed border-[#d1d9e6] text-center text-xs font-bold text-[#64748b] transition hover:border-[#2563eb] hover:text-[#2563eb]" type="button">
          <span>
            <span class="block text-xl">+</span>
            Ajouter un module
          </span>
        </button>
      </div>
    </AppCard>
  </PresidentShellLayout>
</template>
