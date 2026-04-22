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
  <form class="mt-5 grid gap-4 rounded-[32px] border border-[#e6edf8] bg-[#f8fbff] p-5" @submit.prevent="emit('submit')">
    <label class="rounded-3xl border border-dashed border-[#cfdaf2] bg-white p-4">
      <span class="block text-xs font-black uppercase tracking-[0.16em] text-[#6b7280]">Logo du club</span>
      <span class="mt-3 flex flex-wrap items-center justify-between gap-3">
        <span class="flex items-center gap-3">
          <img
            v-if="logoPreview || currentLogoUrl"
            :src="logoPreview || currentLogoUrl"
            alt="Logo club"
            class="h-16 w-16 rounded-2xl object-cover"
          />
          <span v-else class="block h-16 w-16 rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)]"></span>
          <span class="text-sm font-bold text-[#334155]">{{ logoFileName || 'Choisir une image' }}</span>
        </span>
        <span class="rounded-full bg-[#111827] px-4 py-2 text-xs font-black text-white">Choisir image</span>
      </span>
      <input type="file" accept="image/*" class="sr-only" @change="emit('choose-logo', $event)" />
      <span v-if="lireErreur('logo')" class="mt-2 block text-xs font-semibold text-red-600">{{ lireErreur('logo') }}</span>
    </label>

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
        <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Email</span>
        <input
          :value="modelValue.email"
          type="email"
          class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold outline-none focus:border-[#4c6fff]"
          @input="emit('update-field', 'email', $event.target.value)"
        />
        <span v-if="lireErreur('email')" class="mt-1 block text-xs font-semibold text-red-600">{{ lireErreur('email') }}</span>
      </label>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
      <label>
        <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Telephone</span>
        <input
          :value="modelValue.telephone"
          type="text"
          class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold outline-none focus:border-[#4c6fff]"
          @input="emit('update-field', 'telephone', $event.target.value)"
        />
      </label>
      <label>
        <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Adresse</span>
        <input
          :value="modelValue.adresse"
          type="text"
          class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold outline-none focus:border-[#4c6fff]"
          @input="emit('update-field', 'adresse', $event.target.value)"
        />
      </label>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
      <label>
        <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Ville</span>
        <input
          :value="modelValue.ville"
          type="text"
          class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold outline-none focus:border-[#4c6fff]"
          @input="emit('update-field', 'ville', $event.target.value)"
        />
      </label>
      <label>
        <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Pays</span>
        <input
          :value="modelValue.pays"
          type="text"
          class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold outline-none focus:border-[#4c6fff]"
          @input="emit('update-field', 'pays', $event.target.value)"
        />
      </label>
    </div>

    <label>
      <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Description</span>
      <textarea
        :value="modelValue.description"
        rows="4"
        class="mt-2 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 py-3 text-sm font-semibold outline-none focus:border-[#4c6fff]"
        @input="emit('update-field', 'description', $event.target.value)"
      ></textarea>
    </label>

    <div class="flex justify-end">
      <button :disabled="loading" type="submit" class="rounded-full bg-[#111827] px-5 py-3 text-xs font-black text-white transition hover:bg-[#1f36bf] disabled:opacity-60">
        {{ loading ? loadingLabel : submitLabel }}
      </button>
    </div>
  </form>
</template>
