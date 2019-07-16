<template>
  <div>
    <div class="row mb-4">
      <div class="col-12">
        <div class="float-right">
          <b-btn variant="primary" @click="createConversation">Create</b-btn>
        </div>
      </div>
    </div>

    <table class="table table-striped">
      <thead class="thead-light">
        <tr>
          <th scope="col">#</th>
          <th scope="col">Name</th>
          <th scope="col">Status</th>
          <th scope="col">Yaml</th>
          <th scope="col">Schema</th>
          <th scope="col">Scenes</th>
          <th scope="col">Model</th>
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
            {{ conversation.yaml_schema_validation_status }}
          </td>
          <td>
            {{ conversation.scenes_validation_status }}
          </td>
          <td>
            {{ conversation.model_validation_status }}
          </td>
          <td>
            <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="View" @click.stop="viewConversation(conversation.id)">
              <i class="fa fa-eye"></i>
            </button>
            <button class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Edit" @click.stop="editConversation(conversation.id)">
              <i class="fa fa-edit"></i>
            </button>
            <button class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Delete" @click.stop="showDeleteConversationModal(conversation.id)">
              <i class="fa fa-close"></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>

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
