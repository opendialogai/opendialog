<template>
  <div v-if="globalContext">
    <h2 class="mb-3">Global Context</h2>

    <div class="row mb-4">
      <div class="col-12">
        <div class="float-right">
          <b-btn variant="primary" @click="editGlobalContext">Edit</b-btn>
          <b-btn variant="danger" @click="showDeleteGlobalContextModal">Delete</b-btn>
        </div>
      </div>
    </div>
    <b-card header="General">
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Name</b-col>
        <b-col cols="10">{{ globalContext.name }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Value</b-col>
        <b-col cols="10">{{ globalContext.value }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Created at</b-col>
        <b-col cols="10">{{ globalContext.created_at }}</b-col>
      </b-row>
      <b-row>
        <b-col class="font-weight-bold" cols="2">Updated at</b-col>
        <b-col cols="10">{{ globalContext.updated_at }}</b-col>
      </b-row>
    </b-card>

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
export default {
  name: 'global-context',
  props: ['id'],
  data() {
    return {
      globalContext: null,
    };
  },
  watch: {
    '$route' () {
      this.fetchGlobalContext();
    },
  },
  mounted() {
    this.fetchGlobalContext();
  },
  methods: {
    fetchGlobalContext() {
      this.globalContext = null;

      axios.get('/admin/api/global-context/' + this.id).then(
        (response) => {
          this.globalContext = response.data.data;
        },
      );
    },
    editGlobalContext() {
      this.$router.push({ name: 'edit-global-context', params: { id: this.globalContext.id } });
    },
    showDeleteGlobalContextModal() {
      $('#deleteGlobalContextModal').modal();
    },
    deleteGlobalContext() {
      $('#deleteGlobalContextModal').modal('hide');

      axios.delete('/admin/api/global-context/' + this.globalContext.id);

      this.$router.push({ name: 'global-contexts' });
    },
  },
};
</script>
