<template>
  <div>
    <div class="alert alert-danger" role="alert" v-if="errorMessage">
      <span>{{ errorMessage }}</span>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>

    <b-card header="Add User">
      <b-form-group>
        <label>Name</label>
        <b-form-input type="text" v-model="name" />
      </b-form-group>

      <b-form-group>
        <label>Email</label>
        <b-form-input type="email" v-model="email" />
      </b-form-group>

      <b-form-group>
        <label>Phone Number</label>
        <b-form-input type="text" v-model="phone_number" />
      </b-form-group>

      <b-btn variant="primary" @click="addUser">Create</b-btn>
    </b-card>
  </div>
</template>

<script>
export default {
  name: 'add-user',
  data() {
    return {
      name: '',
      email: '',
      phone_number: '',
      errorMessage: '',
    };
  },
  methods: {
    addUser() {
      const data = {
        name: this.name,
        email: this.email,
        phone_number: this.phone_number,
      };

      axios.post('/admin/api/user', data).then(
        (response) => {
          this.$router.push({ name: 'view-user', params: { id: response.data.data.id } });
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
