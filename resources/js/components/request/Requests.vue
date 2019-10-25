<template>
  <div>
    <div class="overflow-auto">
      <table class="table table-hover">
        <thead class="thead-light">
          <tr>
            <th scope="col">Request ID</th>
            <th scope="col">Url</th>
            <th scope="col">Method</th>
            <th scope="col">Source ip</th>
            <th scope="col">Http status</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="request in requests" @click="viewRequest(request.requestLog.request_id)">
            <td>
              {{ request.requestLog.request_id }}
            </td>
            <td>
              {{ request.requestLog.url }}
            </td>
            <td>
              {{ request.requestLog.method }}
            </td>
            <td>
              {{ request.requestLog.source_ip }}
            </td>
            <td>
              <template v-if="request.responseLog">
                {{ request.responseLog.http_status }}
              </template>
            </td>
            <td class="actions">
              <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="View" @click.stop="viewRequest(request.requestLog.request_id)">
                <i class="fa fa-eye"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <nav aria-label="navigation">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="(currentPage == 1) ? 'disabled' : ''">
          <router-link class="page-link" :to="{ name: 'requests', query: { page: currentPage - 1 } }">Previous</router-link>
        </li>

        <li class="page-item" :class="(pageNumber == currentPage) ? 'active' : ''" v-for="pageNumber in totalPages">
          <template v-if="showPageNumber(pageNumber)">
            <router-link class="page-link" :to="{ name: 'requests', query: { page: pageNumber } }">{{ pageNumber }}</router-link>
          </template>
          <template v-if="showPageEllipsis(pageNumber)">
            <span class="page-link">...</span>
          </template>
        </li>

        <li class="page-item" :class="(currentPage == totalPages) ? 'disabled' : ''">
          <router-link class="page-link" :to="{ name: 'requests', query: { page: currentPage + 1 } }">Next</router-link>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
import Pager from '@/mixins/Pager';

export default {
  name: 'requests',
  mixins: [Pager],
  data() {
    return {
      requests: [],
    };
  },
  watch: {
    '$route' () {
      this.fetchRequests();
    }
  },
  mounted() {
    this.fetchRequests();
  },
  methods: {
    fetchRequests() {
      this.currentPage = parseInt(this.$route.query.page || 1);

      axios.get('/admin/api/requests?page=' + this.currentPage).then(
        (response) => {
          this.totalPages = parseInt(response.data.meta.last_page);
          this.requests = response.data.data;
        },
      );
    },
    viewRequest(id) {
      this.$router.push({ name: 'view-request', params: { id } });
    },
  },
};
</script>
