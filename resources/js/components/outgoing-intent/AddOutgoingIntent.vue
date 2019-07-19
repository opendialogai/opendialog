<template>
  <div>
    <div class="alert alert-danger" role="alert" v-if="errorMessage">
      <span>{{ errorMessage }}</span>
      <button type="button" class="close" @click="errorMessage = ''">
        <span>&times;</span>
      </button>
    </div>

    <b-card header="Add Outgoing Intent">
      <b-form-group>
        <label>Name</label>
        <b-form-input type="text" v-model="name" />
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
      errorMessage: '',
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

      axios.post('/admin/api/outgoing-intents', data).then(
        (response) => {
          this.$router.push({ name: 'view-message-template', params: { id: response.data.data.id } });
        },
      ).catch(
        (error) => {
          if (error.response.status === 400) {
            this.errorMessage = error.response.data;
          }
        },
      );
    },
  },
};
</script>
