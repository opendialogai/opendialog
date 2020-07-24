<template>
  <div v-if="messageTemplate">
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
  </div>
</template>

<script>
import Prism from 'vue-prismjs';
import 'prismjs/themes/prism.css';

import MessageBuilder from './MessageBuilder';

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
    };
  },
  mounted() {
    axios.get('/admin/api/outgoing-intent/' + this.outgoingIntent + '/message-templates/' + this.id).then(
      (response) => {
        this.messageTemplate = response.data.data;
      },
    );
  },
  methods: {
    editMessageTemplate() {
      this.$router.push({ name: 'edit-message-template', params: { outgoingIntent: this.outgoingIntent, id: this.messageTemplate.id } });
    },
    showDeleteMessageTemplateModal() {
      $('#deleteMessageTemplateModal').modal();
    },
    deleteMessageTemplate() {
      $('#deleteMessageTemplateModal').modal('hide');

      axios.delete('/admin/api/outgoing-intent/' + this.outgoingIntent + '/message-templates/' + this.messageTemplate.id);

      this.$router.push({ name: 'message-templates', params: { outgoingIntent: this.outgoingIntent } });
    },
  },
};
</script>
