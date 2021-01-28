<template>
    <div>
        <Breadcrumbs />
        <template v-if="currentRouteName === 'view-warning'">
      <Warning :id="id" />
    </template>
    <template v-else>
      <Warnings />
    </template>
  </div>
</template>

<script>
export default {
  name: 'warnings',
  props: ['id'],
  computed: {
    currentRouteName() {
      return this.$route.name;
    },
  },
};
</script>
