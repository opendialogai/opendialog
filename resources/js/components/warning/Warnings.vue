<template>
  <div>
    <h2 class="mb-3">Warnings</h2>

    <div class="overflow-auto">
      <table class="table table-hover">
        <thead class="thead-light">
          <tr>
            <th scope="col">Request ID</th>
            <th scope="col">User ID</th>
            <th scope="col">Message</th>
            <th scope="col">Context</th>
            <th scope="col">Created at</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="warning in warnings">
            <td>
              {{ warning.request_id }}
            </td>
            <td>
              {{ warning.user_id }}
            </td>
            <td>
              {{ warning.message }}
            </td>
            <td>
              <prism v-if="warning.context.length !== 0" language="json" :code="toJson(warning.context)"></prism>
            </td>
            <td>
              {{ warning.created_at }}
            </td>
            <td>
              <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="View" @click.stop="viewWarning(warning.id)">
                <i class="fa fa-eye"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import Prism from 'vue-prismjs';
import 'prismjs/themes/prism.css';

import Pager from '@/mixins/Pager';

export default {
  name: 'warnings',
  mixins: [Pager],
  components: {
    Prism,
  },
  data() {
    return {
      warnings: [],
    };
  },
  watch: {
    '$route' () {
      this.fetchWarnings();
    }
  },
  mounted() {
    this.fetchWarnings();
  },
  methods: {
    fetchWarnings() {
      this.currentPage = parseInt(this.$route.query.page || 1);

      axios.get('/admin/api/warnings?page=' + this.currentPage).then(
        (response) => {
          this.totalPages = parseInt(response.data.meta.last_page);
          this.warnings = response.data.data;
        },
      );
    },
    viewWarning(id) {
      this.$router.push({ name: 'view-warning', params: { id } });
    },
    toJson(object) {
      return JSON.stringify(object, null, 2);
    },
  },
};
</script>
