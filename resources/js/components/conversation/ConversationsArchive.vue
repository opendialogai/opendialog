<template>
  <div>
    <h2 class="mb-3">Conversations - Archive</h2>

    <div class="alert alert-danger" role="alert" v-if="errorMessage">
      <span>{{ errorMessage }}</span>
      <button type="button" class="close" @click="errorMessage = ''">
        <span>&times;</span>
      </button>
    </div>

    <div class="alert alert-success" role="alert" v-if="successMessage">
      <span>{{ successMessage }}</span>
      <button type="button" class="close" @click="successMessage = ''">
        <span>&times;</span>
      </button>
    </div>

    <div class="row mb-4">
      <div class="col-12">
        <div class="float-right">
            <b-btn variant="secondary" @click="viewIndex">Back</b-btn>
          <b-btn variant="primary" @click="createConversation">Create</b-btn>
        </div>
      </div>
    </div>

    <div class="overflow-auto">
      <table class="table table-hover">
        <thead class="thead-light">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Name</th>
            <th scope="col">Opening Intents</th>
            <th scope="col">Outgoing Intents</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="conversation in conversations">
            <td>
              {{ conversation.id }}
            </td>
            <td>
              {{ conversation.name }}
            </td>
            <td>
                <span v-for="(opening_intent, index) in conversation.opening_intents">
                    {{ opening_intent }}<span v-if="index < (conversation.opening_intents.length - 1)">, </span>
                </span>
            </td>
            <td>
              <span v-for="(outgoing_intent, index) in conversation.outgoing_intents">
                <template v-if="outgoing_intent.id">
                  <router-link :to="{ name: 'view-outgoing-intent', params: { id: outgoing_intent.id } }">{{ outgoing_intent.name }}</router-link><span v-if="index < (conversation.outgoing_intents.length - 1)">, </span>
                </template>
                <template v-else>
                  <router-link :to="{ name: 'add-outgoing-intent', query: { name: outgoing_intent.name } }">{{ outgoing_intent.name }}</router-link><span v-if="index < (conversation.outgoing_intents.length - 1)">, </span>
                </template>
              </span>
            </td>
            <td class="actions">
              <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="View" @click.stop="viewConversation(conversation.id)">
                <i class="fa fa-eye"></i>
              </button>
              <button class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Unarchive" @click.stop="unarchiveConversation(conversation.id)">
                  <i class="fa fa-refresh"></i>
              </button>
              <button class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Delete" @click.stop="showDeleteConversationModal(conversation)">
                <i class="fa fa-close"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <nav aria-label="navigation">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="(currentPage == 1) ? 'disabled' : ''">
          <router-link class="page-link" :to="{ name: 'conversations', query: { page: currentPage - 1 } }">Previous</router-link>
        </li>

        <li class="page-item" :class="(pageNumber == currentPage) ? 'active' : ''" v-for="pageNumber in totalPages">
          <template v-if="showPageNumber(pageNumber)">
            <router-link class="page-link" :to="{ name: 'conversations', query: { page: pageNumber } }">{{ pageNumber }}</router-link>
          </template>
          <template v-if="showPageEllipsis(pageNumber)">
            <span class="page-link">...</span>
          </template>
        </li>

        <li class="page-item" :class="(currentPage == totalPages) ? 'disabled' : ''">
          <router-link class="page-link" :to="{ name: 'conversations', query: { page: currentPage + 1 } }">Next</router-link>
        </li>
      </ul>
    </nav>

    <div class="modal modal-danger fade" id="deleteConversationModal" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Delete Conversation</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p v-if="currentConversationHasBeenUsed">This conversation has already been used, are you sure you want to delete it rather than keeping it in the archive?</p>
            <p v-else>Are you sure you want to delete this conversation?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
            <button type="button" class="btn btn-danger" @click="deleteConversation">Yes</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Pager from '@/mixins/Pager';

export default {
  name: 'conversations-archive',
  mixins: [Pager],
  data() {
    return {
      errorMessage: '',
      successMessage: '',
      conversations: [],
      currentConversation: null,
      currentConversationHasBeenUsed: false
    };
  },
  watch: {
    '$route' () {
      this.fetchConversations();
    }
  },
  mounted() {
    this.fetchConversations();
  },
  methods: {
    fetchConversations() {
      this.currentPage = parseInt(this.$route.query.page || 1);

      axios.get('/admin/api/conversation-archive?page=' + this.currentPage).then(
        (response) => {
          this.totalPages = parseInt(response.data.meta.last_page);
          this.conversations = response.data.data;
        },
      );
    },
    viewIndex() {
      this.$router.push({ name: 'conversations' });
    },
    createConversation() {
      this.$router.push({ name: 'add-conversation' });
    },
    viewConversation(id) {
      this.$router.push({ name: 'view-conversation', params: { id } });
    },
    showDeleteConversationModal(conversation) {
      this.currentConversation = conversation.id;
      this.currentConversationHasBeenUsed = conversation.has_been_used;
      $('#deleteConversationModal').modal();
    },
    deleteConversation() {
      $('#deleteConversationModal').modal('hide');

      this.conversations = this.conversations.filter(obj => obj.id !== this.currentConversation);

      axios.delete('/admin/api/conversation/' + this.currentConversation);
    },
    unarchiveConversation(id) {
      this.currentConversation = id;
      this.errorMessage = '';
      this.successMessage = '';

      axios.get('/admin/api/conversation/' + id + '/deactivate').then(
        (response) => {
          if (response.data) {
            this.successMessage = 'Conversation unarchived.';
            this.conversations = this.conversations.filter(obj => obj.id !== this.currentConversation);
          } else {
            this.errorMessage = 'Sorry, I wasn\'t able to unarchive this conversation.';
          }
        },
      );
    }
  },
};
</script>

<style lang="scss" scoped>
table td.actions {
  min-width: 250px;
}
</style>
