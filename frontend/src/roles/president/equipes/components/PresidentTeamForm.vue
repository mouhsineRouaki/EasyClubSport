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
  logoPreview: {
    type: String,
    default: '',
  },
  currentLogoUrl: {
    type: String,
    default: '',
  },
  logoFileName: {
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
})

const emit = defineEmits(['submit', 'update-field', 'choose-logo'])

const lireErreur = (champ) => props.errors?.[champ]?.[0] || ''
</script>

<template>
  <form class="grid gap-3" @submit.prevent="emit('submit')">
    <label class="rounded-xl border border-dashed border-[#d8e2f1] bg-[#f8fbff] p-3 transition hover:border-[#2563eb] hover:bg-white">
      <span class="block text-xs font-bold text-[#64748b]">Logo de l'equipe</span>
      <span class="mt-3 flex flex-wrap items-center justify-between gap-3">
        <span class="flex items-center gap-3">
          <img
            v-if="logoPreview || currentLogoUrl"
            :src="logoPreview || currentLogoUrl"
            alt="Logo equipe"
            class="h-16 w-16 rounded-2xl object-cover"
          />
          <span v-else class="block h-16 w-16 rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)]"></span>
          <span class="text-sm font-bold text-[#334155]">{{ logoFileName || 'Choisir une image' }}</span>
        </span>
        <span class="ecs-btn-secondary text-[11px]">Choisir image</span>
      </span>
      <input type="file" accept="image/*" class="sr-only" @change="emit('choose-logo', $event)" />
      <span v-if="lireErreur('logo')" class="mt-2 block text-xs text-red-600">{{ lireErreur('logo') }}</span>
    </label>

    <div class="grid gap-3 md:grid-cols-2">
      <label>
        <span class="text-xs font-bold text-[#64748b]">Nom</span>
        <input
          :value="modelValue.nom"
          type="text"
          class="ecs-input"
          @input="emit('update-field', 'nom', $event.target.value)"
        />
        <span v-if="lireErreur('nom')" class="mt-1 block text-xs text-red-600">{{ lireErreur('nom') }}</span>
      </label>

      <label>
        <span class="text-xs font-bold text-[#64748b]">Categorie</span>
        <input
          :value="modelValue.categorie"
          type="text"
          placeholder="Senior, U18, U15..."
          class="ecs-input"
          @input="emit('update-field', 'categorie', $event.target.value)"
        />
        <span v-if="lireErreur('categorie')" class="mt-1 block text-xs text-red-600">{{ lireErreur('categorie') }}</span>
      </label>
    </div>

    <label>
      <span class="text-xs font-bold text-[#64748b]">Statut</span>
      <select
        :value="modelValue.statut"
        class="ecs-select"
        @change="emit('update-field', 'statut', $event.target.value)"
      >
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
      <span v-if="lireErreur('statut')" class="mt-1 block text-xs text-red-600">{{ lireErreur('statut') }}</span>
    </label>

    <label>
      <span class="text-xs font-bold text-[#64748b]">Description</span>
      <textarea
        :value="modelValue.description"
        rows="4"
        class="ecs-textarea"
        @input="emit('update-field', 'description', $event.target.value)"
      ></textarea>
    </label>

    <div class="flex justify-end">
      <button :disabled="loading" type="submit" class="ecs-btn-primary">
        {{ loading ? loadingLabel : submitLabel }}
      </button>
    </div>
  </form>
</template>
