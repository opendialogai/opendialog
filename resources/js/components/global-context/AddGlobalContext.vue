<template>
  <div>
    <h2 class="mb-3">Global Context</h2>

    <div class="alert alert-danger" role="alert" v-if="error.message">
      <span>{{ error.message }}</span>
      <button type="button" class="close" @click="error.message = ''">
        <span>&times;</span>
      </button>
    </div>

    <b-card header="Add Global Context">
      <b-form-group>
        <label>Name</label>
        <b-form-input type="text" v-model="name" :class="(error.field == 'name') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-form-group>
        <label>Value</label>
        <b-form-input type="text" v-model="value" :class="(error.field == 'value') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-btn variant="primary" @click="addGlobalContext">Create</b-btn>
    </b-card>
  </div>
</template>

<script>
export default {
  name: 'add-global-context',
  data() {
    return {
      name: '',
      value: '',
      error: {},
    };
  },
  methods: {
    addGlobalContext() {
      const data = {
        name: this.name,
        value: this.value,
      };

      axios.post('/admin/api/global-context', data).then(
        (response) => {
          this.$router.push({ name: 'view-global-context', params: { id: response.data.data.id } });
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
