<script setup>
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
  clubs: {
    type: Array,
    default: () => [],
  },
  imagePreview: {
    type: String,
    default: '',
  },
  currentImageUrl: {
    type: String,
    default: '',
  },
  imageFileName: {
    type: String,
    default: '',
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
})

const emit = defineEmits(['submit', 'update-field', 'choose-image'])

const lireErreur = (champ) => props.errors?.[champ]?.[0] || ''
</script>

<template>
  <form class="mt-5 grid gap-4 rounded-[32px] border border-[#e6edf8] bg-[#f8fbff] p-5" @submit.prevent="emit('submit')">
    <label class="rounded-3xl border border-dashed border-[#cfdaf2] bg-white p-4">
      <span class="block text-xs font-black uppercase tracking-[0.16em] text-[#6b7280]">Image de l'annonce</span>
      <span class="mt-3 flex flex-wrap items-center justify-between gap-3">
        <span class="flex items-center gap-3">
          <img
            v-if="imagePreview || currentImageUrl"
            :src="imagePreview || currentImageUrl"
            alt="Image annonce"
            class="h-16 w-16 rounded-2xl object-cover"
          />
          <span v-else class="block h-16 w-16 rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)]"></span>
          <span class="text-sm font-bold text-[#334155]">{{ imageFileName || 'Choisir une image' }}</span>
        </span>
        <span class="rounded-full bg-[#111827] px-4 py-2 text-xs font-black text-white">Choisir image</span>
      </span>
      <input type="file" accept="image/*" class="sr-only" @change="emit('choose-image', $event)" />
      <span v-if="lireErreur('image')" class="mt-2 block text-xs font-semibold text-red-600">{{ lireErreur('image') }}</span>
    </label>

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
        <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Titre</span>
        <input
          :value="modelValue.titre"
          type="text"
          class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold outline-none focus:border-[#4c6fff]"
          @input="emit('update-field', 'titre', $event.target.value)"
        />
        <span v-if="lireErreur('titre')" class="mt-1 block text-xs font-semibold text-red-600">{{ lireErreur('titre') }}</span>
      </label>
    </div>

    <label>
      <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Contenu</span>
      <textarea
        :value="modelValue.contenu"
        rows="7"
        class="mt-2 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 py-3 text-sm font-semibold outline-none focus:border-[#4c6fff]"
        @input="emit('update-field', 'contenu', $event.target.value)"
      ></textarea>
      <span v-if="lireErreur('contenu')" class="mt-1 block text-xs font-semibold text-red-600">{{ lireErreur('contenu') }}</span>
    </label>

    <label class="flex items-center gap-3 rounded-2xl border border-[#dbe2ef] bg-white px-4 py-3 text-sm font-semibold text-[#1f2a44]">
      <input
        :checked="Boolean(modelValue.est_active)"
        type="checkbox"
        class="h-4 w-4 accent-[#2446d8]"
        @change="emit('update-field', 'est_active', $event.target.checked)"
      />
      Annonce visible pour le club
    </label>
    <span v-if="lireErreur('est_active')" class="-mt-2 block text-xs font-semibold text-red-600">{{ lireErreur('est_active') }}</span>

    <div class="flex justify-end">
      <AppButton :disabled="loading" type="submit">
        {{ loading ? loadingLabel : submitLabel }}
      </AppButton>
    </div>
  </form>
</template>
