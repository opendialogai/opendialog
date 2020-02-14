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
          <b-btn variant="secondary" @click="viewArchive">View archive</b-btn>
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
            <th scope="col">Draft</th>
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
              {{ conversation.persisted_status }}
            </td>
            <td>
              {{ conversation.is_draft ? "Yes" : "No" }}
            </td>
            <td class="actions">
              <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="View" @click.stop="viewConversation(conversation.id)">
                <i class="fa fa-eye"></i>
              </button>
              <button class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Edit" @click.stop="editConversation(conversation.id)">
                <i class="fa fa-edit"></i>
              </button>
              <button v-if="conversation.status == 'archived'" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Delete" @click.stop="showDeleteConversationModal(conversation.id)">
                <i class="fa fa-close"></i>
              </button>
              <button v-else v-bind:class="[conversation.status != 'deactivated' ? 'disabled' : '', 'btn', 'btn-danger']" data-toggle="tooltip" data-placement="top" title="Archive" @click.stop="showArchiveConversationModal(conversation.id)" :disabled="conversation.status != 'deactivated'" :aria-disabled="conversation.status != 'deactivated'">
                <i class="fa fa-trash"></i>
              </button>

              <template v-if="conversation.status == 'activated'">
                <button class="btn btn-primary ml-2 disabled" data-toggle="tooltip" data-placement="top" title="Activate" @click.stop="activateConversation(conversation)" disabled aria-disabled>
                  <i class="fa fa-upload"></i>
                </button>
                <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Deactivate" @click.stop="deactivateConversation(conversation)">
                  <i class="fa fa-download"></i>
                </button>
              </template>
              <template v-else>
                <button class="btn btn-primary ml-2" data-toggle="tooltip" data-placement="top" title="Activate" @click.stop="activateConversation(conversation)">
                  <i class="fa fa-upload"></i>
                </button>
                <button class="btn btn-primary disabled" data-toggle="tooltip" data-placement="top" title="Deactivate" @click.stop="deactivateConversation(conversation)" disabled aria-disabled>
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
            <p>Are you sure you want to delete this conversation?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
            <button type="button" class="btn btn-danger" @click="deleteConversation">Yes</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal modal-danger fade" id="archiveConversationModal" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Archive Conversation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to archive this conversation?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                    <button type="button" class="btn btn-danger" @click="archiveConversation">Yes</button>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
    import Pager from '@/mixins/Pager';

    export default {
  name: 'conversations',
  mixins: [Pager],
  data() {
    return {
      errorMessage: '',
      successMessage: '',
      conversations: [],
      currentConversation: null,
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

      axios.get('/admin/api/conversation?page=' + this.currentPage).then(
        (response) => {
          this.totalPages = parseInt(response.data.meta.last_page);
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
    viewArchive() {
      this.$router.push({ name: 'conversations-archive' });
    },
    activateConversation(conversation) {
      this.errorMessage = '';
      this.successMessage = '';

      axios.get('/admin/api/conversation/' + conversation.id + '/activate').then(
        (response) => {
          if (response.data) {
            this.successMessage = 'Conversation activated.';
            conversation.persisted_status = 'activated';
            conversation.status = 'activated';
            conversation.is_draft = false;
            conversation.version_number++;
          } else {
            this.errorMessage = 'Sorry, I wasn\'t able to activate this conversation to DGraph.';
          }
        },
      );
    },
    deactivateConversation(conversation) {
      this.errorMessage = '';
      this.successMessage = '';

      axios.get('/admin/api/conversation/' + conversation.id + '/deactivate').then(
        (response) => {
          if (response.data) {
            this.successMessage = 'Conversation deactivated.';
            conversation.persisted_status = 'deactivated';
            conversation.status = 'deactivated';
            conversation.is_draft = false;
          } else {
            this.errorMessage = 'Sorry, I wasn\'t able to deactivate this conversation from DGraph.';
          }
        },
      );
    },
    showDeleteConversationModal(id) {
      this.currentConversation = id;
      $('#deleteConversationModal').modal();
    },
    showArchiveConversationModal(id) {
        this.currentConversation = id;
        $('#archiveConversationModal').modal();
    },
    deleteConversation() {
      $('#deleteConversationModal').modal('hide');

      this.conversations = this.conversations.filter(obj => obj.id !== this.currentConversation);

      axios.delete('/admin/api/conversation/' + this.currentConversation);
    },
    archiveConversation() {
        $('#archiveConversationModal').modal('hide');

        axios.get('/admin/api/conversation/' + this.currentConversation + '/archive').then(
            () => {
                this.successMessage = 'Conversation archived.';
                this.conversations = this.conversations.filter(obj => obj.id !== this.currentConversation);
            }
        ).catch(
            (error) => {
                this.errorMessage = 'Sorry, I wasn\'t able to archive this conversation from DGraph.';
            }
        );
    },
  },
};
</script>

<style lang="scss" scoped>
table td.actions {
  min-width: 250px;
}
</style>
