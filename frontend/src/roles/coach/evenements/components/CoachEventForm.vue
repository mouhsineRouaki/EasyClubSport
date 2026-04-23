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
  <form class="grid gap-4 md:grid-cols-2" @submit.prevent="emit('submit')">
    <label class="md:col-span-2">
      <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Titre</span>
      <input :value="modelValue.titre" type="text" class="ecs-input" @input="emit('update-field', 'titre', $event.target.value)" />
      <span v-if="lireErreur('titre')" class="mt-2 block text-xs text-[#e11d48]">{{ lireErreur('titre') }}</span>
    </label>

    <label>
      <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Type</span>
      <select :value="modelValue.type" class="ecs-select" @change="emit('update-field', 'type', $event.target.value)">
        <option value="match">Match</option>
        <option value="entrainement">Entrainement</option>
        <option value="reunion">Reunion</option>
      </select>
    </label>

    <label>
      <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Statut</span>
      <select :value="modelValue.statut" class="ecs-select" @change="emit('update-field', 'statut', $event.target.value)">
        <option value="planifie">Planifie</option>
        <option value="termine">Termine</option>
        <option value="annule">Annule</option>
      </select>
    </label>

    <label>
      <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Date debut</span>
      <input :value="modelValue.date_debut" type="datetime-local" class="ecs-input" @input="emit('update-field', 'date_debut', $event.target.value)" />
      <span v-if="lireErreur('date_debut')" class="mt-2 block text-xs text-[#e11d48]">{{ lireErreur('date_debut') }}</span>
    </label>

    <label>
      <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Date fin</span>
      <input :value="modelValue.date_fin" type="datetime-local" class="ecs-input" @input="emit('update-field', 'date_fin', $event.target.value)" />
    </label>

    <label>
      <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Lieu</span>
      <input :value="modelValue.lieu" type="text" class="ecs-input" @input="emit('update-field', 'lieu', $event.target.value)" />
    </label>

    <label v-if="modelValue.type === 'match'">
      <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Equipe adverse ID</span>
      <input :value="modelValue.adversaire_equipe_id" type="number" class="ecs-input" @input="emit('update-field', 'adversaire_equipe_id', $event.target.value)" />
      <span v-if="lireErreur('adversaire_equipe_id')" class="mt-2 block text-xs text-[#e11d48]">{{ lireErreur('adversaire_equipe_id') }}</span>
    </label>

    <label v-if="modelValue.type === 'match'" class="md:col-span-2">
      <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Adversaire texte</span>
      <input :value="modelValue.adversaire" type="text" class="ecs-input" @input="emit('update-field', 'adversaire', $event.target.value)" />
    </label>

    <label class="md:col-span-2">
      <span class="mb-2 block text-xs font-black uppercase tracking-[0.18em] text-[#64748b]">Description</span>
      <textarea :value="modelValue.description" rows="4" class="ecs-textarea" @input="emit('update-field', 'description', $event.target.value)"></textarea>
    </label>

    <div class="md:col-span-2 flex justify-end">
      <button type="submit" class="ecs-btn-primary" :disabled="loading">
        {{ loading ? 'Enregistrement...' : 'Enregistrer' }}
      </button>
    </div>
  </form>
</template>
