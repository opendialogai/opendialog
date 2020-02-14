<template>
    <div>
        <Breadcrumbs />
        <template v-if="currentRouteName == 'view-warning'">
      <Warning :id="id" />
    </template>
    <template v-else>
      <Warnings />
    </template>
  </div>
</template>

<script>
import Warning from '@/components/warning/Warning';
import Warnings from '@/components/warning/Warnings';
import Breadcrumbs from "@/components/breadcrumbs/Breadcrumbs";

export default {
  name: 'chatbot-users',
  props: ['id'],
  components: {
    Breadcrumbs,
    Warning,
    Warnings,
  },
  computed: {
    currentRouteName() {
      return this.$route.name;
    },
  },
};
</script>
