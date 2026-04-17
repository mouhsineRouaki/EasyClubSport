<script setup>
const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
  clubs: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  submitLabel: {
    type: String,
    default: 'Enregistrer',
  },
  loadingLabel: {
    type: String,
    default: 'Enregistrement...',
  },
  disableClub: {
    type: Boolean,
    default: false,
  },
  fileName: {
    type: String,
    default: '',
  },
  requireFile: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['submit', 'update-field', 'choose-file'])

const lireErreur = (champ) => props.errors?.[champ]?.[0] || ''
</script>

<template>
  <form class="mt-5 grid gap-4 rounded-[32px] border border-[#e6edf8] bg-[#f8fbff] p-5" @submit.prevent="emit('submit')">
    <div class="grid gap-4 md:grid-cols-2">
      <label>
        <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Club</span>
        <select
          :value="modelValue.club_id"
          class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold outline-none focus:border-[#4c6fff] disabled:cursor-not-allowed disabled:bg-[#f8fafc]"
          :disabled="disableClub"
          @change="emit('update-field', 'club_id', $event.target.value)"
        >
          <option value="">Choisir un club</option>
          <option v-for="club in clubs" :key="club.id" :value="String(club.id)">
            {{ club.nom }}
          </option>
        </select>
        <span v-if="lireErreur('club_id')" class="mt-1 block text-xs font-semibold text-red-600">{{ lireErreur('club_id') }}</span>
      </label>

      <label>
        <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Utilisateur ID</span>
        <input
          :value="modelValue.utilisateur_id"
          type="number"
          min="1"
          class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold outline-none focus:border-[#4c6fff]"
          :disabled="disableClub"
          @input="emit('update-field', 'utilisateur_id', $event.target.value)"
        />
        <span v-if="lireErreur('utilisateur_id')" class="mt-1 block text-xs font-semibold text-red-600">{{ lireErreur('utilisateur_id') }}</span>
      </label>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
      <label>
        <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Nom</span>
        <input
          :value="modelValue.nom"
          type="text"
          class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold outline-none focus:border-[#4c6fff]"
          @input="emit('update-field', 'nom', $event.target.value)"
        />
        <span v-if="lireErreur('nom')" class="mt-1 block text-xs font-semibold text-red-600">{{ lireErreur('nom') }}</span>
      </label>

      <label>
        <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Type document</span>
        <input
          :value="modelValue.type_document"
          type="text"
          class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold outline-none focus:border-[#4c6fff]"
          @input="emit('update-field', 'type_document', $event.target.value)"
        />
        <span v-if="lireErreur('type_document')" class="mt-1 block text-xs font-semibold text-red-600">{{ lireErreur('type_document') }}</span>
      </label>
    </div>

    <label class="rounded-3xl border border-dashed border-[#cfdaf2] bg-white p-4">
      <span class="block text-xs font-black uppercase tracking-[0.16em] text-[#6b7280]">Fichier {{ requireFile ? '' : '(optionnel)' }}</span>
      <span class="mt-3 flex flex-wrap items-center justify-between gap-3">
        <span class="text-sm font-bold text-[#334155]">{{ fileName || 'Choisir un fichier' }}</span>
        <span class="rounded-full bg-[#111827] px-4 py-2 text-xs font-black text-white">Choisir fichier</span>
      </span>
      <input type="file" class="sr-only" @change="emit('choose-file', $event)" />
      <span v-if="lireErreur('fichier')" class="mt-2 block text-xs font-semibold text-red-600">{{ lireErreur('fichier') }}</span>
    </label>

    <div class="flex justify-end">
      <button :disabled="loading" type="submit" class="rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white transition hover:bg-[#1f36bf] disabled:opacity-60">
        {{ loading ? loadingLabel : submitLabel }}
      </button>
    </div>
  </form>
</template>
