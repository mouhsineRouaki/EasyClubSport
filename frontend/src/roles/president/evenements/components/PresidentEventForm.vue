<script setup>
import { computed, ref } from 'vue'

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
  submitLabel: {
    type: String,
    default: 'Enregistrer',
  },
  loadingLabel: {
    type: String,
    default: 'Enregistrement...',
  },
  adversaireOptions: {
    type: Array,
    default: () => [],
  },
  equipeLocale: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['submit', 'update-field', 'search-adversaire'])

const rechercheAdversaire = ref('')
const changerRechercheAdversaire = (event) => {
  rechercheAdversaire.value = event.target.value
  emit('search-adversaire', rechercheAdversaire.value)
}
const lireErreur = (champ) => props.errors?.[champ]?.[0] || ''
const adversaireSelectionne = computed(() => {
  return props.adversaireOptions.find((equipe) => String(equipe.id) === String(props.modelValue.adversaire_equipe_id)) || null
})
const adversairesFiltres = computed(() => {
  const terme = rechercheAdversaire.value.trim().toLowerCase()

  if (!terme) {
    return props.adversaireOptions
  }

  return props.adversaireOptions.filter((equipe) => {
    return [
      equipe.nom,
      equipe.categorie,
      equipe.club?.nom,
      equipe.club?.ville,
      equipe.coach?.name,
      equipe.coach?.nom,
    ]
      .filter(Boolean)
      .some((valeur) => String(valeur).toLowerCase().includes(terme))
  })
})

const logoEquipe = (equipe) => equipe?.logo_url || equipe?.logo || equipe?.club?.logo_url || ''
</script>

<template>
  <form class="mt-5 grid gap-4 rounded-[32px] border border-[#e6edf8] bg-[#f8fbff] p-5" @submit.prevent="emit('submit')">
    <div class="grid gap-4 md:grid-cols-2">
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

      <label>
        <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Type</span>
        <select
          :value="modelValue.type"
          class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold outline-none focus:border-[#4c6fff]"
          @change="emit('update-field', 'type', $event.target.value)"
        >
          <option value="match">Match</option>
          <option value="entrainement">Entrainement</option>
          <option value="reunion">Reunion</option>
        </select>
        <span v-if="lireErreur('type')" class="mt-1 block text-xs font-semibold text-red-600">{{ lireErreur('type') }}</span>
      </label>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
      <label>
        <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Date debut</span>
        <input
          :value="modelValue.date_debut"
          type="datetime-local"
          class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold outline-none focus:border-[#4c6fff]"
          @input="emit('update-field', 'date_debut', $event.target.value)"
        />
        <span v-if="lireErreur('date_debut')" class="mt-1 block text-xs font-semibold text-red-600">{{ lireErreur('date_debut') }}</span>
      </label>

      <label>
        <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Date fin</span>
        <input
          :value="modelValue.date_fin"
          type="datetime-local"
          class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold outline-none focus:border-[#4c6fff]"
          @input="emit('update-field', 'date_fin', $event.target.value)"
        />
        <span v-if="lireErreur('date_fin')" class="mt-1 block text-xs font-semibold text-red-600">{{ lireErreur('date_fin') }}</span>
      </label>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
      <label>
        <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Lieu</span>
        <input
          :value="modelValue.lieu"
          type="text"
          class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold outline-none focus:border-[#4c6fff]"
          @input="emit('update-field', 'lieu', $event.target.value)"
        />
      </label>

      <label v-if="modelValue.type === 'match'">
        <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Equipe adversaire</span>
        <input
          :value="rechercheAdversaire"
          type="search"
          placeholder="Rechercher par equipe, club, ville..."
          class="mt-2 h-10 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-xs font-semibold outline-none placeholder:text-[#94a3b8] focus:border-[#4c6fff]"
          @input="changerRechercheAdversaire"
        />
        <select
          :value="modelValue.adversaire_equipe_id"
          class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold outline-none focus:border-[#4c6fff]"
          @change="emit('update-field', 'adversaire_equipe_id', $event.target.value)"
        >
          <option value="">Selectionner une equipe</option>
          <option v-for="equipe in adversairesFiltres" :key="equipe.id" :value="String(equipe.id)">
            {{ equipe.nom }}{{ equipe.club?.nom ? ` - ${equipe.club.nom}` : '' }}
          </option>
        </select>
        <p v-if="rechercheAdversaire && !adversairesFiltres.length" class="mt-1 text-xs font-semibold text-[#ef4444]">Aucune equipe trouvee pour cette recherche.</p>
        <span v-if="lireErreur('adversaire_equipe_id')" class="mt-1 block text-xs font-semibold text-red-600">{{ lireErreur('adversaire_equipe_id') }}</span>
      </label>

      <div v-else class="rounded-2xl border border-[#dbe2ef] bg-white px-4 py-3">
        <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Affichage</span>
        <p class="mt-2 text-sm font-black text-[#111827]">{{ modelValue.type === 'entrainement' ? 'Entrainement' : 'Reunion' }}</p>
        <p class="mt-1 text-xs font-semibold text-[#64748b]">La carte affichera uniquement le logo de l equipe.</p>
      </div>
    </div>

    <section class="rounded-[26px] border border-[#e6edf8] bg-white p-4">
      <div v-if="modelValue.type === 'match'" class="grid gap-3 md:grid-cols-[1fr_auto_1fr] md:items-center">
        <div class="rounded-[22px] bg-[#f8fbff] p-4">
          <div class="flex items-center gap-3">
            <img v-if="logoEquipe(equipeLocale)" :src="logoEquipe(equipeLocale)" :alt="equipeLocale?.nom || 'Equipe'" class="h-12 w-12 rounded-2xl object-cover" />
            <span v-else class="block h-12 w-12 rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)]"></span>
            <div>
              <p class="text-sm font-black text-[#111827]">{{ equipeLocale?.nom || 'Equipe locale' }}</p>
              <p class="text-xs font-semibold text-[#64748b]">{{ equipeLocale?.club?.nom || 'Club actuel' }}</p>
            </div>
          </div>
        </div>
        <span class="mx-auto rounded-full bg-[#111827] px-4 py-2 text-xs font-black text-white">VS</span>
        <div class="rounded-[22px] bg-[#f8fbff] p-4">
          <div class="flex items-center gap-3">
            <img v-if="logoEquipe(adversaireSelectionne)" :src="logoEquipe(adversaireSelectionne)" :alt="adversaireSelectionne?.nom || 'Adversaire'" class="h-12 w-12 rounded-2xl object-cover" />
            <span v-else class="block h-12 w-12 rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#e2e8f0_38%,#94a3b8_90%)]"></span>
            <div>
              <p class="text-sm font-black text-[#111827]">{{ adversaireSelectionne?.nom || 'Adversaire a choisir' }}</p>
              <p class="text-xs font-semibold text-[#64748b]">{{ adversaireSelectionne?.club?.nom || 'Equipe de la plateforme' }}</p>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="flex items-center gap-3">
        <img v-if="logoEquipe(equipeLocale)" :src="logoEquipe(equipeLocale)" :alt="equipeLocale?.nom || 'Equipe'" class="h-14 w-14 rounded-2xl object-cover" />
        <span v-else class="block h-14 w-14 rounded-2xl bg-[radial-gradient(circle_at_35%_25%,#ffffff_0%,#dbe7ff_28%,#2446d8_72%)]"></span>
        <div>
          <p class="text-sm font-black text-[#111827]">{{ modelValue.type === 'entrainement' ? 'Entrainement' : 'Reunion' }}</p>
          <p class="text-xs font-semibold text-[#64748b]">{{ equipeLocale?.nom || 'Equipe locale' }}</p>
        </div>
      </div>
    </section>

    <label>
      <span class="text-xs font-black uppercase tracking-[0.14em] text-[#6b7280]">Statut</span>
      <select
        :value="modelValue.statut"
        class="mt-2 h-11 w-full rounded-2xl border border-[#dbe2ef] bg-white px-4 text-sm font-semibold outline-none focus:border-[#4c6fff]"
        @change="emit('update-field', 'statut', $event.target.value)"
      >
        <option value="planifie">Planifie</option>
        <option value="termine">Termine</option>
        <option value="annule">Annule</option>
      </select>
      <span v-if="lireErreur('statut')" class="mt-1 block text-xs font-semibold text-red-600">{{ lireErreur('statut') }}</span>
    </label>

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
