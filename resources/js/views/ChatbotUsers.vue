<template>
  <div>
    <template v-if="currentRouteName == 'view-chatbot-user'">
      <ChatbotUser :id="id" />
    </template>
    <template v-else>
      <ChatbotUsers />
    </template>
  </div>
</template>

<script>
import ChatbotUser from '@/components/chatbot-user/ChatbotUser';
import ChatbotUsers from '@/components/chatbot-user/ChatbotUsers';

export default {
  name: 'chatbot-users',
  props: ['id'],
  components: {
    ChatbotUser,
    ChatbotUsers,
  },
  computed: {
    currentRouteName() {
      return this.$route.name;
    },
  },
};
</script>
