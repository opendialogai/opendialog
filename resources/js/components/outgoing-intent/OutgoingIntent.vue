<template>
  <div v-if="outgoingIntent">
    <h2 class="mb-3">Outgoing Intent</h2>

    <div class="row mb-4">
      <div class="col-12">
        <div class="float-right">
          <b-btn variant="primary mr-4" @click="createMessageTemplate">Create Message Template</b-btn>
          <b-btn variant="primary" @click="editOutgoingIntent">Edit Outgoing Intent Name</b-btn>
          <b-btn variant="danger" @click="showDeleteOutgoingIntentModal">Delete</b-btn>
        </div>
      </div>
    </div>

    <b-card header="General">
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">ID</b-col>
        <b-col cols="10">{{ outgoingIntent.id }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Name</b-col>
        <b-col cols="10">{{ outgoingIntent.name }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Created at</b-col>
        <b-col cols="10">{{ outgoingIntent.created_at }}</b-col>
      </b-row>
      <b-row>
        <b-col class="font-weight-bold" cols="2">Updated at</b-col>
        <b-col cols="10">{{ outgoingIntent.updated_at }}</b-col>
      </b-row>
    </b-card>

    <b-card header="Message Templates">
      <div class="overflow-auto">
        <table class="table table-hover">
          <thead class="thead-light">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Name</th>
              <th scope="col">Message Preview</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="messageTemplate in messageTemplates">
              <td>
                {{ messageTemplate.id }}
              </td>
              <td>
                {{ messageTemplate.name }}
              </td>
              <td>
                <MessageBuilder :message="messageTemplate" />
              </td>
              <td class="actions">
                <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="View" @click.stop="viewMessageTemplate(messageTemplate.id)">
                  <i class="fa fa-eye"></i>
                </button>
                <button class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Edit" @click.stop="editMessageTemplate(messageTemplate.id)">
                  <i class="fa fa-edit"></i>
                </button>
                <button class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Delete" @click.stop="showDeleteMessageTemplateModal(messageTemplate.id)">
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
            <router-link class="page-link" :to="{ name: 'view-outgoing-intent', params: { id }, query: { page: currentPage - 1 } }">Previous</router-link>
          </li>

          <li class="page-item" :class="(pageNumber == currentPage) ? 'active' : ''" v-for="pageNumber in totalPages">
            <template v-if="showPageNumber(pageNumber)">
              <router-link class="page-link" :to="{ name: 'view-outgoing-intent', params: { id }, query: { page: pageNumber } }">{{ pageNumber }}</router-link>
            </template>
            <template v-if="showPageEllipsis(pageNumber)">
              <span class="page-link">...</span>
            </template>
          </li>

          <li class="page-item" :class="(currentPage == totalPages) ? 'disabled' : ''">
            <router-link class="page-link" :to="{ name: 'view-outgoing-intent', params: { id }, query: { page: currentPage + 1 } }">Next</router-link>
          </li>
        </ul>
      </nav>
    </b-card>

    <div class="modal modal-danger fade" id="deleteOutgoingIntentModal" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Delete Outgoing Intent</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete this outgoing intent?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
            <button type="button" class="btn btn-danger" @click="deleteOutgoingIntent">Yes</button>
          </div>
        </div>
      </div>
    </div>

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
import MessageBuilder from '@/components/message-template/MessageBuilder';
import Pager from '@/mixins/Pager';

export default {
  name: 'outgoing-intent',
  mixins: [Pager],
  props: ['id'],
  components: {
    MessageBuilder,
  },
  data() {
    return {
      outgoingIntent: null,
      messageTemplates: [],
      currentMessageTemplate: null,
    };
  },
  mounted() {
    const urlParams = new URLSearchParams(window.location.search);
    this.currentPage = (urlParams.get('page')) ? urlParams.get('page') : 1;

    axios.get('/admin/api/outgoing-intents/' + this.id).then(
      (response) => {
        this.outgoingIntent = response.data.data;
      },
    );

    axios.get('/admin/api/outgoing-intents/' + this.id + '/message-templates?page=' + this.currentPage).then(
      (response) => {
        this.totalPages = parseInt(response.data.meta.last_page);
        this.messageTemplates = response.data.data;
      },
    );
  },
  methods: {
    editOutgoingIntent() {
      this.$router.push({ name: 'edit-outgoing-intent', params: { id: this.outgoingIntent.id } });
    },
    showDeleteOutgoingIntentModal() {
      $('#deleteOutgoingIntentModal').modal();
    },
    deleteOutgoingIntent() {
      $('#deleteOutgoingIntentModal').modal('hide');

      axios.delete('/admin/api/outgoing-intents/' + this.outgoingIntent.id);

      this.$router.push({ name: 'outgoing-intents', params: { outgoingIntent: this.outgoingIntent } });
    },
    createMessageTemplate() {
      this.$router.push({ name: 'add-message-template', params: { outgoingIntent: this.id } });
    },
    viewMessageTemplate(id) {
      this.$router.push({ name: 'view-message-template', params: { outgoingIntent: this.id, id } });
    },
    editMessageTemplate(id) {
      this.$router.push({ name: 'edit-message-template', params: { outgoingIntent: this.id, id } });
    },
    showDeleteMessageTemplateModal(id) {
      this.currentMessageTemplate = id;
      $('#deleteMessageTemplateModal').modal();
    },
    deleteMessageTemplate() {
      $('#deleteMessageTemplateModal').modal('hide');

      this.messageTemplates = this.messageTemplates.filter(obj => obj.id !== this.currentMessageTemplate);

      axios.delete('/admin/api/outgoing-intents/' + this.id);
    },
  },
};
</script>

<style lang="scss">
.table {
  tr:hover {
    .message {
      .text-message,
      .button-message,
      .image-message,
      .rich-message {
        background: #fff;
      }
    }
  }
  td.actions {
    min-width: 160px;
  }
}
</style>
