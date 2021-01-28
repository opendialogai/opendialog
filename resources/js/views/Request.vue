<template>
  <div>
    <Breadcrumbs/>
    <template v-if="currentRouteName == 'view-request'">
      <Request :id="id" />
    </template>
    <template v-else>
      <Requests />
    </template>
  </div>
</template>

<script>
import Request from '@/components/request/Request';
import Requests from '@/components/request/Requests';

export default {
  name: 'request',
  props: ['id'],
  components: {
    Request,
    Requests,
  },
  computed: {
    currentRouteName() {
      return this.$route.name;
    },
  },
};
</script>
