<script setup>
import { computed, ref } from 'vue'
import blueBackground from '@/assets/Background.jpg'
import AppModuleHeader from '@/shared/components/AppModuleHeader.vue'

const props = defineProps({
  convocations: { type: Array, default: () => [] },
  equipe: { type: Object, default: null },
  chargement: { type: Boolean, default: false },
  recherche: { type: String, default: '' },
})

const emit = defineEmits(['update:recherche', 'repondre'])

const convocationActive = ref(null)

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
      <article
        v-for="convocation in convocationsFiltrees"
        :key="convocation.id"
        class="group relative overflow-hidden rounded-[26px] border border-[#edf1f7] bg-white p-4 transition duration-300 hover:-translate-y-1 hover:border-[#d7e0f5] hover:bg-[#fbfcff]"
      >
        <button
          type="button"
          class="block w-full text-left"
          @click="ouvrirDetails(convocation)"
        >
          <div
            class="relative min-h-[190px] overflow-hidden rounded-[20px] bg-cover bg-center p-5 text-white"
            :style="{ backgroundImage: styleImage(convocation) }"
          >
            <div class="absolute inset-0 rounded-[20px] bg-[radial-gradient(circle_at_20%_15%,rgba(255,255,255,0.25),transparent_26%),linear-gradient(180deg,rgba(7,16,58,0.08),rgba(7,16,58,0.82))]"></div>

            <div class="relative z-10 flex h-full min-h-[170px] flex-col justify-between">
              <div class="flex items-start justify-between gap-3">
                <span class="rounded-full border border-white/25 bg-white/12 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] backdrop-blur-md">
                  {{ convocation.evenement?.type || 'Evenement' }}
                </span>
                <span
                  class="rounded-full px-2.5 py-1 text-[10px] font-black"
                  :class="statutConvocation(convocation.statut).classe"
                >
                  {{ statutConvocation(convocation.statut).label }}
                </span>
              </div>

              <div>
                <p class="text-[11px] font-bold text-white/70">
                  {{ convocation.club?.nom || 'EasyClubSport' }}
                </p>
                <h4 class="mt-2 text-2xl font-black leading-tight text-white">
                  {{ nomEquipeDomicile(convocation) }}
                  <span class="px-2 text-white/75">vs</span>
                  {{ nomEquipeAdverse(convocation) }}
                </h4>
                <p class="mt-2 text-sm font-semibold text-white/80">
                  {{ convocation.evenement?.titre || 'Evenement du club' }}
                </p>
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
            @click="emit('repondre', { convocation, reponse: 'confirme' })"
          >
            Confirmer
          </button>
          <button
            v-if="peutRepondre(convocation.statut)"
            type="button"
            class="flex-1 rounded-full border border-[#dbe2ef] bg-white px-4 py-3 text-sm font-black text-[#111827] transition hover:border-[#b8c7e6] hover:bg-[#f8fbff]"
            @click="emit('repondre', { convocation, reponse: 'refuse' })"
          >
            Refuser
          </button>
          <button
            v-else
            type="button"
            class="w-full rounded-full border border-[#dbe2ef] bg-white px-4 py-3 text-sm font-black text-[#111827] transition hover:border-[#b8c7e6] hover:bg-[#f8fbff]"
            @click="ouvrirDetails(convocation)"
          >
            Voir les details
          </button>
        </div>

        <div class="pointer-events-none absolute -bottom-10 -right-10 h-28 w-28 rounded-full bg-[#2446d8]/8 transition duration-300 group-hover:scale-125 group-hover:bg-[#2446d8]/12"></div>
      </article>
    </div>

    <div v-else-if="!equipe" class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <p class="text-sm font-semibold text-[#6b7280]">Vous n'etes dans aucune equipe pour le moment.</p>
    </div>

    <div v-else class="mt-6 rounded-[32px] border border-dashed border-[#cfdaf2] bg-[#f8fbff] px-5 py-12 text-center">
      <h4 class="text-2xl text-[#111827]">Aucune convocation</h4>
      <p class="mt-2 text-sm font-semibold text-[#6b7280]">Aucune convocation n'est disponible pour le moment.</p>
    </div>

    <Teleport to="body">
      <div
        v-if="convocationActive"
        class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 px-3 pb-3 pt-10 backdrop-blur-sm sm:items-center sm:p-6"
        @click.self="fermerDetails"
      >
        <div class="w-full max-w-4xl overflow-hidden rounded-[30px] bg-white shadow-[0_24px_80px_rgba(15,23,42,0.22)]">
          <div
            class="relative min-h-[240px] bg-cover bg-center p-6 text-white sm:p-8"
            :style="{ backgroundImage: styleImage(convocationActive) }"
          >
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(255,255,255,0.22),transparent_24%),linear-gradient(180deg,rgba(7,16,58,0.16),rgba(7,16,58,0.88))]"></div>

            <div class="relative z-10">
              <button
                type="button"
                class="absolute right-0 top-0 rounded-full border border-white/25 bg-white/12 px-4 py-2 text-xs font-black text-white transition hover:bg-white/20"
                @click="fermerDetails"
              >
                Fermer
              </button>

              <div class="max-w-3xl">
                <span class="inline-flex rounded-full border border-white/25 bg-white/12 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] backdrop-blur-md">
                  {{ convocationActive.evenement?.type || 'Evenement' }}
                </span>
                <h4 class="mt-4 text-3xl font-black leading-tight text-white sm:text-4xl">
                  {{ nomEquipeDomicile(convocationActive) }}
                  <span class="px-2 text-white/70">vs</span>
                  {{ nomEquipeAdverse(convocationActive) }}
                </h4>
                <p class="mt-3 max-w-2xl text-sm font-semibold text-white/80">
                  {{ convocationActive.evenement?.titre || 'Evenement du club' }}
                </p>
              </div>
            </div>
          </div>

          <div class="space-y-5 p-5 sm:p-6">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
              <div class="rounded-[18px] bg-[#f8fbff] px-4 py-3">
                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-[#94a3b8]">Date</p>
                <p class="mt-1 text-sm font-black text-[#111827]">{{ formatDate(convocationActive.evenement?.date_debut) }}</p>
              </div>
              <div class="rounded-[18px] bg-[#f8fbff] px-4 py-3">
                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-[#94a3b8]">Heure</p>
                <p class="mt-1 text-sm font-black text-[#111827]">{{ formatHeure(convocationActive.evenement?.date_debut) }}</p>
              </div>
              <div class="rounded-[18px] bg-[#f8fbff] px-4 py-3">
                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-[#94a3b8]">Lieu</p>
                <p class="mt-1 text-sm font-black text-[#111827]">{{ convocationActive.evenement?.lieu || '-' }}</p>
              </div>
              <div class="rounded-[18px] bg-[#f8fbff] px-4 py-3">
                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-[#94a3b8]">Statut</p>
                <span
                  class="mt-1 inline-flex rounded-full px-2.5 py-1 text-[10px] font-black"
                  :class="statutConvocation(convocationActive.statut).classe"
                >
                  {{ statutConvocation(convocationActive.statut).label }}
                </span>
              </div>
            </div>

            <div class="rounded-[22px] border border-[#e6edf8] bg-white p-4">
              <p class="text-[10px] font-black uppercase tracking-[0.16em] text-[#94a3b8]">Description</p>
              <p class="mt-2 text-sm font-semibold leading-6 text-[#475569]">
                {{ convocationActive.evenement?.description || 'Aucune description detaillee pour cet evenement.' }}
              </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
              <button
                type="button"
                class="w-full rounded-full px-4 py-3 text-sm font-black transition sm:flex-1"
                :class="peutRepondre(convocationActive.statut) ? 'bg-[#0f172a] text-white hover:bg-black' : 'cursor-not-allowed bg-[#e5e7eb] text-[#94a3b8]'"
                :disabled="!peutRepondre(convocationActive.statut)"
                @click="repondrePuisFermer('confirme')"
              >
                {{ convocationActive.statut === 'confirme' ? 'Presence deja confirmee' : 'Confirmer ma presence' }}
              </button>
              <button
                type="button"
                class="w-full rounded-full border px-4 py-3 text-sm font-black transition sm:flex-1"
                :class="peutRepondre(convocationActive.statut) ? 'border-[#dbe2ef] bg-white text-[#111827] hover:border-[#b8c7e6] hover:bg-[#f8fbff]' : 'cursor-not-allowed border-[#e5e7eb] bg-[#f8fafc] text-[#94a3b8]'"
                :disabled="!peutRepondre(convocationActive.statut)"
                @click="repondrePuisFermer('refuse')"
              >
                {{ convocationActive.statut === 'refuse' ? 'Convocation deja refusee' : 'Refuser la convocation' }}
              </button>
              <button
                type="button"
                class="w-full rounded-full border border-[#dbe2ef] bg-[#f8fbff] px-4 py-3 text-sm font-black text-[#475569] transition hover:border-[#b8c7e6] hover:bg-white sm:flex-1"
                @click="fermerDetails"
              >
                Retour
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </section>
</template>
