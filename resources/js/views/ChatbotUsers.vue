<template>
  <div>
      <Breadcrumbs />
      <template v-if="currentRouteName == 'view-chatbot-user'">
      <ChatbotUser :id="id" />
    </template>
    <template v-else>
      <ChatbotUsers />
    </template>
  </div>
</template>

<script>
import ChatbotUsers from '@/components/chatbot-user/ChatbotUsers';


export default {
  name: 'chatbot-users',
  props: ['id'],
  components: {
    ChatbotUsers,
  },
  computed: {
    currentRouteName() {
      return this.$route.name;
    },
  },
};
</script>
