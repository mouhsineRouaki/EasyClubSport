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
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['submit', 'update-field'])

const lireErreur = (champ) => props.errors?.[champ]?.[0] || ''
</script>

<template>
  <form class="grid gap-3" @submit.prevent="emit('submit')">
    <label>
      <span class="text-xs font-bold text-[#64748b]">Utilisateur ID *</span>
      <input
        :value="modelValue.utilisateur_id"
        type="number"
        min="1"
        placeholder="Ex: 17"
        class="ecs-input"
        @input="emit('update-field', 'utilisateur_id', $event.target.value)"
      />
      <span v-if="lireErreur('utilisateur_id')" class="mt-1 block text-xs text-[#e11d48]">{{ lireErreur('utilisateur_id') }}</span>
    </label>

    <div class="flex justify-end">
      <button :disabled="loading" class="ecs-btn-primary" type="submit">
        {{ loading ? 'Ajout...' : 'Ajouter joueur' }}
      </button>
    </div>
  </form>
</template>
