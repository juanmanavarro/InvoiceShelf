<template>
  <transition
    enter-active-class="transition duration-500 ease-in-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition ease-in-out"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-show="show"
      class="relative z-10 rounded border border-gray-200 bg-gray-100 p-4 text-gray-900 transition-colors md:p-8 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
    >
      <slot name="filter-header" />

      <label
        class="absolute top-2.5 right-3.5 cursor-pointer text-sm leading-snug text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100"
        @click="$emit('clear')"
      >
        {{ $t('general.clear_all') }}
      </label>

      <div
        class="flex flex-col space-y-3"
        :class="
          rowOnXl
            ? 'xl:flex-row xl:space-x-4 xl:space-y-0 xl:items-center'
            : 'lg:flex-row lg:space-x-4 lg:space-y-0 lg:items-center'
        "
      >
        <slot />
      </div>
    </div>
  </transition>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  rowOnXl: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['clear'])
</script>
