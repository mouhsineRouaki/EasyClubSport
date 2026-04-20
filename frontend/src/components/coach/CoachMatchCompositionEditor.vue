<script setup>
import { computed, reactive, watch } from 'vue'
import MatchCompositionPlayerCard from '../common/MatchCompositionPlayerCard.vue'

const props = defineProps({
  composition: {
    type: Object,
    default: null,
  },
  chargement: {
    type: Boolean,
    default: false,
  },
  enregistrement: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['enregistrer'])

const formulaire = reactive({
  formation: '',
  notes: '',
  est_validee: false,
  titulaires: [],
  remplacants: [],
  absents: [],
})

const synchroniserDepuisComposition = (composition) => {
  formulaire.formation = composition?.formation || ''
  formulaire.notes = composition?.notes || ''
  formulaire.est_validee = Boolean(composition?.est_validee)
  formulaire.titulaires = (composition?.titulaires || []).map((joueur) => ({
    ...joueur,
    position_joueur: joueur.position_joueur || '',
  }))
  formulaire.remplacants = (composition?.remplacants || []).map((joueur) => ({
    ...joueur,
    position_joueur: joueur.position_joueur || '',
  }))
  formulaire.absents = [...(composition?.absents || [])]
}

watch(
  () => props.composition,
  (composition) => synchroniserDepuisComposition(composition),
  { immediate: true, deep: true }
)

const retirerDeTousLesGroupes = (joueurId) => {
  formulaire.titulaires = formulaire.titulaires.filter((joueur) => String(joueur.id) !== String(joueurId))
  formulaire.remplacants = formulaire.remplacants.filter((joueur) => String(joueur.id) !== String(joueurId))
  formulaire.absents = formulaire.absents.filter((joueur) => String(joueur.id) !== String(joueurId))
}

const deplacerVers = (joueur, groupe) => {
  retirerDeTousLesGroupes(joueur.id)

  const copie = {
    ...joueur,
    position_joueur: joueur.position_joueur || '',
  }

  if (groupe === 'titulaires') {
    formulaire.titulaires = [...formulaire.titulaires, copie]
    return
  }

  if (groupe === 'remplacants') {
    formulaire.remplacants = [...formulaire.remplacants, copie]
    return
  }

  formulaire.absents = [...formulaire.absents, { ...copie, position_joueur: null }]
}

const payload = computed(() => ({
  formation: formulaire.formation || null,
  notes: formulaire.notes || null,
  est_validee: formulaire.est_validee,
  titulaires: formulaire.titulaires.map((joueur) => ({
    utilisateur_id: joueur.id,
    position_joueur: joueur.position_joueur || null,
  })),
  remplacants: formulaire.remplacants.map((joueur) => ({
    utilisateur_id: joueur.id,
    position_joueur: joueur.position_joueur || null,
  })),
}))

const enregistrerComposition = (estValidee = formulaire.est_validee) => {
  emit('enregistrer', {
    ...payload.value,
    est_validee: estValidee,
  })
}
</script>

<template>
  <section class="mt-5 rounded-[22px] bg-white p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="text-sm font-black text-[#111827]">Composition du match</p>
        <p class="mt-1 text-xs font-semibold text-[#64748b]">
          Choisissez les titulaires, les remplacants et laissez les autres joueurs dans les absents.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <button
          type="button"
          class="rounded-full border border-[#dbe2ef] bg-white px-4 py-2.5 text-xs font-black text-[#111827] transition hover:bg-[#f8fbff] disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="chargement || enregistrement"
          @click="enregistrerComposition(false)"
        >
          {{ enregistrement ? 'Enregistrement...' : 'Enregistrer en brouillon' }}
        </button>
        <button
          type="button"
          class="rounded-full bg-[#111827] px-5 py-2.5 text-xs font-black text-white transition hover:bg-[#2446d8] disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="chargement || enregistrement"
          @click="enregistrerComposition(true)"
        >
          {{ enregistrement ? 'Enregistrement...' : 'Valider la composition' }}
        </button>
      </div>
    </div>

    <div v-if="chargement" class="mt-4 grid gap-4 lg:grid-cols-3">
      <div v-for="n in 3" :key="n" class="rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-4">
        <div class="h-6 w-28 animate-pulse rounded-full bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
        <div class="mt-4 space-y-3">
          <div v-for="m in 3" :key="m" class="h-[96px] animate-pulse rounded-[20px] bg-[linear-gradient(120deg,#f8fbff,#eef3ff,#f8fbff)]"></div>
        </div>
      </div>
    </div>

    <template v-else>
      <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <section class="rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-4">
          <div class="flex items-center justify-between gap-2">
            <p class="text-sm font-black text-[#111827]">Titulaires</p>
            <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-black text-[#16a34a]">
              {{ formulaire.titulaires.length }}
            </span>
          </div>

          <div v-if="formulaire.titulaires.length" class="mt-4 space-y-3">
            <article v-for="joueur in formulaire.titulaires" :key="`titulaire-${joueur.id}`" class="rounded-[22px] border border-[#d9f1e1] bg-[#f3fbf6] p-3">
              <MatchCompositionPlayerCard :joueur="joueur" ton="green" />
              <input
                v-model="joueur.position_joueur"
                type="text"
                placeholder="Position du joueur"
                class="mt-3 h-10 w-full rounded-2xl border border-[#d7e3f5] bg-white px-3 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]"
              />
              <div class="mt-3 flex gap-2">
                <button type="button" class="flex-1 rounded-full border border-[#f59e0b] bg-white py-2 text-[11px] font-black text-[#f59e0b] transition hover:bg-[#fff7ed]" @click="deplacerVers(joueur, 'remplacants')">
                  Passer remplacant
                </button>
                <button type="button" class="flex-1 rounded-full border border-[#dbe2ef] bg-white py-2 text-[11px] font-black text-[#64748b] transition hover:bg-[#f8fafc]" @click="deplacerVers(joueur, 'absents')">
                  Mettre absent
                </button>
              </div>
            </article>
          </div>

          <div v-else class="mt-4 rounded-[20px] border border-dashed border-[#d7e3f5] bg-white px-3 py-8 text-center text-xs font-semibold text-[#6b7280]">
            Aucun titulaire selectionne.
          </div>
        </section>

        <section class="rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-4">
          <div class="flex items-center justify-between gap-2">
            <p class="text-sm font-black text-[#111827]">Remplacants</p>
            <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-black text-[#f59e0b]">
              {{ formulaire.remplacants.length }}
            </span>
          </div>

          <div v-if="formulaire.remplacants.length" class="mt-4 space-y-3">
            <article v-for="joueur in formulaire.remplacants" :key="`remplacant-${joueur.id}`" class="rounded-[22px] border border-[#fde7c2] bg-[#fff9f0] p-3">
              <MatchCompositionPlayerCard :joueur="joueur" ton="amber" />
              <input
                v-model="joueur.position_joueur"
                type="text"
                placeholder="Position du joueur"
                class="mt-3 h-10 w-full rounded-2xl border border-[#f5dcc0] bg-white px-3 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#f59e0b]"
              />
              <div class="mt-3 flex gap-2">
                <button type="button" class="flex-1 rounded-full bg-[#111827] py-2 text-[11px] font-black text-white transition hover:bg-[#2446d8]" @click="deplacerVers(joueur, 'titulaires')">
                  Passer titulaire
                </button>
                <button type="button" class="flex-1 rounded-full border border-[#dbe2ef] bg-white py-2 text-[11px] font-black text-[#64748b] transition hover:bg-[#f8fafc]" @click="deplacerVers(joueur, 'absents')">
                  Mettre absent
                </button>
              </div>
            </article>
          </div>

          <div v-else class="mt-4 rounded-[20px] border border-dashed border-[#d7e3f5] bg-white px-3 py-8 text-center text-xs font-semibold text-[#6b7280]">
            Aucun remplacant selectionne.
          </div>
        </section>

        <section class="rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-4">
          <div class="flex items-center justify-between gap-2">
            <p class="text-sm font-black text-[#111827]">Absents</p>
            <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-black text-[#64748b]">
              {{ formulaire.absents.length }}
            </span>
          </div>

          <div v-if="formulaire.absents.length" class="mt-4 space-y-3">
            <article v-for="joueur in formulaire.absents" :key="`absent-${joueur.id}`" class="rounded-[22px] border border-[#e2e8f0] bg-[#f8fafc] p-3">
              <MatchCompositionPlayerCard :joueur="joueur" ton="slate" />
              <div class="mt-3 flex gap-2">
                <button type="button" class="flex-1 rounded-full bg-[#111827] py-2 text-[11px] font-black text-white transition hover:bg-[#2446d8]" @click="deplacerVers(joueur, 'titulaires')">
                  Ajouter titulaire
                </button>
                <button type="button" class="flex-1 rounded-full border border-[#f59e0b] bg-white py-2 text-[11px] font-black text-[#f59e0b] transition hover:bg-[#fff7ed]" @click="deplacerVers(joueur, 'remplacants')">
                  Ajouter remplacant
                </button>
              </div>
            </article>
          </div>

          <div v-else class="mt-4 rounded-[20px] border border-dashed border-[#d7e3f5] bg-white px-3 py-8 text-center text-xs font-semibold text-[#6b7280]">
            Aucun absent dans le groupe.
          </div>
        </section>
      </div>

      <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <section class="rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-4">
          <label class="text-[11px] font-black uppercase tracking-[0.16em] text-[#7c8aa5]">Formation</label>
          <input
            v-model="formulaire.formation"
            type="text"
            placeholder="Exemple : 4-3-3"
            class="mt-3 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]"
          />
        </section>

        <section class="rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-4">
          <label class="flex items-center gap-3 text-sm font-black text-[#111827]">
            <input v-model="formulaire.est_validee" type="checkbox" class="h-4 w-4 rounded border-[#dbe2ef]" />
            Valider la composition
          </label>
          <p class="mt-2 text-xs font-semibold text-[#64748b]">
            Activez cette option quand la composition est finalisee.
          </p>
        </section>
      </div>

      <section class="mt-4 rounded-[24px] border border-[#e6edf8] bg-[#f8fbff] p-4">
        <label class="text-[11px] font-black uppercase tracking-[0.16em] text-[#7c8aa5]">Notes</label>
        <textarea
          v-model="formulaire.notes"
          rows="3"
          class="mt-3 w-full resize-none rounded-2xl border border-[#dbe2ef] bg-white px-4 py-3 text-sm font-semibold text-[#1f2a44] outline-none focus:border-[#4c6fff]"
          placeholder="Consignes, remarques ou ajustements de match..."
        ></textarea>
      </section>
    </template>
  </section>
</template>
