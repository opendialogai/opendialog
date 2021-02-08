<template>
  <div>
    <h2 class="mb-3">Dynamic Attributes</h2>

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
          <input ref="file" type="file" hidden @change="importDynamicAttributes"/>

          <b-btn class="ml-3 mr-1" variant="info" @click="downloadDynamicAttributes">Download</b-btn>
          <b-btn v-if="!importingDynamicAttributes" variant="info" @click="uploadDynamicAttributes">Upload</b-btn>
          <b-btn v-if="importingDynamicAttributes" variant="primary">
            <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
            Uploading ...
          </b-btn>
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
        <tbody v-if="dynamicAttributes && availableAttributeTypes">
          <tr v-for="dynamicAttribute in dynamicAttributes">
            <td>
              {{ dynamicAttribute.id }}
            </td>
            <td>
              {{ dynamicAttribute.attribute_id }}
            </td>
            <td>
              {{ attributeTypeName(dynamicAttribute.attribute_type) }}
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

    <div class="modal modal-danger fade" id="deleteDynamicAttributeModal" role="dialog" aria-hidden="true">
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
      dynamicAttributes: null,
      availableAttributeTypes: null,
      currentDynamicAttribute: null,
      importingDynamicAttributes: false,
      successMessage: '',
      errorMessage: ''
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
    this.fetchDynamicAttributeTypes();
  },
  methods: {
    attributeTypeName(attributeTypeId) {
      const found = this.availableAttributeTypes.find(type => type.component_data.id === attributeTypeId);
      return found.component_data.name || attributeTypeId;
    },
    fetchDynamicAttributeTypes() {
      axios.get('/reflection/all').then(response => {
        this.availableAttributeTypes =  Object.values(response.data.attribute_engine.available_attribute_types)
      })
    },
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
      $('#deleteDynamicAttributeModal').modal();
    },
    deleteDynamicAttribute() {
      $('#deleteDynamicAttributeModal').modal('hide');

      this.dynamicAttributes = this.dynamicAttributes.filter(attr => attr.id !== this.currentDynamicAttribute);
      axios.delete('/admin/api/dynamic-attribute/' + this.currentDynamicAttribute);
    },
    downloadDynamicAttributes() {
      axios.get('/admin/api/dynamic-attributes/download', { responseType: 'blob' }).then(
        (response) => {
          const url = window.URL.createObjectURL(response.data);
          const link = document.createElement('a');
          link.href = url;
          link.setAttribute('download', 'custom-attributes.json');
          document.body.appendChild(link);
          link.click();
        },
      );
    },
    uploadDynamicAttributes() {
      this.$refs.file.click();
    },
    importDynamicAttributes(event) {
      this.errorMessage = '';
      this.successMessage = '';
      this.importingDynamicAttributes = true;

      const file = event.target.files[0];
      const reader = new FileReader();
      reader.onloadend = (ev) => {
        axios.post('/admin/api/dynamic-attributes/upload', reader.result, {
          headers: {
            'Content-Type': 'application/json',
          },
        }).then((response) => {
          if (response.status === 201) {
            this.successMessage = 'Dynamic Attributes updated.';
            this.fetchDynamicAttributes();
          } else {
            this.errorMessage = 'Sorry, I wasn\'t able to update the dynamic attributes.';
          }

          this.$refs.file.value = null;
          this.importingDynamicAttributes = false;
        }).catch(e => {
          if (e.response.data) {
            this.errorMessage = e.response.data.message;
          } else {
            this.errorMessage = 'Sorry, I wasn\'t able to import the dynamic attributes file.';
          }

          this.$refs.file.value = null;
          this.importingDynamicAttributes = false;
        });
      }
      reader.readAsText(file);
    },
  },
};
</script>

<style lang="scss" scoped>
table td.actions {
  min-width: 160px;
}
</style>
