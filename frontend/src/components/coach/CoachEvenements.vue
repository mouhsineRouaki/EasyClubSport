<script setup>
import { computed, reactive, ref, watch } from 'vue'
import blueBackground from '../../assets/Background.jpg'
import AppModuleHeader from '../common/AppModuleHeader.vue'
import CoachMatchCompositionEditor from './CoachMatchCompositionEditor.vue'
import CoachMatchSheetEditor from './CoachMatchSheetEditor.vue'
import CoachMatchStatisticsEditor from './CoachMatchStatisticsEditor.vue'

const props = defineProps({
  evenements: { type: Array, default: () => [] },
  equipes: { type: Array, default: () => [] },
  equipeId: { type: String, default: '' },
  chargement: { type: Boolean, default: false },
  disponibilitesEvenement: { type: Array, default: () => [] },
  chargementDisponibilites: { type: Boolean, default: false },
  compositionMatch: { type: Object, default: null },
  chargementComposition: { type: Boolean, default: false },
  enregistrementComposition: { type: Boolean, default: false },
  feuilleMatch: { type: Object, default: null },
  chargementFeuilleMatch: { type: Boolean, default: false },
  enregistrementFeuilleMatch: { type: Boolean, default: false },
  statistiquesMatch: { type: Object, default: () => ({ resume: {}, joueurs: [] }) },
  chargementStatistiquesMatch: { type: Boolean, default: false },
  enregistrementStatistiquesMatch: { type: Boolean, default: false },
})

const emit = defineEmits([
  'update:equipeId',
  'creer',
  'modifier',
  'supprimer',
  'ouvrir-detail',
  'convoquer-joueur',
  'enregistrer-composition',
  'enregistrer-feuille-match',
  'enregistrer-statistiques-match',
])

const mode = ref('liste')
const evenementSelectionne = ref(null)
const envoiEvenement = ref(false)
const erreursEvenement = ref({})

const formulaire = reactive({
  titre: '',
  type: 'match',
  date_debut: '',
  date_fin: '',
  lieu: '',
  adversaire: '',
  description: '',
  statut: 'planifie',
})

const formatDate = (date) => {
  if (!date) return '-'
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(new Date(date))
}

const formatDateHeure = (date) => {
  if (!date) return '-'
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(date))
}

const formatDateTimeInput = (date) => {
  if (!date) return ''
  const v = new Date(date)
  if (Number.isNaN(v.getTime())) return ''
  return new Date(v.getTime() - v.getTimezoneOffset() * 60000).toISOString().slice(0, 16)
}

const imageEvenement = (ev = {}) => ev.image_url || ev.photo_url || ev.image || blueBackground
const backgroundEvenement = (ev = {}) => `linear-gradient(180deg, rgba(7,16,58,0.18), rgba(7,16,58,0.86)), url(${imageEvenement(ev)})`

const badgeStatutEvenement = (statut) => ({
  planifie: { label: 'Planifie', cls: 'bg-[#eef2ff] text-[#1f36bf]' },
  en_cours: { label: 'En cours', cls: 'bg-[#ecfdf5] text-[#16a34a]' },
  termine: { label: 'Termine', cls: 'bg-[#f1f5f9] text-[#64748b]' },
  annule: { label: 'Annule', cls: 'bg-[#fef2f2] text-[#ef4444]' },
}[statut] || { label: statut, cls: 'bg-[#f8fbff] text-[#64748b]' })

const badgeDisponibilite = (reponse) => ({
  present: { label: 'Present', cls: 'bg-[#ecfdf5] text-[#16a34a]' },
  absent: { label: 'Absent', cls: 'bg-[#fef2f2] text-[#ef4444]' },
  incertain: { label: 'Incertain', cls: 'bg-[#fff7ed] text-[#f59e0b]' },
  sans_reponse: { label: 'Sans reponse', cls: 'bg-[#eef2ff] text-[#1f36bf]' },
}[reponse] || { label: reponse || 'Sans reponse', cls: 'bg-[#f1f5f9] text-[#64748b]' })

const badgeConvocation = (statut) => ({
  convoque: { label: 'Convoque', cls: 'bg-[#eef2ff] text-[#1f36bf]' },
  confirme: { label: 'Confirme', cls: 'bg-[#ecfdf5] text-[#16a34a]' },
  refuse: { label: 'Refuse', cls: 'bg-[#fef2f2] text-[#ef4444]' },
  en_attente: { label: 'En attente', cls: 'bg-[#fff7ed] text-[#f59e0b]' },
}[statut] || { label: statut || 'Aucune', cls: 'bg-[#f1f5f9] text-[#64748b]' })

const reinitialiser = () => {
  Object.assign(formulaire, { titre: '', type: 'match', date_debut: '', date_fin: '', lieu: '', adversaire: '', description: '', statut: 'planifie' })
  erreursEvenement.value = {}
}

const ouvrirCreation = () => {
  evenementSelectionne.value = null
  reinitialiser()
  mode.value = 'creation'
}

const ouvrirEdition = (ev) => {
  evenementSelectionne.value = ev
  Object.assign(formulaire, {
    titre: ev?.titre || '',
    type: ev?.type || 'match',
    date_debut: formatDateTimeInput(ev?.date_debut),
    date_fin: formatDateTimeInput(ev?.date_fin),
    lieu: ev?.lieu || '',
    adversaire: ev?.adversaire || '',
    description: ev?.description || '',
    statut: ev?.statut || 'planifie',
  })
  erreursEvenement.value = {}
  mode.value = 'edition'
}

const ouvrirDetail = async (ev) => {
  evenementSelectionne.value = ev
  mode.value = 'detail'
  await emit('ouvrir-detail', ev)
}

const retourListe = () => {
  mode.value = 'liste'
  erreursEvenement.value = {}
  evenementSelectionne.value = null
}

const soumettre = async () => {
  envoiEvenement.value = true
  erreursEvenement.value = {}
  const payload = {
    titre: formulaire.titre,
    type: formulaire.type,
    date_debut: formulaire.date_debut,
    date_fin: formulaire.date_fin || null,
    lieu: formulaire.lieu || null,
    adversaire: formulaire.type === 'match' ? formulaire.adversaire || null : null,
    description: formulaire.description || null,
    statut: formulaire.statut,
  }
  try {
    if (mode.value === 'creation') {
      await emit('creer', { payload, onErreur: (e) => { erreursEvenement.value = e } })
    } else {
      await emit('modifier', { id: evenementSelectionne.value.id, payload, onErreur: (e) => { erreursEvenement.value = e } })
    }
    mode.value = 'liste'
  } finally {
    envoiEvenement.value = false
  }
}

const disponibilitesConvocables = computed(() =>
  props.disponibilitesEvenement.filter((item) => ['present', 'incertain'].includes(item.disponibilite?.reponse) || !item.disponibilite)
)

const statistiquesDisponibilites = computed(() =>
  props.disponibilitesEvenement.reduce((acc, item) => {
    const reponse = item.disponibilite?.reponse || 'sans_reponse'
    acc.total += 1
    if (reponse === 'present') acc.presents += 1
    else if (reponse === 'absent') acc.absents += 1
    else if (reponse === 'incertain') acc.incertains += 1
    else acc.sansReponse += 1
    return acc
  }, { total: 0, presents: 0, absents: 0, incertains: 0, sansReponse: 0 })
)

const convoquerJoueur = async (item) => {
  if (!evenementSelectionne.value) return
  await emit('convoquer-joueur', { evenement: evenementSelectionne.value, item })
}

const enregistrerComposition = async (payload) => {
  if (!evenementSelectionne.value) return
  await emit('enregistrer-composition', { evenement: evenementSelectionne.value, payload })
}

const enregistrerFeuilleMatch = async (payload) => {
  if (!evenementSelectionne.value) return
  await emit('enregistrer-feuille-match', { evenement: evenementSelectionne.value, payload })
}

const enregistrerStatistiquesMatch = async (payload) => {
  if (!evenementSelectionne.value) return
  await emit('enregistrer-statistiques-match', { evenement: evenementSelectionne.value, payload })
}

watch(
  () => props.evenements,
  (liste) => {
    if (!evenementSelectionne.value) return
    evenementSelectionne.value = liste.find((ev) => String(ev.id) === String(evenementSelectionne.value.id)) || evenementSelectionne.value
  },
  { deep: true }
)
</script>

<template>
  <section class="mt-6">
    <template v-if="mode === 'liste'">
      <AppModuleHeader
        badge="Gestion coach"
        titre="Gestion des evenements"
        description="Choisissez une equipe, puis creez ou modifiez ses evenements."
      >
        <div class="mx-auto max-w-2xl space-y-2">
          <select
            :value="equipeId"
            class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]"
            @change="emit('update:equipeId', $event.target.value)"
          >
            <option value="">Choisir une equipe</option>
            <option v-for="eq in equipes" :key="eq.id" :value="String(eq.id)">{{ eq.nom }}</option>
          </select>
        </div>

        <button type="button" class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white transition hover:bg-[#2446d8]" @click="ouvrirCreation">
          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" /></svg>
          Nouvel evenement
        </button>
      </AppModuleHeader>

      <div v-if="chargement" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="n in 6" :key="n" class="h-[320px] animate-pulse rounded-[26px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
      </div>

      <div v-else-if="evenements.length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <article
          v-for="ev in evenements"
          :key="ev.id"
          class="group relative overflow-hidden rounded-[26px] border border-[#edf1f7] bg-white p-4 text-left transition duration-300 hover:-translate-y-1 hover:border-[#d7e0f5] hover:bg-[#fbfcff]"
        >
          <button type="button" class="block w-full text-left" @click="ouvrirDetail(ev)">
            <div class="relative h-[170px] overflow-hidden rounded-[20px] bg-cover bg-center" :style="{ backgroundImage: backgroundEvenement(ev) }">
              <div class="absolute inset-0 rounded-[20px] bg-[radial-gradient(circle_at_25%_10%,rgba(255,255,255,0.30),transparent_28%),linear-gradient(180deg,transparent,rgba(0,0,0,0.82))]"></div>
              <div class="relative z-10 flex h-full flex-col justify-between p-4 text-white">
                <div class="flex items-start justify-between gap-3">
                  <span class="rounded-full border border-white/30 bg-white/14 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] backdrop-blur-md">
                    {{ ev.type }}
                  </span>
                  <span class="rounded-full px-2.5 py-1 text-[10px] font-black" :class="badgeStatutEvenement(ev.statut).cls">
                    {{ badgeStatutEvenement(ev.statut).label }}
                  </span>
                </div>
                <div>
                  <p class="text-[11px] font-bold text-white/70">{{ formatDate(ev.date_debut) }}</p>
                  <h4 class="mt-1 line-clamp-2 text-2xl font-black leading-tight text-white">{{ ev.titre }}</h4>
                  <p v-if="ev.lieu" class="mt-1 line-clamp-1 text-xs font-semibold text-white/72">{{ ev.lieu }}</p>
                </div>
              </div>
            </div>
          </button>

          <div v-if="ev.type === 'match'" class="mt-4 rounded-[22px] bg-[#f5f7fb] p-3">
            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2">
              <div class="min-w-0 text-center">
                <p class="truncate text-xs font-black text-[#111827]">{{ ev.equipe?.nom || 'Equipe' }}</p>
              </div>
              <span class="rounded-full bg-[#111827] px-2.5 py-1 text-[9px] font-black text-white">VS</span>
              <div class="min-w-0 text-center">
                <p class="truncate text-xs font-black text-[#111827]">{{ ev.adversaire_equipe?.nom || ev.adversaire || 'Adversaire' }}</p>
              </div>
            </div>
          </div>

          <div v-else class="mt-4 rounded-[22px] bg-[#f5f7fb] p-3">
            <p class="truncate text-sm font-black text-[#111827]">{{ ev.type === 'entrainement' ? 'Entrainement' : 'Reunion' }}</p>
            <p class="mt-1 truncate text-xs font-semibold text-[#64748b]">{{ ev.equipe?.nom || 'Equipe' }}</p>
          </div>

          <div class="mt-4 flex items-center gap-2">
            <button type="button" class="flex-1 rounded-full bg-[#111827] py-2 text-xs font-black text-white transition hover:bg-[#2446d8]" @click="ouvrirEdition(ev)">
              Modifier
            </button>
            <button type="button" class="flex-1 rounded-full border border-[#dbe2ef] bg-white py-2 text-xs font-black text-[#111827] transition hover:border-[#fecaca] hover:bg-[#fef2f2] hover:text-[#ef4444]" @click="emit('supprimer', ev)">
              Supprimer
            </button>
          </div>

          <div class="pointer-events-none absolute -bottom-10 -right-10 h-28 w-28 rounded-full bg-[#2446d8]/8 transition duration-300 group-hover:scale-125 group-hover:bg-[#2446d8]/12"></div>
        </article>
      </div>

      <div v-else-if="equipeId" class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
        <h4 class="text-2xl text-[#111827]">Aucun evenement</h4>
        <p class="mt-2 text-sm font-semibold text-[#6b7280]">Creez le premier evenement pour cette equipe.</p>
        <button type="button" class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white hover:bg-[#2446d8]" @click="ouvrirCreation">
          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" /></svg>
          Creer un evenement
        </button>
      </div>

      <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
        <p class="text-sm font-semibold text-[#6b7280]">Selectionnez une equipe pour voir ses evenements.</p>
      </div>
    </template>

    <template v-else-if="mode === 'detail' && evenementSelectionne">
      <div class="mx-auto max-w-6xl">
        <button type="button" class="mb-5 inline-flex items-center gap-2 text-xs font-black text-[#1f36bf] hover:underline" @click="retourListe">
          ? Retour a la liste
        </button>

        <div class="overflow-hidden rounded-[28px] border border-[#e6edf8] bg-white">
          <div class="relative min-h-[240px] bg-cover bg-center p-6 text-white sm:p-8" :style="{ backgroundImage: backgroundEvenement(evenementSelectionne) }">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(255,255,255,0.22),transparent_24%),linear-gradient(180deg,rgba(7,16,58,0.16),rgba(7,16,58,0.88))]"></div>
            <div class="relative z-10 flex flex-wrap items-start justify-between gap-4">
              <div class="max-w-3xl">
                <span class="inline-flex rounded-full border border-white/25 bg-white/12 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] backdrop-blur-md">
                  {{ evenementSelectionne.type || 'Evenement' }}
                </span>
                <h3 class="mt-4 text-3xl font-black leading-tight text-white sm:text-4xl">{{ evenementSelectionne.titre }}</h3>
                <p class="mt-3 text-sm font-semibold text-white/80">
                  {{ evenementSelectionne.club?.nom || 'Club' }} · {{ evenementSelectionne.equipe?.nom || 'Equipe' }}
                </p>
              </div>
              <span class="rounded-full px-3 py-1.5 text-[10px] font-black" :class="badgeStatutEvenement(evenementSelectionne.statut).cls">
                {{ badgeStatutEvenement(evenementSelectionne.statut).label }}
              </span>
            </div>
          </div>

          <div class="grid gap-4 border-t border-[#eef2f8] bg-white p-5 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-[24px] border border-[#e8edf6] bg-[#f8fbff] p-4">
              <p class="text-sm font-black text-[#111827]">Date debut</p>
              <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ formatDateHeure(evenementSelectionne.date_debut) }}</p>
            </article>
            <article class="rounded-[24px] border border-[#e8edf6] bg-[#f8fbff] p-4">
              <p class="text-sm font-black text-[#111827]">Date fin</p>
              <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ evenementSelectionne.date_fin ? formatDateHeure(evenementSelectionne.date_fin) : 'Non definie' }}</p>
            </article>
            <article class="rounded-[24px] border border-[#e8edf6] bg-[#f8fbff] p-4">
              <p class="text-sm font-black text-[#111827]">Lieu</p>
              <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ evenementSelectionne.lieu || '-' }}</p>
            </article>
            <article class="rounded-[24px] border border-[#e8edf6] bg-[#f8fbff] p-4">
              <p class="text-sm font-black text-[#111827]">Type</p>
              <p class="mt-2 text-sm font-semibold capitalize text-[#64748b]">{{ evenementSelectionne.type || '-' }}</p>
            </article>
          </div>

          <div class="grid gap-4 border-t border-[#eef2f8] bg-white p-5 lg:grid-cols-2">
            <article class="rounded-[26px] border border-[#e8edf6] bg-white p-5">
              <p class="text-sm font-black text-[#111827]">Description</p>
              <p class="mt-3 text-sm font-semibold leading-6 text-[#64748b]">
                {{ evenementSelectionne.description || 'Aucune description disponible pour cet evenement.' }}
              </p>
            </article>

            <article class="rounded-[26px] border border-[#e8edf6] bg-white p-5">
              <p class="text-sm font-black text-[#111827]">Disponibilites</p>
              <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                <div class="rounded-[14px] bg-[#f5f7fb] px-3 py-2 text-center">
                  <p class="text-lg font-black text-[#111827]">{{ statistiquesDisponibilites.total }}</p>
                  <p class="text-[9px] font-black uppercase tracking-[0.1em] text-[#6b7280]">Total</p>
                </div>
                <div class="rounded-[14px] bg-[#f5f7fb] px-3 py-2 text-center">
                  <p class="text-lg font-black text-[#16a34a]">{{ statistiquesDisponibilites.presents }}</p>
                  <p class="text-[9px] font-black uppercase tracking-[0.1em] text-[#6b7280]">Presents</p>
                </div>
                <div class="rounded-[14px] bg-[#f5f7fb] px-3 py-2 text-center">
                  <p class="text-lg font-black text-[#f59e0b]">{{ statistiquesDisponibilites.incertains }}</p>
                  <p class="text-[9px] font-black uppercase tracking-[0.1em] text-[#6b7280]">Incertains</p>
                </div>
                <div class="rounded-[14px] bg-[#f5f7fb] px-3 py-2 text-center">
                  <p class="text-lg font-black text-[#ef4444]">{{ statistiquesDisponibilites.absents }}</p>
                  <p class="text-[9px] font-black uppercase tracking-[0.1em] text-[#6b7280]">Absents</p>
                </div>
              </div>
            </article>
          </div>

          <CoachMatchCompositionEditor
            v-if="evenementSelectionne.type === 'match'"
            :composition="compositionMatch"
            :chargement="chargementComposition"
            :enregistrement="enregistrementComposition"
            @enregistrer="enregistrerComposition"
          />

          <CoachMatchSheetEditor
            v-if="evenementSelectionne.type === 'match'"
            :feuille-match="feuilleMatch"
            :chargement="chargementFeuilleMatch"
            :enregistrement="enregistrementFeuilleMatch"
            @enregistrer="enregistrerFeuilleMatch"
          />

          <CoachMatchStatisticsEditor
            v-if="evenementSelectionne.type === 'match'"
            :statistiques="statistiquesMatch"
            :composition="compositionMatch"
            :chargement="chargementStatistiquesMatch"
            :enregistrement="enregistrementStatistiquesMatch"
            @enregistrer="enregistrerStatistiquesMatch"
          />

          <div class="border-t border-[#eef2f8] bg-white p-5">
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-sm font-black text-[#111827]">Joueurs disponibles a convoquer</p>
                <p class="mt-1 text-sm font-semibold text-[#64748b]">User story : le coach ouvre l evenement, consulte les disponibilites, puis convoque directement les joueurs disponibles.</p>
              </div>
              <span class="rounded-full bg-[#eef4ff] px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.14em] text-[#2446d8]">
                {{ disponibilitesConvocables.length }} joueur(s)
              </span>
            </div>

            <div v-if="chargementDisponibilites" class="mt-4 grid gap-3">
              <div v-for="n in 4" :key="n" class="h-[88px] animate-pulse rounded-[24px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
            </div>

            <div v-else-if="disponibilitesConvocables.length" class="mt-4 grid gap-3">
              <article
                v-for="item in disponibilitesConvocables"
                :key="item.utilisateur_id"
                class="group relative overflow-hidden rounded-[26px] border border-[#edf1f7] bg-white p-4 text-left transition duration-300 hover:-translate-y-1 hover:border-[#d7e0f5] hover:bg-[#fbfcff]"
              >
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="flex min-w-0 items-center gap-3">
                    <span class="grid h-12 w-12 shrink-0 place-items-center overflow-hidden rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff,#dbe7ff_28%,#2446d8_72%)] text-sm font-black text-white">
                      <img v-if="item.joueur?.photo_url" :src="item.joueur.photo_url" :alt="item.joueur?.name || 'Joueur'" class="h-full w-full object-cover" />
                      <span v-else>{{ (item.joueur?.prenom || item.joueur?.name || 'J').slice(0, 1) }}</span>
                    </span>
                    <div class="min-w-0">
                      <p class="truncate text-sm font-black text-[#111827]">
                        {{ [item.joueur?.prenom, item.joueur?.nom].filter(Boolean).join(' ') || item.joueur?.name || 'Joueur' }}
                      </p>
                      <p class="truncate text-[11px] font-semibold text-[#64748b]">{{ item.joueur?.email || 'Email non defini' }}</p>
                    </div>
                  </div>

                  <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-3 py-1.5 text-[10px] font-black" :class="badgeDisponibilite(item.disponibilite?.reponse || 'sans_reponse').cls">
                      {{ badgeDisponibilite(item.disponibilite?.reponse || 'sans_reponse').label }}
                    </span>
                    <span class="rounded-full px-3 py-1.5 text-[10px] font-black" :class="badgeConvocation(item.convocation?.statut).cls">
                      {{ badgeConvocation(item.convocation?.statut).label }}
                    </span>
                  </div>
                </div>

                <div class="mt-4 grid gap-3 lg:grid-cols-[1fr_auto]">
                  <div class="rounded-[14px] bg-[#f5f7fb] px-3 py-3">
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-[#7c8aa5]">Commentaire</p>
                    <p class="mt-2 text-sm font-semibold leading-6 text-[#64748b]">
                      {{ item.disponibilite?.commentaire || 'Aucun commentaire.' }}
                    </p>
                  </div>

                  <button
                    type="button"
                    class="self-end rounded-full bg-[#111827] px-5 py-2.5 text-xs font-black text-white transition hover:bg-[#2446d8] disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="Boolean(item.convocation)"
                    @click="convoquerJoueur(item)"
                  >
                    {{ item.convocation ? 'Deja convoque' : 'Convoquer' }}
                  </button>
                </div>

                <div class="pointer-events-none absolute -bottom-10 -right-10 h-28 w-28 rounded-full bg-[#2446d8]/8 transition duration-300 group-hover:scale-125 group-hover:bg-[#2446d8]/12"></div>
              </article>
            </div>

            <div v-else class="mt-4 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-10 text-center">
              <p class="text-sm font-semibold text-[#6b7280]">Aucun joueur disponible a convoquer pour cet evenement.</p>
            </div>
          </div>
        </div>
      </div>
    </template>

    <template v-else>
      <div class="mx-auto max-w-2xl">
        <button type="button" class="mb-5 inline-flex items-center gap-2 text-xs font-black text-[#1f36bf] hover:underline" @click="retourListe">
          ? Retour a la liste
        </button>

        <div class="rounded-[28px] border border-[#e6edf8] bg-white p-6">
          <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Gestion coach</p>
          <h3 class="text-2xl font-black text-[#111827]">
            {{ mode === 'creation' ? 'Nouvel evenement' : "Modifier l'evenement" }}
          </h3>

          <div class="mt-5 space-y-4">
            <div>
              <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">Titre *</label>
              <input v-model="formulaire.titre" type="text" class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff] focus:bg-white" />
              <p v-if="erreursEvenement.titre" class="mt-1 text-xs text-red-500">{{ erreursEvenement.titre[0] }}</p>
            </div>

            <div>
              <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">Type *</label>
              <select v-model="formulaire.type" class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]">
                <option value="match">Match</option>
                <option value="entrainement">Entrainement</option>
                <option value="reunion">Reunion</option>
              </select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">Date debut *</label>
                <input v-model="formulaire.date_debut" type="datetime-local" class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">Date fin</label>
                <input v-model="formulaire.date_fin" type="datetime-local" class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]" />
              </div>
            </div>

            <div>
              <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">Lieu</label>
              <input v-model="formulaire.lieu" type="text" class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff] focus:bg-white" />
            </div>

            <div v-if="formulaire.type === 'match'">
              <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">Adversaire</label>
              <input v-model="formulaire.adversaire" type="text" class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff] focus:bg-white" />
            </div>

            <div>
              <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">Description</label>
              <textarea v-model="formulaire.description" rows="3" class="w-full resize-none rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 py-3 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff] focus:bg-white"></textarea>
            </div>

            <div>
              <label class="mb-1 block text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">Statut</label>
              <select v-model="formulaire.statut" class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]">
                <option value="planifie">Planifie</option>
                <option value="en_cours">En cours</option>
                <option value="termine">Termine</option>
                <option value="annule">Annule</option>
              </select>
            </div>
          </div>

          <div class="mt-6 flex gap-3">
            <button
              type="button"
              class="flex-1 rounded-full bg-[#111827] py-3 text-sm font-black text-white transition hover:bg-[#2446d8] disabled:opacity-60"
              :disabled="envoiEvenement"
              @click="soumettre"
            >
              {{ envoiEvenement ? 'Enregistrement...' : mode === 'creation' ? "Creer l'evenement" : 'Enregistrer les modifications' }}
            </button>
            <button type="button" class="rounded-full border border-[#dbe2ef] px-5 py-3 text-sm font-black text-[#6b7280] transition hover:bg-[#f8fbff]" @click="retourListe">
              Annuler
            </button>
          </div>
        </div>
      </div>
    </template>
  </section>
</template>

