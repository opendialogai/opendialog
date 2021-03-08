<template>
  <div v-if="dynamicAttribute" class="container-fluid">
    <h2 class="mb-3">Dynamic attribute</h2>

    <div class="row mb-4">
      <div class="col-12">
        <div class="float-right">
          <b-btn variant="primary" @click="editDynamicAttribute">Edit</b-btn>
          <b-btn variant="danger" @click="showDeleteDynamicAttributeModal">Delete</b-btn>
        </div>
      </div>
    </div>
    <b-card header="General">
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">ID</b-col>
        <b-col cols="10">{{ dynamicAttribute.id }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Attribute ID</b-col>
        <b-col cols="10">{{ dynamicAttribute.attribute_id }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Attribute type</b-col>
        <b-col cols="10">{{ attributeTypeName(dynamicAttribute.attribute_type) }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Created at</b-col>
        <b-col cols="10">{{ dynamicAttribute.created_at }}</b-col>
      </b-row>
      <b-row>
        <b-col class="font-weight-bold" cols="2">Updated at</b-col>
        <b-col cols="10">{{ dynamicAttribute.updated_at }}</b-col>
      </b-row>
    </b-card>

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
export default {
  name: 'dynamic-attribute',
  props: ['id'],
  data() {
    return {
      dynamicAttribute: null,
      availableAttributeTypes: null
    };
  },
  computed: {
  },
  mounted() {
    axios.all([axios.get('/admin/api/dynamic-attribute/' + this.id), axios.get('/reflection/all')]).then(([dynamicAttributeResponse, reflectionResponse]) => {
      this.dynamicAttribute = dynamicAttributeResponse.data.data;
      this.availableAttributeTypes = Object.values(reflectionResponse.data.attribute_engine.available_attribute_types);
    })
  },
  methods: {
    attributeTypeName(attributeTypeId) {
      const found = this.availableAttributeTypes.find(type => type.component_data.id === attributeTypeId);
      return found.component_data.name || attributeTypeId;
    },
    editDynamicAttribute() {
      this.$router.push({ name: 'edit-dynamic-attribute', params: { id: this.dynamicAttribute.id } });
    },
    showDeleteDynamicAttributeModal() {
      $('#deleteDynamicAttributeModal').modal();
    },
    deleteDynamicAttribute() {
      $('#deleteDynamicAttributeModal').modal('hide');

      axios.delete('/admin/api/dynamic-attribute/' + this.dynamicAttribute.id);

      this.$router.push({ name: 'dynamic-attributes' });
    },
  },
};
</script>
