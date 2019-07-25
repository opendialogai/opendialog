<template>
  <div v-if="user">
    <h2 class="mb-3">User</h2>

    <div class="row mb-4">
      <div class="col-12">
        <div class="float-right">
          <b-btn variant="primary" @click="editUser">Edit</b-btn>
          <template v-if="user.id != userId">
            <b-btn variant="danger" @click="showDeleteUserModal">Delete</b-btn>
          </template>
        </div>
      </div>
    </div>
    <b-card header="General">
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">ID</b-col>
        <b-col cols="10">{{ user.id }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Name</b-col>
        <b-col cols="10">{{ user.name }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Email</b-col>
        <b-col cols="10">{{ user.email }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Phone Number</b-col>
        <b-col cols="10">{{ user.phone_number }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Created at</b-col>
        <b-col cols="10">{{ user.created_at }}</b-col>
      </b-row>
      <b-row>
        <b-col class="font-weight-bold" cols="2">Updated at</b-col>
        <b-col cols="10">{{ user.updated_at }}</b-col>
      </b-row>
    </b-card>

    <div class="modal modal-danger fade" id="deleteUserModal" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Delete User</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete this user?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
            <button type="button" class="btn btn-danger" @click="deleteUser">Yes</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'user',
  props: ['id'],
  data() {
    return {
      user: null,
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
    editUser() {
      this.$router.push({ name: 'edit-user', params: { id: this.user.id } });
    },
    showDeleteUserModal() {
      $('#deleteUserModal').modal();
    },
    deleteUser() {
      $('#deleteUserModal').modal('hide');

      axios.delete('/admin/api/user/' + this.user.id);

      this.$router.push({ name: 'users' });
    },
  },
};
</script>
