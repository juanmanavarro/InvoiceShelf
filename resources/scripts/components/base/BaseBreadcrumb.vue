<template>
  <nav>
    <ol class="list-reset flex flex-wrap rounded py-4 text-gray-900 dark:text-gray-200">
      <slot />
    </ol>
  </nav>
</template>

<script>
export default {
  name: 'BaseBreadcrumb',
}
</script>
