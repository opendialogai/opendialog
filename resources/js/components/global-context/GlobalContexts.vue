<template>
  <div>
    <h2 class="mb-3">Global Context</h2>

    <div class="row mb-4">
      <div class="col-12">
        <div class="float-right">
          <b-btn variant="primary" @click="createGlobalContext">Create</b-btn>
        </div>
      </div>
    </div>

    <div class="overflow-auto">
      <table class="table table-hover">
        <thead class="thead-light">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Name</th>
            <th scope="col">Value</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="globalContext in globalContexts">
            <td>
              {{ globalContext.id }}
            </td>
            <td>
              {{ globalContext.name }}
            </td>
            <td>
              {{ globalContext.value }}
            </td>
            <td class="actions">
              <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="View" @click.stop="viewGlobalContext(globalContext.id)">
                <i class="fa fa-eye"></i>
              </button>
              <button class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Edit" @click.stop="editGlobalContext(globalContext.id)">
                <i class="fa fa-edit"></i>
              </button>
              <button class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Delete" @click.stop="showDeleteGlobalContextModal(globalContext.id)">
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
          <router-link class="page-link" :to="{ name: 'global-contexts', query: { page: currentPage - 1 } }">Previous</router-link>
        </li>

        <li class="page-item" :class="(pageNumber == currentPage) ? 'active' : ''" v-for="pageNumber in totalPages">
          <template v-if="showPageNumber(pageNumber)">
            <router-link class="page-link" :to="{ name: 'global-contexts', query: { page: pageNumber } }">{{ pageNumber }}</router-link>
          </template>
          <template v-if="showPageEllipsis(pageNumber)">
            <span class="page-link">...</span>
          </template>
        </li>

        <li class="page-item" :class="(currentPage == totalPages) ? 'disabled' : ''">
          <router-link class="page-link" :to="{ name: 'global-contexts', query: { page: currentPage + 1 } }">Next</router-link>
        </li>
      </ul>
    </nav>

    <div class="modal modal-danger fade" id="deleteGlobalContextModal" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Delete Global Context</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete this global context?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
            <button type="button" class="btn btn-danger" @click="deleteGlobalContext">Yes</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Pager from '@/mixins/Pager';

export default {
  name: 'global-contexts',
  mixins: [Pager],
  data() {
    return {
      errorMessage: '',
      successMessage: '',
      globalContexts: [],
      currentGlobalContext: null,
    };
  },
  watch: {
    '$route' () {
      this.fetchGlobalContexts();
    }
  },
  mounted() {
    this.fetchGlobalContexts();
  },
  methods: {
    fetchGlobalContexts() {
      this.currentPage = parseInt(this.$route.query.page || 1);

      axios.get('/admin/api/global-context?page=' + this.currentPage).then(
        (response) => {
          this.totalPages = parseInt(response.data.meta.last_page);
          this.globalContexts = response.data.data;
        },
      );
    },
    createGlobalContext() {
      this.$router.push({ name: 'add-global-context' });
    },
    viewGlobalContext(id) {
      this.$router.push({ name: 'view-global-context', params: { id } });
    },
    editGlobalContext(id) {
      this.$router.push({ name: 'edit-global-context', params: { id } });
    },
    showDeleteGlobalContextModal(id) {
      this.currentGlobalContext = id;
      $('#deleteGlobalContextModal').modal();
    },
    deleteGlobalContext() {
      $('#deleteGlobalContextModal').modal('hide');

      this.globalContexts = this.globalContexts.filter(obj => obj.id !== this.currentGlobalContext);

      axios.delete('/admin/api/global-context/' + this.currentGlobalContext);
    },
  },
};
</script>

<style lang="scss" scoped>
table td.actions {
  min-width: 160px;
}
</style>
