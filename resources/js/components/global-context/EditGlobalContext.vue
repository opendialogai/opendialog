<template>
  <div v-if="globalContext">
    <h2 class="mb-3">Global Context</h2>

    <div class="alert alert-danger" role="alert" v-if="error.message">
      <span>{{ error.message }}</span>
      <button type="button" class="close" @click="error.message = ''">
        <span>&times;</span>
      </button>
    </div>

    <b-card header="Edit Global Context">
      <b-form-group>
        <label>Name</label>
        <b-form-input type="text" v-model="globalContext.name" :class="(error.field == 'name') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-form-group>
        <label>Value</label>
        <b-form-input type="text" v-model="globalContext.value" :class="(error.field == 'value') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-btn variant="primary" @click="saveGlobalContext">Save</b-btn>
    </b-card>
  </div>
</template>

<script>
export default {
  name: 'edit-global-context',
  props: ['id'],
  data() {
    return {
      globalContext: null,
      error: {},
    };
  },
  mounted() {
    axios.get('/admin/api/global-context/' + this.id).then(
      (response) => {
        this.globalContext = response.data.data;
      },
    );
  },
  methods: {
    saveGlobalContext() {
      this.error = {};

      const data = {
        name: this.globalContext.name,
        value: this.globalContext.value,
      };

      axios.patch('/admin/api/global-context/' + this.id, data).then(
        (response) => {
          this.$router.push({ name: 'view-global-context', params: { id: this.globalContext.id } });
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
