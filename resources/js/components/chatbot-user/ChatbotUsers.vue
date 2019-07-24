<template>
  <div>
    <h2 class="mb-3">Chatbot Users</h2>

    <div class="overflow-auto">
      <table class="table table-hover">
        <thead class="thead-light">
          <tr>
            <th scope="col">User ID</th>
            <th scope="col">Email</th>
            <th scope="col">First Name</th>
            <th scope="col">Last Name</th>
            <th scope="col">First Seen</th>
            <th scope="col">Last Seen</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="chatbotUser in chatbotUsers" @click="viewChatbotUser(chatbotUser.user_id)">
            <td>
              {{ chatbotUser.user_id }}
            </td>
            <td>
              {{ chatbotUser.email }}
            </td>
            <td>
              {{ chatbotUser.first_name }}
            </td>
            <td>
              {{ chatbotUser.last_name }}
            </td>
            <td>
              {{ chatbotUser.first_seen }}
            </td>
            <td>
              {{ chatbotUser.last_seen }}
            </td>
            <td>
              <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="View" @click.stop="viewChatbotUser(chatbotUser.user_id)">
                <i class="fa fa-eye"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <nav aria-label="navigation">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="(currentPage == 1) ? 'disabled' : ''">
          <router-link class="page-link" :to="{ name: 'chatbot-users', query: { page: currentPage - 1 } }">Previous</router-link>
        </li>

        <li class="page-item" v-for="pageNumber in totalPages">
          <router-link class="page-link" :to="{ name: 'chatbot-users', query: { page: pageNumber } }">{{ pageNumber }}</router-link>
        </li>

        <li class="page-item" :class="(currentPage == totalPages) ? 'disabled' : ''">
          <router-link class="page-link" :to="{ name: 'chatbot-users', query: { page: currentPage + 1 } }">Next</router-link>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
export default {
  name: 'chatbot-users',
  data() {
    return {
      chatbotUsers: [],
      currentPage: 1,
      totalPages: 1,
    };
  },
  watch: {
    '$route' () {
      this.fetchChatbotUsers();
    }
  },
  mounted() {
    this.fetchChatbotUsers();
  },
  methods: {
    fetchChatbotUsers() {
      this.currentPage = this.$route.query.page || 1;

      axios.get('/admin/api/chatbot-user?page=' + this.currentPage).then(
        (response) => {
          this.totalPages = response.data.meta.last_page;
          this.chatbotUsers = response.data.data;
        },
      );
    },
    viewChatbotUser(id) {
      this.$router.push({ name: 'view-chatbot-user', params: { id } });
    },
  },
};
</script>
