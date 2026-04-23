<script setup>
import blueBackground from '@/assets/Background.jpg'

const props = defineProps({
  convocation: {
    type: Object,
    required: true,
  },
  equipe: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['open', 'reply'])

const statutConvocation = (statut) =>
  ({
    convoque: { label: 'Convoque', classe: 'bg-[#eef2ff] text-[#1f36bf]' },
    confirme: { label: 'Confirme', classe: 'bg-[#ecfdf5] text-[#16a34a]' },
    refuse: { label: 'Refuse', classe: 'bg-[#fef2f2] text-[#ef4444]' },
    en_attente: { label: 'En attente', classe: 'bg-[#fff7ed] text-[#f59e0b]' },
  }[statut] || { label: statut || 'Statut', classe: 'bg-[#f8fbff] text-[#64748b]' })

const formatDate = (date) => {
  if (!date) return '-'
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'long' }).format(new Date(date))
}

const imageConvocation = (convocation) =>
  convocation?.evenement?.image_url
  || convocation?.equipe?.logo_url
  || convocation?.evenement?.adversaire_equipe?.logo_url
  || convocation?.club?.logo_url
  || blueBackground

const styleImage = (convocation) =>
  `linear-gradient(180deg, rgba(5, 10, 34, 0.14), rgba(5, 10, 34, 0.88)), url(${imageConvocation(convocation)})`

const nomEquipeDomicile = (convocation) => convocation?.equipe?.nom || props.equipe?.nom || 'Mon equipe'

const nomEquipeAdverse = (convocation) =>
  convocation?.evenement?.adversaire_equipe?.nom
  || convocation?.evenement?.adversaire
  || 'Adversaire'

const peutRepondre = (statut) => ['convoque', 'en_attente'].includes(statut)
</script>

<template>
  <article class="group relative overflow-hidden rounded-[26px] border border-[#edf1f7] bg-white p-4 transition duration-300 hover:-translate-y-1 hover:border-[#d7e0f5] hover:bg-[#fbfcff]">
    <button type="button" class="block w-full text-left" @click="emit('open', convocation)">
      <div class="relative min-h-[190px] overflow-hidden rounded-[20px] bg-cover bg-center p-5 text-white" :style="{ backgroundImage: styleImage(convocation) }">
        <div class="absolute inset-0 rounded-[20px] bg-[radial-gradient(circle_at_20%_15%,rgba(255,255,255,0.25),transparent_26%),linear-gradient(180deg,rgba(7,16,58,0.08),rgba(7,16,58,0.82))]"></div>

        <div class="relative z-10 flex h-full min-h-[170px] flex-col justify-between">
          <div class="flex items-start justify-between gap-3">
            <span class="rounded-full border border-white/25 bg-white/12 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] backdrop-blur-md">
              {{ convocation.evenement?.type || 'Evenement' }}
            </span>
            <span class="rounded-full px-2.5 py-1 text-[10px] font-black" :class="statutConvocation(convocation.statut).classe">
              {{ statutConvocation(convocation.statut).label }}
            </span>
          </div>

          <div>
            <p class="text-[11px] font-bold text-white/70">{{ convocation.club?.nom || 'EasyClubSport' }}</p>
            <h4 class="mt-2 text-2xl font-black leading-tight text-white">
              {{ nomEquipeDomicile(convocation) }}
              <span class="px-2 text-white/75">vs</span>
              {{ nomEquipeAdverse(convocation) }}
            </h4>
            <p class="mt-2 text-sm font-semibold text-white/80">{{ convocation.evenement?.titre || 'Evenement du club' }}</p>
            <p class="mt-1 text-xs text-white/65">
              {{ formatDate(convocation.evenement?.date_debut) }}
              <span v-if="convocation.evenement?.lieu"> · {{ convocation.evenement.lieu }}</span>
            </p>
          </div>
        </div>
      </div>
    </button>

    <div class="mt-4 rounded-[22px] bg-[#f5f7fb] p-3">
      <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2">
        <div class="min-w-0 text-center">
          <p class="truncate text-xs font-black text-[#111827]">{{ nomEquipeDomicile(convocation) }}</p>
        </div>
        <span class="rounded-full bg-[#111827] px-2.5 py-1 text-[9px] font-black text-white">VS</span>
        <div class="min-w-0 text-center">
          <p class="truncate text-xs font-black text-[#111827]">{{ nomEquipeAdverse(convocation) }}</p>
        </div>
      </div>
    </div>

    <div class="mt-4 flex items-center gap-3">
      <button
        v-if="peutRepondre(convocation.statut)"
        type="button"
        class="flex-1 rounded-full bg-[#0f172a] px-4 py-3 text-sm font-black text-white transition hover:bg-black"
        @click="emit('reply', { convocation, reponse: 'confirme' })"
      >
        Confirmer
      </button>
      <button
        v-if="peutRepondre(convocation.statut)"
        type="button"
        class="flex-1 rounded-full border border-[#dbe2ef] bg-white px-4 py-3 text-sm font-black text-[#111827] transition hover:border-[#b8c7e6] hover:bg-[#f8fbff]"
        @click="emit('reply', { convocation, reponse: 'refuse' })"
      >
        Refuser
      </button>
      <button
        v-else
        type="button"
        class="w-full rounded-full border border-[#dbe2ef] bg-white px-4 py-3 text-sm font-black text-[#111827] transition hover:border-[#b8c7e6] hover:bg-[#f8fbff]"
        @click="emit('open', convocation)"
      >
        Voir les details
      </button>
    </div>

    <div class="pointer-events-none absolute -bottom-10 -right-10 h-28 w-28 rounded-full bg-[#2446d8]/8 transition duration-300 group-hover:scale-125 group-hover:bg-[#2446d8]/12"></div>
  </article>
</template>
