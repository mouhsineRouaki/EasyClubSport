<script setup>
import { computed } from 'vue'
import AppButton from '@/shared/components/ui/AppButton.vue'
import AppCoverCard from '@/shared/components/ui/AppCoverCard.vue'
import { resolveCoverImage } from '@/shared/utils/coverImage'

const props = defineProps({
  club: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['edit', 'delete'])

const imageClub = computed(() =>
  resolveCoverImage(props.club.logo_url, props.club.logo)
)
</script>

<template>
  <AppCoverCard
    :image="imageClub"
    :badge="club.ville || 'Club'"
    status-label="Club"
    min-height-class="min-h-[310px]"
    type="button"
  >
    <template #body>
      <div>
        <h4 class="line-clamp-2 text-2xl font-black leading-tight text-white">
          {{ club.nom }}
        </h4>
        <p class="mt-2 text-xs font-semibold text-white/78">
          {{ club.ville || 'Ville non definie' }}<span v-if="club.pays">, {{ club.pays }}</span>
        </p>
        <p class="mt-2 line-clamp-2 text-xs font-semibold leading-5 text-white/72">
          {{ club.description || club.adresse || 'Aucune description disponible pour ce club.' }}
        </p>
      </div>
    </template>

    <template #footer>
      <div>
        <div class="grid grid-cols-3 gap-2 rounded-2xl border border-white/18 bg-white/14 p-3 text-center backdrop-blur-md">
          <div>
            <p class="text-lg font-black text-white">{{ club.equipes_total || 0 }}</p>
            <p class="text-[10px] font-bold uppercase text-white/68">Equipes</p>
          </div>
          <div>
            <p class="text-lg font-black text-white">{{ club.joueurs_total || 0 }}</p>
            <p class="text-[10px] font-bold uppercase text-white/68">Joueurs</p>
          </div>
          <div>
            <p class="text-lg font-black text-white">{{ club.coachs_total || 0 }}</p>
            <p class="text-[10px] font-bold uppercase text-white/68">Coachs</p>
          </div>
        </div>

        <div class="mt-2 rounded-2xl border border-white/15 bg-white/12 px-3 py-2 text-[11px] font-semibold leading-5 text-white/80 backdrop-blur-md">
          <p class="truncate">Email : {{ club.email || '-' }}</p>
          <p class="truncate">Telephone : {{ club.telephone || '-' }}</p>
        </div>

        <div class="mt-3 flex gap-2">
          <AppButton type="button" variant="secondary" size="sm" block @click.stop="emit('edit', club)">
            Modifier
          </AppButton>
          <AppButton type="button" variant="danger" size="sm" block @click.stop="emit('delete', club)">
            Supprimer
          </AppButton>
        </div>
      </div>
    </template>
  </AppCoverCard>
</template>
