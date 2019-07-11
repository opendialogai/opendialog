<template>
  <div>
    <template v-if="currentRouteName == 'view-outgoing-intent'">
      <OutgoingIntent :id="id" />
    </template>
    <template v-else-if="currentRouteName == 'edit-outgoing-intent'">
      <EditOutgoingIntent :id="id" />
    </template>
    <template v-else>
      <OutgoingIntents />
    </template>
  </div>
</template>

<script>
import OutgoingIntent from '@/components/outgoing-intent/OutgoingIntent';
import OutgoingIntents from '@/components/outgoing-intent/OutgoingIntents';
import EditOutgoingIntent from '@/components/outgoing-intent/EditOutgoingIntent';

export default {
  name: 'outgoing-intent',
  props: ['id'],
  components: {
    OutgoingIntent,
    OutgoingIntents,
    EditOutgoingIntent,
  },
  computed: {
    currentRouteName() {
      return this.$route.name;
    },
  },
};
</script>
