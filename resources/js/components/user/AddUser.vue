<template>
  <div>
    <h2 class="mb-3">User</h2>

    <div class="alert alert-danger" role="alert" v-if="error.message">
      <span>{{ error.message }}</span>
      <button type="button" class="close" @click="error.message = ''">
        <span>&times;</span>
      </button>
    </div>

    <b-card header="Add User">
      <b-form-group>
        <label>Name*</label>
        <b-form-input type="text" v-model="name" :class="(error.field == 'name') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-form-group>
        <label>Email*</label>
        <b-form-input type="email" v-model="email" :class="(error.field == 'email') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-form-group>
        <label>Phone Number (optional)</label>
        <b-form-input type="text" v-model="phone_number" :class="(error.field == 'phone_number') ? 'is-invalid' : ''" />
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
      error: {},
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
            this.error = error.response.data;
          }
        },
      );
    },
  },
};
</script>
