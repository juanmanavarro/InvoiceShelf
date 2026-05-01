<template>
  <Disclosure
    v-slot="{ open }"
    as="nav"
    class="fixed top-0 left-0 z-20 w-full bg-white text-gray-900 shadow-xs transition-colors"
  >
    <div class="mx-auto px-8">
      <div class="flex justify-between h-16 w-full">
        <div class="flex">
          <div class="shrink-0 flex items-center">
            <a
              :href="`/${globalStore.companySlug}/customer/dashboard`"
              class="
                float-none
                text-lg
                not-italic
                font-black
                tracking-wider
                text-white
                brand-main
                md:float-left
                font-base
              "
            >
              <MainLogo v-if="!customerLogo" class="h-6" />
              <img v-else :src="customerLogo" class="h-6" />
            </a>
          </div>
          <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
            <router-link
              v-for="item in globalStore.mainMenu"
              :key="item.title"
              :to="`/${globalStore.companySlug}${item.link}`"
              :class="[
                hasActiveUrl(item.link)
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-300 dark:hover:text-white dark:hover:border-gray-600',
                  'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                ]"
            >
              {{ $t(item.title) }}
            </router-link>
          </div>
        </div>
        <div class="hidden sm:ml-6 sm:flex sm:items-center">
          <button
            type="button"
            class="rounded-full bg-white p-2 text-gray-500 transition hover:text-gray-700 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-300 dark:hover:text-white dark:focus:ring-offset-gray-900"
            :aria-label="isDark ? 'Activate light mode' : 'Activate dark mode'"
            @click="toggleColorMode"
          >
            <component :is="isDark ? SunIcon : MoonIcon" class="h-5 w-5" />
          </button>

          <!-- Profile dropdown -->

          <Menu as="div" class="ml-3 relative">
            <BaseDropdown width-class="w-48">
              <template #activator>
                <MenuButton
                  class="
                    bg-white
                    flex
                    text-sm
                    rounded-full
                    focus:outline-hidden
                    focus:ring-2
                    focus:ring-offset-2
                    focus:ring-primary-500
                    dark:bg-gray-800 dark:focus:ring-offset-gray-900
                  "
                >
                  <img
                    class="h-8 w-8 rounded-full"
                    :src="previewAvatar"
                    alt=""
                  />
                </MenuButton>
              </template>
              <router-link :to="{ name: 'customer.profile' }">
                <BaseDropdownItem>
                  <CogIcon
                    class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-500"
                    aria-hidden="true"
                  />
                  {{ $t('navigation.settings') }}
                </BaseDropdownItem>
              </router-link>

              <BaseDropdownItem @click="logout">
                <ArrowRightOnRectangleIcon
                  class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-500"
                  aria-hidden="true"
                />
                {{ $t('navigation.logout') }}
              </BaseDropdownItem>
            </BaseDropdown>
          </Menu>
        </div>
        <div class="-mr-2 flex items-center sm:hidden">
          <!-- Mobile menu button -->
          <DisclosureButton
            class="
              bg-white
              inline-flex
              items-center
              justify-center
              p-2
              rounded-md
              text-gray-400
              hover:text-gray-500 hover:bg-gray-100
              focus:outline-hidden
              focus:ring-2
              focus:ring-offset-2
              focus:ring-primary-500
              dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-offset-gray-900
            "
          >
            <span class="sr-only">Open main menu</span>
            <Bars3Icon v-if="!open" class="block h-6 w-6" aria-hidden="true" />
            <XMarkIcon v-else class="block h-6 w-6" aria-hidden="true" />
          </DisclosureButton>
        </div>
      </div>
    </div>

    <DisclosurePanel class="bg-white transition-colors dark:bg-gray-900 sm:hidden">
      <div class="pt-2 pb-3 space-y-1">
        <router-link
          v-for="item in globalStore.mainMenu"
          :key="item.title"
          :to="`/${globalStore.companySlug}${item.link}`"
          :class="[
            hasActiveUrl(item.link)
              ? 'bg-primary-50 border-primary-500 text-primary-700 dark:bg-gray-800 dark:text-primary-400'
              : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:border-gray-600 dark:hover:text-white',
            'block pl-3 pr-4 py-2 border-l-4 text-base font-medium',
          ]"
          :aria-current="item.current ? 'page' : undefined"
          >{{ $t(item.title) }}
        </router-link>
      </div>
      <div class="pt-4 pb-3 border-t border-gray-200 dark:border-gray-800">
        <div class="flex items-center px-4">
          <div class="shrink-0">
            <img class="h-10 w-10 rounded-full" :src="previewAvatar" alt="" />
          </div>
          <div class="ml-3">
            <div class="text-base font-medium text-gray-800 dark:text-gray-100">
              {{ globalStore.currentUser.title }}
            </div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
              {{ globalStore.currentUser.email }}
            </div>
          </div>
          <button
            type="button"
            class="ml-auto shrink-0 rounded-full bg-white p-2 text-gray-500 transition hover:text-gray-700 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-300 dark:hover:text-white dark:focus:ring-offset-gray-900"
            :aria-label="isDark ? 'Activate light mode' : 'Activate dark mode'"
            @click="toggleColorMode"
          >
            <component :is="isDark ? SunIcon : MoonIcon" class="h-5 w-5" />
          </button>
        </div>
        <div class="mt-3 space-y-1">
          <router-link
            v-for="item in userNavigation"
            :key="item.title"
            :to="item.link"
            :class="[
              hasActiveUrl(item.link)
                ? 'bg-primary-50 border-primary-500 text-primary-700 dark:bg-gray-800 dark:text-primary-400'
                : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:border-gray-600 dark:hover:text-white',
              'block pl-3 pr-4 py-2 border-l-4 text-base font-medium',
            ]"
            >{{ $t(item.title) }}</router-link
          >
        </div>
      </div>
    </DisclosurePanel>
  </Disclosure>
</template>

<script setup>
import { useAuthStore } from '@/scripts/customer/stores/auth'
import { useRoute, useRouter } from 'vue-router'
import { ref, watch, computed } from 'vue'
import { useGlobalStore } from '@/scripts/customer/stores/global'
import MainLogo from '@/scripts/components/icons/MainLogo.vue'
import { isDarkMode, toggleTheme } from '@/scripts/services/theme'
import {
  Disclosure,
  DisclosureButton,
  DisclosurePanel,
  Menu,
  MenuButton,
} from '@headlessui/vue'
import {
  Bars3Icon,
  XMarkIcon,
  ArrowRightOnRectangleIcon,
  CogIcon,
  MoonIcon,
  SunIcon,
} from '@heroicons/vue/24/outline'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const route = useRoute()
const globalStore = useGlobalStore()

const userNavigation = [
  {
    title: t('navigation.logout'),
    link: `/${globalStore.companySlug}/customer/login`,
  },
]

const authStore = useAuthStore()
const router = useRouter()
const activeRoute = ref('')
const isDark = ref(isDarkMode())

const previewAvatar = computed(() => {
  return globalStore.currentUser && globalStore.currentUser.avatar !== 0
    ? globalStore.currentUser.avatar
    : getDefaultAvatar()
})

function getDefaultAvatar() {
  const imgUrl = new URL('$images/default-avatar.jpg', import.meta.url)
  return imgUrl
}

watch(
  route,
  (val) => {
    activeRoute.value = val.path
  },
  { immediate: true }
)

const customerLogo = computed(() => {
  if (window.customer_logo) {
    return window.customer_logo
  }

  return false
})

function hasActiveUrl(url) {
  return route.path.indexOf(url) > -1
}

function logout() {
  authStore.logout(globalStore.companySlug).then((res) => {
    if (res) {
      router.push({ name: 'customer.login' })
    }
  })
}

function toggleColorMode() {
  isDark.value = toggleTheme() === 'dark'
}
</script>
