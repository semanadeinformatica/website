<script setup>
import { computed } from 'vue'

const props = defineProps({ status: Number })

const title = computed(() => {
  return {
    500: '500: Server Error',
    404: '404: Page Not Found',
    403: '403: Forbidden',
  }[props.status]
})

const description = computed(() => {
  return {
    500: 'Whoops, something went wrong on our servers.',
    404: 'Sorry, the page you are looking for could not be found.',
    403: 'Sorry, you are forbidden from accessing this page.',
  }[props.status]
})
</script>

<template>
  <div>
    <H1>{{ title }}</H1>
    <div>{{ description }}</div>
  </div>
</template>