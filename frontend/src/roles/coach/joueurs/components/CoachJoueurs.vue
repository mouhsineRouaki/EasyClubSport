<script setup>
import AppEmptyState from '@/shared/components/ui/AppEmptyState.vue'
import CoachPlayerCard from '@/roles/coach/joueurs/components/CoachPlayerCard.vue'
import CoachPlayersToolbar from '@/roles/coach/joueurs/components/CoachPlayersToolbar.vue'

defineProps({
  equipes: {
    type: Array,
    default: () => [],
  },
  equipeId: {
    type: String,
    default: '',
  },
  recherche: {
    type: String,
    default: '',
  },
  chargementEquipes: {
    type: Boolean,
    default: false,
  },
  chargementJoueurs: {
    type: Boolean,
    default: false,
  },
  joueurs: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['update:equipeId', 'update:recherche', 'add', 'select-player'])
</script>

<template>
  <section class="mt-6 space-y-6">
    <CoachPlayersToolbar
      :equipes="equipes"
      :equipe-id="equipeId"
      :recherche="recherche"
      :chargement="chargementEquipes"
      @update:equipe-id="emit('update:equipeId', $event)"
      @update:recherche="emit('update:recherche', $event)"
      @add="emit('add')"
    />

    <div v-if="chargementJoueurs" class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
      <div v-for="item in 6" :key="item" class="h-[310px] animate-pulse rounded-[28px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
    </div>

    <div v-else-if="joueurs.length" class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
      <CoachPlayerCard
        v-for="joueur in joueurs"
        :key="joueur.id"
        :joueur="joueur"
        @select="emit('select-player', $event)"
      />
    </div>

    <AppEmptyState
      v-else-if="equipeId"
      title="Aucun joueur trouve"
      description="Ajoutez un joueur ou changez votre recherche."
    />

    <AppEmptyState
      v-else
      title="Selectionnez une equipe"
      description="Choisissez une equipe pour afficher et gerer ses joueurs."
    />
  </section>
</template>
