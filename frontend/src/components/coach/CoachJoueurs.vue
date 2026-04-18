<script setup>
import { computed } from 'vue'

const props = defineProps({
  joueurs: { type: Array, default: () => [] },
  equipes: { type: Array, default: () => [] },
  equipeId: { type: String, default: '' },
  chargement: { type: Boolean, default: false },
  recherche: { type: String, default: '' },
})

const emit = defineEmits(['update:equipeId', 'update:recherche'])

const joueursFiltres = computed(() => {
  const q = props.recherche.toLowerCase()
  if (!q) return props.joueurs
  return props.joueurs.filter((j) =>
    [j.nom, j.prenom, j.name, j.email].some((v) => v?.toLowerCase().includes(q))
  )
})
</script>

<template>
  <section class="mt-6">
    <div class="mx-auto max-w-3xl text-center">
      <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Gestion coach</p>
      <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Joueurs de l'equipe</h3>
      <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#6b7280]">Choisissez une equipe pour voir ses joueurs.</p>

      <div class="mx-auto mt-5 max-w-2xl">
        <select
          :value="equipeId"
          class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]"
          @change="emit('update:equipeId', $event.target.value)"
        >
          <option value="">Choisir une equipe</option>
          <option v-for="eq in equipes" :key="eq.id" :value="String(eq.id)">{{ eq.nom }}</option>
        </select>
      </div>
    </div>

    <div v-if="chargement" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <div v-for="n in 8" :key="n" class="h-[140px] animate-pulse rounded-[26px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
    </div>

    <div v-else-if="joueursFiltres.length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <article
        v-for="joueur in joueursFiltres"
        :key="joueur.id"
        class="rounded-[26px] border border-[#e6edf8] bg-white p-4 transition hover:border-[#cfdaf2] hover:shadow-sm"
      >
        <div class="flex items-center gap-3">
          <img v-if="joueur.photo_url || joueur.photo" :src="joueur.photo_url || joueur.photo" :alt="joueur.nom" class="h-12 w-12 rounded-2xl object-cover" />
          <span v-else class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff,#dbe7ff_28%,#2446d8_72%)] text-lg font-black text-white">
            {{ (joueur.prenom || joueur.name || 'J')[0] }}
          </span>
          <div class="min-w-0">
            <p class="truncate text-sm font-black text-[#111827]">
              {{ [joueur.prenom, joueur.nom].filter(Boolean).join(' ') || joueur.name || 'Joueur' }}
            </p>
            <p class="truncate text-[11px] font-semibold text-[#64748b]">{{ joueur.email || '—' }}</p>
          </div>
        </div>
        <div class="mt-3 flex items-center justify-between">
          <span class="rounded-full px-2.5 py-1 text-[10px] font-black"
            :class="joueur.statut === 'actif' ? 'bg-[#ecfdf5] text-[#16a34a]' : joueur.statut === 'blesse' ? 'bg-[#fef2f2] text-[#ef4444]' : 'bg-[#f1f5f9] text-[#64748b]'"
          >
            {{ joueur.statut || 'actif' }}
          </span>
          <span class="text-[11px] font-semibold text-[#94a3b8]">{{ joueur.telephone || '' }}</span>
        </div>
      </article>
    </div>

    <div v-else-if="equipeId" class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <h4 class="text-2xl text-[#111827]">Aucun joueur trouve</h4>
      <p class="mt-2 text-sm font-semibold text-[#6b7280]">Cette equipe n'a pas encore de joueurs.</p>
    </div>

    <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <p class="text-sm font-semibold text-[#6b7280]">Selectionnez une equipe pour voir ses joueurs.</p>
    </div>
  </section>
</template>
