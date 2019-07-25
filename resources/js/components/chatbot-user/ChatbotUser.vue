<template>
  <div v-if="chatbotUser">
    <h2 class="mb-3">Chatbot User</h2>

    <b-card header="General">
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Conversation Log</b-col>
        <b-col cols="10"><router-link :to="{ name: 'conversation-log', params: { id: chatbotUser.user_id } }">view</router-link></b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">User ID</b-col>
        <b-col cols="10">{{ chatbotUser.user_id }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Email</b-col>
        <b-col cols="10">{{ chatbotUser.email }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">First Name</b-col>
        <b-col cols="10">{{ chatbotUser.first_name }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Last Name</b-col>
        <b-col cols="10">{{ chatbotUser.last_name }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">First Seen</b-col>
        <b-col cols="10">{{ chatbotUser.first_seen }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Last Seen</b-col>
        <b-col cols="10">{{ chatbotUser.last_seen }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">IP Address</b-col>
        <b-col cols="10">{{ chatbotUser.ip_address }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Country</b-col>
        <b-col cols="10">{{ chatbotUser.country }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Browser language</b-col>
        <b-col cols="10">{{ chatbotUser.browser_language }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Operating system</b-col>
        <b-col cols="10">{{ chatbotUser.os }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Browser</b-col>
        <b-col cols="10">{{ chatbotUser.browser }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Timezone</b-col>
        <b-col cols="10">{{ chatbotUser.timezone }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Platform</b-col>
        <b-col cols="10">{{ chatbotUser.platform }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Created at</b-col>
        <b-col cols="10">{{ chatbotUser.created_at }}</b-col>
      </b-row>
      <b-row>
        <b-col class="font-weight-bold" cols="2">Updated at</b-col>
        <b-col cols="10">{{ chatbotUser.updated_at }}</b-col>
      </b-row>
    </b-card>
  </div>
</template>

<script>
export default {
  name: 'chatbot-user',
  props: ['id'],
  data() {
    return {
      chatbotUser: null,
    };
  },
  mounted() {
    axios.get('/admin/api/chatbot-user/' + this.id).then(
      (response) => {
        this.chatbotUser = response.data.data;
      },
    );
  },
};
</script>
