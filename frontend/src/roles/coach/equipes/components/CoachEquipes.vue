<script setup>
import AppModuleHeader from '@/shared/components/AppModuleHeader.vue'
import CoachEquipeCard from '@/roles/coach/equipes/components/CoachEquipeCard.vue'

const props = defineProps({
  equipes: { type: Array, default: () => [] },
  chargement: { type: Boolean, default: false },
  recherche: { type: String, default: '' },
})

const emit = defineEmits(['update:recherche', 'voir-joueurs'])

</script>

<template>
  <section class="mt-6">
    <AppModuleHeader badge="Gestion coach" titre="Mon equipe"
      description="Consultez l equipe que vous encadrez avec le meme langage visuel que l espace president.">
      <div class="mx-auto max-w-2xl rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-2">
        <input :value="recherche" type="text" placeholder="Rechercher une equipe..."
          class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
          @input="emit('update:recherche', $event.target.value)" />
      </div>
    </AppModuleHeader>

    <div v-if="chargement" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="n in 3" :key="n"
        class="h-[255px] animate-pulse rounded-[26px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
    </div>

    <div v-else-if="equipes.length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <CoachEquipeCard
        v-for="equipe in equipes"
        :key="equipe.id"
        :equipe="equipe"
        @show-players="emit('voir-joueurs', $event)"
      />
    </div>

    <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <h4 class="text-2xl text-[#111827]">Aucune equipe trouvee</h4>
      <p class="mt-2 text-sm font-semibold text-[#6b7280]">Vous n'avez pas encore d'equipe assignee.</p>
    </div>
  </section>
</template>
