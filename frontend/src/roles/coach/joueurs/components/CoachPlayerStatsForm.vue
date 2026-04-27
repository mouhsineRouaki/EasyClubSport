<script setup>
import AppField from '@/shared/components/ui/AppField.vue'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
})

const emit = defineEmits(['update-field'])

const readError = (field) => props.errors?.[field]?.[0] || ''
const stats = [
  ['attaque', 'Attaque'],
  ['defense', 'Defense'],
  ['vitesse', 'Vitesse'],
  ['passe', 'Passe'],
  ['dribble', 'Dribble'],
  ['physique', 'Physique'],
]
</script>

<template>
  <section class="relative overflow-hidden rounded-[28px] border border-[#dbe5f2] bg-[linear-gradient(145deg,#0f172a_0%,#172554_38%,#1d4ed8_72%,#14b8a6_100%)] p-5 text-white shadow-[0_24px_60px_rgba(15,23,42,0.18)]">
    <div class="absolute -right-10 top-6 h-28 w-28 rounded-full bg-white/10 blur-3xl"></div>
    <div class="relative">
      <div class="flex items-center justify-between gap-3">
        <div>
          <p class="text-[11px] font-black uppercase tracking-[0.18em] text-white/72">Statistiques</p>
          <h3 class="mt-1 text-2xl font-black text-white">Profil football</h3>
        </div>
        <span class="rounded-full border border-white/18 bg-white/12 px-3 py-2 text-[11px] font-black text-white backdrop-blur-md">1 a 99</span>
      </div>

      <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="[key, label] in stats"
          :key="key"
          class="rounded-[22px] border border-white/16 bg-white/10 p-3 backdrop-blur-md"
        >
          <AppField :label="label" :error="readError(key)">
            <input
              :value="modelValue[key]"
              type="number"
              min="1"
              max="99"
              class="ecs-input mt-2"
              @input="emit('update-field', key, $event.target.value)"
            />
          </AppField>
        </div>
      </div>
    </div>
  </section>
</template>
