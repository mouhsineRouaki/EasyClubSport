<script setup>
import AppButton from '@/shared/components/ui/AppButton.vue'
import AppCoverCard from '@/shared/components/ui/AppCoverCard.vue'
import { resolveCoverImage } from '@/shared/utils/coverImage'

const props = defineProps({
  equipe: {
    type: Object,
    required: true,
  },
  active: {
    type: Boolean,
    default: false,
  },
  selectable: {
    type: Boolean,
    default: false,
  },
  actionLabel: {
    type: String,
    default: 'Voir joueurs',
  },
})

const emit = defineEmits(['show-players', 'select'])

const imageEquipe = (eq = {}) => resolveCoverImage(eq?.image_url, eq?.logo_url, eq?.logo, eq?.photo_url, eq?.club?.logo_url)
</script>

<template>
  <AppCoverCard
    :image="imageEquipe(equipe)"
    :active="active"
    :badge="equipe.categorie || 'Equipe'"
    status-label="Coach"
    min-height-class="min-h-[255px]"
    :class="selectable ? 'cursor-pointer' : ''"
    @click="selectable ? emit('select', equipe) : null"
  >
    <template #body>
      <div>
        <h4 class="line-clamp-2 text-2xl font-black leading-tight text-white">
          {{ equipe.nom }}
        </h4>
        <p class="mt-2 line-clamp-2 text-xs font-semibold leading-5 text-white/78">
          {{ equipe.club?.nom || 'Club non defini' }}
        </p>
      </div>
    </template>
    <template #footer>
      <div>
        <div class="grid grid-cols-3 gap-1.5 rounded-2xl border border-white/20 bg-white/16 p-2 text-center backdrop-blur-md">
          <div>
            <p class="text-lg font-black text-white">{{ equipe.joueurs_total || 0 }}</p>
            <p class="text-[9px] font-bold uppercase text-white/68">Joueurs</p>
          </div>
          <div>
            <p class="text-lg font-black text-white">{{ equipe.evenements_total || 0 }}</p>
            <p class="text-[9px] font-bold uppercase text-white/68">Events</p>
          </div>
          <div>
            <p class="text-lg font-black text-white">{{ equipe.statut === 'active' ? 1 : 0 }}</p>
            <p class="text-[9px] font-bold uppercase text-white/68">Active</p>
          </div>
        </div>
        <div class="mt-2 flex items-center justify-between gap-2 rounded-2xl border border-white/15 bg-white/12 px-3 py-2 text-[10px] font-semibold text-white/80 backdrop-blur-md">
          <span>{{ equipe.code_invitation || 'Code non defini' }}</span>
          <AppButton
            type="button"
            variant="secondary"
            size="xs"
            class="!border-white !bg-white !text-[#1f36bf] hover:!bg-[#eef4ff]"
            @click.stop="emit('show-players', equipe.id)"
          >
            {{ actionLabel }}
          </AppButton>
        </div>
      </div>
    </template>
  </AppCoverCard>
</template>
