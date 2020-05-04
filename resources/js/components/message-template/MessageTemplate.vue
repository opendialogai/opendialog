<template>
  <div v-if="messageTemplate">
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

    <h2 class="mb-3">Message Template</h2>

    <div class="row mb-4">
      <div class="col-12">
        <div class="float-right">
          <b-btn variant="primary" @click="editMessageTemplate">Edit</b-btn>
          <b-btn variant="danger" @click="showDeleteMessageTemplateModal">Delete</b-btn>
        </div>
      </div>
    </div>

    <b-card header="General">
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Name</b-col>
        <b-col cols="10">{{ messageTemplate.name }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Created at</b-col>
        <b-col cols="10">{{ messageTemplate.created_at }}</b-col>
      </b-row>
      <b-row>
        <b-col class="font-weight-bold" cols="2">Updated at</b-col>
        <b-col cols="10">{{ messageTemplate.updated_at }}</b-col>
      </b-row>
    </b-card>

    <b-card header="Conditions">
      <prism language="yaml">{{ messageTemplate.conditions }}</prism>
    </b-card>

    <b-card header="Message Mark-up">
      <MessageBuilder :message="messageTemplate" />
    </b-card>

    <h4 class="mb-3">History</h4>
    <b-card class="history overflow-auto">
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Version</b-col>
        <b-col class="font-weight-bold" cols="4">Date</b-col>
        <b-col class="font-weight-bold" cols="6">Actions</b-col>
      </b-row>
      <b-row v-for="history_item in messageTemplate.history" v-bind:key="history_item.id" class="border-bottom mb-2 pb-2">
        <b-col cols="2">{{ history_item.attributes.version_number }}</b-col>
        <b-col cols="4">{{ history_item.timestamp | date }}</b-col>
        <b-col cols="6">
          <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="View" @click.stop="showViewMessageTemplateModel(history_item)">
            View
          </button>

          <button class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Edit" @click.stop="showEditMessageTemplateModel(history_item.id)">
            Edit
          </button>
        </b-col>
      </b-row>
    </b-card>

    <div class="modal modal-danger fade" id="deleteMessageTemplateModal" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Delete Message Template</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete this message template?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
            <button type="button" class="btn btn-danger" @click="deleteMessageTemplate">Yes</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal modal-primary fade" id="editMessageTemplateModal" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title">Restore Previous Message Template Version</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <p>Are you sure you want to restore this version?</p>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                  <button type="button" class="btn btn-primary" @click="editPreviousVersion">Yes</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal modal-primary fade" id="viewMessageTemplateModal" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Viewing Previous Message Template Version</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <h4 class="mb-2">Conditions</h4>
            <prism language="yaml" :code="currentHistoryConditions"></prism>

            <h4 class="mt-3 mb-2">Message Mark-up</h4>
            <MessageBuilder :message="currentHistory" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Prism from 'vue-prismjs';
import 'prismjs/themes/prism.css';

import MessageBuilder from './MessageBuilder';

const moment = require('moment');

export default {
  name: 'message-template',
  props: ['outgoingIntent', 'id'],
  components: {
    MessageBuilder,
    Prism,
  },
  data() {
    return {
      messageTemplate: null,
      errorMessage: '',
      successMessage: '',
      currentHistoryConditions: null,
      currentHistory: '',
      currentHistoryId: null,
    };
  },
  mounted() {
    this.fetchMessageTemplate();
    axios.get('/admin/api/outgoing-intents/' + this.outgoingIntent + '/message-templates/' + this.id).then(
      (response) => {
        this.messageTemplate = response.data.data;
      },
    );
  },
  filters: {
    date: (value) => {
      if (value) {
        return moment(value).format('MMMM D, YYYY HH:mm');
      }
    },
  },
  methods: {
    fetchMessageTemplate() {
      this.messageTemplate = null;

      axios.get('/admin/api/outgoing-intents/' + this.outgoingIntent + '/message-templates/' + this.id).then(
        (response) => {
          this.messageTemplate = response.data.data;
        },
      );
    },
    editMessageTemplate() {
      this.$router.push({ name: 'edit-message-template', params: { outgoingIntent: this.outgoingIntent, id: this.messageTemplate.id } });
    },
    showDeleteMessageTemplateModal() {
      $('#deleteMessageTemplateModal').modal();
    },
    deleteMessageTemplate() {
      $('#deleteMessageTemplateModal').modal('hide');

      axios.delete('/admin/api/outgoing-intents/' + this.outgoingIntent + '/message-templates/' + this.messageTemplate.id);

      this.$router.push({ name: 'message-templates', params: { outgoingIntent: this.outgoingIntent } });
    },
    showViewMessageTemplateModel(history_item) {
      this.currentHistoryConditions = history_item.conditions;
      this.currentHistory = history_item.attributes;
      $('#viewMessageTemplateModal').modal();
    },
    showEditMessageTemplateModel(id) {
      this.currentHistoryId = id;
      $('#editMessageTemplateModal').modal();
    },
    editPreviousVersion() {
      this.errorMessage = '';
      this.successMessage = '';
      $('#editMessageTemplateModal').modal('hide');

      axios.get('/admin/api/message-templates/' + this.messageTemplate.id + '/restore/' + this.currentHistoryId).then(
        (response) => {
            this.successMessage = 'Message template restored.';
            this.fetchMessageTemplate();
        }
      ).catch(() => this.errorMessage = 'Sorry, I wasn\'t able to restore this message template version.');
    },
  },
};
</script>
