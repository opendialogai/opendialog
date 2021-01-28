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
import Breadcrumbs from "@/components/breadcrumbs/Breadcrumbs";

export default {
  name: 'warnings',
  props: ['id'],
  components: {
    Breadcrumbs
  },
  computed: {
    currentRouteName() {
      return this.$route.name;
    },
  },
};
</script>
