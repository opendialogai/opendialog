<template>
  <div>
    <h2 class="mb-3">Outgoing Intents</h2>

    <div class="row mb-4">
      <div class="col-12">
        <div class="float-right">
          <b-btn variant="primary" @click="createOutgoingIntent">Create</b-btn>
        </div>
      </div>
    </div>

    <div class="inline mb-2">
      <label class="mr-2 mt-1 mb-0">Filter intents:</label>
      <input class="form-control mt-1 mr-1" v-model="searchStringIntents" @keyup="searchIntents" />
      <button class="btn btn-danger mt-1" @click="clearSearchIntents">Clear</button>
    </div>

    <div class="inline mb-4">
      <label class="mr-2 mt-1 mb-0">Search message content:</label>
      <input class="form-control mt-1 mr-1" v-model="searchStringMessageContent" @keyup="searchMessageContent" />
      <button class="btn btn-danger mt-1" @click="clearSearchMessageContent">Clear</button>
    </div>

    <div class="overflow-auto">
      <table class="table table-hover">
        <thead class="thead-light">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Name</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="outgoingIntent in outgoingIntents">
            <td>
              {{ outgoingIntent.id }}
            </td>
            <td>
              <div>{{ outgoingIntent.name }}</div>

              <div class="mt-3 messages" v-if="outgoingIntent.message_templates && expandedRow == outgoingIntent.id">
                <div class="message mt-1" v-for="message in outgoingIntent.message_templates">
                  {{ message.message_markup }}
                </div>
              </div>
            </td>
            <td class="actions">
              <template v-if="searchStringMessageContent.length">
                <button class="btn btn-warning" data-toggle="tooltip" data-placement="top" title="Expand" @click.stop="toggleRow(outgoingIntent.id)">
                  <i class="fa fa-expand"></i>
                </button>
              </template>

              <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="View" @click.stop="viewOutgoingIntent(outgoingIntent.id)">
                <i class="fa fa-eye"></i>
              </button>
              <button class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Delete" @click.stop="showDeleteOutgoingIntentModal(outgoingIntent.id)">
                <i class="fa fa-close"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <nav aria-label="navigation">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="(currentPage == 1) ? 'disabled' : ''">
          <router-link class="page-link" :to="{ name: 'outgoing-intents', query: { page: currentPage - 1 } }">Previous</router-link>
        </li>

        <li class="page-item" :class="(pageNumber == currentPage) ? 'active' : ''" v-for="pageNumber in totalPages">
          <template v-if="showPageNumber(pageNumber)">
            <router-link class="page-link" :to="{ name: 'outgoing-intents', query: { page: pageNumber } }">{{ pageNumber }}</router-link>
          </template>
          <template v-if="showPageEllipsis(pageNumber)">
            <span class="page-link">...</span>
          </template>
        </li>

        <li class="page-item" :class="(currentPage == totalPages) ? 'disabled' : ''">
          <router-link class="page-link" :to="{ name: 'outgoing-intents', query: { page: currentPage + 1 } }">Next</router-link>
        </li>
      </ul>
    </nav>

    <div class="modal modal-danger fade" id="deleteOutgoingIntentModal" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Delete Outgoing Intent</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete this outgoing intent?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
            <button type="button" class="btn btn-danger" @click="deleteOutgoingIntent">Yes</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Pager from '@/mixins/Pager';

export default {
  name: 'outgoing-intents',
  mixins: [Pager],
  data() {
    return {
      outgoingIntents: [],
      currentOutgoingIntent: null,
      searchStringMessageContent: '',
      searchStringIntents: '',
      expandedRow: 0,
    };
  },
  watch: {
    '$route' () {
      this.fetchOutgoingIntents();
    }
  },
  mounted() {
    this.fetchOutgoingIntents();
  },
  methods: {
    fetchOutgoingIntents() {
      this.searchStringMessageContent = this.$route.query.filterMessageContent || '';
      this.searchStringIntents = this.$route.query.filterIntents || '';
      this.currentPage = parseInt(this.$route.query.page || 1);

      axios.get('/admin/api/outgoing-intents?page=' + this.currentPage + '&filterMessageContent=' + this.searchStringMessageContent + '&filterIntents=' + this.searchStringIntents).then(
        (response) => {
          this.totalPages = parseInt(response.data.meta.last_page);
          this.outgoingIntents = response.data.data;
        },
      );
    },
    createOutgoingIntent() {
      this.$router.push({ name: 'add-outgoing-intent' });
    },
    viewOutgoingIntent(id) {
      this.$router.push({ name: 'view-outgoing-intent', params: { id } });
    },
    editOutgoingIntent(id) {
      this.$router.push({ name: 'edit-outgoing-intent', params: { id } });
    },
    showDeleteOutgoingIntentModal(id) {
      this.currentOutgoingIntent = id;
      $('#deleteOutgoingIntentModal').modal();
    },
    deleteOutgoingIntent() {
      $('#deleteOutgoingIntentModal').modal('hide');

      this.outgoingIntents = this.outgoingIntents.filter(obj => obj.id !== this.currentOutgoingIntent);

      axios.delete('/admin/api/outgoing-intents/' + this.currentOutgoingIntent);
    },
    searchMessageContent(event) {
      if (this.searchStringMessageContent.length >= 4 || event.keyCode == 13 || event.keyCode == 8) {
        this.$router.push({ name: 'outgoing-intents', query: {
          filterMessageContent: this.searchStringMessageContent,
          filterIntents: this.searchStringIntents,
        } });
      }
    },
    searchIntents(event) {
      if (this.searchStringIntents.length >= 4 || event.keyCode == 13 || event.keyCode == 8) {
        this.$router.push({ name: 'outgoing-intents', query: {
          filterMessageContent: this.searchStringMessageContent,
          filterIntents: this.searchStringIntents,
        } });
      }
    },
    clearSearchIntents() {
      this.$router.push({ name: 'outgoing-intents', query: {
        filterMessageContent: this.searchStringMessageContent,
        filterIntents: '',
      } });
    },
    clearSearchMessageContent() {
      this.$router.push({ name: 'outgoing-intents', query: {
        filterMessageContent: '',
        filterIntents: this.searchStringIntents,
      } });
    },
    toggleRow(outgoingIntentId) {
      if (this.expandedRow == outgoingIntentId) {
        this.expandedRow = 0;
      } else {
        this.expandedRow = outgoingIntentId;
      }
    },
  },
};
</script>

<style lang="scss" scoped>
table td.actions {
  min-width: 160px;
}

.inline {
  * {
    display: inline-block;
    vertical-align: middle;
  }
  .form-control {
    width: 300px;
  }
}

tr {
  .messages {
    max-width: 700px;
  }
  .message {
    border-radius: 6px;
    padding: 7px 10px;
    background: #eaeaea;
  }
  &:hover {
    .message {
      background: #fff;
    }
  }
}
</style>
