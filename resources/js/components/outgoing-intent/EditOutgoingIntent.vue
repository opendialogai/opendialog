<template>
  <div v-if="outgoingIntent">
    <div class="alert alert-danger" role="alert" v-if="errorMessage">
      <span>{{ errorMessage }}</span>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>

    <b-card header="Edit Outgoing Intent">
      <b-form-group>
        <label>Name</label>
        <b-form-input type="text" v-model="outgoingIntent.name" />
      </b-form-group>

      <b-btn variant="primary" @click="saveOutgoingIntent">Save</b-btn>
    </b-card>
  </div>
</template>

<script>
export default {
  name: 'edit-outgoing-intent',
  props: ['id'],
  data() {
    return {
      outgoingIntent: null,
      errorMessage: '',
    };
  },
  mounted() {
    axios.get('/admin/api/outgoing-intents/' + this.id).then(
      (response) => {
        this.outgoingIntent = response.data.data;
      },
    );
  },
  methods: {
    saveOutgoingIntent() {
      this.errorMessage = '';

      const data = {
        name: this.outgoingIntent.name,
      };

      axios.patch('/admin/api/outgoing-intents/' + this.id, data).then(
        (response) => {
          this.$router.push({ name: 'view-outgoing-intent', params: { id: this.outgoingIntent.id } });
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
