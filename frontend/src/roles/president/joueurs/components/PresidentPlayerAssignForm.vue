<script setup>
import AppListState from '@/shared/components/AppListState.vue'
import AppPagination from '@/shared/components/AppPagination.vue'
import AppButton from '@/shared/components/ui/AppButton.vue'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
  loading: {
    type: Boolean,
    default: false,
  },
  players: {
    type: Array,
    default: () => [],
  },
  playersLoading: {
    type: Boolean,
    default: false,
  },
  playersPagination: {
    type: Object,
    default: null,
  },
  search: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['submit', 'update-field', 'update-search', 'change-page', 'change-per-page'])

const lireErreur = (champ) => props.errors?.[champ]?.[0] || ''
const nomJoueur = (joueur) => `${joueur.prenom || ''} ${joueur.nom || ''}`.trim() || joueur.name || 'Joueur'
const joueurSelectionne = (joueur) => String(props.modelValue.utilisateur_id || '') === String(joueur.id)
</script>

<template>
  <form class="grid gap-4" @submit.prevent="emit('submit')">
    <label class="rounded-[22px] border border-[#e6edf8] bg-[#f8fbff] p-3">
      <span class="text-xs font-bold text-[#64748b]">Recherche joueur</span>
      <input
        :value="search"
        type="text"
        placeholder="Rechercher par nom ou email..."
        class="ecs-input mt-2"
        @input="emit('update-search', $event.target.value)"
      />
    </label>

    <div class="rounded-[24px] border border-[#e6edf8] bg-white p-3">
      <div class="mb-3 flex items-center justify-between gap-3">
        <div>
          <p class="text-sm font-black text-[#0f172a]">Joueurs disponibles</p>
          <p class="text-xs font-semibold text-[#64748b]">Choisissez un joueur libre puis ajoutez-le a l equipe.</p>
        </div>
        <div class="rounded-full bg-[#eef4ff] px-3 py-1 text-[11px] font-black text-[#1f36bf]">
          Selection : {{ modelValue.utilisateur_id || '-' }}
        </div>
      </div>

      <AppListState
        :loading="playersLoading"
        :has-data="players.length > 0"
        empty-title="Aucun joueur disponible."
        empty-description="Essayez une autre recherche ou verifiez si les joueurs sont deja affectes."
      >
        <div class="grid gap-3">
          <button
            v-for="joueur in players"
            :key="joueur.id"
            type="button"
            class="w-full rounded-[22px] border p-3 text-left transition"
            :class="joueurSelectionne(joueur) ? 'border-[#2446d8] bg-[#eef4ff] shadow-[0_12px_30px_rgba(36,70,216,0.12)]' : 'border-[#e6edf8] bg-[#f8fbff] hover:border-[#cfdaf2] hover:bg-white'"
            @click="emit('update-field', 'utilisateur_id', joueur.id)"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="truncate text-sm font-black text-[#0f172a]">{{ nomJoueur(joueur) }}</p>
                <p class="mt-1 truncate text-xs font-semibold text-[#64748b]">{{ joueur.email || 'Email non renseigne' }}</p>
                <p class="mt-1 text-xs text-[#64748b]">{{ joueur.telephone || 'Telephone non renseigne' }}</p>
              </div>
              <span
                class="rounded-full px-3 py-1 text-[11px] font-black"
                :class="joueurSelectionne(joueur) ? 'bg-[#2446d8] text-white' : 'bg-white text-[#1f2a44]'"
              >
                {{ joueurSelectionne(joueur) ? 'Selectionne' : 'Choisir' }}
              </span>
            </div>
          </button>
        </div>
      </AppListState>

      <AppPagination
        :pagination="playersPagination"
        @change-page="emit('change-page', $event)"
        @change-per-page="emit('change-per-page', $event)"
      />
    </div>

    <span v-if="lireErreur('utilisateur_id')" class="text-xs font-semibold text-[#e11d48]">{{ lireErreur('utilisateur_id') }}</span>

    <div class="flex justify-end">
      <AppButton :disabled="loading" type="submit">
        {{ loading ? 'Ajout...' : 'Ajouter joueur' }}
      </AppButton>
    </div>
  </form>
</template>
