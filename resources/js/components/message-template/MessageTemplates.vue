<template>
  <div>
    <table class="table table-striped">
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

    <nav aria-label="navigation">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="(currentPage == 1) ? 'disabled' : ''">
          <a class="page-link" href="#">Previous</a>
        </li>

        <li class="page-item" v-for="pageNumber in totalPages">
          <a class="page-link" :href="'?page=' + pageNumber">{{ pageNumber }}</a>
        </li>

        <li class="page-item" :class="(currentPage == totalPages) ? 'disabled' : ''">
          <a class="page-link" href="#">Next</a>
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
export default {
  name: 'message-templates',
  props: ['outgoingIntent'],
  data() {
    return {
      messageTemplates: [],
      currentMessageTemplate: null,
      currentPage: 1,
      totalPages: 1,
    };
  },
  mounted() {
    const urlParams = new URLSearchParams(window.location.search);
    this.currentPage = (urlParams.get('page')) ? urlParams.get('page') : 1;

    axios.get('/admin/api/outgoing-intents/' + this.outgoingIntent + '/message-templates?page=' + this.currentPage).then(
      (response) => {
        this.totalPages = response.data.meta.last_page;
        this.messageTemplates = response.data.data;
      },
    );
  },
  methods: {
    viewMessageTemplate(id) {
      this.$router.push({ name: 'view-message-template', params: { outgoingIntent, id } });
    },
    editMessageTemplate(id) {
      this.$router.push({ name: 'edit-message-template', params: { outgoingIntent, id } });
    },
    showDeleteMessageTemplateModal(id) {
      this.currentMessageTemplate = id;
      $('#deleteMessageTemplateModal').modal();
    },
    deleteMessageTemplate() {
      $('#deleteMessageTemplateModal').modal('hide');

      this.messageTemplates = this.messageTemplates.filter(obj => obj.id !== this.currentMessageTemplate);

      axios.delete('/admin/api/outgoing-intents/' + this.outgoingIntent + '/message-templates/' + this.currentMessageTemplate);
    },
  },
};
</script>
