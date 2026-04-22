<script setup>
import { computed, ref, watch } from 'vue'
import blueBackground from '@/assets/Background.jpg'
import AppModuleHeader from '@/shared/components/AppModuleHeader.vue'
import MatchCompositionSection from '@/shared/components/MatchCompositionSection.vue'
import MatchSheetSection from '@/shared/components/MatchSheetSection.vue'
import MatchStatisticsSection from '@/shared/components/MatchStatisticsSection.vue'

const props = defineProps({
  evenements: { type: Array, default: () => [] },
  equipe: { type: Object, default: null },
  chargement: { type: Boolean, default: false },
  recherche: { type: String, default: '' },
  compositionMatch: { type: Object, default: null },
  chargementComposition: { type: Boolean, default: false },
  feuilleMatch: { type: Object, default: null },
  chargementFeuilleMatch: { type: Boolean, default: false },
  statistiquesMatch: { type: Object, default: () => ({ resume: {}, joueurs: [] }) },
  chargementStatistiquesMatch: { type: Boolean, default: false },
})

const emit = defineEmits(['update:recherche', 'repondre-disponibilite', 'ouvrir-detail'])

const evenementSelectionne = ref(null)
const mode = ref('liste')
const commentaireDisponibilite = ref('')
const envoiDisponibilite = ref(false)

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
  commentaireDisponibilite.value = evenement?.disponibilite?.commentaire || ''
  mode.value = 'detail'
  emit('ouvrir-detail', evenement)
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
    } else {
      commentaireDisponibilite.value = evenementSelectionne.value?.disponibilite?.commentaire || commentaireDisponibilite.value
    }
  },
  { deep: true }
)

const badgeDisponibilite = (reponse) => ({
  present: { label: 'Present', cls: 'bg-[#ecfdf5] text-[#16a34a]' },
  absent: { label: 'Absent', cls: 'bg-[#fef2f2] text-[#ef4444]' },
  incertain: { label: 'Incertain', cls: 'bg-[#fff7ed] text-[#f59e0b]' },
}[reponse] || { label: 'Sans reponse', cls: 'bg-[#eef2ff] text-[#1f36bf]' })

const repondreDisponibilite = async (reponse) => {
  if (!evenementSelectionne.value) return
  envoiDisponibilite.value = true
  try {
    await emit('repondre-disponibilite', {
      evenement: evenementSelectionne.value,
      reponse,
      commentaire: commentaireDisponibilite.value?.trim() || null,
    })
  } finally {
    envoiDisponibilite.value = false
  }
}
</script>

<template>
  <section class="mt-6">
    <AppModuleHeader badge="Mon espace joueur" titre="Evenements"
      :description="equipe ? '' : 'Consultez les evenements lies a votre equipe et indiquez votre disponibilite.'">
      <p v-if="equipe" class="mx-auto mt-1 max-w-2xl text-sm leading-6 text-[#6b7280]">
        Evenements de <span class="font-black text-[#111827]">{{ equipe.nom }}</span>
      </p>

      <div class="mx-auto mt-4 max-w-2xl rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-2">
        <input :value="recherche" type="text" placeholder="Rechercher un evenement..."
          class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
          @input="emit('update:recherche', $event.target.value)" />
      </div>
    </AppModuleHeader>

    <div v-if="chargement" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="n in 6" :key="n"
        class="h-[270px] animate-pulse rounded-[30px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
    </div>

    <template v-else-if="mode === 'detail' && evenementSelectionne">
      <div class="mt-6 rounded-[30px] border border-[#e6edf8] bg-white p-4 sm:p-5">
        <div class="mb-4 flex items-center justify-between gap-3">
          <button type="button"
            class="inline-flex items-center gap-2 rounded-full border border-[#dbe2ef] px-4 py-2 text-xs font-black text-[#1f2a44] transition hover:bg-[#f8fbff]"
            @click="retourListe">
            <span aria-hidden="true">←</span>
          </button>
          <span class="rounded-full px-4 py-2 text-xs font-black capitalize"
            :class="badgeStatut(evenementSelectionne.statut).cls">
            {{ badgeStatut(evenementSelectionne.statut).label }}
          </span>
        </div>

        <div class="overflow-hidden rounded-[28px] border border-[#e6edf8]">
          <div class="min-h-[260px] bg-cover bg-center p-5 text-white sm:p-6"
            :style="{ backgroundImage: backgroundEvenement(evenementSelectionne) }">
            <div class="flex flex-wrap items-center gap-2">
              <span class="rounded-full bg-white/90 px-4 py-2 text-xs font-black capitalize text-[#2446d8]">
                {{ evenementSelectionne.type || 'evenement' }}
              </span>
              <span v-if="evenementSelectionne.convocation || evenementSelectionne.ma_convocation"
                class="rounded-full px-4 py-2 text-xs font-black"
                :class="badgeConvocation((evenementSelectionne.convocation || evenementSelectionne.ma_convocation).statut).cls">
                {{ badgeConvocation((evenementSelectionne.convocation ||
                  evenementSelectionne.ma_convocation).statut).label }}
              </span>
            </div>

            <div v-if="evenementSelectionne.type === 'match'"
              class="mx-auto mt-6 grid w-full max-w-2xl grid-cols-[1fr_auto_1fr] items-center gap-3 rounded-[26px] bg-white/14 p-4 text-center backdrop-blur-md">
              <div class="min-w-0 text-center">
                <img v-if="logoEquipe(evenementSelectionne.equipe || equipe)"
                  :src="logoEquipe(evenementSelectionne.equipe || equipe)"
                  :alt="evenementSelectionne.equipe?.nom || equipe?.nom || 'Equipe'"
                  class="mx-auto h-16 w-16 rounded-[22px] object-cover ring-4 ring-white/25" />
                <span v-else class="mx-auto block h-16 w-16 rounded-[22px] bg-white/20"></span>
                <p class="mt-2 truncate text-sm font-black text-white">{{ evenementSelectionne.equipe?.nom ||
                  equipe?.nom || 'Equipe' }}</p>
              </div>
              <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-[#111827]">VS</span>
              <div class="min-w-0 text-center">
                <img v-if="logoEquipe(evenementSelectionne.adversaire_equipe)"
                  :src="logoEquipe(evenementSelectionne.adversaire_equipe)"
                  :alt="evenementSelectionne.adversaire_equipe?.nom || 'Adversaire'"
                  class="mx-auto h-16 w-16 rounded-[22px] object-cover ring-4 ring-white/25" />
                <span v-else class="mx-auto block h-16 w-16 rounded-[22px] bg-white/20"></span>
                <p class="mt-2 truncate text-sm font-black text-white">{{ evenementSelectionne.adversaire_equipe?.nom ||
                  evenementSelectionne.adversaire || 'Adversaire' }}</p>
              </div>
            </div>

            <div v-else class="mt-6 flex items-center gap-4 rounded-[26px] bg-white/14 p-4 backdrop-blur-md">
              <img v-if="logoEquipe(evenementSelectionne.equipe || equipe)"
                :src="logoEquipe(evenementSelectionne.equipe || equipe)"
                :alt="evenementSelectionne.equipe?.nom || equipe?.nom || 'Equipe'"
                class="h-16 w-16 rounded-[22px] object-cover ring-4 ring-white/25" />
              <span v-else class="block h-16 w-16 rounded-[22px] bg-white/20"></span>
              <div>
                <p class="text-xl font-black text-white">{{ evenementSelectionne.type === 'entrainement' ?
                  'Entrainement' : 'Reunion' }}</p>
                <p class="text-sm font-semibold text-white/80">{{ evenementSelectionne.equipe?.nom || equipe?.nom ||
                  'Equipe' }}</p>
              </div>
            </div>

            <h4 class="mt-6 text-4xl font-black leading-tight text-white sm:text-5xl">{{ evenementSelectionne.titre }}
            </h4>
            <p class="mt-3 max-w-3xl text-sm font-semibold text-white/80">
              {{ evenementSelectionne.equipe?.club?.nom || equipe?.club?.nom || 'Club' }} - {{
                evenementSelectionne.equipe?.nom || equipe?.nom || 'Equipe' }}
            </p>
          </div>

          <div class="grid gap-4 bg-white p-5 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-[24px] border border-[#e8edf6] bg-[#f8fbff] p-4">
              <p class="text-sm font-black text-[#111827]">Date debut</p>
              <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ formatDateHeure(evenementSelectionne.date_debut)
                }}</p>
            </article>
            <article class="rounded-[24px] border border-[#e8edf6] bg-[#f8fbff] p-4">
              <p class="text-sm font-black text-[#111827]">Date fin</p>
              <p class="mt-2 text-sm font-semibold text-[#64748b]">{{ evenementSelectionne.date_fin ?
                formatDateHeure(evenementSelectionne.date_fin) : 'Non definie' }}</p>
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
                  <p class="mt-2 text-lg font-black text-[#111827]">{{ evenementSelectionne.equipe?.club?.nom ||
                    equipe?.club?.nom || '-' }}</p>
                  <p class="mt-1 text-xs font-semibold text-[#64748b]">{{ evenementSelectionne.equipe?.club?.ville ||
                    equipe?.club?.ville || 'Ville non definie' }}</p>
                </div>
                <div class="rounded-[20px] bg-[#f8fbff] p-4">
                  <p class="text-xs font-black uppercase tracking-[0.14em] text-[#64748b]">Equipe</p>
                  <p class="mt-2 text-lg font-black text-[#111827]">{{ evenementSelectionne.equipe?.nom || equipe?.nom
                    || '-' }}</p>
                  <p class="mt-1 text-xs font-semibold text-[#64748b]">{{ evenementSelectionne.equipe?.categorie ||
                    equipe?.categorie || 'Categorie non definie' }}</p>
                </div>
              </div>
            </article>
          </div>

          <MatchCompositionSection v-if="evenementSelectionne.type === 'match'" :composition="compositionMatch"
            :chargement="chargementComposition" titre="Composition du match"
            description="Lecture seule de la composition preparee par votre coach." />

          <MatchSheetSection v-if="evenementSelectionne.type === 'match'" :feuille-match="feuilleMatch"
            :chargement="chargementFeuilleMatch" titre="Feuille de match"
            description="Lecture seule du score et du resume de la rencontre." />

          <MatchStatisticsSection v-if="evenementSelectionne.type === 'match'" :statistiques="statistiquesMatch"
            :chargement="chargementStatistiquesMatch" titre="Statistiques du match"
            description="Lecture seule des statistiques individuelles de la rencontre." />

          <div class="border-t border-[#eef2f8] bg-white p-5">
            <article class="rounded-[26px] border border-[#e8edf6] bg-white p-5">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <p class="text-sm font-black text-[#111827]">Ma disponibilite</p>
                  <p class="mt-2 text-sm font-semibold text-[#64748b]">
                    Repondez avant la convocation pour aider votre coach a preparer le groupe.
                  </p>
                </div>
                <span class="rounded-full px-3 py-1.5 text-[10px] font-black"
                  :class="badgeDisponibilite(evenementSelectionne.disponibilite?.reponse).cls">
                  {{ badgeDisponibilite(evenementSelectionne.disponibilite?.reponse).label }}
                </span>
              </div>

              <textarea v-model="commentaireDisponibilite" rows="3"
                class="mt-4 w-full resize-none rounded-2xl border border-[#dbe2ef] bg-[#f8fbff] px-4 py-3 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff] focus:bg-white"
                placeholder="Ajouter un commentaire pour votre coach..."></textarea>

              <div class="mt-4 flex flex-wrap gap-2">
                <button type="button"
                  class="rounded-full bg-[#111827] px-5 py-2.5 text-xs font-black text-white transition hover:bg-[#1f36bf] disabled:opacity-60"
                  :disabled="envoiDisponibilite" @click="repondreDisponibilite('present')">
                  {{ envoiDisponibilite ? 'Envoi...' : 'Je suis present' }}
                </button>
                <button type="button"
                  class="rounded-full border border-[#f59e0b] bg-white px-5 py-2.5 text-xs font-black text-[#f59e0b] transition hover:bg-[#fff7ed] disabled:opacity-60"
                  :disabled="envoiDisponibilite" @click="repondreDisponibilite('incertain')">
                  Je suis incertain
                </button>
                <button type="button"
                  class="rounded-full border border-[#ef4444] bg-white px-5 py-2.5 text-xs font-black text-[#ef4444] transition hover:bg-[#fef2f2] disabled:opacity-60"
                  :disabled="envoiDisponibilite" @click="repondreDisponibilite('absent')">
                  Je suis absent
                </button>
              </div>
            </article>
          </div>
        </div>
      </div>
    </template>

    <div v-else-if="evenementsFiltres.length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <article v-for="ev in evenementsFiltres" :key="ev.id"
        class="group relative cursor-pointer overflow-hidden rounded-[26px] border border-[#edf1f7] bg-white p-4 transition duration-300 hover:-translate-y-1 hover:border-[#d7e0f5] hover:bg-[#fbfcff]"
        @click="ouvrirDetail(ev)">
        <div class="relative h-[170px] overflow-hidden rounded-[20px] bg-cover bg-center"
          :style="{ backgroundImage: backgroundEvenement(ev) }">
          <div
            class="absolute inset-0 rounded-[20px] bg-[radial-gradient(circle_at_25%_10%,rgba(255,255,255,0.30),transparent_28%),linear-gradient(180deg,transparent,rgba(0,0,0,0.82))]">
          </div>
          <div class="relative z-10 flex h-full flex-col justify-between p-4 text-white">
            <div class="flex items-start justify-between gap-3">
              <span
                class="rounded-full border border-white/30 bg-white/14 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] backdrop-blur-md">
                {{ ev.type }}
              </span>
              <span class="rounded-full px-2.5 py-1 text-[10px] font-black" :class="badgeStatut(ev.statut).cls">
                {{ badgeStatut(ev.statut).label }}
              </span>
            </div>
            <div>
              <p class="text-[11px] font-bold text-white/70">{{ formatDate(ev.date_debut) }}</p>
              <h4 class="mt-1 line-clamp-2 text-2xl font-black leading-tight text-white">{{ ev.titre }}</h4>
              <p v-if="ev.lieu" class="mt-1 line-clamp-1 text-xs font-semibold text-white/72">{{ ev.lieu }}</p>
            </div>
          </div>
        </div>

        <div v-if="ev.type === 'match'" class="mt-4 rounded-[22px] bg-[#f5f7fb] p-3">
          <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2">
            <div class="min-w-0 text-center">
              <p class="truncate text-xs font-black text-[#111827]">{{ ev.equipe?.nom || equipe?.nom || 'Equipe' }}</p>
            </div>
            <span class="rounded-full bg-[#111827] px-2.5 py-1 text-[9px] font-black text-white">VS</span>
            <div class="min-w-0 text-center">
              <p class="truncate text-xs font-black text-[#111827]">{{ ev.adversaire_equipe?.nom || ev.adversaire ||
                'Adversaire' }}</p>
            </div>
          </div>
        </div>

        <div v-else class="mt-4 rounded-[22px] bg-[#f5f7fb] p-3">
          <p class="truncate text-sm font-black text-[#111827]">{{ ev.type === 'entrainement' ? 'Entrainement' :
            'Reunion'
            }}</p>
          <p class="mt-1 truncate text-xs font-semibold text-[#64748b]">{{ ev.equipe?.nom || equipe?.nom || 'Equipe' }}
          </p>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
          <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full px-3 py-1.5 text-[10px] font-black"
              :class="badgeDisponibilite(ev.disponibilite?.reponse).cls">
              {{ badgeDisponibilite(ev.disponibilite?.reponse).label }}
            </span>
            <span v-if="ev.convocation || ev.ma_convocation" class="rounded-full px-3 py-1.5 text-[10px] font-black"
              :class="badgeConvocation((ev.convocation || ev.ma_convocation).statut).cls">
              {{ badgeConvocation((ev.convocation || ev.ma_convocation).statut).label }}
            </span>
            <span v-else class="rounded-full bg-[#f1f5f9] px-3 py-1.5 text-[10px] font-black text-[#64748b]">
              Non convoque
            </span>
          </div>
          <button type="button"
            class="rounded-full bg-[#111827] px-4 py-2 text-xs font-black text-white transition hover:bg-[#2446d8]"
            @click.stop="ouvrirDetail(ev)">
            Ouvrir
          </button>
        </div>

        <div
          class="pointer-events-none absolute -bottom-10 -right-10 h-28 w-28 rounded-full bg-[#2446d8]/8 transition duration-300 group-hover:scale-125 group-hover:bg-[#2446d8]/12">
        </div>
      </article>
    </div>

    <div v-else-if="!equipe"
      class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <p class="text-sm font-semibold text-[#6b7280]">Vous n'etes dans aucune equipe pour le moment.</p>
    </div>

    <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <h4 class="text-2xl text-[#111827]">Aucun evenement</h4>
      <p class="mt-2 text-sm font-semibold text-[#6b7280]">Aucun evenement planifie pour votre equipe.</p>
    </div>
  </section>
</template>
