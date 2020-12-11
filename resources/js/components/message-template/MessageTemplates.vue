<template>
  <div>
    <h2 class="mb-3">Message Templates</h2>

    <div class="row mb-4">
      <div class="col-12">
        <div class="float-right">
          <b-btn variant="primary" @click="createMessageTemplate">Create</b-btn>
        </div>
      </div>
    </div>

    <div class="overflow-auto">
      <table class="table table-hover">
        <thead class="thead-light">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Name</th>
            <th scope="col">Created at</th>
            <th scope="col">Updated at</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="messageTemplate in messageTemplates" @click="viewMessageTemplate(messageTemplate.id)">
            <td>
              {{ messageTemplate.id }}
            </td>
            <td>
              {{ messageTemplate.name }}
            </td>
            <td>
              {{ messageTemplate.created_at }}
            </td>
            <td>
              {{ messageTemplate.updated_at }}
            </td>
            <td>
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
          <router-link class="page-link" :to="{ name: 'message-templates', params: { outgoingIntent }, query: { page: currentPage - 1 } }">Previous</router-link>
        </li>

        <li class="page-item" :class="(pageNumber == currentPage) ? 'active' : ''" v-for="pageNumber in totalPages">
          <template v-if="showPageNumber(pageNumber)">
            <router-link class="page-link" :to="{ name: 'message-templates', params: { outgoingIntent }, query: { page: pageNumber } }">{{ pageNumber }}</router-link>
          </template>
          <template v-if="showPageEllipsis(pageNumber)">
            <span class="page-link">...</span>
          </template>
        </li>

        <li class="page-item" :class="(currentPage == totalPages) ? 'disabled' : ''">
          <router-link class="page-link" :to="{ name: 'message-templates', params: { outgoingIntent }, query: { page: currentPage + 1 } }">Next</router-link>
        </li>
      </ul>
    </nav>

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
import Pager from '@/mixins/Pager';

export default {
  name: 'message-templates',
  mixins: [Pager],
  props: ['outgoingIntent'],
  data() {
    return {
      messageTemplates: [],
      currentMessageTemplate: null,
    };
  },
  watch: {
    '$route' () {
      this.fetchMessageTemplates();
    }
  },
  mounted() {
    this.fetchMessageTemplates();
  },
  methods: {
    fetchMessageTemplates() {
      this.currentPage = parseInt(this.$route.query.page || 1);

      axios.get('/admin/api/outgoing-intent/' + this.outgoingIntent + '/message-templates?page=' + this.currentPage).then(
        (response) => {
          this.totalPages = parseInt(response.data.meta.last_page);
          this.messageTemplates = response.data.data;
        },
      );
    },
    createMessageTemplate() {
      this.$router.push({ name: 'add-message-template', params: { outgoingIntent: this.outgoingIntent } });
    },
    viewMessageTemplate(id) {
      this.$router.push({ name: 'view-message-template', params: { outgoingIntent: this.outgoingIntent, id } });
    },
    editMessageTemplate(id) {
      this.$router.push({ name: 'edit-message-template', params: { outgoingIntent: this.outgoingIntent, id } });
    },
    showDeleteMessageTemplateModal(id) {
      this.currentMessageTemplate = id;
      $('#deleteMessageTemplateModal').modal();
    },
    deleteMessageTemplate() {
      $('#deleteMessageTemplateModal').modal('hide');

      this.messageTemplates = this.messageTemplates.filter(obj => obj.id !== this.currentMessageTemplate);

      axios.delete('/admin/api/outgoing-intent/' + this.outgoingIntent + '/message-templates/' + this.currentMessageTemplate);
    },
  },
};
</script>
