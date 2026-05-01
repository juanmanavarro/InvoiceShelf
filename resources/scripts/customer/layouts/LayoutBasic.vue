<template>
  <div v-if="isAppLoaded" class="h-full">
    <NotificationRoot />

    <SiteHeader />

    <!-- <SiteSidebar /> -->

    <main
      class="mt-16 h-screen min-h-0 overflow-y-auto bg-gray-100 pb-16 transition-colors dark:bg-gray-950"
    >
      <router-view />
    </main>
  </div>

  <!-- <BaseGlobalLoader v-else /> -->
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import SiteHeader from '@/scripts/customer/layouts/partials/TheSiteHeader.vue'
import NotificationRoot from '@/scripts/components/notifications/NotificationRoot.vue'
import { useGlobalStore } from '@/scripts/customer/stores/global'
import { useRoute } from 'vue-router'

const globalStore = useGlobalStore()
const route = useRoute()

const isAppLoaded = computed(() => {
  return globalStore.isAppLoaded
})

loadData()

async function loadData() {
  await globalStore.bootstrap(route.params.company)
}
</script>
