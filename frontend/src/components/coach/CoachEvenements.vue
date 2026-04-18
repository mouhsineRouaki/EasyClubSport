<script setup>
import { reactive, ref } from 'vue'
import blueBackground from '../../assets/Background.jpg'

const props = defineProps({
  evenements: { type: Array, default: () => [] },
  equipes: { type: Array, default: () => [] },
  equipeId: { type: String, default: '' },
  chargement: { type: Boolean, default: false },
})

const emit = defineEmits(['update:equipeId', 'creer', 'modifier', 'supprimer'])

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
</script>

<template>
  <section class="mt-6">

    <!-- liste -->
    <template v-if="mode === 'liste'">
      <div class="mx-auto max-w-3xl text-center">
        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Gestion coach</p>
        <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Gestion des evenements</h3>
        <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#6b7280]">Choisissez une equipe, puis creez ou modifiez ses evenements.</p>

        <div class="mx-auto mt-5 max-w-2xl space-y-2">
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
      </div>

      <div v-if="chargement" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="n in 6" :key="n" class="h-[270px] animate-pulse rounded-[30px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
      </div>

      <div v-else-if="evenements.length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <article
          v-for="ev in evenements"
          :key="ev.id"
          class="relative min-h-[270px] overflow-hidden rounded-[30px] bg-cover bg-center p-5 text-white"
          :style="{ backgroundImage: backgroundEvenement(ev) }"
        >
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_10%,rgba(255,255,255,0.30),transparent_28%),linear-gradient(180deg,transparent,rgba(0,0,0,0.82))]"></div>
          <div class="relative z-10 flex h-full min-h-[230px] flex-col justify-between">
            <div class="flex items-start justify-between">
              <span class="rounded-full border border-white/30 bg-white/14 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] backdrop-blur-md">
                {{ ev.type }}
              </span>
              <span class="rounded-full px-2.5 py-1 text-[10px] font-black" :class="badgeStatutEvenement(ev.statut).cls">
                {{ badgeStatutEvenement(ev.statut).label }}
              </span>
            </div>
            <div>
              <p class="text-[11px] font-bold text-white/70">{{ formatDate(ev.date_debut) }}</p>
              <h4 class="mt-1 text-2xl font-black leading-tight text-white">{{ ev.titre }}</h4>
              <p v-if="ev.lieu" class="mt-1 text-xs text-white/65">📍 {{ ev.lieu }}</p>
            </div>
            <div class="flex items-center gap-2">
              <button type="button" class="flex-1 rounded-full bg-white py-2 text-xs font-black text-[#1f36bf] hover:bg-[#eef4ff]" @click="ouvrirEdition(ev)">
                Modifier
              </button>
              <button type="button" class="flex-1 rounded-full border border-white/30 bg-white/10 py-2 text-xs font-black text-white hover:bg-[#fef2f2] hover:text-[#ef4444] hover:border-[#fecaca]" @click="emit('supprimer', ev)">
                Supprimer
              </button>
            </div>
          </div>
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

    <!-- formulaire creation / edition -->
    <template v-else>
      <div class="mx-auto max-w-2xl">
        <button type="button" class="mb-5 inline-flex items-center gap-2 text-xs font-black text-[#1f36bf] hover:underline" @click="retourListe">
          ← Retour a la liste
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
