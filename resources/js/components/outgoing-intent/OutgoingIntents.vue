<template>
  <div>
    <h2 class="mb-3">Message Templates</h2>

    <div class="row mb-4">
      <div class="col-12">
        <div class="float-right">
          <b-btn variant="primary" @click="createOutgoingIntent">Create</b-btn>
        </div>
      </div>
    </div>

    <div class="inline mb-2">
      <label class="mr-2 mt-1 mb-0">Filter conversation:</label>
      <v-select :options="conversations" :reduce="option => option.id" label="name" v-model="searchConversation" @input="conversationFilterInput"></v-select>
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
      <table class="table">
        <thead class="thead-light">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Name</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(outgoingIntent, idx) in outgoingIntents">
            <td>
              {{ outgoingIntent.id }}
            </td>
            <td>
              <div>{{ outgoingIntent.name }}</div>

              <div class=" mt-3 collapse" :id="'collapse-' + idx">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">Name</th>
                      <th scope="col">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="messageTemplate in outgoingIntent.message_templates">
                      <td style="width: 10%">{{ messageTemplate.id }}</td>
                      <td style="width: 70%">{{ messageTemplate.name }}</td>
                      <td style="width: 20%">
                        <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="View" @click.stop="viewMessageTemplate(messageTemplate)">
                          <i class="fa fa-eye"></i>
                        </button>
                        <button class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Edit" @click.stop="editMessageTemplate(messageTemplate)">
                          <i class="fa fa-edit"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </td>
            <td class="actions">
              <button class="btn btn-warning mr-2" data-toggle="collapse" :data-target="'#collapse-' + idx" aria-expanded="false" :aria-controls="'collapse-' + idx">
                <span class="expand-label">expand</span>
                <span class="collapse-label">collapse</span>
              </button>
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
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

import Pager from '@/mixins/Pager';

export default {
  name: 'outgoing-intents',
  mixins: [Pager],
  components: {
    vSelect,
  },
  data() {
    return {
      outgoingIntents: [],
      currentOutgoingIntent: null,
      filterConversation: null,
      searchConversation: null,
      searchStringMessageContent: '',
      searchStringIntents: '',
      conversations: [],
    };
  },
  watch: {
    '$route' () {
      this.fetchConversations();
      this.fetchOutgoingIntents();
    }
  },
  mounted() {
    this.fetchConversations();
    this.fetchOutgoingIntents();
  },
  methods: {
    fetchConversations() {
      axios.get('/admin/api/conversation-list').then(
        (response) => {
          this.conversations = response.data;

          if (this.filterConversation) {
            this.conversations.forEach((conversation) => {
              if (conversation.id == this.filterConversation) {
                this.searchConversation = conversation;
              }
            });
          }
        },
      );
    },
    fetchOutgoingIntents() {
      this.filterConversation = (this.$route.query.conversation) ? (this.$route.query.conversation) : '';
      this.searchStringMessageContent = this.$route.query.filterMessageContent || '';
      this.searchStringIntents = this.$route.query.filterIntents || '';
      this.currentPage = parseInt(this.$route.query.page || 1);

      axios.get('/admin/api/outgoing-intents?page=' + this.currentPage + '&filterMessageContent=' + this.searchStringMessageContent + '&filterIntents=' + this.searchStringIntents + '&conversation=' + this.filterConversation).then(
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
          conversation: this.filterConversation,
        } });
      }
    },
    searchIntents(event) {
      if (this.searchStringIntents.length >= 4 || event.keyCode == 13 || event.keyCode == 8) {
        this.$router.push({ name: 'outgoing-intents', query: {
          filterMessageContent: this.searchStringMessageContent,
          filterIntents: this.searchStringIntents,
          conversation: this.filterConversation,
        } });
      }
    },
    clearSearchIntents() {
      this.$router.push({ name: 'outgoing-intents', query: {
        filterMessageContent: this.searchStringMessageContent,
        filterIntents: '',
        conversation: this.filterConversation,
      } });
    },
    clearSearchMessageContent() {
      this.$router.push({ name: 'outgoing-intents', query: {
        filterMessageContent: '',
        filterIntents: this.searchStringIntents,
        conversation: this.filterConversation,
      } });
    },
    conversationFilterInput(value) {
      this.filterConversation = value;

      this.$router.push({ name: 'outgoing-intents', query: {
        filterMessageContent: this.searchStringMessageContent,
        filterIntents: this.searchStringIntents,
        conversation: this.filterConversation,
      } });
    },
    viewMessageTemplate(messageTemplate) {
      this.$router.push({ name: 'view-message-template', params: {
        outgoingIntent: messageTemplate.outgoing_intent_id,
        id: messageTemplate.id,
      } });
    },
    editMessageTemplate(messageTemplate) {
      this.$router.push({ name: 'edit-message-template', params: {
        outgoingIntent: messageTemplate.outgoing_intent_id,
        id: messageTemplate.id,
      } });
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

.v-select {
  min-width: 300px;
  background: white;
}

tr {
  td.actions {
    button[aria-expanded="true"] {
      .expand-label {
        display: none;
      }
    }
    button[aria-expanded="false"] {
      .collapse-label {
        display: none;
      }
    }
  }
}
</style>
