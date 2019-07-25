<template>
  <div v-if="conversation">
    <h2 class="mb-3">Conversation</h2>

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
          <b-btn variant="primary" @click="editConversation">Edit</b-btn>
          <b-btn variant="danger mr-4" @click="showDeleteConversationModal">Delete</b-btn>

          <template v-if="conversation.status == 'published'">
            <b-btn variant="primary" @click="publishConversation" disabled>Publish</b-btn>
            <b-btn variant="primary" @click="unpublishConversation">Unpublish</b-btn>
          </template>
          <template v-else>
            <b-btn variant="primary" @click="publishConversation">Publish</b-btn>
            <b-btn variant="primary" @click="unpublishConversation" disabled>Unpublish</b-btn>
          </template>
        </div>
      </div>
    </div>
    <b-card header="General">
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Name</b-col>
        <b-col cols="10">{{ conversation.name }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Status</b-col>
        <b-col cols="10">{{ conversation.status }}</b-col>
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
        <b-col class="font-weight-bold" cols="2">Opening Intent</b-col>
        <b-col cols="10">{{ conversation.opening_intent }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Outgoing Intents</b-col>
        <b-col cols="10">
          <span v-for="(outgoing_intent, index) in conversation.outgoing_intents">
            <template v-if="outgoing_intent.id">
              <router-link :to="{ name: 'view-outgoing-intent', params: { id: outgoing_intent.id } }">{{ outgoing_intent.name }}</router-link><span v-if="index < (conversation.outgoing_intents.length - 1)">, </span>
            </template>
            <template v-else>
              <router-link :to="{ name: 'add-outgoing-intent', query: { name: outgoing_intent.name } }">{{ outgoing_intent.name }}</router-link><span v-if="index < (conversation.outgoing_intents.length - 1)">, </span>
            </template>
          </span>
        </b-col>
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
      errorMessage: '',
      successMessage: '',
    };
  },
  watch: {
    '$route' () {
      this.fetchConversation();
    },
  },
  mounted() {
    this.fetchConversation();
  },
  methods: {
    fetchConversation() {
      this.conversation = null;

      axios.get('/admin/api/conversation/' + this.id).then(
        (response) => {
          this.conversation = response.data.data;
        },
      );
    },
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
      this.errorMessage = '';
      this.successMessage = '';

      axios.get('/admin/api/conversation/' + this.conversation.id + '/publish').then(
        (response) => {
          if (response.data) {
            this.successMessage = 'Conversation published.';
            this.conversation.status = 'published';
          } else {
            this.errorMessage = 'Sorry, I wasn\'t able to publish this conversation to DGraph.';
          }
        },
      );
    },
    unpublishConversation() {
      this.errorMessage = '';
      this.successMessage = '';

      axios.get('/admin/api/conversation/' + this.conversation.id + '/unpublish').then(
        (response) => {
          if (response.data) {
            this.successMessage = 'Conversation unpublished.';
            this.conversation.status = 'validated';
          } else {
            this.errorMessage = 'Sorry, I wasn\'t able to unpublish this conversation from DGraph.';
          }
        },
      );
    },
  },
};
</script>
