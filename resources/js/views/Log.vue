<template>
  <div>
    <template v-if="currentRouteName == 'view-log'">
      <Log :id="id" />
    </template>
    <template v-else>
      <Logs />
    </template>
  </div>
</template>

<script>
import Log from '@/components/log/Log';
import Logs from '@/components/log/Logs';

export default {
  name: 'chatbot-users',
  props: ['id'],
  components: {
    Log,
    Logs,
  },
  computed: {
    currentRouteName() {
      return this.$route.name;
    },
  },
};
</script>
