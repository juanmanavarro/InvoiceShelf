import ls from '@/scripts/services/ls'

const STORAGE_KEY = 'invoiceshelf-theme'
const DARK_MODE_CLASS = 'dark'

function getPreferredTheme() {
  const storedTheme = ls.get(STORAGE_KEY)

  if (storedTheme === 'dark' || storedTheme === 'light') {
    return storedTheme
  }

  if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    return 'dark'
  }

  return 'light'
}

function applyTheme(theme) {
  const activeTheme = theme === 'dark' ? 'dark' : 'light'

  document.documentElement.classList.toggle(
    DARK_MODE_CLASS,
    activeTheme === 'dark'
  )
  document.documentElement.style.colorScheme = activeTheme
  window.dispatchEvent(
    new CustomEvent('invoiceshelf-theme-changed', {
      detail: { theme: activeTheme },
    })
  )

  return activeTheme
}

function setTheme(theme) {
  const activeTheme = applyTheme(theme)

  ls.set(STORAGE_KEY, activeTheme)

  return activeTheme
}

function toggleTheme() {
  return setTheme(isDarkMode() ? 'light' : 'dark')
}

function isDarkMode() {
  return document.documentElement.classList.contains(DARK_MODE_CLASS)
}

function initializeTheme() {
  applyTheme(getPreferredTheme())
}

export {
  getPreferredTheme,
  applyTheme,
  setTheme,
  toggleTheme,
  isDarkMode,
  initializeTheme,
}
