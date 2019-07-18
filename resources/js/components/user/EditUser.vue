<template>
  <div v-if="user">
    <div class="alert alert-danger" role="alert" v-if="errorMessage">
      <span>{{ errorMessage }}</span>
      <button type="button" class="close" @click="errorMessage = ''">
        <span>&times;</span>
      </button>
    </div>

    <b-card header="Edit User">
      <b-form-group>
        <label>Name</label>
        <b-form-input type="text" v-model="user.name" />
      </b-form-group>

      <b-form-group>
        <label>Email</label>
        <b-form-input type="email" v-model="user.email" />
      </b-form-group>

      <b-form-group>
        <label>Phone Number</label>
        <b-form-input type="text" v-model="user.phone_number" />
      </b-form-group>

      <b-btn variant="primary" @click="saveUser">Save</b-btn>
    </b-card>
  </div>
</template>

<script>
export default {
  name: 'edit-user',
  props: ['id'],
  data() {
    return {
      user: null,
      errorMessage: '',
    };
  },
  mounted() {
    axios.get('/admin/api/user/' + this.id).then(
      (response) => {
        this.user = response.data.data;

        if (this.user.phone_number && this.user.phone_country_code) {
          this.user.phone_number = '+' + this.user.phone_country_code + ' ' + this.user.phone_number;
        }
      },
    );
  },
  methods: {
    saveUser() {
      this.errorMessage = '';

      const data = {
        name: this.user.name,
        email: this.user.model,
        phone_number: this.user.phone_number,
      };

      axios.patch('/admin/api/user/' + this.id, data).then(
        (response) => {
          this.$router.push({ name: 'view-user', params: { id: this.user.id } });
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
