<script setup>
import { computed } from 'vue'
import AppModuleHeader from '@/shared/components/AppModuleHeader.vue'

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
    <AppModuleHeader
      badge="Gestion coach"
      titre="Joueurs de l'equipe"
      description="Retrouvez les joueurs avec les memes cartes et surfaces que dans l espace president."
    >
      <div class="mx-auto mt-5 max-w-2xl space-y-3">
        <select
          :value="equipeId"
          class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]"
          @change="emit('update:equipeId', $event.target.value)"
        >
          <option value="">Choisir une equipe</option>
          <option v-for="eq in equipes" :key="eq.id" :value="String(eq.id)">{{ eq.nom }}</option>
        </select>

        <div class="rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-2">
          <input
            :value="recherche"
            type="text"
            placeholder="Rechercher un joueur..."
            class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
            @input="emit('update:recherche', $event.target.value)"
          />
        </div>
      </div>
    </AppModuleHeader>

    <div v-if="chargement" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <div v-for="n in 8" :key="n" class="h-[170px] animate-pulse rounded-[26px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
    </div>

    <div v-else-if="joueursFiltres.length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <article
        v-for="joueur in joueursFiltres"
        :key="joueur.id"
        class="group relative overflow-hidden rounded-[26px] border border-[#edf1f7] bg-white p-4 text-left transition duration-300 hover:-translate-y-1 hover:border-[#d7e0f5] hover:bg-[#fbfcff]"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="flex min-w-0 items-center gap-3">
            <img v-if="joueur.photo_url || joueur.photo" :src="joueur.photo_url || joueur.photo" :alt="joueur.nom" class="h-12 w-12 rounded-2xl object-cover" />
            <span v-else class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff,#dbe7ff_28%,#2446d8_72%)] text-lg font-black text-white">
              {{ (joueur.prenom || joueur.name || 'J')[0] }}
            </span>
            <div class="min-w-0">
              <p class="truncate text-[10px] font-black uppercase tracking-[0.14em] text-[#6b7280]">Joueur</p>
              <h4 class="mt-2 truncate text-lg font-black leading-tight text-[#111827]">
                {{ [joueur.prenom, joueur.nom].filter(Boolean).join(' ') || joueur.name || 'Joueur' }}
              </h4>
            </div>
          </div>
          <span class="rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.12em]" :class="joueur.statut === 'actif' ? 'bg-[#ecfdf5] text-[#16a34a]' : joueur.statut === 'blesse' ? 'bg-[#fef2f2] text-[#ef4444]' : 'bg-[#f8fafc] text-[#64748b]'">
            {{ joueur.statut || 'Actif' }}
          </span>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-2">
          <div class="rounded-[14px] bg-[#f5f7fb] px-3 py-2">
            <p class="truncate text-xs font-black text-[#111827]">{{ joueur.email || '-' }}</p>
            <p class="text-[9px] font-black uppercase tracking-[0.1em] text-[#6b7280]">Email</p>
          </div>
          <div class="rounded-[14px] bg-[#f5f7fb] px-3 py-2">
            <p class="truncate text-xs font-black text-[#111827]">{{ joueur.telephone || '-' }}</p>
            <p class="text-[9px] font-black uppercase tracking-[0.1em] text-[#6b7280]">Telephone</p>
          </div>
        </div>

        <div class="pointer-events-none absolute -bottom-10 -right-10 h-28 w-28 rounded-full bg-[#2446d8]/8 transition duration-300 group-hover:scale-125 group-hover:bg-[#2446d8]/12"></div>
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

