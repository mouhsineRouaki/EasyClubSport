<script setup>
import AppButton from '@/shared/components/ui/AppButton.vue'

const props = defineProps({
  message: {
    type: Object,
    required: true,
  },
  own: {
    type: Boolean,
    default: false,
  },
  editing: {
    type: Boolean,
    default: false,
  },
  editingValue: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['edit', 'cancel-edit', 'save-edit', 'delete', 'update:editingValue'])

const formatHour = (value) => {
  if (!value) {
    return ''
  }

  return new Intl.DateTimeFormat('fr-FR', {
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(value))
}
</script>

<template>
  <article class="flex" :class="own ? 'justify-end' : 'justify-start'">
    <div class="max-w-[78%]">
      <div class="mb-1 flex items-center gap-2 px-1" :class="own ? 'justify-end' : 'justify-start'">
        <p class="text-[11px] font-semibold text-[#64748b]">
          {{ message.expediteur?.nom || 'Utilisateur' }}
        </p>
        <span class="text-[10px] text-[#94a3b8]">{{ formatHour(message.created_at) }}</span>
      </div>

      <div
        class="rounded-[24px] px-4 py-3 shadow-[0_18px_35px_rgba(15,23,42,0.06)]"
        :class="
          own
            ? 'rounded-br-md bg-[linear-gradient(135deg,#172554_0%,#1d4ed8_62%,#14b8a6_100%)] text-white'
            : 'rounded-bl-md border border-[#e8edf6] bg-white text-[#0f172a]'
        "
      >
        <div v-if="editing" class="space-y-3">
          <textarea
            :value="editingValue"
            rows="3"
            class="w-full rounded-2xl border border-[#d7e1fb] bg-white/95 px-3 py-2 text-sm text-[#0f172a] outline-none focus:border-[#4c6fff]"
            @input="emit('update:editingValue', $event.target.value)"
          ></textarea>

          <div class="flex justify-end gap-2">
            <AppButton type="button" variant="secondary" size="sm" @click="emit('cancel-edit')">
              Annuler
            </AppButton>
            <AppButton type="button" size="sm" @click="emit('save-edit', message)">
              Enregistrer
            </AppButton>
          </div>
        </div>

        <p v-else class="whitespace-pre-wrap text-sm leading-6">{{ message.contenu }}</p>
      </div>

      <div v-if="own && !editing" class="mt-2 flex justify-end gap-2 px-1">
        <AppButton type="button" variant="secondary" size="sm" @click="emit('edit', message)">
          Modifier
        </AppButton>
        <AppButton type="button" variant="danger" size="sm" @click="emit('delete', message)">
          Supprimer
        </AppButton>
      </div>
    </div>
  </article>
</template>
