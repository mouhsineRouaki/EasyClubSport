<script setup>
import blueBackground from '../../assets/Background.jpg'

const props = defineProps({
  equipes: { type: Array, default: () => [] },
  chargement: { type: Boolean, default: false },
  recherche: { type: String, default: '' },
})

const emit = defineEmits(['update:recherche', 'voir-joueurs'])

const imageEquipe = (eq = {}) => eq?.logo_url || eq?.logo || blueBackground
const backgroundEquipe = (eq = {}) => `linear-gradient(145deg, rgba(8,18,72,0.86), rgba(36,70,216,0.64)), url(${imageEquipe(eq)})`
</script>

<template>
  <section class="mt-6">
    <div class="mx-auto max-w-3xl text-center">
      <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Gestion coach</p>
      <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Mes equipes</h3>
      <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#6b7280]">Consultez toutes les equipes que vous encadrez.</p>
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
      <article
        v-for="(equipe, idx) in equipes"
        :key="equipe.id"
        class="relative min-h-[230px] overflow-hidden rounded-[30px] border border-white/70 bg-cover bg-center p-5 text-white transition hover:-translate-y-1 cursor-pointer"
        :class="idx % 2 === 1 ? 'md:translate-y-5' : ''"
        :style="{ backgroundImage: backgroundEquipe(equipe) }"
      >
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_10%,rgba(255,255,255,0.32),transparent_26%),linear-gradient(180deg,transparent,rgba(0,0,0,0.18))]"></div>
        <div class="relative z-10 flex min-h-[190px] flex-col justify-between">
          <div class="flex items-start justify-between">
            <p class="w-max rounded-full border border-white/30 bg-white/14 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] backdrop-blur-md">
              {{ equipe.categorie || 'Equipe' }}
            </p>
            <span class="rounded-full px-2 py-1 text-[10px] font-black" :class="equipe.statut === 'active' ? 'bg-[#ecfdf5] text-[#16a34a]' : 'bg-[#f1f5f9] text-[#64748b]'">
              {{ equipe.statut || 'active' }}
            </span>
          </div>
          <div>
            <h4 class="text-3xl font-black leading-tight text-white">{{ equipe.nom }}</h4>
            <p class="mt-2 text-xs font-semibold text-white/76">{{ equipe.club?.nom || 'Club non defini' }}</p>
            <p class="mt-1 text-[11px] font-semibold text-white/60">Code: {{ equipe.code_invitation || '—' }}</p>
          </div>
          <div class="flex items-center justify-between">
            <p class="text-xs font-black text-white/82">{{ equipe.joueurs_total || 0 }} joueurs</p>
            <button type="button" class="rounded-full bg-white px-4 py-2 text-xs font-black text-[#1f36bf] hover:bg-[#eef4ff]" @click.stop="emit('voir-joueurs', equipe.id)">
              Voir joueurs
            </button>
          </div>
        </div>
      </article>
    </div>

    <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <h4 class="text-2xl text-[#111827]">Aucune equipe trouvee</h4>
      <p class="mt-2 text-sm font-semibold text-[#6b7280]">Vous n'avez pas encore d'equipes assignees.</p>
    </div>
  </section>
</template>
