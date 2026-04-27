<script setup>
import AppAvatar from '@/shared/components/ui/AppAvatar.vue'

const props = defineProps({
  joueur: {
    type: Object,
    required: true,
  },
})

const nomComplet = () =>
  [props.joueur?.prenom, props.joueur?.nom_famille].filter(Boolean).join(' ') ||
  props.joueur?.nom ||
  props.joueur?.name ||
  'Joueur'

const initials = () =>
  nomComplet()
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase() || 'JR'

const stats = [
  ['Attaque', props.joueur?.attaque || 0],
  ['Defense', props.joueur?.defense || 0],
  ['Vitesse', props.joueur?.vitesse || 0],
  ['Passe', props.joueur?.passe || 0],
  ['Dribble', props.joueur?.dribble || 0],
  ['Physique', props.joueur?.physique || 0],
]
</script>

<template>
  <article class="relative overflow-hidden rounded-[30px] border border-[#dbe5f2] bg-[linear-gradient(145deg,#0f172a_0%,#172554_38%,#1d4ed8_72%,#14b8a6_100%)] p-5 text-white shadow-[0_24px_60px_rgba(15,23,42,0.24)]">
    <div class="absolute -right-16 top-8 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
    <div class="absolute -left-10 bottom-0 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>

    <div class="relative">
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-center gap-4">
          <AppAvatar
            :src="joueur.photo_url || joueur.photo || ''"
            :alt="nomComplet()"
            :initials="initials()"
            size-class="h-20 w-20"
            rounded-class="rounded-[24px]"
          />

          <div>
            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-white/70">Carte premium</p>
            <h3 class="mt-2 text-2xl font-black">{{ nomComplet() }}</h3>
            <p class="mt-1 text-sm font-semibold text-white/75">
              {{ joueur.poste_principal || 'Poste a definir' }}
              <span v-if="joueur.numero_joueur"> · #{{ joueur.numero_joueur }}</span>
            </p>
          </div>
        </div>

        <div class="rounded-[24px] border border-white/15 bg-white/10 px-4 py-3 text-center backdrop-blur">
          <p class="text-[11px] font-black uppercase tracking-[0.18em] text-white/65">Note</p>
          <p class="mt-2 text-4xl font-black">{{ joueur.note_globale || '--' }}</p>
        </div>
      </div>

      <div class="mt-5 grid gap-3 sm:grid-cols-2">
        <div class="rounded-[24px] border border-white/10 bg-white/10 p-4 backdrop-blur">
          <p class="text-[11px] font-black uppercase tracking-[0.18em] text-white/70">Profil</p>
          <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
            <div>
              <p class="text-white/60">Pied fort</p>
              <p class="mt-1 font-black">{{ joueur.pied_fort || 'Non defini' }}</p>
            </div>
            <div>
              <p class="text-white/60">Statut</p>
              <p class="mt-1 font-black">{{ joueur.statut || 'Actif' }}</p>
            </div>
            <div class="col-span-2">
              <p class="text-white/60">Poste secondaire</p>
              <p class="mt-1 font-black">{{ joueur.poste_secondaire || 'Aucun' }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-[24px] border border-white/10 bg-white/10 p-4 backdrop-blur">
          <p class="text-[11px] font-black uppercase tracking-[0.18em] text-white/70">Attributs</p>
          <div class="mt-3 space-y-3">
            <div v-for="[label, value] in stats" :key="label">
              <div class="mb-1 flex items-center justify-between text-xs font-bold uppercase tracking-[0.14em] text-white/75">
                <span>{{ label }}</span>
                <span>{{ value }}</span>
              </div>
              <div class="h-2 rounded-full bg-white/15">
                <div class="h-2 rounded-full bg-white" :style="{ width: `${Math.min(Number(value) || 0, 99)}%` }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </article>
</template>
