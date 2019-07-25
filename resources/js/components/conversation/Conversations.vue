<template>
  <div>
    <h2 class="mb-3">Conversations</h2>

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
            <th scope="col">Status</th>
            <th scope="col">Yaml</th>
            <th scope="col">Opening Intent</th>
            <th scope="col">Outgoing Intents</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="conversation in conversations" @click="viewConversation(conversation.id)">
            <td>
              {{ conversation.id }}
            </td>
            <td>
              {{ conversation.name }}
            </td>
            <td>
              {{ conversation.status }}
            </td>
            <td>
              {{ conversation.yaml_validation_status }}
            </td>
            <td>
              {{ conversation.opening_intent }}
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
              <button class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Edit" @click.stop="editConversation(conversation.id)">
                <i class="fa fa-edit"></i>
              </button>
              <button class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Delete" @click.stop="showDeleteConversationModal(conversation.id)">
                <i class="fa fa-close"></i>
              </button>

              <template v-if="conversation.status == 'published'">
                <button class="btn btn-primary ml-2" data-toggle="tooltip" data-placement="top" title="Publish" @click.stop="publishConversation(conversation)" disabled>
                  <i class="fa fa-upload"></i>
                </button>
                <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Unpublish" @click.stop="unpublishConversation(conversation)">
                  <i class="fa fa-download"></i>
                </button>
              </template>
              <template v-else>
                <button class="btn btn-primary ml-2" data-toggle="tooltip" data-placement="top" title="Publish" @click.stop="publishConversation(conversation)">
                  <i class="fa fa-upload"></i>
                </button>
                <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Unpublish" @click.stop="unpublishConversation(conversation)" disabled>
                  <i class="fa fa-download"></i>
                </button>
              </template>
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

        <li class="page-item" v-for="pageNumber in totalPages">
          <router-link class="page-link" :to="{ name: 'conversations', query: { page: pageNumber } }">{{ pageNumber }}</router-link>
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
            <p>Are you sure you want to delete this conversation?</p>
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
export default {
  name: 'conversations',
  data() {
    return {
      errorMessage: '',
      successMessage: '',
      conversations: [],
      currentConversation: null,
      currentPage: 1,
      totalPages: 1,
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
      this.currentPage = this.$route.query.page || 1;

      axios.get('/admin/api/conversation?page=' + this.currentPage).then(
        (response) => {
          this.totalPages = response.data.meta.last_page;
          this.conversations = response.data.data;
        },
      );
    },
    createConversation() {
      this.$router.push({ name: 'add-conversation' });
    },
    viewConversation(id) {
      this.$router.push({ name: 'view-conversation', params: { id } });
    },
    editConversation(id) {
      this.$router.push({ name: 'edit-conversation', params: { id } });
    },
    publishConversation(conversation) {
      this.errorMessage = '';
      this.successMessage = '';

      axios.get('/admin/api/conversation/' + conversation.id + '/publish').then(
        (response) => {
          if (response.data) {
            this.successMessage = 'Conversation published.';
            conversation.status = 'published';
          } else {
            this.errorMessage = 'Sorry, I wasn\'t able to publish this conversation to DGraph.';
          }
        },
      );
    },
    unpublishConversation(conversation) {
      this.errorMessage = '';
      this.successMessage = '';

      axios.get('/admin/api/conversation/' + conversation.id + '/unpublish').then(
        (response) => {
          if (response.data) {
            this.successMessage = 'Conversation unpublished.';
            conversation.status = 'validated';
          } else {
            this.errorMessage = 'Sorry, I wasn\'t able to unpublish this conversation from DGraph.';
          }
        },
      );
    },
    showDeleteConversationModal(id) {
      this.currentConversation = id;
      $('#deleteConversationModal').modal();
    },
    deleteConversation() {
      $('#deleteConversationModal').modal('hide');

      this.conversations = this.conversations.filter(obj => obj.id !== this.currentConversation);

      axios.delete('/admin/api/conversation/' + this.currentConversation);
    },
  },
};
</script>

<style lang="scss" scoped>
table td.actions {
  min-width: 250px;
}
</style>
