<template>
  <div>
      <Breadcrumbs />
      <template v-if="currentRouteName == 'view-outgoing-intent'">
      <OutgoingIntent :id="id" />
    </template>
    <template v-else-if="currentRouteName == 'edit-outgoing-intent'">
      <EditOutgoingIntent :id="id" />
    </template>
    <template v-else-if="currentRouteName == 'add-outgoing-intent'">
      <AddOutgoingIntent />
    </template>
    <template v-else>
      <OutgoingIntents />
    </template>
  </div>
</template>

<script>
import AddOutgoingIntent from '@/components/outgoing-intent/AddOutgoingIntent';
import OutgoingIntent from '@/components/outgoing-intent/OutgoingIntent';
import OutgoingIntents from '@/components/outgoing-intent/OutgoingIntents';
import EditOutgoingIntent from '@/components/outgoing-intent/EditOutgoingIntent';
import Breadcrumbs from "@/components/breadcrumbs/Breadcrumbs";

export default {
  name: 'outgoing-intent',
  props: ['id'],
  components: {
    AddOutgoingIntent,
    OutgoingIntent,
    OutgoingIntents,
    EditOutgoingIntent,
    Breadcrumbs
  },
  computed: {
    currentRouteName() {
      return this.$route.name;
    },
  },
};
</script>
