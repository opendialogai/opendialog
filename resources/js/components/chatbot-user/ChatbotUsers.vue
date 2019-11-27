<template>
  <div>
    <h2 class="mb-3">Chatbot Users</h2>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" v-model="usersInteract" id="usersInteract" @change="changeUsersInteract">
      <label class="form-check-label" for="usersInteract">
        Show only users that have had an interaction with the chatbot
      </label>
    </div>

    <div class="overflow-auto">
      <table class="table table-hover">
        <thead class="thead-light">
          <tr>
            <th scope="col">User ID</th>
            <th scope="col">Email</th>
            <th scope="col">First Name</th>
            <th scope="col">Last Name</th>
            <th scope="col" :class="(currentOrder == 'first_seen') ? 'sort selected-sort' : 'sort'" @click="sortByFirstSeen">
              First Seen
              <template v-if="currentOrder == 'first_seen'">
                <img v-if="firstSeenSort" class="sort-icon" src="/images/arrow-down.svg">
                <img v-if="!firstSeenSort" class="sort-icon" src="/images/arrow-up.svg">
              </template>
            </th>
            <th scope="col" :class="(currentOrder == 'last_seen') ? 'sort selected-sort' : 'sort'" @click="sortByLastSeen">
              Last Seen
              <template v-if="currentOrder == 'last_seen'">
                <img v-if="lastSeenSort" class="sort-icon" src="/images/arrow-down.svg">
                <img v-if="!lastSeenSort" class="sort-icon" src="/images/arrow-up.svg">
              </template>
            </th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="chatbotUser in chatbotUsers">
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
          <router-link class="page-link" :to="{ name: 'chatbot-users', query: { page: currentPage - 1, order: currentOrder, sort: currentSort, interact } }">Previous</router-link>
        </li>

        <li class="page-item" :class="(pageNumber == currentPage) ? 'active' : ''" v-for="pageNumber in totalPages">
          <template v-if="showPageNumber(pageNumber)">
            <router-link class="page-link" :to="{ name: 'chatbot-users', query: { page: pageNumber, order: currentOrder, sort: currentSort, interact } }">{{ pageNumber }}</router-link>
          </template>
          <template v-if="showPageEllipsis(pageNumber)">
            <span class="page-link">...</span>
          </template>
        </li>

        <li class="page-item" :class="(currentPage == totalPages) ? 'disabled' : ''">
          <router-link class="page-link" :to="{ name: 'chatbot-users', query: { page: currentPage + 1, order: currentOrder, sort: currentSort, interact } }">Next</router-link>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
import Pager from '@/mixins/Pager';

export default {
  name: 'chatbot-users',
  mixins: [Pager],
  data() {
    return {
      chatbotUsers: [],
      firstSeenSort: 1,
      lastSeenSort: 1,
      currentOrder: 'last_seen',
      usersInteract: false,
    };
  },
  computed: {
    currentSort() {
      let sort = 'asc';
      if (this.currentOrder == 'first_seen' && this.firstSeenSort) {
        sort = 'desc';
      }
      if (this.currentOrder == 'last_seen' && this.lastSeenSort) {
        sort = 'desc';
      }
      return sort;
    },
    interact() {
      return (this.usersInteract) ? '1' : '0';
    },
  },
  watch: {
    '$route' () {
      this.fetchChatbotUsers();
    }
  },
  mounted() {
    this.currentOrder = this.$route.query.order || 'last_seen';

    if (this.$route.query.sort == 'asc') {
      if (this.currentOrder == 'first_seen') {
        this.firstSeenSort = 0;
      } else if (this.currentOrder == 'last_seen') {
        this.lastSeenSort = 0;
      }
    }

    this.usersInteract = (this.$route.query.interact == '1') ? true : false;

    this.fetchChatbotUsers();
  },
  methods: {
    fetchChatbotUsers() {
      this.currentPage = parseInt(this.$route.query.page || 1);

      axios.get('/admin/api/chatbot-user?page=' + this.currentPage + '&order=' + this.currentOrder + '&sort=' + this.currentSort + '&interact=' + this.interact).then(
        (response) => {
          this.totalPages = parseInt(response.data.meta.last_page);
          this.chatbotUsers = response.data.data;
        },
      );
    },
    viewChatbotUser(id) {
      this.$router.push({ name: 'view-chatbot-user', params: { id } });
    },
    sortByFirstSeen() {
      this.firstSeenSort = (this.firstSeenSort) ? 0 : 1;
      this.currentOrder = 'first_seen';

      this.$router.push({ name: 'chatbot-users', query: { page: this.currentPage, order: this.currentOrder, sort: this.currentSort, interact: this.interact } });
    },
    sortByLastSeen() {
      this.lastSeenSort = (this.lastSeenSort) ? 0 : 1;
      this.currentOrder = 'last_seen';

      this.$router.push({ name: 'chatbot-users', query: { page: this.currentPage, order: this.currentOrder, sort: this.currentSort, interact: this.interact } });
    },
    changeUsersInteract() {
      this.$router.push({ name: 'chatbot-users', query: { page: this.currentPage, order: this.currentOrder, sort: this.currentSort, interact: this.interact } });
    },
  },
};
</script>

<style lang="scss" scoped>
th.sort {
  text-decoration: underline;
  &.selected-sort {
    background-color: #c2cfd6;
  }
}
</style>
