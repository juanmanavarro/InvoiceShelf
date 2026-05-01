<template>
  <span :class="[badgeColorClasses, defaultClass]" class="">
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
    defaultClass: {
      type: String,
      default: 'px-1 py-0.5 text-xs',
    },
  },

  setup(props) {
    const badgeColorClasses = computed(() => {
      switch (props.status) {
        case 'PAID':
          return 'rounded-full bg-primary-300/25 text-primary-800 uppercase font-medium text-center dark:bg-primary-500/20 dark:text-primary-200'
        case 'UNPAID':
          return 'rounded-full bg-yellow-500/25 text-yellow-900 uppercase font-medium text-center dark:bg-yellow-500/20 dark:text-yellow-200'
        case 'PARTIALLY_PAID':
          return 'rounded-full bg-blue-400/25 text-blue-900 uppercase font-medium text-center dark:bg-blue-500/20 dark:text-blue-200'
        case 'OVERDUE':
          return 'rounded-full bg-red-300/50 px-2 py-1 text-sm text-red-900 uppercase font-medium text-center dark:bg-red-500/20 dark:text-red-200'
        default:
          return 'rounded-full bg-gray-500/25 text-gray-900 uppercase font-medium text-center dark:bg-gray-700 dark:text-gray-100'
      }
    })
    return { badgeColorClasses }
  },
}
</script>
