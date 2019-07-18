<template>
  <div v-if="conversation">
    <div class="row mb-4">
      <div class="col-12">
        <div class="float-right">
          <b-btn variant="primary" @click="editConversation">Edit</b-btn>
          <b-btn variant="danger mr-4" @click="showDeleteConversationModal">Delete</b-btn>
          <b-btn variant="primary" @click="publishConversation">Publish</b-btn>
          <b-btn variant="primary" @click="unpublishConversation">Unpublish</b-btn>
        </div>
      </div>
    </div>
    <b-card header="General">
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Name</b-col>
        <b-col cols="10">{{ conversation.name }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Yaml</b-col>
        <b-col cols="10">{{ conversation.yaml_validation_status }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Schema</b-col>
        <b-col cols="10">{{ conversation.yaml_schema_validation_status }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Scenes</b-col>
        <b-col cols="10">{{ conversation.scenes_validation_status }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Model</b-col>
        <b-col cols="10">{{ conversation.model_validation_status }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Created at</b-col>
        <b-col cols="10">{{ conversation.created_at }}</b-col>
      </b-row>
      <b-row>
        <b-col class="font-weight-bold" cols="2">Updated at</b-col>
        <b-col cols="10">{{ conversation.updated_at }}</b-col>
      </b-row>
    </b-card>
    <b-card header="Model">
      <prism language="yaml">{{ conversation.model }}</prism>
    </b-card>
    <b-card header="Notes">
      <b-form-textarea :value="conversation.notes" disabled />
    </b-card>

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
import Prism from 'vue-prismjs';
import 'prismjs/themes/prism.css';

export default {
  name: 'conversation',
  props: ['id'],
  components: {
    Prism,
  },
  data() {
    return {
      conversation: null,
    };
  },
  mounted() {
    axios.get('/admin/api/conversation/' + this.id).then(
      (response) => {
        this.conversation = response.data.data;
      },
    );
  },
  methods: {
    editConversation() {
      this.$router.push({ name: 'edit-conversation', params: { id: this.conversation.id } });
    },
    showDeleteConversationModal() {
      $('#deleteConversationModal').modal();
    },
    deleteConversation() {
      $('#deleteConversationModal').modal('hide');

      axios.delete('/admin/api/conversation/' + this.conversation.id);

      this.$router.push({ name: 'conversations' });
    },
    publishConversation() {
      axios.get('/admin/api/conversation/' + this.conversation.id + '/publish');
    },
    unpublishConversation() {
      axios.get('/admin/api/conversation/' + this.conversation.id + '/unpublish');
    },
  },
};
</script>
