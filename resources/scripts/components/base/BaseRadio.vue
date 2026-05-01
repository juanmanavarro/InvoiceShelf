<template>
  <RadioGroup v-model="selected">
    <RadioGroupLabel class="sr-only"> Privacy setting </RadioGroupLabel>
    <div class="-space-y-px rounded-md">
      <RadioGroupOption
        :id="id"
        v-slot="{ checked, active }"
        as="template"
        :value="value"
        :name="name"
        v-bind="$attrs"
      >
        <div class="relative flex cursor-pointer focus:outline-hidden">
          <span
            :class="[
              checked ? checkedStateClass : unCheckedStateClass,
              active ? optionGroupActiveStateClass : '',
              optionGroupClass,
            ]"
            aria-hidden="true"
          >
            <span class="w-1.5 h-1.5 rounded-full bg-white dark:bg-gray-900" />
          </span>
          <div class="flex flex-col ml-3">
            <RadioGroupLabel
              as="span"
              :class="[
                checked ? checkedStateLabelClass : unCheckedStateLabelClass,
                optionGroupLabelClass,
              ]"
            >
              {{ label }}
            </RadioGroupLabel>
          </div>
        </div>
      </RadioGroupOption>
    </div>
  </RadioGroup>
</template>

<script setup>
import { computed } from 'vue'
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'

const props = defineProps({
  id: {
    type: [String, Number],
    required: false,
    default: () => `radio_${Math.random().toString(36).substr(2, 9)}`,
  },
  label: {
    type: String,
    default: '',
  },
  modelValue: {
    type: [String, Number],
    default: '',
  },
  value: {
    type: [String, Number],
    default: '',
  },
  name: {
    type: [String, Number],
    default: '',
  },
  checkedStateClass: {
    type: String,
    default: 'bg-primary-600',
  },
  unCheckedStateClass: {
    type: String,
    default: 'bg-white border-gray-300 dark:bg-gray-800 dark:border-gray-600',
  },
  optionGroupActiveStateClass: {
    type: String,
    default: 'ring-2 ring-offset-2 ring-primary-500',
  },
  checkedStateLabelClass: {
    type: String,
    default: 'text-primary-900 dark:text-primary-400',
  },
  unCheckedStateLabelClass: {
    type: String,
    default: 'text-gray-900 dark:text-gray-200',
  },
  optionGroupClass: {
    type: String,
    default:
      'h-4 w-4 mt-0.5 cursor-pointer rounded-full border flex items-center justify-center',
  },
  optionGroupLabelClass: {
    type: String,
    default: 'block text-sm font-light',
  },
})

const emit = defineEmits(['update:modelValue'])

const selected = computed({
  get: () => props.modelValue,
  set: (modelValue) => emit('update:modelValue', modelValue),
})
</script>
