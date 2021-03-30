<template>
  <div v-if="dynamicAttribute && availableAttributeTypes">
    <h2 class="mb-3">Dynamic attribute</h2>

    <div class="alert alert-danger" role="alert" v-if="error.message">
      <span>{{ error.message }}</span>
      <button type="button" class="close" @click="error.message = ''">
        <span>&times;</span>
      </button>
    </div>

    <b-card header="Edit Dynamic-attribute">
      <b-form-group>
        <label>Attribute id</label>
        <b-form-input type="text" v-model="dynamicAttribute.attribute_id" :class="(error.field == 'attribute_id') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-form-group>
        <label>Attribute type</label>
        <b-form-select v-model="dynamicAttribute.attribute_type" :options="attributeTypeOptions(availableAttributeTypes)" :class="(error.field == 'attribute_type') ? 'is-invalid' : ''"></b-form-select>
      </b-form-group>

      <b-btn variant="primary" @click="saveDynamicAttribute">Save</b-btn>
    </b-card>
  </div>
</template>

<script>
export default {
  name: 'edit-dynamic-attribute',
  props: ['id'],
  data() {
    return {
      dynamicAttribute: null,
      availableAttributeTypes: null,
      error: {},
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
    attributeTypeOptions(attributeTypes) {
    return attributeTypes.map(attributeType => ({
        value: attributeType.component_data.id,
        text: attributeType.component_data.name || attributeType.component_data.id
      }))
    },
    saveDynamicAttribute() {
      this.error = {};

      const data = {
        attribute_id: this.dynamicAttribute.attribute_id,
        attribute_type: this.dynamicAttribute.attribute_type,
      };

      axios.patch('/admin/api/dynamic-attribute/' + this.id, data).then(
        (response) => {
          this.$router.push({ name: 'view-dynamic-attribute', params: { id: this.dynamicAttribute.id } });
        },
      ).catch(
        (error) => {
          if (error.response.status === 400) {
            this.error = error.response.data;
          }
        },
      );
    },
  },
};
</script>
