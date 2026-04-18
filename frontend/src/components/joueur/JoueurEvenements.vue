<script setup>
import { computed, ref, watch } from 'vue'
import blueBackground from '../../assets/Background.jpg'

const props = defineProps({
  evenements: { type: Array, default: () => [] },
  equipe: { type: Object, default: null },
  chargement: { type: Boolean, default: false },
  recherche: { type: String, default: '' },
})

const emit = defineEmits(['update:recherche'])

const evenementSelectionne = ref(null)
const mode = ref('liste')

const formatDate = (date) => {
  if (!date) return '-'
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(new Date(date))
}

const formatDateHeure = (date) => {
  if (!date) return '-'
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(date))
}

const imageEvenement = (ev = {}) => ev.image_url || ev.photo_url || ev.image || ev.equipe?.logo_url || ev.adversaire_equipe?.logo_url || props.equipe?.club?.logo_url || blueBackground
const backgroundEvenement = (ev = {}) => `linear-gradient(180deg, rgba(7,16,58,0.14), rgba(7,16,58,0.88)), url(${imageEvenement(ev)})`
const logoEquipe = (equipe) => equipe?.logo_url || equipe?.logo || equipe?.club?.logo_url || ''

const badgeStatut = (statut) => ({
  planifie: { label: 'Planifie', cls: 'bg-[#eef2ff] text-[#1f36bf]' },
  en_cours: { label: 'En cours', cls: 'bg-[#ecfdf5] text-[#16a34a]' },
  termine: { label: 'Termine', cls: 'bg-[#f1f5f9] text-[#64748b]' },
  annule: { label: 'Annule', cls: 'bg-[#fef2f2] text-[#ef4444]' },
}[statut] || { label: statut || 'Statut', cls: 'bg-[#f8fbff] text-[#64748b]' })

const badgeConvocation = (statut) => ({
  confirme: { label: 'Confirmee', cls: 'bg-[#ecfdf5] text-[#16a34a]' },
  refuse: { label: 'Refusee', cls: 'bg-[#fef2f2] text-[#ef4444]' },
  en_attente: { label: 'En attente', cls: 'bg-[#fff7ed] text-[#f59e0b]' },
  convoque: { label: 'Convoque', cls: 'bg-[#eef2ff] text-[#1f36bf]' },
}[statut] || { label: statut || 'Aucune', cls: 'bg-[#f8fbff] text-[#64748b]' })

const evenementsFiltres = computed(() => {
  const q = props.recherche.toLowerCase().trim()
  if (!q) return props.evenements
  return props.evenements.filter((ev) =>
    [ev.titre, ev.lieu, ev.type, ev.adversaire, ev.adversaire_equipe?.nom, ev.equipe?.nom]
      .filter(Boolean)
      .some((v) => String(v).toLowerCase().includes(q))
  )
})

const ouvrirDetail = (evenement) => {
  evenementSelectionne.value = evenement
  mode.value = 'detail'
}

const retourListe = () => {
  mode.value = 'liste'
}

watch(
  () => props.evenements,
  (liste) => {
    if (!evenementSelectionne.value) return
    evenementSelectionne.value = liste.find((ev) => String(ev.id) === String(evenementSelectionne.value.id)) || null
    if (!evenementSelectionne.value) {
      mode.value = 'liste'
    }
  },
  { deep: true }
)
</script>

<template>
  <section class="mt-6">
    <div class="mx-auto max-w-3xl text-center">
      <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Mon espace joueur</p>
      <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Evenements</h3>
      <p v-if="equipe" class="mx-auto mt-1 max-w-2xl text-sm leading-6 text-[#6b7280]">
        Evenements de <span class="font-black text-[#111827]">{{ equipe.nom }}</span>
      </p>
    </div>

    <div class="mx-auto mt-4 max-w-2xl rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-2">
      <input
        :value="recherche"
        type="text"
        placeholder="Rechercher un evenement..."
        class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
        @input="emit('update:recherche', $event.target.value)"
      />
    </div>

    <div v-if="chargement" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="n in 6" :key="n" class="h-[270px] animate-pulse rounded-[30px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
    </div>

    <template v-else-if="mode === 'detail' && evenementSelectionne">
      <div class="mt-6 rounded-[30px] border border-[#e6edf8] bg-white p-4 sm:p-5">
        <div class="mb-4 flex items-center justify-between gap-3">
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-full border border-[#dbe2ef] px-4 py-2 text-xs font-black text-[#1f2a44] transition hover:bg-[#f8fbff]"
            @click="retourListe"
          >
            <span aria-hidden="true">←</span>
          </button>
          <span
            class="rounded-full px-4 py-2 text-xs font-black capitalize"
            :class="badgeStatut(evenementSelectionne.statut).cls"
          >
            {{ badgeStatut(evenementSelectionne.statut).label }}
          </span>
        </div>

        <div class="overflow-hidden rounded-[28px] border border-[#e6edf8]">
          <div class="min-h-[260px] bg-cover bg-center p-5 text-white sm:p-6" :style="{ backgroundImage: backgroundEvenement(evenementSelectionne) }">
            <div class="flex flex-wrap items-center gap-2">
              <span class="rounded-full bg-white/90 px-4 py-2 text-xs font-black capitalize text-[#2446d8]">
                {{ evenementSelectionne.type || 'evenement' }}
              </span>
              <span
                v-if="evenementSelectionne.ma_convocation"
                class="rounded-full px-4 py-2 text-xs font-black"
                :class="badgeConvocation(evenementSelectionne.ma_convocation.statut).cls"
              >
                {{ badgeConvocation(evenementSelectionne.ma_convocation.statut).label }}
              </span>
            </div>

            <div v-if="evenementSelectionne.type === 'match'" class="mx-auto mt-6 grid w-full max-w-2xl grid-cols-[1fr_auto_1fr] items-center gap-3 rounded-[26px] bg-white/14 p-4 text-center backdrop-blur-md">
              <div class="min-w-0 text-center">
                <img v-if="logoEquipe(evenementSelectionne.equipe || equipe)" :src="logoEquipe(evenementSelectionne.equipe || equipe)" :alt="evenementSelectionne.equipe?.nom || equipe?.nom || 'Equipe'" class="mx-auto h-16 w-16 rounded-[22px] object-cover ring-4 ring-white/25" />
                <span v-else class="mx-auto block h-16 w-16 rounded-[22px] bg-white/20"></span>
                <p class="mt-2 truncate text-sm font-black text-white">{{ evenementSelectionne.equipe?.nom || equipe?.nom || 'Equipe' }}</p>
              </div>
              <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-[#111827]">VS</span>
              <div class="min-w-0 text-center">
                <img v-if="logoEquipe(evenementSelectionne.adversaire_equipe)" :src="logoEquipe(evenementSelectionne.adversaire_equipe)" :alt="evenementSelectionne.adversaire_equipe?.nom || 'Adversaire'" class="mx-auto h-16 w-16 rounded-[22px] object-cover ring-4 ring-white/25" />
                <span v-else class="mx-auto block h-16 w-16 rounded-[22px] bg-white/20"></span>
                <p class="mt-2 truncate text-sm font-black text-white">{{ evenementSelectionne.adversaire_equipe?.nom || evenementSelectionne.adversaire || 'Adversaire' }}</p>
              </div>
            </div>

            <div v-else class="mt-6 flex items-center gap-4 rounded-[26px] bg-white/14 p-4 backdrop-blur-md">
              <img v-if="logoEquipe(evenementSelectionne.equipe || equipe)" :src="logoEquipe(evenementSelectionne.equipe || equipe)" :alt="evenementSelectionne.equipe?.nom || equipe?.nom || 'Equipe'" class="h-16 w-16 rounded-[22px] object-cover ring-4 ring-white/25" />
              <span v-else class="block h-16 w-16 rounded-[22px] bg-white/20"></span>
              <div>
                <p class="text-xl font-black text-white">{{ evenementSelectionne.type === 'entrainement' ? 'Entrainement' : 'Reunion' }}</p>
                <p class="text-sm font-semibold text-white/80">{{ evenementSelectionne.equipe?.nom || equipe?.nom || 'Equipe' }}</p>
              </div>
            </div>

            <h4 class="mt-6 text-4xl font-black leading-tight text-white sm:text-5xl">{{ evenementSelectionne.titre }}</h4>
            <p class="mt-3 max-w-3xl text-sm font-semibold text-white/80">
              {{ evenementSelectionne.equipe?.club?.nom || equipe?.club?.nom || 'Club' }} - {{ evenementSelectionne.equipe?.nom || equipe?.nom || 'Equipe' }}
            </p>
          </div>

          <div class="grid gap-4 bg-white p-5 sm:grid-cols-2 xl:grid-cols-4">
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
              <p class="text-sm font-black text-[#111827]">Informations de l equipe</p>
              <div class="mt-3 space-y-3">
                <div class="rounded-[20px] bg-[#f8fbff] p-4">
                  <p class="text-xs font-black uppercase tracking-[0.14em] text-[#64748b]">Club</p>
                  <p class="mt-2 text-lg font-black text-[#111827]">{{ evenementSelectionne.equipe?.club?.nom || equipe?.club?.nom || '-' }}</p>
                  <p class="mt-1 text-xs font-semibold text-[#64748b]">{{ evenementSelectionne.equipe?.club?.ville || equipe?.club?.ville || 'Ville non definie' }}</p>
                </div>
                <div class="rounded-[20px] bg-[#f8fbff] p-4">
                  <p class="text-xs font-black uppercase tracking-[0.14em] text-[#64748b]">Equipe</p>
                  <p class="mt-2 text-lg font-black text-[#111827]">{{ evenementSelectionne.equipe?.nom || equipe?.nom || '-' }}</p>
                  <p class="mt-1 text-xs font-semibold text-[#64748b]">{{ evenementSelectionne.equipe?.categorie || equipe?.categorie || 'Categorie non definie' }}</p>
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </template>

    <div v-else-if="evenementsFiltres.length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <article
        v-for="ev in evenementsFiltres"
        :key="ev.id"
        class="relative min-h-[270px] cursor-pointer overflow-hidden rounded-[30px] bg-cover bg-center p-5 text-white transition hover:-translate-y-[2px]"
        :style="{ backgroundImage: backgroundEvenement(ev) }"
        @click="ouvrirDetail(ev)"
      >
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_10%,rgba(255,255,255,0.30),transparent_28%),linear-gradient(180deg,transparent,rgba(0,0,0,0.82))]"></div>
        <div class="relative z-10 flex h-full min-h-[230px] flex-col justify-between">
          <div class="flex items-start justify-between">
            <span class="rounded-full border border-white/30 bg-white/14 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] backdrop-blur-md">
              {{ ev.type }}
            </span>
            <span class="rounded-full px-2.5 py-1 text-[10px] font-black" :class="badgeStatut(ev.statut).cls">
              {{ badgeStatut(ev.statut).label }}
            </span>
          </div>
          <div>
            <p class="text-[11px] font-bold text-white/70">{{ formatDate(ev.date_debut) }}</p>
            <h4 class="mt-1 text-2xl font-black leading-tight text-white">{{ ev.titre }}</h4>
            <p v-if="ev.lieu" class="mt-1 text-xs text-white/65">{{ ev.lieu }}</p>
            <p v-if="ev.type === 'match' && (ev.adversaire_equipe?.nom || ev.adversaire)" class="mt-1 text-xs font-semibold text-white/70">
              vs {{ ev.adversaire_equipe?.nom || ev.adversaire }}
            </p>
          </div>
          <div class="flex items-center justify-between gap-2">
            <span
              v-if="ev.ma_convocation"
              class="rounded-full px-3 py-1.5 text-[10px] font-black"
              :class="badgeConvocation(ev.ma_convocation.statut).cls"
            >
              {{ badgeConvocation(ev.ma_convocation.statut).label }}
            </span>
            <span v-else class="rounded-full bg-white/10 px-3 py-1.5 text-[10px] font-black text-white/60">
              Non convoque
            </span>

            <button
              type="button"
              class="rounded-full bg-white px-4 py-2 text-xs font-black text-[#2446d8] transition hover:bg-[#eef2ff]"
              @click.stop="ouvrirDetail(ev)"
            >
              Ouvrir
            </button>
          </div>
        </div>
      </article>
    </div>

    <div v-else-if="!equipe" class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <p class="text-sm font-semibold text-[#6b7280]">Vous n'etes dans aucune equipe pour le moment.</p>
    </div>

    <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <h4 class="text-2xl text-[#111827]">Aucun evenement</h4>
      <p class="mt-2 text-sm font-semibold text-[#6b7280]">Aucun evenement planifie pour votre equipe.</p>
    </div>
  </section>
</template>
