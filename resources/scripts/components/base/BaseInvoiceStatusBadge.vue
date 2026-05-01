<template>
  <span :class="badgeColorClasses">
    <slot />
  </span>
</template>

<script>
import { computed } from 'vue'

export default {
  props: {
    status: {
      type: String,
      required: false,
      default: '',
    },
  },

  setup(props) {
    const badgeColorClasses = computed(() => {
      switch (props.status) {
        case 'DRAFT':
          return 'inline-flex items-center justify-center rounded-full bg-gray-200 px-2 py-1 text-sm text-gray-700 uppercase font-medium text-center dark:bg-gray-700 dark:text-gray-100'
        case 'SENT':
          return 'inline-flex items-center justify-center rounded-full bg-yellow-500/25 px-2 py-1 text-sm text-yellow-900 uppercase font-medium text-center dark:bg-yellow-500/20 dark:text-yellow-200'
        case 'VIEWED':
          return 'inline-flex items-center justify-center rounded-full bg-blue-400/25 px-2 py-1 text-sm text-blue-900 uppercase font-medium text-center dark:bg-blue-500/20 dark:text-blue-200'
        case 'COMPLETED':
          return 'inline-flex items-center justify-center rounded-full bg-green-500/25 px-2 py-1 text-sm text-green-900 uppercase font-medium text-center dark:bg-green-500/20 dark:text-green-200'
        case 'DUE':
          return 'inline-flex items-center justify-center rounded-full bg-yellow-500/25 px-2 py-1 text-sm text-yellow-900 uppercase font-medium text-center dark:bg-yellow-500/20 dark:text-yellow-200'
        case 'OVERDUE':
          return 'inline-flex items-center justify-center rounded-full bg-red-300/50 px-2 py-1 text-sm text-red-900 uppercase font-medium text-center dark:bg-red-500/20 dark:text-red-200'
        case 'UNPAID':
          return 'inline-flex items-center justify-center rounded-full bg-yellow-500/25 px-2 py-1 text-sm text-yellow-900 uppercase font-medium text-center dark:bg-yellow-500/20 dark:text-yellow-200'
        case 'PARTIALLY_PAID':
          return 'inline-flex items-center justify-center rounded-full bg-blue-400/25 px-2 py-1 text-sm text-blue-900 uppercase font-medium text-center dark:bg-blue-500/20 dark:text-blue-200'
        case 'PAID':
          return 'inline-flex items-center justify-center rounded-full bg-green-500/25 px-2 py-1 text-sm text-green-900 uppercase font-medium text-center dark:bg-green-500/20 dark:text-green-200'
        default:
          return 'inline-flex items-center justify-center rounded-full bg-gray-500/25 px-2 py-1 text-sm text-gray-900 uppercase font-medium text-center dark:bg-gray-700 dark:text-gray-100'
      }
    })
    return { badgeColorClasses }
  },
}
</script>
