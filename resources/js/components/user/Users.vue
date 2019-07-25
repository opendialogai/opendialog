<template>
  <div>
    <h2 class="mb-3">Users</h2>

    <div class="row mb-4">
      <div class="col-12">
        <div class="float-right">
          <b-btn variant="primary" @click="createUser">Create</b-btn>
        </div>
      </div>
    </div>

    <div class="overflow-auto">
      <table class="table table-hover">
        <thead class="thead-light">
          <tr>
            <th scope="col">ID</th>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
            <th scope="col">Phone Number</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" @click="viewUser(user.id)">
            <td>
              {{ user.id }}
            </td>
            <td>
              {{ user.name }}
            </td>
            <td>
              {{ user.email }}
            </td>
            <td>
              {{ user.phone_number }}
            </td>
            <td class="actions">
              <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="View" @click.stop="viewUser(user.id)">
                <i class="fa fa-eye"></i>
              </button>
              <button class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Edit" @click.stop="editUser(user.id)">
                <i class="fa fa-edit"></i>
              </button>
              <template v-if="user.id != userId">
                <button class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Delete" @click.stop="showDeleteUserModal(user.id)">
                  <i class="fa fa-close"></i>
                </button>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <nav aria-label="navigation">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="(currentPage == 1) ? 'disabled' : ''">
          <router-link class="page-link" :to="{ name: 'chatbot-users', query: { page: currentPage - 1 } }">Previous</router-link>
        </li>

        <li class="page-item" v-for="pageNumber in totalPages">
          <router-link class="page-link" :to="{ name: 'chatbot-users', query: { page: pageNumber } }">{{ pageNumber }}</router-link>
        </li>

        <li class="page-item" :class="(currentPage == totalPages) ? 'disabled' : ''">
          <router-link class="page-link" :to="{ name: 'chatbot-users', query: { page: currentPage + 1 } }">Next</router-link>
        </li>
      </ul>
    </nav>

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
  name: 'users',
  data() {
    return {
      users: [],
      currentUser: null,
      currentPage: 1,
      totalPages: 1,
    };
  },
  computed: {
    userId() {
      return window.Laravel.userId;
    },
  },
  watch: {
    '$route' () {
      this.fetchUsers();
    }
  },
  mounted() {
    this.fetchUsers();
  },
  methods: {
    fetchUsers() {
      this.currentPage = this.$route.query.page || 1;

      axios.get('/admin/api/user?page=' + this.currentPage).then(
        (response) => {
          this.totalPages = response.data.meta.last_page;
          this.users = [];

          response.data.data.forEach((user) => {
            if (user.phone_number && user.phone_country_code) {
              user.phone_number = '+' + user.phone_country_code + ' ' + user.phone_number;
            }
            this.users.push(user);
          });
        },
      );
    },
    createUser() {
      this.$router.push({ name: 'add-user' });
    },
    viewUser(id) {
      this.$router.push({ name: 'view-user', params: { id } });
    },
    editUser(id) {
      this.$router.push({ name: 'edit-user', params: { id } });
    },
    showDeleteUserModal(id) {
      this.currentUser = id;
      $('#deleteUserModal').modal();
    },
    deleteUser() {
      $('#deleteUserModal').modal('hide');

      this.users = this.users.filter(obj => obj.id !== this.currentUser);

      axios.delete('/admin/api/user/' + this.currentUser);
    },
  },
};
</script>

<style lang="scss" scoped>
table td.actions {
  min-width: 160px;
}
</style>
