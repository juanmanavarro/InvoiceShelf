<template>
  <div
    class="
      min-h-screen
      bg-gray-200 dark:bg-gray-950
      flex flex-col
      justify-center
      py-12
      sm:px-6
      lg:px-8
    "
  >
    <NotificationRoot />

    <div class="sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
      <MainLogo
        v-if="!customerLogo"
        class="block w-48 h-auto max-w-full text-primary-400 mx-auto"
      />
      <img
        v-else
        :src="customerLogo"
        class="block w-48 h-auto max-w-full text-primary-400 mx-auto"
      />
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
      <div
        class="rounded-lg bg-white px-4 py-8 text-gray-900 shadow transition-colors dark:bg-gray-900 dark:text-gray-100 sm:px-10"
      >
        <router-view />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import NotificationRoot from '@/scripts/components/notifications/NotificationRoot.vue'
import MainLogo from '@/scripts/components/icons/MainLogo.vue'

const customerLogo = computed(() => {
  if (window.customer_logo) {
    return window.customer_logo
  }

  return false
})
</script>
