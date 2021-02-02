<template>
  <div>
    <h2 class="mb-3">Dynamic Attributes</h2>

    <div class="row mb-4">
      <div class="col-12">
        <div class="float-right">
          <b-btn variant="primary" @click="createDynamicAttribute">Create</b-btn>
        </div>
      </div>
    </div>

    <div class="overflow-auto">
      <table class="table table-hover">
        <thead class="thead-light">
          <tr>
            <th scope="col">ID</th>
            <th scope="col">Attribute Name</th>
            <th scope="col">Attribute Type</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="dynamicAttribute in dynamicAttributes">
            <td>
              {{ dynamicAttribute.id }}
            </td>
            <td>
              {{ dynamicAttribute.attribute_id }}
            </td>
            <td>
              {{ dynamicAttribute.attribute_type }}
            </td>
            <td class="actions">
              <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="View" @click.stop="viewDynamicAttribute(dynamicAttribute.id)">
                <i class="fa fa-eye"></i>
              </button>
              <button class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Edit" @click.stop="editDynamicAttribute(dynamicAttribute.id)">
                <i class="fa fa-edit"></i>
              </button>
              <template>
                <button class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Delete" @click.stop="showDeleteDynamicAttributeModal(dynamicAttribute.id)">
                  <i class="fa fa-close"></i>
                </button>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <nav aria-label="navigation">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="(currentPage == 1) ? 'disabled' : ''">
          <router-link class="page-link" :to="{ name: 'dynamic-attributes', query: { page: currentPage - 1 } }">Previous</router-link>
        </li>

        <li class="page-item" :class="(pageNumber == currentPage) ? 'active' : ''" v-for="pageNumber in totalPages">
          <template v-if="showPageNumber(pageNumber)">
            <router-link class="page-link" :to="{ name: 'dynamic-attributes', query: { page: pageNumber } }">{{ pageNumber }}</router-link>
          </template>
          <template v-if="showPageEllipsis(pageNumber)">
            <span class="page-link">...</span>
          </template>
        </li>

        <li class="page-item" :class="(currentPage == totalPages) ? 'disabled' : ''">
          <router-link class="page-link" :to="{ name: 'dynamic-attributes', query: { page: currentPage + 1 } }">Next</router-link>
        </li>
      </ul>
    </nav>

    <div class="modal modal-danger fade" id="deleteDynamicAttributeModel" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Delete dynamic attribute</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete this dynamic attribute?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
            <button type="button" class="btn btn-danger" @click="deleteDynamicAttribute">Yes</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Pager from '@/mixins/Pager';

export default {
  name: 'dynamic-attributes',
  mixins: [Pager],
  data() {
    return {
      dynamicAttributes: [],
      currentDynamicAttribute: null,
    };
  },
  computed: {
  },
  watch: {
    '$route' () {
      this.fetchDynamicAttributes();
    }
  },
  mounted() {
    this.fetchDynamicAttributes();
  },
  methods: {
    fetchDynamicAttributes() {
      this.currentPage = parseInt(this.$route.query.page || 1);

      axios.get('/admin/api/dynamic-attribute?page=' + this.currentPage).then(
        (response) => {
          this.totalPages = parseInt(response.data.meta.last_page);
          this.dynamicAttributes = response.data.data;
        },
      );
    },
    createDynamicAttribute() {
      this.$router.push({ name: 'add-dynamic-attribute' });
    },
    viewDynamicAttribute(id) {
      this.$router.push({ name: 'view-dynamic-attribute', params: { id } });
    },
    editDynamicAttribute(id) {
      this.$router.push({ name: 'edit-dynamic-attribute', params: { id } });
    },
    showDeleteDynamicAttributeModal(id) {
      this.currentDynamicAttribute = id;
      $('#deleteDynamicAttributeModel').modal();
    },
    deleteDynamicAttribute() {
      $('#deleteDynamicAttributeModal').modal('hide');

      this.dynamicAttributes = this.dynamicAttributes.filter(attr => attr.id !== this.currentDynamicAttribute);
      axios.delete('/admin/api/dynamic-attribute/' + this.currentDynamicAttribute);
    },
  },
};
</script>

<style lang="scss" scoped>
table td.actions {
  min-width: 160px;
}
</style>
