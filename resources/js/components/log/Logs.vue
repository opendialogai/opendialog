<template>
  <div>
    <h2 class="mb-3">Logs</h2>

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
          <tr v-for="log in logs">
            <td>
              {{ log.request_id }}
            </td>
            <td>
              {{ log.user_id }}
            </td>
            <td>
              {{ log.message }}
            </td>
            <td>
              <prism v-if="log.context.length !== 0" language="json" :code="toJson(log.context)"></prism>
            </td>
            <td>
              {{ log.created_at }}
            </td>
            <td>
              <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="View" @click.stop="viewLog(log.id)">
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
  name: 'logs',
  mixins: [Pager],
  components: {
    Prism,
  },
  data() {
    return {
      logs: [],
    };
  },
  watch: {
    '$route' () {
      this.fetchLogs();
    }
  },
  mounted() {
    this.fetchLogs();
  },
  methods: {
    fetchLogs() {
      this.currentPage = parseInt(this.$route.query.page || 1);

      axios.get('/admin/api/logs?page=' + this.currentPage).then(
        (response) => {
          this.totalPages = parseInt(response.data.meta.last_page);
          this.logs = response.data.data;
        },
      );
    },
    viewLog(id) {
      this.$router.push({ name: 'view-log', params: { id } });
    },
    toJson(object) {
      return JSON.stringify(object, null, 2);
    },
  },
};
</script>
