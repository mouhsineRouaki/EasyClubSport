<script setup>
const props = defineProps({
  evenement: {
    type: Object,
    required: true,
  },
  active: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['select'])

const formatDate = (date) => {
  if (!date) {
    return '-'
  }

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(date))
}

const logoEquipe = (equipe) => equipe?.logo_url || equipe?.logo || equipe?.club?.logo_url || ''
const libelleInvitation = (statut) => ({
  en_attente: 'En attente',
  accepte: 'Accepte',
  refuse: 'Refuse',
}[statut] || '')
</script>

<template>
  <button
    type="button"
    class="group relative overflow-hidden rounded-[26px] border bg-white p-4 text-left transition duration-300 hover:-translate-y-1 hover:bg-[#fbfcff]"
    :class="active ? 'border-[#2446d8] ring-4 ring-[#2446d8]/10' : 'border-[#edf1f7] hover:border-[#d7e0f5]'"
    @click="emit('select', evenement)"
  >
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-[#6b7280]">
          {{ evenement.type || 'evenement' }}
        </p>
        <h4 class="mt-2 line-clamp-2 text-xl font-black leading-tight tracking-normal text-[#111827]">
          {{ evenement.titre }}
        </h4>
      </div>
      <span class="rounded-full px-2.5 py-1 text-[9px] font-black capitalize" :class="evenement.statut === 'annule' ? 'bg-red-50 text-red-600' : evenement.statut === 'termine' ? 'bg-[#f1f5f9] text-[#475569]' : 'bg-[#ecfdf5] text-[#16a34a]'">
        {{ evenement.statut || 'planifie' }}
      </span>
    </div>
    <p v-if="evenement.type === 'match' && libelleInvitation(evenement.statut_invitation_adversaire)" class="mt-2 text-[10px] font-black uppercase tracking-[0.12em] text-[#f59e0b]">
      {{ libelleInvitation(evenement.statut_invitation_adversaire) }}
    </p>

    <div v-if="evenement.type === 'match'" class="mt-4 rounded-[22px] bg-[#f5f7fb] p-3">
      <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2">
        <div class="min-w-0 text-center">
          <img v-if="logoEquipe(evenement.equipe)" :src="logoEquipe(evenement.equipe)" :alt="evenement.equipe?.nom || 'Equipe'" class="mx-auto h-12 w-12 rounded-2xl object-cover" />
          <span v-else class="mx-auto block h-12 w-12 rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)]"></span>
          <p class="mt-2 truncate text-xs font-black text-[#111827]">{{ evenement.equipe?.nom || 'Equipe' }}</p>
        </div>
        <span class="rounded-full bg-[#111827] px-2.5 py-1 text-[9px] font-black text-white">VS</span>
        <div class="min-w-0 text-center">
          <img v-if="logoEquipe(evenement.adversaire_equipe)" :src="logoEquipe(evenement.adversaire_equipe)" :alt="evenement.adversaire_equipe?.nom || 'Adversaire'" class="mx-auto h-12 w-12 rounded-2xl object-cover" />
          <span v-else class="mx-auto block h-12 w-12 rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#e2e8f0_38%,#94a3b8_90%)]"></span>
          <p class="mt-2 truncate text-xs font-black text-[#111827]">{{ evenement.adversaire_equipe?.nom || evenement.adversaire || 'Adversaire' }}</p>
        </div>
      </div>
    </div>

    <div v-else class="mt-4 rounded-[22px] bg-[#f5f7fb] p-3">
      <div class="flex items-center gap-3">
        <img v-if="logoEquipe(evenement.equipe)" :src="logoEquipe(evenement.equipe)" :alt="evenement.equipe?.nom || 'Equipe'" class="h-12 w-12 rounded-2xl object-cover" />
        <span v-else class="block h-12 w-12 rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)]"></span>
        <div class="min-w-0">
          <p class="truncate text-sm font-black text-[#111827]">{{ evenement.type === 'entrainement' ? 'Entrainement' : 'Reunion' }}</p>
          <p class="truncate text-xs font-semibold text-[#64748b]">{{ evenement.equipe?.nom || 'Equipe' }}</p>
        </div>
      </div>
    </div>

    <div class="mt-4">
      <p class="text-xs font-bold text-[#2446d8]">
        {{ formatDate(evenement.date_debut) }}
      </p>
      <p class="mt-2 line-clamp-1 text-xs font-semibold text-[#64748b]">
        {{ evenement.lieu || 'Lieu non defini' }}
      </p>
    </div>

    <div class="pointer-events-none absolute -bottom-10 -right-10 h-28 w-28 rounded-full bg-[#2446d8]/8 transition duration-300 group-hover:scale-125 group-hover:bg-[#2446d8]/12"></div>
  </button>
</template>
