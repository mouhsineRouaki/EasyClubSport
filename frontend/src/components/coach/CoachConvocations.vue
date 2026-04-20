<script setup>
import { computed } from 'vue'
import AppModuleHeader from '../common/AppModuleHeader.vue'

const props = defineProps({
  convocations: { type: Array, default: () => [] },
  equipes: { type: Array, default: () => [] },
  equipeId: { type: String, default: '' },
  evenements: { type: Array, default: () => [] },
  evenementId: { type: String, default: '' },
  chargement: { type: Boolean, default: false },
  recherche: { type: String, default: '' },
})

const emit = defineEmits(['update:equipeId', 'update:evenementId', 'update:recherche', 'modifier-statut'])

const badgeStatutConvocation = (statut) => ({
  convoque: { label: 'Convoque', cls: 'bg-[#eef2ff] text-[#1f36bf]' },
  confirme: { label: 'Confirme', cls: 'bg-[#ecfdf5] text-[#16a34a]' },
  refuse: { label: 'Refuse', cls: 'bg-[#fef2f2] text-[#ef4444]' },
  en_attente: { label: 'En attente', cls: 'bg-[#fff7ed] text-[#f59e0b]' },
}[statut] || { label: statut, cls: 'bg-[#f8fbff] text-[#64748b]' })

const convocationsFiltrees = computed(() => {
  const q = props.recherche.toLowerCase()
  const listeParEvenement = props.evenementId
    ? props.convocations.filter((c) => String(c.evenement?.id) === String(props.evenementId))
    : []

  if (!q) return listeParEvenement
  return listeParEvenement.filter((c) =>
    [c.joueur?.nom, c.joueur?.prenom, c.joueur?.name, c.joueur?.email, c.evenement?.titre]
      .some((v) => v?.toLowerCase().includes(q))
  )
})
</script>

<template>
  <section class="mt-6">
    <AppModuleHeader badge="Gestion coach" titre="Convocations"
      description="Selectionnez une equipe puis un evenement pour voir les joueurs convoques et leur reponse.">
      <div class="mx-auto mt-5 max-w-2xl space-y-3">
        <select :value="equipeId"
          class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]"
          @change="emit('update:equipeId', $event.target.value)">
          <option value="">Choisir une equipe</option>
          <option v-for="eq in equipes" :key="eq.id" :value="String(eq.id)">{{ eq.nom }}</option>
        </select>

        <select :value="evenementId"
          class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]"
          @change="emit('update:evenementId', $event.target.value)">
          <option value="">Choisir un evenement</option>
          <option v-for="evenement in evenements" :key="evenement.id" :value="String(evenement.id)">
            {{ evenement.titre }} - {{ evenement.date_debut ? new Date(evenement.date_debut).toLocaleDateString('fr-FR')
              : 'Date non definie' }}
          </option>
        </select>

        <div class="rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-2">
          <input :value="recherche" type="text" placeholder="Rechercher un joueur convoque..."
            class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
            @input="emit('update:recherche', $event.target.value)" />
        </div>
      </div>
    </AppModuleHeader>

    <div v-if="chargement" class="mt-6 space-y-3">
      <div v-for="n in 5" :key="n"
        class="h-[72px] animate-pulse rounded-2xl bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
    </div>

    <div v-else-if="convocationsFiltrees.length" class="mt-6 space-y-3">
      <article v-for="conv in convocationsFiltrees" :key="conv.id"
        class="flex items-center justify-between gap-4 rounded-2xl border border-[#e6edf8] bg-white px-4 py-3">
        <div class="flex items-center gap-3 min-w-0">
          <span
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[radial-gradient(circle_at_35%_25%,#ffffff,#dbe7ff_28%,#2446d8_72%)] text-sm font-black text-white">
            {{ (conv.joueur?.nom || conv.joueur?.name || 'J')[0] }}
          </span>
          <div class="min-w-0">
            <p class="truncate text-sm font-black text-[#111827]">
              {{ conv.joueur?.nom || conv.joueur?.name || 'Joueur' }}
            </p>
            <p class="truncate text-[11px] font-semibold text-[#64748b]">{{ conv.joueur?.email || 'Email non defini' }}
            </p>
          </div>
        </div>

        <div class="flex shrink-0 items-center gap-2">
          <span class="rounded-full px-2.5 py-1 text-[10px] font-black"
            :class="badgeStatutConvocation(conv.statut).cls">
            {{ badgeStatutConvocation(conv.statut).label }}
          </span>
          <select :value="conv.statut"
            class="h-8 rounded-full border border-[#dbe2ef] bg-white px-2 text-[11px] font-bold text-[#1f2a44] outline-none"
            @change="emit('modifier-statut', { convocation: conv, statut: $event.target.value })">
            <option value="convoque">Convoque</option>
            <option value="confirme">Confirme</option>
            <option value="refuse">Refuse</option>
            <option value="en_attente">En attente</option>
          </select>
        </div>
      </article>
    </div>

    <div v-else-if="equipeId && evenementId"
      class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <h4 class="text-2xl text-[#111827]">Aucune convocation</h4>
      <p class="mt-2 text-sm font-semibold text-[#6b7280]">Aucun joueur n'est convoque pour cet evenement.</p>
    </div>

    <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <p class="text-sm font-semibold text-[#6b7280]">Selectionnez une equipe puis un evenement pour voir les
        convocations.</p>
    </div>
  </section>
</template>
