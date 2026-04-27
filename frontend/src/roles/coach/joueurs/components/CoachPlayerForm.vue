<script setup>
import AppButton from '@/shared/components/ui/AppButton.vue'
import AppField from '@/shared/components/ui/AppField.vue'
import CoachPlayerStatsForm from '@/roles/coach/joueurs/components/CoachPlayerStatsForm.vue'

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
  mode: {
    type: String,
    default: 'create',
  },
})

const emit = defineEmits(['submit', 'update-field'])

const readError = (field) => props.errors?.[field]?.[0] || ''
</script>

<template>
  <form class="space-y-5" @submit.prevent="emit('submit')">
    <section class="ecs-panel-soft p-4">
      <p class="ecs-kicker">Identite</p>
      <div class="mt-4 grid gap-3 sm:grid-cols-2">
        <AppField label="Prenom" :error="readError('prenom')">
          <input :value="modelValue.prenom" type="text" class="ecs-input" @input="emit('update-field', 'prenom', $event.target.value)" />
        </AppField>
        <AppField label="Nom" :error="readError('nom')">
          <input :value="modelValue.nom" type="text" class="ecs-input" @input="emit('update-field', 'nom', $event.target.value)" />
        </AppField>
        <AppField label="Email" :error="readError('email')">
          <input
            :value="modelValue.email"
            type="email"
            class="ecs-input"
            :readonly="mode === 'edit'"
            @input="emit('update-field', 'email', $event.target.value)"
          />
        </AppField>
        <AppField label="Telephone" :error="readError('telephone')">
          <input :value="modelValue.telephone" type="text" class="ecs-input" @input="emit('update-field', 'telephone', $event.target.value)" />
        </AppField>
        <AppField label="Adresse" :error="readError('adresse')" class="sm:col-span-2">
          <input :value="modelValue.adresse" type="text" class="ecs-input" @input="emit('update-field', 'adresse', $event.target.value)" />
        </AppField>
      </div>
    </section>

    <section class="ecs-panel-soft p-4">
      <p class="ecs-kicker">Football</p>
      <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <AppField label="Numero" :error="readError('numero_joueur')">
          <input :value="modelValue.numero_joueur" type="number" min="1" max="99" class="ecs-input" @input="emit('update-field', 'numero_joueur', $event.target.value)" />
        </AppField>
        <AppField label="Poste principal" :error="readError('poste_principal')">
          <input :value="modelValue.poste_principal" type="text" class="ecs-input" @input="emit('update-field', 'poste_principal', $event.target.value)" />
        </AppField>
        <AppField label="Poste secondaire" :error="readError('poste_secondaire')">
          <input :value="modelValue.poste_secondaire" type="text" class="ecs-input" @input="emit('update-field', 'poste_secondaire', $event.target.value)" />
        </AppField>
        <AppField label="Pied fort" :error="readError('pied_fort')">
          <select :value="modelValue.pied_fort" class="ecs-select" @change="emit('update-field', 'pied_fort', $event.target.value)">
            <option value="">Choisir</option>
            <option value="droit">Droit</option>
            <option value="gauche">Gauche</option>
            <option value="ambidextre">Ambidextre</option>
          </select>
        </AppField>
        <AppField label="Statut" :error="readError('statut')">
          <select :value="modelValue.statut" class="ecs-select" @change="emit('update-field', 'statut', $event.target.value)">
            <option value="actif">Actif</option>
            <option value="blesse">Blesse</option>
            <option value="suspendu">Suspendu</option>
            <option value="inactif">Inactif</option>
          </select>
        </AppField>
        <AppField label="Note globale" :error="readError('note_globale')">
          <input :value="modelValue.note_globale" type="number" min="1" max="99" class="ecs-input" @input="emit('update-field', 'note_globale', $event.target.value)" />
        </AppField>
      </div>

      <div class="mt-4">
        <AppField label="Photo" :error="readError('photo')" hint="Optionnel. JPG, PNG ou WEBP.">
          <input type="file" accept="image/png,image/jpeg,image/webp" class="ecs-input h-auto py-3" @change="emit('update-field', 'photo', $event.target.files?.[0] || null)" />
        </AppField>
      </div>
    </section>

    <CoachPlayerStatsForm
      :model-value="modelValue"
      :errors="errors"
      @update-field="(field, value) => emit('update-field', field, value)"
    />

    <div class="flex justify-end gap-3">
      <AppButton type="submit" :disabled="loading">
        {{ loading ? 'Enregistrement...' : mode === 'edit' ? 'Modifier le joueur' : 'Creer le joueur' }}
      </AppButton>
    </div>
  </form>
</template>
