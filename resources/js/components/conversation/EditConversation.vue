<template>
  <div v-if="conversation">
    <div class="alert alert-danger" role="alert" v-if="errorMessage">
      <span>{{ errorMessage }}</span>
      <button type="button" class="close" @click="errorMessage = ''">
        <span>&times;</span>
      </button>
    </div>

    <b-card header="Edit Conversation">
      <b-form-group>
        <label>Name</label>
        <b-form-input type="text" v-model="conversation.name" />
      </b-form-group>

      <b-form-group>
        <label>Model</label>
        <codemirror v-model="conversation.model" :options="cmOptions" />
      </b-form-group>

      <b-form-group>
        <label>Notes</label>
        <b-form-textarea v-model="conversation.notes" />
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
      errorMessage: '',
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
      this.errorMessage = '';

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
            this.errorMessage = error.response.data;
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
}
</style>
