<script setup>
import blueBackground from '../../assets/Background.jpg'

const props = defineProps({
  statsCards: { type: Array, default: () => [] },
  evenementsDashboard: { type: Array, default: () => [] },
  evenementsCarousel: { type: Array, default: () => [] },
  equipeActive: { type: Object, default: null },
  chargementEquipes: { type: Boolean, default: false },
  derniereMiseAJour: { type: String, default: null },
})

const emit = defineEmits(['aller-module'])

const formatDate = (date) => {
  if (!date) return '-'
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(new Date(date))
}

const formatDateHeure = (date) => {
  if (!date) return 'Jamais'
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'short', timeStyle: 'medium' }).format(new Date(date))
}

const imageEvenement = (ev = {}) => ev.image_url || ev.photo_url || ev.image || blueBackground
const backgroundEvenement = (ev = {}) => `linear-gradient(180deg, rgba(7,16,58,0.18), rgba(7,16,58,0.86)), url(${imageEvenement(ev)})`
const logoEquipe = (eq = {}) => eq?.logo_url || eq?.logo || ''
const imageEquipe = (eq = {}) => eq?.logo_url || eq?.logo || blueBackground
const backgroundEquipe = (eq = {}) => `linear-gradient(145deg, rgba(8,18,72,0.86), rgba(36,70,216,0.64)), url(${imageEquipe(eq)})`
</script>

<template>
  <div>
    <!-- stats -->
    <section class="mt-6">
      <div class="text-center">
        <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Statistiques principales</h3>
        <p class="mx-auto mt-1 max-w-xl text-xs font-semibold text-[#6b7280]">Etat rapide de votre activite coach.</p>
        <span class="mt-3 inline-flex rounded-full bg-[#f2f6ff] px-3 py-1 text-[11px] font-bold text-[#1f36bf]">
          Maj {{ formatDateHeure(derniereMiseAJour) }}
        </span>
      </div>

      <div class="mt-5 flex flex-wrap justify-center gap-2.5">
        <article
          v-for="card in statsCards"
          :key="card.label"
          class="inline-flex min-w-[150px] items-center justify-between gap-4 rounded-full border border-[#e6edf8] bg-white px-4 py-3 transition hover:border-[#cfdaf2]"
        >
          <span class="text-xs font-black uppercase tracking-[0.12em] text-[#6b7280]">{{ card.label }}</span>
          <strong class="text-2xl font-black tracking-[-0.05em] text-[#111827]">{{ card.value }}</strong>
        </article>
      </div>
    </section>

    <!-- prochains evenements -->
    <section class="mt-7 rounded-[22px] border border-[#e6edf8] bg-white p-4">
      <div class="text-center">
        <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Evenements proches</h3>
        <p class="mx-auto mt-1 max-w-xl text-xs font-semibold text-[#6b7280]">Les prochains rendez-vous a suivre en priorite.</p>
        <button type="button" class="mt-3 inline-flex rounded-full border border-[#dbe2ef] px-3 py-1.5 text-xs font-extrabold text-[#1f36bf] transition hover:bg-[#f8fbff]" @click="emit('aller-module', 'evenements')">
          Voir tous
        </button>
      </div>

      <div v-if="evenementsDashboard.length" class="mt-5 overflow-hidden rounded-[30px] bg-[#f7f9ff] p-3 [mask-image:linear-gradient(90deg,transparent,black_8%,black_92%,transparent)]">
        <div class="dashboard-event-carousel flex w-max gap-4">
          <article
            v-for="(ev, i) in evenementsCarousel"
            :key="`${ev.id}-${i}`"
            class="relative h-[270px] w-[250px] shrink-0 overflow-hidden rounded-[30px] border border-white/60 bg-cover bg-center p-4 text-white sm:w-[320px]"
            :style="{ backgroundImage: backgroundEvenement(ev) }"
          >
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_10%,rgba(255,255,255,0.34),transparent_28%),linear-gradient(180deg,transparent,rgba(0,0,0,0.22))]"></div>
            <div class="relative z-10 flex h-full flex-col items-center justify-center text-center">
              <span class="rounded-full border border-white/35 bg-white/18 px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] backdrop-blur-md">
                {{ formatDate(ev.date_debut) }}
              </span>
              <div v-if="ev.type === 'match'" class="mt-4 grid w-full max-w-[230px] grid-cols-[1fr_auto_1fr] items-center gap-2">
                <img v-if="logoEquipe(ev.equipe)" :src="logoEquipe(ev.equipe)" :alt="ev.equipe?.nom" class="mx-auto h-12 w-12 rounded-2xl object-cover ring-4 ring-white/20" />
                <span v-else class="mx-auto block h-12 w-12 rounded-2xl bg-white/25 ring-4 ring-white/20"></span>
                <span class="rounded-full bg-white px-2.5 py-1 text-[9px] font-black text-[#111827]">VS</span>
                <span class="mx-auto block h-12 w-12 rounded-2xl bg-white/25 ring-4 ring-white/20"></span>
              </div>
              <img v-else-if="logoEquipe(ev.equipe)" :src="logoEquipe(ev.equipe)" :alt="ev.equipe?.nom" class="mt-4 h-14 w-14 rounded-2xl object-cover ring-4 ring-white/20" />
              <h4 class="mt-4 text-3xl font-black leading-tight text-white sm:text-4xl">{{ ev.titre }}</h4>
              <p class="mt-3 max-w-[220px] text-xs font-semibold leading-5 text-white/78">
                {{ ev.type === 'match' ? `${ev.equipe?.nom || 'Equipe'} vs ${ev.adversaire || 'Adversaire'}` : ev.equipe?.nom || '' }}
                <span v-if="ev.lieu"> - {{ ev.lieu }}</span>
              </p>
              <button type="button" class="mt-5 rounded-full bg-white px-5 py-2 text-xs font-black text-[#1f36bf] transition hover:bg-[#eef4ff]" @click="emit('aller-module', 'evenements')">
                Ouvrir
              </button>
            </div>
          </article>
        </div>
      </div>

      <p v-else class="mt-4 rounded-2xl border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-4 py-8 text-center text-sm font-semibold text-[#6b7280]">
        Aucun evenement proche pour le moment.
      </p>
    </section>

    <!-- mon equipe -->
    <section class="mt-7 rounded-[22px] border border-[#e6edf8] bg-white p-4">
      <div class="text-center">
        <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Mon equipe</h3>
        <p class="mx-auto mt-1 max-w-xl text-xs font-semibold text-[#6b7280]">L equipe active que vous encadrez dans votre espace coach.</p>
        <button type="button" class="mt-3 inline-flex rounded-full border border-[#dbe2ef] px-3 py-1.5 text-xs font-extrabold text-[#1f36bf] transition hover:bg-[#f8fbff]" @click="emit('aller-module', 'equipes')">
          Voir l equipe
        </button>
      </div>

      <div v-if="chargementEquipes" class="mt-5">
        <div class="h-[230px] animate-pulse rounded-[30px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
      </div>

      <div v-else-if="equipeActive" class="mt-5">
        <article
          class="relative min-h-[230px] overflow-hidden rounded-[30px] border border-white/70 bg-cover bg-center p-5 text-white transition hover:-translate-y-1"
          :style="{ backgroundImage: backgroundEquipe(equipeActive) }"
        >
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_10%,rgba(255,255,255,0.32),transparent_26%),linear-gradient(180deg,transparent,rgba(0,0,0,0.18))]"></div>
          <div class="relative z-10 flex min-h-[190px] flex-col justify-between">
            <p class="w-max rounded-full border border-white/30 bg-white/14 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] backdrop-blur-md">
              {{ equipeActive.categorie || 'Equipe' }}
            </p>
            <div>
              <h4 class="text-3xl font-black leading-tight text-white">{{ equipeActive.nom }}</h4>
              <p class="mt-2 text-xs font-semibold leading-5 text-white/76">{{ equipeActive.club?.nom || 'Club non defini' }}</p>
              <p class="mt-1 text-[11px] font-semibold text-white/70">{{ equipeActive.club?.ville || 'Ville non definie' }}</p>
            </div>
            <div class="flex items-center justify-between gap-3">
              <div class="flex items-center gap-4 text-xs font-black text-white/82">
                <p>{{ equipeActive.joueurs_total || 0 }} joueurs</p>
                <p>{{ equipeActive.evenements_total || 0 }} evenements</p>
              </div>
              <button type="button" class="rounded-full bg-white px-4 py-2 text-xs font-black text-[#1f36bf] transition hover:bg-[#eef4ff]" @click="emit('aller-module', 'equipes')">
                Details
              </button>
            </div>
          </div>
        </article>
      </div>

      <p v-else class="mt-4 rounded-2xl border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-4 py-8 text-center text-sm font-semibold text-[#6b7280]">
        Aucune equipe assignee pour le moment.
      </p>
    </section>
  </div>
</template>

<style scoped>
.dashboard-event-carousel {
  animation: carousel-scroll 28s linear infinite;
}
.dashboard-event-carousel:hover {
  animation-play-state: paused;
}
@keyframes carousel-scroll {
  from { transform: translateX(0); }
  to { transform: translateX(-50%); }
}
</style>
