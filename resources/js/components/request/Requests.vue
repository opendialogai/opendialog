<template>
  <div>
    <b-card no-body>
      <b-button block href="#" v-b-toggle.filters>Filters</b-button>

      <b-collapse id="filters">
        <b-card-body>
          <b-form-group
            label-cols-sm="2"
            label="Url"
          >
            <b-form-input type="text" v-model="urlFilter" />
          </b-form-group>
          <b-form-group
            label-cols-sm="2"
            label="Http status"
          >
            <b-form-input type="text" v-model="httpStatusFilter" />
          </b-form-group>
          <b-form-group
            label-cols-sm="2"
            label="Source ip"
          >
            <b-form-input type="text" v-model="sourceIpFilter" />
          </b-form-group>
          <b-form-group
            label-cols-sm="2"
            label="Username"
          >
            <b-form-input type="text" v-model="userIdFilter" />
          </b-form-group>

          <b-btn
            v-if="updatingFilters"
            class="btn-filter"
            variant="primary"
            @click="updateFilters"
            disabled
          >
            Updating ...
          </b-btn>
          <b-btn
            v-else
            class="btn-filter"
            variant="primary"
            @click="updateFilters"
          >
            Update
          </b-btn>
        </b-card-body>
      </b-collapse>
    </b-card>

    <div class="overflow-auto">
      <table class="table table-hover">
        <thead class="thead-light">
          <tr>
            <th scope="col">Request ID</th>
            <th scope="col">Url</th>
            <th scope="col">Method</th>
            <th scope="col">Source ip</th>
            <th scope="col">Time</th>
            <th scope="col">Http status</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="request in requests">
            <td>
              {{ request.request_id }}
            </td>
            <td>
              {{ request.url }}
            </td>
            <td>
              {{ request.method }}
            </td>
            <td>
              {{ request.source_ip }}
            </td>
            <td>
              {{ request.microtime }}
            </td>
            <td>
              <template v-if="request.response_log">
                {{ request.response_log.http_status }}
              </template>
            </td>
            <td class="actions">
              <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="View" @click.stop="viewRequest(request.request_id)">
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
      urlFilter: '',
      httpStatusFilter: '',
      sourceIpFilter: '',
      userIdFilter: '',
      updatingFilters: false,
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

      const filters = {};
      if (this.urlFilter) {
        filters.url = this.urlFilter;
      }
      if (this.httpStatusFilter) {
        filters.http_status = this.httpStatusFilter;
      }
      if (this.sourceIpFilter) {
        filters.source_ip = this.sourceIpFilter;
      }
      if (this.userIdFilter) {
        filters.user_id = this.userIdFilter;
      }

      axios.get('/admin/api/requests?page=' + this.currentPage, { params: filters }).then(
        (response) => {
          this.totalPages = parseInt(response.data.meta.last_page);
          this.requests = response.data.data;

          this.updatingFilters = false;
        },
      );
    },
    viewRequest(id) {
      this.$router.push({ name: 'view-request', params: { id } });
    },
    updateFilters() {
      this.updatingFilters = true;
      this.fetchRequests();
    },
  },
};
</script>

<style lang="scss" scoped>
.btn-filter {
  min-width: 150px;
}
</style>
