<template>
  <div>
    <h2 class="mb-3">Dynamic Attribute</h2>

    <div class="alert alert-danger" role="alert" v-if="error.message">
      <span>{{ error.message }}</span>
      <button type="button" class="close" @click="error.message = ''">
        <span>&times;</span>
      </button>
    </div>

    <b-card header="Add dynamic attribute">
      <b-form-group>
        <label>Attribute ID*</label>
        <b-form-input type="text" v-model="attribute_id" :class="(error.field == 'attribute_id') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-form-group>
        <label>Attribute Type*</label>
        <b-form-input type="email" v-model="attribute_type" :class="(error.field == 'attribute_type') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-btn variant="primary" @click="addDynamicAttribute">Create</b-btn>
    </b-card>
  </div>
</template>

<script>
export default {
  name: 'add-dynamic-attribute',
  data() {
    return {
      attribute_id: '',
      attribute_type: '',
      error: {},
    };
  },
  methods: {
    addDynamicAttribute() {
      const data = {
        attribute_id: this.attribute_id,
        attribute_type: this.attribute_type,
      };

      axios.post('/admin/api/dynamic-attribute', data).then(
        (response) => {
          this.$router.push({ name: 'view-dynamic-attribute', params: { id: response.data.data.id } });
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
