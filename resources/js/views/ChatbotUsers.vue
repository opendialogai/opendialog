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
import ChatbotUser from '@/components/chatbot-user/ChatbotUser';
import ChatbotUsers from '@/components/chatbot-user/ChatbotUsers';
import Breadcrumbs from "@/components/breadcrumbs/Breadcrumbs";


export default {
  name: 'chatbot-users',
  props: ['id'],
  components: {
    ChatbotUser,
    ChatbotUsers,
    Breadcrumbs
  },
  computed: {
    currentRouteName() {
      return this.$route.name;
    },
  },
};
</script>
