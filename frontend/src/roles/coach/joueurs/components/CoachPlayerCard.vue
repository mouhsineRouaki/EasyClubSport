<script setup>
import { computed } from 'vue'
import AppCoverCard from '@/shared/components/ui/AppCoverCard.vue'
import { resolveCoverImage } from '@/shared/utils/coverImage'

const props = defineProps({
  joueur: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['select'])

const nomComplet = (joueur) =>
  [joueur?.prenom, joueur?.nom_famille].filter(Boolean).join(' ') ||
  joueur?.nom ||
  joueur?.name ||
  'Joueur'

const badgeClass = (statut) => {
  if (statut === 'actif') return 'bg-[#ecfdf5] text-[#16a34a]'
  if (statut === 'blesse') return 'bg-[#fef2f2] text-[#dc2626]'
  if (statut === 'suspendu') return 'bg-[#fff7ed] text-[#ea580c]'
  return 'bg-[#f1f5f9] text-[#475569]'
}

const imageJoueur = computed(() =>
  resolveCoverImage(props.joueur?.photo_url, props.joueur?.photo)
)
</script>

<template>
  <AppCoverCard
    :image="imageJoueur"
    :badge="joueur.poste_principal || 'Joueur'"
    :status-label="joueur.statut || 'inactif'"
    :status-class="badgeClass(joueur.statut)"
    min-height-class="min-h-[310px]"
    @click="emit('select', joueur)"
  >
    <template #body>
      <div>
        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-white/72">Effectif coach</p>
        <h3 class="mt-2 line-clamp-2 text-2xl font-black leading-tight text-white">{{ nomComplet(joueur) }}</h3>
        <p class="mt-2 text-sm font-semibold text-white/78">
          {{ joueur.poste_principal || 'Poste a definir' }}
          <span v-if="joueur.numero_joueur"> · #{{ joueur.numero_joueur }}</span>
        </p>
        <div class="mt-4 inline-flex rounded-full border border-white/18 bg-white/14 px-3 py-2 text-sm font-black text-white backdrop-blur-md">
          Note {{ joueur.note_globale || '--' }}
        </div>
      </div>
    </template>

    <template #footer>
      <div class="grid grid-cols-3 gap-2">
        <div class="rounded-[18px] border border-white/18 bg-white/14 px-3 py-2 text-center backdrop-blur-md">
          <p class="text-sm font-black text-white">{{ joueur.attaque || 0 }}</p>
          <p class="text-[10px] font-black uppercase tracking-[0.14em] text-white/68">ATQ</p>
        </div>
        <div class="rounded-[18px] border border-white/18 bg-white/14 px-3 py-2 text-center backdrop-blur-md">
          <p class="text-sm font-black text-white">{{ joueur.vitesse || 0 }}</p>
          <p class="text-[10px] font-black uppercase tracking-[0.14em] text-white/68">VIT</p>
        </div>
        <div class="rounded-[18px] border border-white/18 bg-white/14 px-3 py-2 text-center backdrop-blur-md">
          <p class="text-sm font-black text-white">{{ joueur.defense || 0 }}</p>
          <p class="text-[10px] font-black uppercase tracking-[0.14em] text-white/68">DEF</p>
        </div>
      </div>
    </template>
  </AppCoverCard>
</template>
