<template>
  <div>
    <Breadcrumbs />
    <template v-if="currentRouteName == 'view-message-template'">
      <MessageTemplate :outgoingIntent="outgoingIntent" :id="id" />
    </template>
    <template v-else-if="currentRouteName == 'edit-message-template'">
      <EditMessageTemplate :outgoingIntent="outgoingIntent" :id="id" />
    </template>
    <template v-else-if="currentRouteName == 'add-message-template'">
      <AddMessageTemplate :outgoingIntent="outgoingIntent" />
    </template>
    <template v-else>
      <MessageTemplates :outgoingIntent="outgoingIntent" />
    </template>
  </div>
</template>

<script>
import AddMessageTemplate from '@/components/message-template/AddMessageTemplate';
import MessageTemplate from '@/components/message-template/MessageTemplate';
import MessageTemplates from '@/components/message-template/MessageTemplates';
import EditMessageTemplate from '@/components/message-template/EditMessageTemplate';
import Breadcrumbs from "@/components/breadcrumbs/Breadcrumbs";

export default {
  name: 'message-template',
  props: ['outgoingIntent', 'id'],
  components: {
    AddMessageTemplate,
    MessageTemplate,
    MessageTemplates,
    EditMessageTemplate,
    Breadcrumbs
  },
  computed: {
    currentRouteName() {
      return this.$route.name;
    },
  },
};
</script>
