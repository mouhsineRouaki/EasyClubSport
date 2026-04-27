<script setup>
import AppButton from '@/shared/components/ui/AppButton.vue'
import AppCoverCard from '@/shared/components/ui/AppCoverCard.vue'
import { resolveCoverImage } from '@/shared/utils/coverImage'

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

const logoEquipe = (eq = {}) => eq?.logo_url || eq?.logo || ''
const imageEvenement = (ev = {}) => resolveCoverImage(ev.image_url, ev.photo_url, ev.image, logoEquipe(ev.equipe), ev.adversaire_equipe?.logo_url)
const imageEquipe = (eq = {}) => resolveCoverImage(eq?.image_url, eq?.logo_url, eq?.logo, eq?.photo_url, eq?.club?.logo_url)
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
        <AppButton type="button" variant="secondary" size="sm" class="mt-3" @click="emit('aller-module', 'evenements')">
          Voir tous
        </AppButton>
      </div>

      <div v-if="evenementsDashboard.length" class="mt-5 overflow-hidden rounded-[30px] bg-[#f7f9ff] p-3 [mask-image:linear-gradient(90deg,transparent,black_8%,black_92%,transparent)]">
        <div class="dashboard-event-carousel flex w-max gap-4">
          <AppCoverCard
            v-for="(ev, i) in evenementsCarousel"
            :key="`${ev.id}-${i}`"
            :image="imageEvenement(ev)"
            :badge="formatDate(ev.date_debut)"
            min-height-class="h-[270px]"
            class="w-[250px] shrink-0 sm:w-[320px]"
          >
            <template #body>
              <div class="flex h-full flex-col items-center justify-center text-center">
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
              <AppButton type="button" variant="secondary" size="sm" class="mt-5 !border-white !bg-white !text-[#1f36bf]" @click="emit('aller-module', 'evenements')">
                Ouvrir
              </AppButton>
              </div>
            </template>
          </AppCoverCard>
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
        <AppButton type="button" variant="secondary" size="sm" class="mt-3" @click="emit('aller-module', 'equipes')">
          Voir l equipe
        </AppButton>
      </div>

      <div v-if="chargementEquipes" class="mt-5">
        <div class="h-[230px] animate-pulse rounded-[30px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
      </div>

      <div v-else-if="equipeActive" class="mt-5">
        <AppCoverCard
          :image="imageEquipe(equipeActive)"
          :badge="equipeActive.categorie || 'Equipe'"
          status-label="Coach"
          min-height-class="min-h-[230px]"
        >
          <template #body>
            <div>
              <h4 class="text-3xl font-black leading-tight text-white">{{ equipeActive.nom }}</h4>
              <p class="mt-2 text-xs font-semibold leading-5 text-white/76">{{ equipeActive.club?.nom || 'Club non defini' }}</p>
              <p class="mt-1 text-[11px] font-semibold text-white/70">{{ equipeActive.club?.ville || 'Ville non definie' }}</p>
            </div>
          </template>
          <template #footer>
            <div class="flex items-center justify-between gap-3">
              <div class="flex items-center gap-4 text-xs font-black text-white/82">
                <p>{{ equipeActive.joueurs_total || 0 }} joueurs</p>
                <p>{{ equipeActive.evenements_total || 0 }} evenements</p>
              </div>
              <AppButton type="button" variant="secondary" size="sm" class="!border-white !bg-white !text-[#1f36bf]" @click="emit('aller-module', 'equipes')">
                Details
              </AppButton>
            </div>
          </template>
        </AppCoverCard>
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
