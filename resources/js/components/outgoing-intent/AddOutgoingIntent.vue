<template>
  <div>
    <h2 class="mb-3">Outgoing Intent</h2>

    <div class="alert alert-danger" role="alert" v-if="error.message">
      <span>{{ error.message }}</span>
      <button type="button" class="close" @click="error.message = ''">
        <span>&times;</span>
      </button>
    </div>

    <b-card header="Add Outgoing Intent">
      <b-form-group>
        <label>Name</label>
        <b-form-input type="text" v-model="name" :class="(error.field == 'name') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-btn variant="primary" @click="addOutgoingIntent">Create</b-btn>
    </b-card>
  </div>
</template>

<script>
export default {
  name: 'add-outgoing-intent',
  data() {
    return {
      name: '',
      error: {},
    };
  },
  mounted() {
    this.name = this.$route.query.name || '';
  },
  methods: {
    addOutgoingIntent() {
      const data = {
        name: this.name,
      };

      axios.post('/admin/api/outgoing-intent', data).then(
        (response) => {
          this.$router.push({ name: 'view-outgoing-intent', params: { id: response.data.data.id } });
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
