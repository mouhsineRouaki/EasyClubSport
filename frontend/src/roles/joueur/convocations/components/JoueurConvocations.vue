<script setup>
import { computed, ref } from 'vue'
import AppModuleHeader from '@/shared/components/AppModuleHeader.vue'
import JoueurConvocationCard from '@/roles/joueur/convocations/components/JoueurConvocationCard.vue'
import JoueurConvocationDetailsModal from '@/roles/joueur/convocations/components/JoueurConvocationDetailsModal.vue'

const props = defineProps({
  convocations: { type: Array, default: () => [] },
  equipe: { type: Object, default: null },
  chargement: { type: Boolean, default: false },
  recherche: { type: String, default: '' },
})

const emit = defineEmits(['update:recherche', 'repondre'])

const convocationActive = ref(null)

const convocationsFiltrees = computed(() => {
  const terme = props.recherche.trim().toLowerCase()

  if (!terme) {
    return props.convocations
  }

  return props.convocations.filter((convocation) =>
    [
      convocation?.evenement?.titre,
      convocation?.evenement?.lieu,
      convocation?.evenement?.type,
      convocation?.equipe?.nom,
      convocation?.evenement?.adversaire,
      convocation?.evenement?.adversaire_equipe?.nom,
      convocation?.club?.nom,
    ].some((valeur) => valeur?.toLowerCase().includes(terme))
  )
})

const ouvrirDetails = (convocation) => {
  convocationActive.value = convocation
}

const fermerDetails = () => {
  convocationActive.value = null
}

const repondrePuisFermer = (reponse) => {
  if (!convocationActive.value) return
  emit('repondre', { convocation: convocationActive.value, reponse })
  convocationActive.value = null
}
</script>

<template>
  <section class="mt-6">
    <AppModuleHeader
      badge="Mon espace joueur"
      titre="Convocations"
      :description="equipe ? '' : 'Consultez vos convocations et repondez rapidement a votre coach.'"
    >
      <p v-if="equipe" class="mx-auto mt-1 max-w-2xl text-sm leading-6 text-[#6b7280]">
        Les convocations liees a <span class="font-black text-[#111827]">{{ equipe.nom }}</span>
      </p>

      <div class="mx-auto mt-4 max-w-3xl rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-2">
        <input
          :value="recherche"
          type="text"
          placeholder="Rechercher une convocation..."
          class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
          @input="emit('update:recherche', $event.target.value)"
        />
      </div>
    </AppModuleHeader>

    <div v-if="chargement" class="mt-6 grid gap-4 lg:grid-cols-2">
      <div
        v-for="n in 4"
        :key="n"
        class="h-[280px] animate-pulse rounded-[28px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"
      ></div>
    </div>

    <div v-else-if="convocationsFiltrees.length" class="mt-6 grid gap-4 lg:grid-cols-2">
      <JoueurConvocationCard
        v-for="convocation in convocationsFiltrees"
        :key="convocation.id"
        :convocation="convocation"
        :equipe="equipe"
        @open="ouvrirDetails"
        @reply="emit('repondre', $event)"
      />
    </div>

    <div v-else-if="!equipe" class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <p class="text-sm font-semibold text-[#6b7280]">Vous n'etes dans aucune equipe pour le moment.</p>
    </div>

    <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <h4 class="text-2xl text-[#111827]">Aucune convocation</h4>
      <p class="mt-2 text-sm font-semibold text-[#6b7280]">Aucune convocation n'est disponible pour le moment.</p>
    </div>

    <JoueurConvocationDetailsModal
      v-if="convocationActive"
      :convocation="convocationActive"
      :equipe="equipe"
      @close="fermerDetails"
      @reply="repondrePuisFermer"
    />
  </section>
</template>
