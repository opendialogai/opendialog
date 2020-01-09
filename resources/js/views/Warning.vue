<template>
  <div>
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

export default {
  name: 'chatbot-users',
  props: ['id'],
  components: {
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
