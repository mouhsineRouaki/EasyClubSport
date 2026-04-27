<script setup>
import { computed, reactive, ref, watch } from 'vue'
import AppModuleHeader from './AppModuleHeader.vue'
import AppButton from '@/shared/components/ui/AppButton.vue'
import { useAuthSession } from '@/shared/session/useAuthSession'
import { authGet, authPut } from '@/shared/services/apiClient'
import { notifyError, notifySuccess } from '@/shared/services/toastService'

const props = defineProps({
  visible: { type: Boolean, default: false },
  roleLabel: { type: String, default: 'Utilisateur' },
  profileEndpoint: { type: String, required: true },
  showStatus: { type: Boolean, default: false },
  equipe: { type: Object, default: null },
})

const emit = defineEmits(['saved'])
const { gererErreurAuthentification, sauvegarderUtilisateur } = useAuthSession()

const chargement = ref(false)
const enregistrement = ref(false)
const erreurs = ref({})
const photoFichier = ref(null)
const photoPreview = ref('')
const versionPhoto = ref(Date.now())
const dejaCharge = ref(false)

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

const imageProfil = computed(() => {
  const source = photoPreview.value || formulaire.photo_url || ''

  if (!source || source.startsWith('blob:')) {
    return source
  }

  const separateur = source.includes('?') ? '&' : '?'
  return `${source}${separateur}v=${versionPhoto.value}`
})

const nomComplet = computed(() => {
  return [formulaire.prenom, formulaire.nom].filter(Boolean).join(' ') || formulaire.email || props.roleLabel
})

const initiales = computed(() => {
  return ((formulaire.prenom?.[0] || '') + (formulaire.nom?.[0] || '')).toUpperCase() || props.roleLabel.slice(0, 2).toUpperCase()
})

const lireErreur = (champ) => erreurs.value?.[champ]?.[0] || ''

const remplirFormulaire = (utilisateur = {}) => {
  formulaire.nom = utilisateur.nom || ''
  formulaire.prenom = utilisateur.prenom || ''
  formulaire.email = utilisateur.email || ''
  formulaire.telephone = utilisateur.telephone || ''
  formulaire.adresse = utilisateur.adresse || ''
  formulaire.role = utilisateur.role || ''
  formulaire.statut = utilisateur.statut || ''
  formulaire.photo_url = utilisateur.photo_url || ''
  versionPhoto.value = Date.now()
}

const chargerProfil = async () => {
  chargement.value = true

  try {
    const reponse = await authGet(props.profileEndpoint)
    const data = reponse?.data || {}
    const utilisateur = data.utilisateur || {}
    remplirFormulaire(utilisateur)
    if (Object.keys(utilisateur).length) {
      sauvegarderUtilisateur(utilisateur)
    }
    dejaCharge.value = true
    emit('saved', data)
  } catch (error) {
    if (gererErreurAuthentification(error)) {
      return
    }

    notifyError(error?.response?.message || error.message || 'Impossible de charger le profil.')
  } finally {
    chargement.value = false
  }
}

const choisirPhoto = (event) => {
  const fichier = event?.target?.files?.[0]

  if (!fichier) {
    return
  }

  photoFichier.value = fichier
  photoPreview.value = URL.createObjectURL(fichier)
}

const enregistrerProfil = async () => {
  enregistrement.value = true
  erreurs.value = {}

  const donnees = new FormData()
  donnees.append('nom', formulaire.nom)
  donnees.append('prenom', formulaire.prenom)
  donnees.append('email', formulaire.email)
  donnees.append('telephone', formulaire.telephone || '')
  donnees.append('adresse', formulaire.adresse || '')

  if (props.showStatus) {
    donnees.append('statut', formulaire.statut || 'actif')
  }

  if (photoFichier.value) {
    donnees.append('photo', photoFichier.value)
  }

  try {
    const reponse = await authPut(props.profileEndpoint, donnees)
    const data = reponse?.data || {}
    const utilisateur = data.utilisateur || {}

    remplirFormulaire(utilisateur)
    if (Object.keys(utilisateur).length) {
      sauvegarderUtilisateur(utilisateur)
    }
    photoFichier.value = null
    photoPreview.value = ''
    dejaCharge.value = true
    emit('saved', data)
    notifySuccess(reponse?.message || 'Profil mis a jour avec succes.')
  } catch (error) {
    if (gererErreurAuthentification(error)) {
      return
    }

    erreurs.value = error?.response?.data || {}
    if (!Object.keys(erreurs.value).length) {
      notifyError(error?.response?.message || error.message || 'Impossible de mettre a jour le profil.')
    }
  } finally {
    enregistrement.value = false
  }
}

watch(
  () => props.visible,
  async (visible) => {
    if (visible && !dejaCharge.value && !chargement.value) {
      await chargerProfil()
    }
  },
  { immediate: true }
)
</script>

<template>
  <section class="mt-6 space-y-6">
    <AppModuleHeader
      :badge="`Espace ${roleLabel}`"
      titre="Gestion du profil"
      description="Modifiez vos informations personnelles et votre photo de profil sans quitter votre espace principal."
    />

    <div v-if="chargement" class="grid gap-6 xl:grid-cols-[340px_minmax(0,1fr)]">
      <div class="h-[320px] animate-pulse rounded-[32px] border border-[#eef2f7] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
      <div class="h-[420px] animate-pulse rounded-[32px] border border-[#eef2f7] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
    </div>

    <div v-else class="grid gap-6 xl:grid-cols-[340px_minmax(0,1fr)]">
      <section class="ecs-message-shell">
        <div class="relative px-6 pb-8 pt-10 text-white" style="background: linear-gradient(135deg, #111827 0%, #172554 45%, #1d4ed8 75%, #14b8a6 100%);">
          <div class="absolute inset-x-0 top-0 h-20 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.25),transparent_60%)]"></div>
          <div class="relative text-center">
            <div class="mx-auto grid h-28 w-28 place-items-center overflow-hidden rounded-[28px] border-4 border-white/20 bg-white/15 text-3xl font-black text-white shadow-[0_18px_42px_rgba(15,23,42,0.28)]">
              <img v-if="imageProfil" :src="imageProfil" :alt="nomComplet" class="h-full w-full object-cover" />
              <span v-else>{{ initiales }}</span>
            </div>
            <p class="mt-5 text-2xl font-black">{{ nomComplet }}</p>
            <p class="mt-1 text-sm font-semibold text-white/75">{{ formulaire.role || roleLabel.toLowerCase() }}</p>
            <p v-if="showStatus" class="mt-3 inline-flex rounded-full bg-white/15 px-3 py-1 text-[11px] font-black uppercase tracking-[0.16em] text-white/90">
              {{ formulaire.statut || 'actif' }}
            </p>
          </div>
        </div>

        <div class="space-y-4 p-5">
          <label class="block rounded-[24px] border border-dashed border-[#dbe3f1] bg-[#f8fbff] p-4 transition hover:border-[#4c6fff] hover:bg-white">
            <span class="ecs-field-label text-[#7c8aa5]">Photo de profil</span>
            <span class="mt-2 block text-sm font-semibold text-[#1f2a44]">
              {{ photoFichier?.name || (formulaire.photo_url ? 'Photo actuelle chargee' : 'Aucune photo selectionnee') }}
            </span>
            <span class="ecs-chip mt-4 w-fit text-[#1d4ed8]">
              Changer la photo
            </span>
            <input type="file" class="sr-only" accept="image/*" @change="choisirPhoto" />
          </label>

          <div v-if="equipe" class="ecs-panel-muted p-4">
            <p class="ecs-field-label text-[#7c8aa5]">Equipe actuelle</p>
            <p class="mt-2 text-lg font-black text-[#111827]">{{ equipe.nom || 'Equipe' }}</p>
            <p class="mt-1 text-sm font-semibold text-[#64748b]">{{ equipe.club?.nom || 'Club' }}<span v-if="equipe.categorie"> - {{ equipe.categorie }}</span></p>
            <p v-if="equipe.coach?.nom" class="mt-3 text-xs font-semibold text-[#64748b]">Coach : <span class="text-[#1f2a44]">{{ equipe.coach.nom }}</span></p>
          </div>
        </div>
      </section>

      <form class="ecs-message-shell p-6" @submit.prevent="enregistrerProfil">
        <div class="grid gap-4 md:grid-cols-2">
          <label>
            <span class="ecs-field-label text-[#7c8aa5]">Prenom</span>
            <input v-model="formulaire.prenom" type="text" class="ecs-input mt-2" />
            <span v-if="lireErreur('prenom')" class="mt-2 block text-xs font-semibold text-[#e11d48]">{{ lireErreur('prenom') }}</span>
          </label>

          <label>
            <span class="ecs-field-label text-[#7c8aa5]">Nom</span>
            <input v-model="formulaire.nom" type="text" class="ecs-input mt-2" />
            <span v-if="lireErreur('nom')" class="mt-2 block text-xs font-semibold text-[#e11d48]">{{ lireErreur('nom') }}</span>
          </label>

          <label>
            <span class="ecs-field-label text-[#7c8aa5]">Email</span>
            <input v-model="formulaire.email" type="email" class="ecs-input mt-2" />
            <span v-if="lireErreur('email')" class="mt-2 block text-xs font-semibold text-[#e11d48]">{{ lireErreur('email') }}</span>
          </label>

          <label>
            <span class="ecs-field-label text-[#7c8aa5]">Telephone</span>
            <input v-model="formulaire.telephone" type="text" class="ecs-input mt-2" />
            <span v-if="lireErreur('telephone')" class="mt-2 block text-xs font-semibold text-[#e11d48]">{{ lireErreur('telephone') }}</span>
          </label>

          <label v-if="showStatus" class="md:col-span-2 lg:max-w-[260px]">
            <span class="ecs-field-label text-[#7c8aa5]">Statut</span>
            <select v-model="formulaire.statut" class="ecs-select mt-2">
              <option value="actif">Actif</option>
              <option value="blesse">Blesse</option>
              <option value="suspendu">Suspendu</option>
              <option value="inactif">Inactif</option>
            </select>
            <span v-if="lireErreur('statut')" class="mt-2 block text-xs font-semibold text-[#e11d48]">{{ lireErreur('statut') }}</span>
          </label>
        </div>

        <label class="mt-4 block">
          <span class="ecs-field-label text-[#7c8aa5]">Adresse</span>
          <textarea v-model="formulaire.adresse" rows="4" class="ecs-textarea mt-2"></textarea>
          <span v-if="lireErreur('adresse')" class="mt-2 block text-xs font-semibold text-[#e11d48]">{{ lireErreur('adresse') }}</span>
        </label>

        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-[24px] border border-[#eef2f7] bg-[#fbfdff] p-4">
          <div>
            <p class="text-sm font-black text-[#111827]">Informations personnelles</p>
            <p class="mt-1 text-xs font-semibold text-[#64748b]">Les changements sont synchronises avec votre compte principal.</p>
          </div>
          <AppButton type="submit" size="lg" :disabled="enregistrement">
            {{ enregistrement ? 'Enregistrement...' : 'Enregistrer le profil' }}
          </AppButton>
        </div>
      </form>
    </div>
  </section>
</template>
