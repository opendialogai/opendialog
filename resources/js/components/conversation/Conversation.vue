<template>
  <div v-if="conversation">
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
      <div class="col-md-6">
        <h2>{{ conversation.name | capitalize }} Conversation</h2>
        <div class="conversation-status" :class="getStatusClass(conversation.status)">{{ conversation.status | capitalize }}</div>
        <div>(last modified - {{ conversation.updated_at }})</div>
      </div>
      <div class="col-md-6">
        <div class="float-right">
          <b-btn v-if="conversation.status != 'archived'" variant="primary mr-4" @click="editConversation">Edit</b-btn>
          <template v-else>
            <b-btn variant="danger mr-4" @click="showDeleteConversationModal">Delete</b-btn>
            <b-btn variant="primary" @click="unarchiveConversation">Unarchive</b-btn>
          </template>

          <template v-if="conversation.status == 'activated'">
            <b-btn variant="primary" @click="deactivateConversation">Deactivate</b-btn>
          </template>
          <template v-else-if="['activatable', 'deactivated'].includes(conversation.status)">
            <b-btn v-if="conversation.status == 'deactivated'" variant="danger mr-4" @click="showArchiveConversationModal">Archive</b-btn>
            <b-btn variant="success" @click="activateConversation">Activate</b-btn>
          </template>
        </div>
      </div>
    </div>

    <div class="mb-4">
      <h4 class="mb-3">Conversation Notes</h4>
      <b-form-textarea :value="conversation.notes" disabled />
    </div>

    <div class="mb-4">
      <h4 class="mb-3">Model Version {{ conversation.version_number }}</h4>
      <b-card header="Model">
        <prism language="yaml">{{ conversation.model }}</prism>
      </b-card>
    </div>

    <div class="row">
      <div class="col-md-6">
        <b-card header="Status">
          <b-row>
            <b-col class="font-weight-bold mt-1 mb-1" cols="6" md="2">Yaml</b-col>
            <b-col class="mt-1 mb-1" cols="6" md="4">
              <div class="conversation-status" :class="getStatusClass(conversation.yaml_validation_status)">
                {{ conversation.yaml_validation_status | capitalize }}
              </div>
            </b-col>
            <b-col class="font-weight-bold mt-1 mb-1" cols="6" md="2">Schema</b-col>
            <b-col class="mt-1 mb-1" cols="6" md="4">
              <div class="conversation-status" :class="getStatusClass(conversation.yaml_schema_validation_status)">
                {{ conversation.yaml_schema_validation_status | capitalize }}
              </div>
            </b-col>
          </b-row>
          <b-row>
            <b-col class="font-weight-bold mt-1 mb-1" cols="6" md="2">Scenes</b-col>
            <b-col class="mt-1 mb-1" cols="6" md="4">
              <div class="conversation-status" :class="getStatusClass(conversation.scenes_validation_status)">
                {{ conversation.scenes_validation_status | capitalize }}
              </div>
            </b-col>
            <b-col class="font-weight-bold mt-1 mb-1" cols="6" md="2">Model</b-col>
            <b-col class="mt-1 mb-1" cols="6" md="4">
              <div class="conversation-status" :class="getStatusClass(conversation.model_validation_status)">
                {{ conversation.model_validation_status | capitalize }}
              </div>
            </b-col>
          </b-row>
        </b-card>
      </div>
      <div class="col-md-6">
        <b-card class="intents overflow-auto" header="Intents">
          <b-row>
            <b-col class="font-weight-bold mt-1 mb-1" cols="5" md="3">Opening Intents:</b-col>
            <b-col class="mt-1 mb-1" cols="7" md="9">
                <span v-for="(opening_intent, index) in conversation.opening_intents">
                    {{ opening_intent }}<span v-if="index < (conversation.opening_intents.length - 1)">, </span>
                </span>
            </b-col>
          </b-row>
          <b-row>
            <b-col class="font-weight-bold mt-1 mb-1" cols="5" md="3">Outgoing Intents:</b-col>
            <b-col class="mt-1 mb-1" cols="7" md="9">
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
        </b-card>
      </div>
    </div>

    <h4 class="mb-3">History</h4>
    <b-card class="history overflow-auto">
      <b-row class="border-bottom mb-2 pb-2">
          <b-col class="font-weight-bold" cols="2">Version</b-col>
          <b-col class="font-weight-bold" cols="4">Date</b-col>
          <b-col class="font-weight-bold" cols="6">Actions</b-col>
      </b-row>
      <b-row v-for="history_item in conversation.history" v-bind:key="history_item.id" class="border-bottom mb-2 pb-2">
          <b-col cols="2">{{ history_item.attributes.version_number }}</b-col>
          <b-col cols="4">{{ history_item.timestamp | date }}</b-col>
          <b-col cols="6">
              <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="View" @click.stop="showViewConversationModel(history_item.attributes.model)">
                  View
              </button>

              <button class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Edit" @click.stop="showEditConversationModel(history_item.id)">
                  Edit
              </button>

              <button class="btn btn-primary ml-2" data-toggle="tooltip" data-placement="top" title="Activate" @click.stop="showActivateConversationModel(history_item.id)">
                  Reactivate
              </button>
          </b-col>
      </b-row>
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
              <p v-if="conversation.has_been_used">This conversation has already been used, are you sure you want to delete it rather than keeping it in the archive?</p>
              <p v-else>Are you sure you want to delete this conversation?</p>
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

    <div class="modal modal-primary fade" id="editConversationModal" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title">Restore Previous Conversation Version</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <p>Are you sure you want to restore this version? The conversation model will be updated and automatically set as activatable.</p>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                  <button type="button" class="btn btn-primary" @click="editPreviousVersion">Yes</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal modal-primary fade" id="activateConversationModal" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title">Restore Previous Conversation Version</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <p>Are you sure you want to activate this version?</p>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                  <button type="button" class="btn btn-primary" @click="activatePreviousVersion">Yes</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal modal-primary fade" id="viewConversationModal" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title">Viewing Previous Conversation Version</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <prism language="yaml" :code="currentHistoryModel"></prism>
              </div>
          </div>
      </div>
    </div>
  </div>
</template>

<script>
import Prism from 'vue-prismjs';
import 'prismjs/themes/prism.css';

const moment = require('moment');

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
      currentHistoryModel: null,
      currentHistoryId: null
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
  filters: {
    date: (value) => {
      if (value) {
        return moment(value).format('MMMM D, YYYY HH:mm');
      }
    },
    capitalize: function (value) {
      if (!value) return '';
      value = value.toString();
      return value.charAt(0).toUpperCase() + value.slice(1);
    },
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
    showViewConversationModel(model) {
      this.currentHistoryModel = model;
      $('#viewConversationModal').modal();
    },
    showEditConversationModel(id) {
      this.currentHistoryId = id;
      $('#editConversationModal').modal();
    },
    showActivateConversationModel(id) {
      this.currentHistoryId = id;
      $('#activateConversationModal').modal();
    },
    showArchiveConversationModal() {
      $('#archiveConversationModal').modal();
    },
    activatePreviousVersion() {
      this.errorMessage = '';
      this.successMessage = '';
      $('#activateConversationModal').modal('hide');

      axios.get('/admin/api/conversation/' + this.conversation.id + '/reactivate/' + this.currentHistoryId).then(
        (response) => {
            this.successMessage = 'Conversation reactivated.';
            this.fetchConversation();
        },
      ).catch(() => this.errorMessage = 'Sorry, I wasn\'t able to reactivate this conversation version.');
    },
    editPreviousVersion() {
      this.errorMessage = '';
      this.successMessage = '';
      $('#editConversationModal').modal('hide');

      axios.get('/admin/api/conversation/' + this.conversation.id + '/restore/' + this.currentHistoryId).then(
        (response) => {
            this.successMessage = 'Conversation restored.';
            this.fetchConversation();
        }
      ).catch(() => this.errorMessage = 'Sorry, I wasn\'t able to restore this conversation version.');
    },
    activateConversation() {
      this.errorMessage = '';
      this.successMessage = '';

      axios.get('/admin/api/conversation/' + this.conversation.id + '/activate').then(
        (response) => {
          if (response.data) {
            this.successMessage = 'Conversation activated.';
            this.conversation.status = 'activated';
            this.conversation.version_number++;
            this.fetchConversation();
          } else {
            this.errorMessage = 'Sorry, I wasn\'t able to activate this conversation to DGraph.';
          }
        },
      );
    },
    deactivateConversation() {
      this.errorMessage = '';
      this.successMessage = '';

      axios.get('/admin/api/conversation/' + this.conversation.id + '/deactivate').then(
        (response) => {
          if (response.data) {
            this.successMessage = 'Conversation deactivated.';
            this.conversation.status = 'deactivated';
          } else {
            this.errorMessage = 'Sorry, I wasn\'t able to deactivate this conversation from DGraph.';
          }
        },
      );
    },
    archiveConversation() {
      this.errorMessage = '';
      this.successMessage = '';
      $('#archiveConversationModal').modal('hide');

      axios.get('/admin/api/conversation/' + this.conversation.id + '/archive').then(
        (response) => {
          this.successMessage = 'Conversation archived.';
          this.fetchConversation();
        },
      ).catch(() => this.errorMessage = 'Sorry, I wasn\'t able to archive this conversation version.');
    },
    unarchiveConversation() {
      this.errorMessage = '';
      this.successMessage = '';

      axios.get('/admin/api/conversation/' + this.conversation.id + '/deactivate').then(
        (response) => {
          if (response.data) {
            this.successMessage = 'Conversation unarchived.';
            this.conversation.status = 'deactivated';
            this.fetchConversation();
          } else {
            this.errorMessage = 'Sorry, I wasn\'t able to unarchive this conversation.';
          }
        },
      );
    },
    getStatusClass(status) {
      if (status == 'saved') return 'yellow-status';
      if (status == 'activatable' || status == 'waiting') return 'blue-status';
      if (status == 'activated' || status == 'validated') return 'green-status';
      if (status == 'deactivated' || status == 'invalid') return 'red-status';
      if (status == 'archived') return 'gray-status';
    },
  },
};
</script>

<style lang="scss" scoped>
h2 {
  display: inline-block;
  margin-right: 0.5rem
}
.conversation-status {
  vertical-align: text-bottom;
  border-radius: 10px;
  color: #fff;
  padding: 0 12px;
  display: inline-block;
  &.yellow-status {
    background: var(--yellow);
  }
  &.green-status {
    background: var(--green);
  }
  &.red-status {
    background: var(--red);
  }
  &.blue-status {
    background: var(--blue);
  }
  &.gray-status {
    background: var(--gray);
  }
}
.intents,
.history {
  .card-header,
  .card-body {
    min-width: 520px;
  }
}
</style>
