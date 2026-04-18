<script setup>
import { computed } from 'vue'

const props = defineProps({
  convocations: { type: Array, default: () => [] },
  equipes: { type: Array, default: () => [] },
  equipeId: { type: String, default: '' },
  chargement: { type: Boolean, default: false },
  recherche: { type: String, default: '' },
})

const emit = defineEmits(['update:equipeId', 'update:recherche', 'modifier-statut'])

const badgeStatutConvocation = (statut) => ({
  convoque: { label: 'Convoque', cls: 'bg-[#eef2ff] text-[#1f36bf]' },
  confirme: { label: 'Confirme', cls: 'bg-[#ecfdf5] text-[#16a34a]' },
  refuse: { label: 'Refuse', cls: 'bg-[#fef2f2] text-[#ef4444]' },
  en_attente: { label: 'En attente', cls: 'bg-[#fff7ed] text-[#f59e0b]' },
}[statut] || { label: statut, cls: 'bg-[#f8fbff] text-[#64748b]' })

const convocationsFiltrees = computed(() => {
  const q = props.recherche.toLowerCase()
  if (!q) return props.convocations
  return props.convocations.filter((c) =>
    [c.utilisateur?.nom, c.utilisateur?.prenom, c.utilisateur?.name, c.evenement?.titre].some((v) => v?.toLowerCase().includes(q))
  )
})
</script>

<template>
  <section class="mt-6">
    <div class="mx-auto max-w-3xl text-center">
      <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#4c6fff]">Gestion coach</p>
      <h3 class="text-3xl font-black tracking-normal text-[#111827] sm:text-4xl">Convocations</h3>
      <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[#6b7280]">Consultez et gerez les convocations de vos equipes.</p>

      <div class="mx-auto mt-5 max-w-2xl space-y-2">
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

    <div v-if="chargement" class="mt-6 space-y-3">
      <div v-for="n in 5" :key="n" class="h-[72px] animate-pulse rounded-2xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
    </div>

    <div v-else-if="convocationsFiltrees.length" class="mt-6 space-y-3">
      <article
        v-for="conv in convocationsFiltrees"
        :key="conv.id"
        class="flex items-center justify-between gap-4 rounded-2xl border border-[#e6edf8] bg-white px-4 py-3"
      >
        <div class="flex items-center gap-3 min-w-0">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[radial-gradient(circle_at_35%_25%,#ffffff,#dbe7ff_28%,#2446d8_72%)] text-sm font-black text-white">
            {{ (conv.utilisateur?.prenom || conv.utilisateur?.name || 'J')[0] }}
          </span>
          <div class="min-w-0">
            <p class="truncate text-sm font-black text-[#111827]">
              {{ [conv.utilisateur?.prenom, conv.utilisateur?.nom].filter(Boolean).join(' ') || conv.utilisateur?.name || 'Joueur' }}
            </p>
            <p class="truncate text-[11px] font-semibold text-[#64748b]">{{ conv.evenement?.titre || 'Evenement' }}</p>
          </div>
        </div>

        <div class="flex shrink-0 items-center gap-2">
          <span class="rounded-full px-2.5 py-1 text-[10px] font-black" :class="badgeStatutConvocation(conv.statut).cls">
            {{ badgeStatutConvocation(conv.statut).label }}
          </span>
          <select
            :value="conv.statut"
            class="h-8 rounded-full border border-[#dbe2ef] bg-white px-2 text-[11px] font-bold text-[#1f2a44] outline-none"
            @change="emit('modifier-statut', { convocation: conv, statut: $event.target.value })"
          >
            <option value="convoque">Convoque</option>
            <option value="confirme">Confirme</option>
            <option value="refuse">Refuse</option>
            <option value="en_attente">En attente</option>
          </select>
        </div>
      </article>
    </div>

    <div v-else-if="equipeId" class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <h4 class="text-2xl text-[#111827]">Aucune convocation</h4>
      <p class="mt-2 text-sm font-semibold text-[#6b7280]">Creez des evenements et ajoutez des convocations depuis la section Evenements.</p>
    </div>

    <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <p class="text-sm font-semibold text-[#6b7280]">Selectionnez une equipe pour voir ses convocations.</p>
    </div>
  </section>
</template>
