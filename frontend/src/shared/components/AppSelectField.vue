<script setup>
const props = defineProps({
  label: {
    type: String,
    default: '',
  },
  modelValue: {
    type: [String, Number],
    default: '',
  },
  options: {
    type: Array,
    default: () => [],
  },
  placeholder: {
    type: String,
    default: 'Choisir une option',
  },
  placeholderValue: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  optionValue: {
    type: [String, Function],
    default: 'id',
  },
  optionLabel: {
    type: [String, Function],
    default: 'nom',
  },
  labelClass: {
    type: String,
    default: 'ecs-field-label',
  },
  selectClass: {
    type: String,
    default: 'ecs-select',
  },
})

const emit = defineEmits(['update:modelValue'])

const lireValeurOption = (option) => {
  if (typeof props.optionValue === 'function') {
    return props.optionValue(option)
  }

  return option?.[props.optionValue]
}

const lireLabelOption = (option) => {
  if (typeof props.optionLabel === 'function') {
    return props.optionLabel(option)
  }

  return option?.[props.optionLabel]
}
</script>

<template>
  <label class="block">
    <span v-if="label" :class="labelClass">{{ label }}</span>
    <select
      :value="modelValue"
      :class="selectClass"
      :disabled="disabled"
      @change="emit('update:modelValue', $event.target.value)"
    >
      <option :value="placeholderValue">{{ placeholder }}</option>
      <option
        v-for="option in options"
        :key="String(lireValeurOption(option))"
        :value="String(lireValeurOption(option))"
      >
        {{ lireLabelOption(option) }}
      </option>
    </select>
  </label>
</template>
