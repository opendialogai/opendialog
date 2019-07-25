<template>
  <div v-if="conversation">
    <h2 class="mb-3">Conversation</h2>

    <div class="alert alert-danger" role="alert" v-if="error.message">
      <span>{{ error.message }}</span>
      <button type="button" class="close" @click="error.message = ''">
        <span>&times;</span>
      </button>
    </div>

    <b-card header="Edit Conversation">
      <b-form-group>
        <label>Name</label>
        <b-form-input type="text" v-model="conversation.name" :class="(error.field == 'name') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-form-group>
        <label>Model</label>
        <codemirror v-model="conversation.model" :options="cmOptions" :class="(error.field == 'model') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-form-group>
        <label>Notes</label>
        <b-form-textarea v-model="conversation.notes" :class="(error.field == 'notes') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-btn variant="primary" @click="saveConversation">Save</b-btn>
    </b-card>
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
  methods: {
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
  },
};
</script>

<style lang="scss" scoped>
.vue-codemirror {
  font-size: 14px;
  &.is-invalid {
    border: 3px solid #e3342f;
  }
}
</style>
