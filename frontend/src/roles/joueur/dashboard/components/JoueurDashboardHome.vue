<script setup>
import { computed } from 'vue'
import blueBackground from '@/assets/Background.jpg'

const props = defineProps({
  equipe: { type: Object, default: null },
  evenements: { type: Array, default: () => [] },
  convocations: { type: Array, default: () => [] },
  derniereMiseAJour: { type: String, default: null },
  chargement: { type: Boolean, default: false },
})

const emit = defineEmits(['aller-module', 'ouvrir-rejoindre-equipe'])

const formatDate = (date) => {
  if (!date) return '-'
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(new Date(date))
}

const formatDateHeure = (date) => {
  if (!date) return 'Jamais'
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'short', timeStyle: 'medium' }).format(new Date(date))
}

const imageEvenement = (evenement = {}) => evenement.image_url || evenement.photo_url || evenement.image || blueBackground
const fondEvenement = (evenement = {}) =>
  `linear-gradient(180deg, rgba(7,16,58,0.20), rgba(7,16,58,0.88)), url(${imageEvenement(evenement)})`

const coachs = computed(() => {
  if (!props.equipe) return []
  if (Array.isArray(props.equipe.coachs)) return props.equipe.coachs
  if (props.equipe.coach) return [props.equipe.coach]
  return []
})

const statsCartes = computed(() => [
  { label: 'Joueurs', valeur: props.equipe?.joueurs_total ?? props.equipe?.joueurs?.length ?? 0 },
  { label: 'Evenements', valeur: props.equipe?.evenements_total ?? props.evenements.length ?? 0 },
  { label: 'Convocations', valeur: props.equipe?.convocations_en_attente ?? props.convocations.length ?? 0 },
  { label: 'Coachs', valeur: coachs.value.length },
])

const prochainsEvenements = computed(() => props.evenements.slice(0, 4))
const convocationsRecentes = computed(() => props.convocations.slice(0, 4))

const badgeStatutEvenement = (statut) =>
  ({
    planifie: { label: 'Planifie', classe: 'bg-[#eef2ff] text-[#1f36bf]' },
    en_cours: { label: 'En cours', classe: 'bg-[#ecfdf5] text-[#16a34a]' },
    termine: { label: 'Termine', classe: 'bg-[#f1f5f9] text-[#64748b]' },
    annule: { label: 'Annule', classe: 'bg-[#fef2f2] text-[#ef4444]' },
  }[statut] || { label: statut || 'Statut', classe: 'bg-[#f8fbff] text-[#64748b]' })

const badgeStatutConvocation = (statut) =>
  ({
    convoque: { label: 'Convoque', classe: 'bg-[#eef2ff] text-[#1f36bf]' },
    confirme: { label: 'Confirme', classe: 'bg-[#ecfdf5] text-[#16a34a]' },
    refuse: { label: 'Refuse', classe: 'bg-[#fef2f2] text-[#ef4444]' },
    en_attente: { label: 'En attente', classe: 'bg-[#fff7ed] text-[#f59e0b]' },
  }[statut] || { label: statut || 'Statut', classe: 'bg-[#f8fbff] text-[#64748b]' })
</script>

<template>
  <div>
    <div v-if="chargement" class="space-y-4">
      <div class="h-40 animate-pulse rounded-[28px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
      <div class="h-56 animate-pulse rounded-[28px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
      <div class="grid gap-4 lg:grid-cols-3">
        <div v-for="n in 3" :key="n" class="h-48 animate-pulse rounded-[28px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
      </div>
    </div>

    <template v-else-if="equipe">
      <div class="mt-6 text-center">
        <span class="inline-flex rounded-full bg-[#f2f6ff] px-3 py-1 text-[11px] font-bold text-[#1f36bf]">
          Maj {{ formatDateHeure(derniereMiseAJour) }}
        </span>
      </div>

      <section class="mt-5 rounded-[22px] border border-[#e6edf8] bg-white px-5 py-8">
        <div class="text-center">
          <h2 class="text-3xl font-black text-[#111827] sm:text-4xl">Statistiques principales</h2>
          <p class="mt-2 text-sm font-semibold text-[#6b7280]">Vue rapide de votre equipe et de votre activite.</p>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
          <article
            v-for="stat in statsCartes"
            :key="stat.label"
            class="rounded-full border border-[#dbe2ef] bg-white px-5 py-4 text-center"
          >
            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#7c8aa5]">{{ stat.label }}</p>
            <strong class="mt-1 block text-3xl font-black text-[#111827]">{{ stat.valeur }}</strong>
          </article>
        </div>
      </section>

      <section class="mt-5 rounded-[22px] border border-[#e6edf8] bg-white px-5 py-8">
        <div class="text-center">
          <h2 class="text-3xl font-black text-[#111827] sm:text-4xl">Evenements proches</h2>
          <p class="mt-2 text-sm font-semibold text-[#6b7280]">Les prochains rendez-vous a suivre en priorite.</p>
          <button
            type="button"
            class="mt-4 inline-flex rounded-full border border-[#dbe2ef] px-4 py-2 text-sm font-extrabold text-[#1f36bf] transition hover:bg-[#f8fbff]"
            @click="emit('aller-module', 'evenements')"
          >
            Voir tous
          </button>
        </div>

        <div v-if="prochainsEvenements.length" class="mt-6 grid gap-4 lg:grid-cols-3">
          <article
            v-for="evenement in prochainsEvenements"
            :key="evenement.id"
            class="relative min-h-[240px] overflow-hidden rounded-[26px] border border-[#e6edf8] bg-cover bg-center p-5 text-white"
            :style="{ backgroundImage: fondEvenement(evenement) }"
          >
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_15%,rgba(255,255,255,0.14),transparent_26%),linear-gradient(180deg,rgba(7,16,58,0.10),rgba(7,16,58,0.86))]"></div>
            <div class="relative z-10 flex h-full flex-col justify-between text-center">
              <div class="flex justify-center">
                <span class="rounded-full border border-white/25 bg-white/12 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] backdrop-blur-md">
                  {{ formatDate(evenement.date_debut) }}
                </span>
              </div>

              <div>
                <h3 class="text-3xl font-black leading-tight text-white">{{ evenement.titre }}</h3>
                <p class="mx-auto mt-3 max-w-[240px] text-sm font-semibold text-white/80">
                  {{ evenement.lieu || 'Lieu non defini' }}
                </p>
                <p v-if="evenement.type === 'match' && evenement.adversaire" class="mt-1 text-sm font-semibold text-white/75">
                  vs {{ evenement.adversaire }}
                </p>
              </div>

              <div class="flex justify-center">
                <span
                  class="rounded-full px-3 py-1.5 text-[10px] font-black"
                  :class="badgeStatutEvenement(evenement.statut).classe"
                >
                  {{ badgeStatutEvenement(evenement.statut).label }}
                </span>
              </div>
            </div>
          </article>
        </div>

        <p v-else class="mt-6 rounded-2xl border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-4 py-8 text-center text-sm font-semibold text-[#6b7280]">
          Aucun evenement a venir pour le moment.
        </p>
      </section>

      <section class="mt-5 rounded-[22px] border border-[#e6edf8] bg-white px-5 py-8">
        <div class="text-center">
          <h2 class="text-3xl font-black text-[#111827] sm:text-4xl">Convocations recentes</h2>
          <p class="mt-2 text-sm font-semibold text-[#6b7280]">Les reponses a suivre dans votre espace joueur.</p>
          <button
            type="button"
            class="mt-4 inline-flex rounded-full border border-[#dbe2ef] px-4 py-2 text-sm font-extrabold text-[#1f36bf] transition hover:bg-[#f8fbff]"
            @click="emit('aller-module', 'convocations')"
          >
            Voir toutes
          </button>
        </div>

        <div v-if="convocationsRecentes.length" class="mt-6 grid gap-4 lg:grid-cols-3">
          <article
            v-for="convocation in convocationsRecentes"
            :key="convocation.id"
            class="rounded-[22px] border border-[#e6edf8] bg-[#f8fbff] p-5 text-center"
          >
            <p class="text-lg font-black text-[#111827]">{{ convocation.evenement?.titre || 'Evenement' }}</p>
            <p class="mt-2 text-sm font-semibold text-[#6b7280]">{{ formatDate(convocation.evenement?.date_debut) }}</p>
            <p class="mt-1 text-sm font-semibold text-[#6b7280]">{{ convocation.evenement?.lieu || 'Lieu non defini' }}</p>
            <div class="mt-4 flex justify-center">
              <span
                class="rounded-full px-3 py-1.5 text-[10px] font-black"
                :class="badgeStatutConvocation(convocation.statut).classe"
              >
                {{ badgeStatutConvocation(convocation.statut).label }}
              </span>
            </div>
          </article>
        </div>

        <p v-else class="mt-6 rounded-2xl border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-4 py-8 text-center text-sm font-semibold text-[#6b7280]">
          Aucune convocation pour le moment.
        </p>
      </section>
    </template>

    <div v-else class="mt-10 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-16 text-center">
      <p class="text-2xl font-black text-[#111827]">Vous n'etes dans aucune equipe</p>
      <p class="mt-2 text-sm font-semibold text-[#6b7280]">Rejoignez votre equipe avec un code d invitation partage par votre coach ou votre president.</p>
      <button
        type="button"
        class="mt-5 inline-flex rounded-full bg-[#111827] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#1f2937]"
        @click="emit('ouvrir-rejoindre-equipe')"
      >
        Rejoindre une equipe
      </button>
    </div>
  </div>
</template>
