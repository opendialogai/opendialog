<template>
  <div>
    <h2 class="mb-3">Outgoing Intents</h2>

    <div class="row mb-4">
      <div class="col-12">
        <div class="float-right">
          <b-btn variant="primary" @click="createOutgoingIntent">Create</b-btn>

          <input ref="file" type="file" hidden multiple @change="importOutgoingIntents"/>

          <b-btn class="ml-3" variant="info" @click="downloadOutgoingIntents">Download All</b-btn>
          <b-btn variant="info" @click="uploadOutgoingIntents">Upload All</b-btn>
        </div>
      </div>
    </div>

    <div class="overflow-auto">
      <table class="table table-hover">
        <thead class="thead-light">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Name</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="outgoingIntent in outgoingIntents">
            <td>
              {{ outgoingIntent.id }}
            </td>
            <td>
              {{ outgoingIntent.name }}
            </td>
            <td class="actions">
              <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="View" @click.stop="viewOutgoingIntent(outgoingIntent.id)">
                <i class="fa fa-eye"></i>
              </button>
              <button class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Delete" @click.stop="showDeleteOutgoingIntentModal(outgoingIntent.id)">
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
          <router-link class="page-link" :to="{ name: 'outgoing-intents', query: { page: currentPage - 1 } }">Previous</router-link>
        </li>

        <li class="page-item" :class="(pageNumber == currentPage) ? 'active' : ''" v-for="pageNumber in totalPages">
          <template v-if="showPageNumber(pageNumber)">
            <router-link class="page-link" :to="{ name: 'outgoing-intents', query: { page: pageNumber } }">{{ pageNumber }}</router-link>
          </template>
          <template v-if="showPageEllipsis(pageNumber)">
            <span class="page-link">...</span>
          </template>
        </li>

        <li class="page-item" :class="(currentPage == totalPages) ? 'disabled' : ''">
          <router-link class="page-link" :to="{ name: 'outgoing-intents', query: { page: currentPage + 1 } }">Next</router-link>
        </li>
      </ul>
    </nav>

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
  </div>
</template>

<script>
import Pager from '@/mixins/Pager';

export default {
  name: 'outgoing-intents',
  mixins: [Pager],
  data() {
    return {
      outgoingIntents: [],
      currentOutgoingIntent: null,
    };
  },
  watch: {
    '$route' () {
      this.fetchOutgoingIntents();
    }
  },
  mounted() {
    this.fetchOutgoingIntents();
  },
  methods: {
    fetchOutgoingIntents() {
      this.currentPage = parseInt(this.$route.query.page || 1);

      axios.get('/admin/api/outgoing-intent?page=' + this.currentPage).then(
        (response) => {
          this.totalPages = parseInt(response.data.meta.last_page);
          this.outgoingIntents = response.data.data;
        },
      );
    },
    createOutgoingIntent() {
      this.$router.push({ name: 'add-outgoing-intent' });
    },
    viewOutgoingIntent(id) {
      this.$router.push({ name: 'view-outgoing-intent', params: { id } });
    },
    editOutgoingIntent(id) {
      this.$router.push({ name: 'edit-outgoing-intent', params: { id } });
    },
    showDeleteOutgoingIntentModal(id) {
      this.currentOutgoingIntent = id;
      $('#deleteOutgoingIntentModal').modal();
    },
    deleteOutgoingIntent() {
      $('#deleteOutgoingIntentModal').modal('hide');

      this.outgoingIntents = this.outgoingIntents.filter(obj => obj.id !== this.currentOutgoingIntent);

      axios.delete('/admin/api/outgoing-intent/' + this.currentOutgoingIntent);
    },
    downloadOutgoingIntents() {
      axios.get('/admin/api/outgoing-intents/export', { responseType: 'blob' }).then(
        (response) => {
          const url = window.URL.createObjectURL(response.data);
          const link = document.createElement('a');
          link.href = url;
          link.setAttribute('download', 'outgoing-intents.zip');
          document.body.appendChild(link);
          link.click();
        },
      );
    },
    uploadOutgoingIntents() {
      this.$refs.file.click();
    },
    importOutgoingIntents(event) {
      this.errorMessage = '';
      this.successMessage = '';

      const formData = new FormData();

      event.target.files.forEach((file, i) => {
        formData.append('file' + (i + 1), file);
      });

      axios.post('/admin/api/outgoing-intents/import', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      }).then((response) => {
        if (response.status == 200) {
          this.successMessage = 'Outgoing Intents updated.';
          this.fetchOutgoingIntents();
        } else {
          this.errorMessage = 'Sorry, I wasn\'t able to update this outgoing intents.';
        }
      });
    },
  },
};
</script>

<style lang="scss" scoped>
table td.actions {
  min-width: 160px;
}
</style>
