<template>
  <div>
    <Breadcrumbs />
    <template v-if="currentRouteName == 'view-conversation'">
      <Conversation :id="id" />
    </template>
    <template v-else-if="currentRouteName == 'edit-conversation'">
      <EditConversation :id="id" />
    </template>
    <template v-else-if="currentRouteName == 'add-conversation'">
      <AddConversation />
    </template>
    <template v-else-if="currentRouteName == 'conversations-archive'">
        <ConversationArchive />
    </template>
    <template v-else>
      <Conversations />
    </template>
  </div>
</template>

<script>
import AddConversation from '@/components/conversation/AddConversation';
import Conversation from '@/components/conversation/Conversation';
import Conversations from '@/components/conversation/Conversations';
import EditConversation from '@/components/conversation/EditConversation';
import ConversationArchive from '@/components/conversation/ConversationsArchive';
import Breadcrumbs from "@/components/breadcrumbs/Breadcrumbs";

export default {
  name: 'conversation',
  props: ['id'],
  components: {
    AddConversation,
    Conversation,
    Conversations,
    EditConversation,
    ConversationArchive,
    Breadcrumbs
  },
  computed: {
    currentRouteName() {
      return this.$route.name;
    },
  },
};
</script>
