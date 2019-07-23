<template>
  <div v-if="user">
    <h2 class="mb-3">User</h2>

    <div class="alert alert-danger" role="alert" v-if="error.message">
      <span>{{ error.message }}</span>
      <button type="button" class="close" @click="error.message = ''">
        <span>&times;</span>
      </button>
    </div>

    <b-card header="Edit User">
      <b-form-group>
        <label>Name</label>
        <b-form-input type="text" v-model="user.name" :class="(error.field == 'name') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-form-group>
        <label>Email</label>
        <b-form-input type="email" v-model="user.email" :class="(error.field == 'email') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-form-group>
        <label>Phone Number</label>
        <b-form-input type="text" v-model="user.phone_number" :class="(error.field == 'phone_number') ? 'is-invalid' : ''" />
      </b-form-group>

      <template v-if="id == userId">
        <b-form-group>
          <label>Update password</label>
          <b-form-input type="password" v-model="password" />
        </b-form-group>

        <b-form-group>
          <label>Repeat Password</label>
          <b-form-input type="password" v-model="password2" />
        </b-form-group>
      </template>

      <b-btn variant="primary" @click="saveUser">Save</b-btn>
    </b-card>
  </div>
</template>

<script>
import bcrypt from 'bcryptjs';

export default {
  name: 'edit-user',
  props: ['id'],
  data() {
    return {
      user: null,
      error: {},
      password: '',
      password2: '',
    };
  },
  computed: {
    userId() {
      return window.Laravel.userId;
    },
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
      this.error = {};

      if ((this.password || this.password2) && this.password != this.password2) {
        this.error = {
          message: 'Your password and confirmation password do not match.'
        };
        return;
      }

      const data = {
        name: this.user.name,
        email: this.user.model,
        phone_number: this.user.phone_number,
      };
      if (this.password) {
        data.password = bcrypt.hashSync(this.password);
      }

      axios.patch('/admin/api/user/' + this.id, data).then(
        (response) => {
          this.$router.push({ name: 'view-user', params: { id: this.user.id } });
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
