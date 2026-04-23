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

const emit = defineEmits(['close', 'reply'])

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

const formatHeure = (date) => {
  if (!date) return '-'
  return new Intl.DateTimeFormat('fr-FR', { timeStyle: 'short' }).format(new Date(date))
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
  <Teleport to="body">
    <div
      class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 px-3 pb-3 pt-10 backdrop-blur-sm sm:items-center sm:p-6"
      @click.self="emit('close')"
    >
      <div class="w-full max-w-4xl overflow-hidden rounded-[30px] bg-white shadow-[0_24px_80px_rgba(15,23,42,0.22)]">
        <div class="relative min-h-[240px] bg-cover bg-center p-6 text-white sm:p-8" :style="{ backgroundImage: styleImage(convocation) }">
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(255,255,255,0.22),transparent_24%),linear-gradient(180deg,rgba(7,16,58,0.16),rgba(7,16,58,0.88))]"></div>

          <div class="relative z-10">
            <button
              type="button"
              class="absolute right-0 top-0 rounded-full border border-white/25 bg-white/12 px-4 py-2 text-xs font-black text-white transition hover:bg-white/20"
              @click="emit('close')"
            >
              Fermer
            </button>

            <div class="max-w-3xl">
              <span class="inline-flex rounded-full border border-white/25 bg-white/12 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] backdrop-blur-md">
                {{ convocation.evenement?.type || 'Evenement' }}
              </span>
              <h4 class="mt-4 text-3xl font-black leading-tight text-white sm:text-4xl">
                {{ nomEquipeDomicile(convocation) }}
                <span class="px-2 text-white/70">vs</span>
                {{ nomEquipeAdverse(convocation) }}
              </h4>
              <p class="mt-3 max-w-2xl text-sm font-semibold text-white/80">
                {{ convocation.evenement?.titre || 'Evenement du club' }}
              </p>
            </div>
          </div>
        </div>

        <div class="space-y-5 p-5 sm:p-6">
          <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[18px] bg-[#f8fbff] px-4 py-3">
              <p class="text-[10px] font-black uppercase tracking-[0.16em] text-[#94a3b8]">Date</p>
              <p class="mt-1 text-sm font-black text-[#111827]">{{ formatDate(convocation.evenement?.date_debut) }}</p>
            </div>
            <div class="rounded-[18px] bg-[#f8fbff] px-4 py-3">
              <p class="text-[10px] font-black uppercase tracking-[0.16em] text-[#94a3b8]">Heure</p>
              <p class="mt-1 text-sm font-black text-[#111827]">{{ formatHeure(convocation.evenement?.date_debut) }}</p>
            </div>
            <div class="rounded-[18px] bg-[#f8fbff] px-4 py-3">
              <p class="text-[10px] font-black uppercase tracking-[0.16em] text-[#94a3b8]">Lieu</p>
              <p class="mt-1 text-sm font-black text-[#111827]">{{ convocation.evenement?.lieu || '-' }}</p>
            </div>
            <div class="rounded-[18px] bg-[#f8fbff] px-4 py-3">
              <p class="text-[10px] font-black uppercase tracking-[0.16em] text-[#94a3b8]">Statut</p>
              <span class="mt-1 inline-flex rounded-full px-2.5 py-1 text-[10px] font-black" :class="statutConvocation(convocation.statut).classe">
                {{ statutConvocation(convocation.statut).label }}
              </span>
            </div>
          </div>

          <div class="rounded-[22px] border border-[#e6edf8] bg-white p-4">
            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-[#94a3b8]">Description</p>
            <p class="mt-2 text-sm font-semibold leading-6 text-[#475569]">
              {{ convocation.evenement?.description || 'Aucune description detaillee pour cet evenement.' }}
            </p>
          </div>

          <div class="flex flex-col gap-3 sm:flex-row">
            <button
              type="button"
              class="w-full rounded-full px-4 py-3 text-sm font-black transition sm:flex-1"
              :class="peutRepondre(convocation.statut) ? 'bg-[#0f172a] text-white hover:bg-black' : 'cursor-not-allowed bg-[#e5e7eb] text-[#94a3b8]'"
              :disabled="!peutRepondre(convocation.statut)"
              @click="emit('reply', 'confirme')"
            >
              {{ convocation.statut === 'confirme' ? 'Presence deja confirmee' : 'Confirmer ma presence' }}
            </button>
            <button
              type="button"
              class="w-full rounded-full border px-4 py-3 text-sm font-black transition sm:flex-1"
              :class="peutRepondre(convocation.statut) ? 'border-[#dbe2ef] bg-white text-[#111827] hover:border-[#b8c7e6] hover:bg-[#f8fbff]' : 'cursor-not-allowed border-[#e5e7eb] bg-[#f8fafc] text-[#94a3b8]'"
              :disabled="!peutRepondre(convocation.statut)"
              @click="emit('reply', 'refuse')"
            >
              {{ convocation.statut === 'refuse' ? 'Convocation deja refusee' : 'Refuser la convocation' }}
            </button>
            <button
              type="button"
              class="w-full rounded-full border border-[#dbe2ef] bg-[#f8fbff] px-4 py-3 text-sm font-black text-[#475569] transition hover:border-[#b8c7e6] hover:bg-white sm:flex-1"
              @click="emit('close')"
            >
              Retour
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>
