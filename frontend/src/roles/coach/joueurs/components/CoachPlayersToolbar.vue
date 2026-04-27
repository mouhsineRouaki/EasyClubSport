<script setup>
import AppModuleHeader from '@/shared/components/AppModuleHeader.vue'
import AppButton from '@/shared/components/ui/AppButton.vue'

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
  chargement: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:equipeId', 'update:recherche', 'add'])
</script>

<template>
  <AppModuleHeader
    badge="Gestion coach"
    titre="Gestion des joueurs"
    description="Choisissez une equipe, puis consultez, recherchez ou ajoutez des joueurs avec la meme structure visuelle que les autres modules coach."
  >
    <div class="mx-auto max-w-2xl space-y-3">
      <select
        :value="equipeId"
        class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]"
        :disabled="chargement"
        @change="emit('update:equipeId', $event.target.value)"
      >
        <option value="">Choisir une equipe</option>
        <option v-for="equipe in equipes" :key="equipe.id" :value="String(equipe.id)">
          {{ equipe.nom }}
        </option>
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

    <AppButton type="button" class="mt-4" @click="emit('add')">
      Nouveau joueur
    </AppButton>
  </AppModuleHeader>
</template>
