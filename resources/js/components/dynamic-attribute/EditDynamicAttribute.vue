<template>
  <div v-if="dynamicAttribute">
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
        <b-form-input type="email" v-model="dynamicAttribute.attribute_type" :class="(error.field == 'attribute_type') ? 'is-invalid' : ''" />
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
      error: {},
    };
  },
  computed: {
  },
  mounted() {
    axios.get('/admin/api/dynamic-attribute/' + this.id).then(
      (response) => {
        this.dynamicAttribute = response.data.data;
      },
    );
  },
  methods: {
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
