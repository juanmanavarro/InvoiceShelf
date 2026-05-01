<template>
  <span :class="badgeColorClasses">
    <slot />
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: false,
    default: '',
  },
})

const badgeColorClasses = computed(() => {
  switch (props.status) {
    case 'DRAFT':
      return 'inline-flex items-center justify-center rounded-full bg-gray-200 px-2 py-1 text-sm text-gray-700 uppercase font-medium text-center dark:bg-gray-700 dark:text-gray-100'
    case 'SENT':
      return 'inline-flex items-center justify-center rounded-full bg-yellow-500/25 px-2 py-1 text-sm text-yellow-900 uppercase font-medium text-center dark:bg-yellow-500/20 dark:text-yellow-200'
    case 'VIEWED':
      return 'inline-flex items-center justify-center rounded-full bg-blue-400/25 px-2 py-1 text-sm text-blue-900 uppercase font-medium text-center dark:bg-blue-500/20 dark:text-blue-200'
    case 'EXPIRED':
      return 'inline-flex items-center justify-center rounded-full bg-red-300/25 px-2 py-1 text-sm text-red-800 uppercase font-medium text-center dark:bg-red-500/20 dark:text-red-200'
    case 'ACCEPTED':
      return 'inline-flex items-center justify-center rounded-full bg-green-400/25 px-2 py-1 text-sm text-green-800 uppercase font-medium text-center dark:bg-green-500/20 dark:text-green-200'
    case 'REJECTED':
      return 'inline-flex items-center justify-center rounded-full bg-purple-300/25 px-2 py-1 text-sm text-purple-800 uppercase font-medium text-center dark:bg-purple-500/20 dark:text-purple-200'
    default:
      return 'inline-flex items-center justify-center rounded-full bg-gray-500/25 px-2 py-1 text-sm text-gray-900 uppercase font-medium text-center dark:bg-gray-700 dark:text-gray-100'
  }
})
</script>
