<script setup>
import AppCoverCard from '@/shared/components/ui/AppCoverCard.vue'
import { resolveCoverImage } from '@/shared/utils/coverImage'

const props = defineProps({
  equipes: { type: Array, default: () => [] },
  chargement: { type: Boolean, default: false },
  recherche: { type: String, default: '' },
})

const emit = defineEmits(['update:recherche'])
const imageEquipe = (eq = {}) => resolveCoverImage(eq?.image_url, eq?.logo_url, eq?.logo, eq?.photo_url, eq?.club?.logo_url)
</script>

<template>
  <section class="mt-6">
    <div class="mx-auto max-w-3xl text-center">
      <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Mon espace joueur</p>
      <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Mes equipes</h3>
      <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#6b7280]">Toutes les equipes dont vous faites partie.</p>
      <div class="mx-auto mt-5 max-w-2xl rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-2">
        <input
          :value="recherche"
          type="text"
          placeholder="Rechercher une equipe..."
          class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
          @input="emit('update:recherche', $event.target.value)"
        />
      </div>
    </div>

    <div v-if="chargement" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="n in 6" :key="n" class="h-[230px] animate-pulse rounded-[30px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
    </div>

    <div v-else-if="equipes.length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <AppCoverCard
        v-for="(equipe, idx) in equipes"
        :key="equipe.id"
        :image="imageEquipe(equipe)"
        :badge="equipe.categorie || 'Equipe'"
        :status-label="equipe.statut || 'active'"
        :status-class="equipe.statut === 'active' ? 'bg-[#ecfdf5] text-[#16a34a]' : 'bg-[#f1f5f9] text-[#64748b]'"
        min-height-class="min-h-[230px]"
        :class="idx % 2 === 1 ? 'md:translate-y-5' : ''"
      >
        <template #body>
          <div>
            <h4 class="text-3xl font-black leading-tight text-white">{{ equipe.nom }}</h4>
            <p class="mt-2 text-xs font-semibold text-white/76">{{ equipe.club?.nom || 'Club non defini' }}</p>
            <p class="mt-1 text-[11px] font-semibold text-white/60">Coach : {{ equipe.coach?.nom || equipe.coach?.name || 'Non assign' }}</p>
          </div>
        </template>
        <template #footer>
          <div class="flex items-center justify-between">
            <p class="text-xs font-black text-white/82">{{ equipe.joueurs_total || 0 }} joueurs</p>
            <span class="rounded-full bg-white/20 px-3 py-1.5 text-[10px] font-black text-white backdrop-blur-md">
              {{ equipe.role_equipe || 'Joueur' }}
            </span>
          </div>
        </template>
      </AppCoverCard>
    </div>

    <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <h4 class="text-2xl text-[#111827]">Aucune equipe trouvee</h4>
      <p class="mt-2 text-sm font-semibold text-[#6b7280]">Vous n'avez pas encore ete integre dans une equipe.</p>
    </div>
  </section>
</template>
