<template>
  <div v-if="outgoingIntent">

    <h2 class="mb-3">Edit Outgoing Intent Name</h2>

    <div class="alert alert-danger" role="alert" v-if="error.message">
      <span>{{ error.message }}</span>
      <button type="button" class="close" @click="error.message = ''">
        <span>&times;</span>
      </button>
    </div>

    <b-card header="Edit Outgoing Intent">
      <b-form-group>
        <label>Name</label>
        <b-form-input type="text" v-model="outgoingIntent.name" :class="(error.field == 'name') ? 'is-invalid' : ''" />
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
      error: {},
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
      this.error = {};

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
            this.error = error.response.data;
          }
        },
      );
    },
  },
};
</script>
