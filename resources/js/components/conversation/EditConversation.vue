<template>
  <div v-if="conversation">
    <div class="row mb-1">
      <div class="col-md-6">
        <h2 class="mb-3">Edit '{{ conversation.name | capitalize }} Conversation'</h2>
      </div>
      <div class="col-md-6">
        <div class="float-right">
          <template v-if="conversation.status == 'activated'">
            <b-btn variant="primary mb-3 mr-4" @click="deactivateConversation">Deactivate</b-btn>
          </template>
          <template v-else-if="['activatable', 'deactivated'].includes(conversation.status)">
            <b-btn variant="success mb-3 mr-4" @click="activateConversation">Activate</b-btn>
          </template>
          <b-btn variant="primary mb-3" @click="saveConversation">Save & exit</b-btn>
        </div>
      </div>
    </div>

    <div v-if="conversation.status != 'activated'" class="alert alert-warning mb-4">
      After you have finished editing the model, click 'Activate' to set this version to live.
    </div>

    <div class="alert alert-danger mb-4" role="alert" v-if="error.message">
      <span>{{ error.message }}</span>
      <button type="button" class="close" @click="error.message = ''">
        <span>&times;</span>
      </button>
    </div>

    <div class="alert alert-danger mb-4" role="alert" v-if="errorMessage">
      <span>{{ errorMessage }}</span>
      <button type="button" class="close" @click="errorMessage = ''">
        <span>&times;</span>
      </button>
    </div>

    <div class="alert alert-success mb-4" role="alert" v-if="successMessage">
      <span>{{ successMessage }}</span>
      <button type="button" class="close" @click="successMessage = ''">
        <span>&times;</span>
      </button>
    </div>

    <div class="mb-2">
      <h4 class="model-version mb-3">Model Version {{ conversation.version_number }}</h4>
      <div class="conversation-status" :class="getStatusClass(conversation.status)">{{ conversation.status | capitalize }}</div>
    </div>

    <b-card header="Model">
      <b-form-group>
        <codemirror v-model="conversation.model" :options="cmOptions" :class="(error.field == 'model') ? 'is-invalid' : ''" />
      </b-form-group>
    </b-card>

    <b-card header="Notes">
      <b-form-group>
        <b-form-textarea v-model="conversation.notes" :class="(error.field == 'notes') ? 'is-invalid' : ''" />
      </b-form-group>
    </b-card>

    <div class="row">
      <div class="col-md-6">
        <b-card header="Status">
          <b-row>
            <b-col class="font-weight-bold mt-1 mb-1" cols="6" md="2">Yaml</b-col>
            <b-col class="mt-1 mb-1" cols="6" md="4">
              <div class="conversation-status" :class="getStatusClass(conversation.yaml_validation_status)">
                {{ conversation.yaml_validation_status | capitalize }}
              </div>
            </b-col>
            <b-col class="font-weight-bold mt-1 mb-1" cols="6" md="2">Schema</b-col>
            <b-col class="mt-1 mb-1" cols="6" md="4">
              <div class="conversation-status" :class="getStatusClass(conversation.yaml_schema_validation_status)">
                {{ conversation.yaml_schema_validation_status | capitalize }}
              </div>
            </b-col>
          </b-row>
          <b-row>
            <b-col class="font-weight-bold mt-1 mb-1" cols="6" md="2">Scenes</b-col>
            <b-col class="mt-1 mb-1" cols="6" md="4">
              <div class="conversation-status" :class="getStatusClass(conversation.scenes_validation_status)">
                {{ conversation.scenes_validation_status | capitalize }}
              </div>
            </b-col>
            <b-col class="font-weight-bold mt-1 mb-1" cols="6" md="2">Model</b-col>
            <b-col class="mt-1 mb-1" cols="6" md="4">
              <div class="conversation-status" :class="getStatusClass(conversation.model_validation_status)">
                {{ conversation.model_validation_status | capitalize }}
              </div>
            </b-col>
          </b-row>
        </b-card>
      </div>
      <div class="col-md-6">
        <b-card class="intents overflow-auto" header="Intents">
          <b-row>
            <b-col class="font-weight-bold mt-1 mb-1" cols="5" md="3">Opening Intents:</b-col>
            <b-col class="mt-1 mb-1" cols="7" md="9">
                <span v-for="(opening_intent, index) in conversation.opening_intents">
                    {{ opening_intent }}<span v-if="index < (conversation.opening_intents.length - 1)">, </span>
                </span>
            </b-col>
          </b-row>
          <b-row>
            <b-col class="font-weight-bold mt-1 mb-1" cols="5" md="3">Outgoing Intents:</b-col>
            <b-col class="mt-1 mb-1" cols="7" md="9">
              <span v-for="(outgoing_intent, index) in conversation.outgoing_intents">
                <template v-if="outgoing_intent.id">
                  <router-link :to="{ name: 'view-outgoing-intent', params: { id: outgoing_intent.id } }">{{ outgoing_intent.name }}</router-link><span v-if="index < (conversation.outgoing_intents.length - 1)">, </span>
                </template>
                <template v-else>
                  <router-link :to="{ name: 'add-outgoing-intent', query: { name: outgoing_intent.name } }">{{ outgoing_intent.name }}</router-link><span v-if="index < (conversation.outgoing_intents.length - 1)">, </span>
                </template>
              </span>
            </b-col>
          </b-row>
        </b-card>
      </div>
    </div>
  </div>
</template>

<script>
import { codemirror } from 'vue-codemirror';
import 'codemirror/mode/yaml/yaml';
import 'codemirror/lib/codemirror.css';
import 'codemirror/theme/dracula.css';

export default {
  name: 'edit-conversation',
  props: ['id'],
  components: {
    codemirror,
  },
  data() {
    return {
      cmOptions: {
        tabSize: 4,
        mode: 'text/yaml',
        theme: 'dracula',
        lineNumbers: true,
        line: true,
      },
      conversation: null,
      errorMessage: '',
      successMessage: '',
      error: {},
    };
  },
  mounted() {
    axios.get('/admin/api/conversation/' + this.id).then(
      (response) => {
        this.conversation = response.data.data;
      },
    );
  },
  filters: {
    capitalize: function (value) {
      if (!value) return '';
      value = value.toString();
      return value.charAt(0).toUpperCase() + value.slice(1);
    },
  },
  methods: {
    fetchConversation() {
      this.conversation = null;

      axios.get('/admin/api/conversation/' + this.id).then(
        (response) => {
          this.conversation = response.data.data;
        },
      );
    },
    saveConversation() {
      this.error = {};

      const data = {
        name: this.conversation.name,
        model: this.conversation.model,
        notes: this.conversation.notes,
      };

      axios.patch('/admin/api/conversation/' + this.id, data).then(
        (response) => {
          this.$router.push({ name: 'view-conversation', params: { id: this.conversation.id } });
        },
      ).catch(
        (error) => {
          if (error.response.status === 400) {
            this.error = error.response.data;
          }
        },
      );
    },
    deactivateConversation() {
      this.errorMessage = '';
      this.successMessage = '';

      axios.get('/admin/api/conversation/' + this.conversation.id + '/deactivate').then(
        (response) => {
          if (response.data) {
            this.successMessage = 'Conversation deactivated.';
            this.conversation.status = 'deactivated';
          } else {
            this.errorMessage = 'Sorry, I wasn\'t able to deactivate this conversation from DGraph.';
          }
        },
      );
    },
    activateConversation() {
      this.errorMessage = '';
      this.successMessage = '';
      this.error = {};

      const data = {
        name: this.conversation.name,
        model: this.conversation.model,
        notes: this.conversation.notes,
      };

      axios.patch('/admin/api/conversation/' + this.id, data).then(
        (response) => {
          axios.get('/admin/api/conversation/' + this.conversation.id + '/activate').then(
            (response) => {
              if (response.data) {
                this.successMessage = 'Conversation saved and activated.';
                this.conversation.status = 'activated';
                this.conversation.version_number++;
                this.fetchConversation();
              } else {
                this.errorMessage = 'Sorry, I wasn\'t able to activate this conversation to DGraph.';
              }
            },
          );
        },
      ).catch(
        (error) => {
          if (error.response.status === 400) {
            this.error = error.response.data;
          }
        },
      );
    },
    getStatusClass(status) {
      if (status == 'activated' || status == 'activatable' || status == 'saved') {
        return 'green-status';
      }
      if (status == 'deactivated' || status == 'archived' || status == 'invalid') {
        return 'red-status';
      }
      if (status == 'waiting' || status == 'validated') {
        return 'blue-status';
      }
    },
  },
};
</script>

<style lang="scss" scoped>
h2 {
  display: inline-block;
  margin-right: 0.5rem
}
.vue-codemirror {
  font-size: 14px;
  &.is-invalid {
    border: 3px solid #e3342f;
  }
}
.conversation-status {
  vertical-align: text-bottom;
  border-radius: 10px;
  color: #fff;
  padding: 0 12px;
  display: inline-block;
  &.green-status {
    background: var(--green);
  }
  &.red-status {
    background: var(--red);
  }
  &.blue-status {
    background: var(--blue);
  }
}
.model-version {
  display: inline-block;
  margin-right: 0.5rem
}
.intents {
  .card-header,
  .card-body {
    min-width: 520px;
  }
}
</style>
