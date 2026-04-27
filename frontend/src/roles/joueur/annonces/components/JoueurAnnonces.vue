<script setup>
import { computed, ref, watch } from 'vue'
import AppModuleHeader from '@/shared/components/AppModuleHeader.vue'
import AppDetailsTable from '@/shared/components/details/AppDetailsTable.vue'
import AppButton from '@/shared/components/ui/AppButton.vue'
import PresidentAnnouncementCard from '@/roles/president/annonces/components/PresidentAnnouncementCard.vue'

const props = defineProps({
  annonces: { type: Array, default: () => [] },
  equipe: { type: Object, default: null },
  chargement: { type: Boolean, default: false },
  recherche: { type: String, default: '' },
  pagination: { type: Object, default: null },
})

const emit = defineEmits(['update:recherche', 'change-page'])

const annonceSelectionnee = ref(null)
const mode = ref('liste')

const formatDateHeure = (date) => {
  if (!date) return '-'

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(date))
}

const detailsAnnonce = computed(() => {
  if (!annonceSelectionnee.value) return []

  return [
    { label: 'Club', value: annonceSelectionnee.value.club?.nom || props.equipe?.club?.nom || '-' },
    { label: 'Auteur', value: annonceSelectionnee.value.auteur?.nom || 'President' },
    { label: 'Email', value: annonceSelectionnee.value.auteur?.email || '-' },
    { label: 'Publication', value: formatDateHeure(annonceSelectionnee.value.created_at) },
  ]
})

const ouvrirDetail = (annonce) => {
  annonceSelectionnee.value = annonce
  mode.value = 'detail'
}

const retourListe = () => {
  mode.value = 'liste'
}

watch(
  () => props.annonces,
  (liste) => {
    if (!annonceSelectionnee.value) return

    annonceSelectionnee.value = liste.find((annonce) => String(annonce.id) === String(annonceSelectionnee.value.id)) || annonceSelectionnee.value
  },
  { deep: true },
)
</script>

<template>
  <section class="mt-6">
    <AppModuleHeader
      badge="Mon espace joueur"
      titre="Annonces"
      :description="equipe ? '' : 'Consultez les annonces publiees par votre club.'"
    >
      <p v-if="equipe" class="mx-auto mt-1 max-w-2xl text-sm leading-6 text-[#6b7280]">
        Annonces de <span class="font-black text-[#111827]">{{ equipe.club?.nom || equipe.nom }}</span>
      </p>

      <div class="mx-auto mt-4 max-w-2xl rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-2">
        <input
          :value="recherche"
          type="text"
          placeholder="Rechercher une annonce..."
          class="h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
          @input="emit('update:recherche', $event.target.value)"
        />
      </div>
    </AppModuleHeader>

    <div v-if="chargement" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="n in 6"
        :key="n"
        class="h-[320px] animate-pulse rounded-[30px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"
      ></div>
    </div>

    <template v-else-if="mode === 'detail' && annonceSelectionnee">
      <div class="mt-6 rounded-[30px] border border-[#e6edf8] bg-white p-4 sm:p-5">
        <div class="mb-4 flex items-center justify-between gap-3">
          <AppButton type="button" variant="secondary" size="sm" @click="retourListe">
            Retour
          </AppButton>
          <span class="rounded-full bg-[#ecfdf5] px-4 py-2 text-xs font-black uppercase text-[#16a34a]">
            Active
          </span>
        </div>

        <section class="rounded-[22px] bg-white">
          <img
            v-if="annonceSelectionnee.image_url"
            :src="annonceSelectionnee.image_url"
            :alt="annonceSelectionnee.titre"
            class="h-[240px] w-full rounded-[22px] object-cover"
          />
          <div
            v-else
            class="h-[240px] w-full rounded-[22px] bg-[linear-gradient(135deg,#dbe7ff_0%,#eef4ff_45%,#f8fbff_100%)]"
          ></div>

          <div class="mt-5">
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#4c6fff]">Annonce</p>
            <h3 class="mt-2 text-3xl font-black text-[#111827]">{{ annonceSelectionnee.titre }}</h3>
          </div>

          <div class="mt-5">
            <AppDetailsTable :items="detailsAnnonce" :columns="4" />
          </div>

          <section class="mt-5 rounded-[18px] bg-[#f8fbff] p-4">
            <p class="text-sm font-black text-[#111827]">Contenu</p>
            <p class="mt-3 whitespace-pre-line text-sm font-semibold leading-7 text-[#64748b]">
              {{ annonceSelectionnee.contenu || 'Aucun contenu disponible pour cette annonce.' }}
            </p>
          </section>
        </section>
      </div>
    </template>

    <template v-else-if="!equipe">
      <div class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
        <h4 class="text-2xl font-black text-[#111827]">Aucune equipe active</h4>
        <p class="mt-2 text-sm font-semibold text-[#6b7280]">
          Rejoignez une equipe pour consulter les annonces de votre club.
        </p>
      </div>
    </template>

    <template v-else>
      <div v-if="annonces.length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <PresidentAnnouncementCard
          v-for="annonce in annonces"
          :key="annonce.id"
          :annonce="annonce"
          :active="annonceSelectionnee?.id === annonce.id"
          @select="ouvrirDetail"
        />
      </div>

      <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
        <h4 class="text-2xl font-black text-[#111827]">Aucune annonce trouvee</h4>
        <p class="mt-2 text-sm font-semibold text-[#6b7280]">
          Aucune annonce ne correspond a votre recherche pour le moment.
        </p>
      </div>

      <div v-if="pagination" class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#e6edf8] bg-[#f8fbff] px-4 py-3">
        <p class="text-xs font-bold text-[#6b7280]">Page {{ pagination.current_page || 1 }} / {{ pagination.last_page || 1 }}</p>
        <div class="flex gap-2">
          <button
            type="button"
            class="rounded-full border border-[#dbe2ef] px-4 py-2 text-xs font-black text-[#1f2a44] disabled:opacity-40"
            :disabled="(pagination.current_page || 1) <= 1"
            @click="emit('change-page', (pagination.current_page || 1) - 1)"
          >
            Precedent
          </button>
          <button
            type="button"
            class="rounded-full bg-[#111827] px-4 py-2 text-xs font-black text-white disabled:opacity-40"
            :disabled="(pagination.current_page || 1) >= (pagination.last_page || 1)"
            @click="emit('change-page', (pagination.current_page || 1) + 1)"
          >
            Suivant
          </button>
        </div>
      </div>
    </template>
  </section>
</template>
