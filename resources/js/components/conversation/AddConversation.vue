<template>
  <div>
    <h2 class="mb-3">Conversation</h2>

    <div class="alert alert-danger" role="alert" v-if="error.message">
      <span>{{ error.message }}</span>
      <button type="button" class="close" @click="error.message = ''">
        <span>&times;</span>
      </button>
    </div>

    <b-card header="Add Conversation">
      <b-form-group>
        <label>Model</label>
        <codemirror v-model="model" :options="cmOptions" :class="(error.field == 'model') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-form-group>
        <label>Notes</label>
        <b-form-textarea v-model="notes" :class="(error.field == 'notes') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-btn variant="primary" @click="addConversation">Create</b-btn>
    </b-card>
  </div>
</template>

<script>
import { codemirror } from 'vue-codemirror';
import 'codemirror/mode/yaml/yaml';
import 'codemirror/lib/codemirror.css';
import 'codemirror/theme/dracula.css';

export default {
  name: 'add-conversation',
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
      name: '',
      model: '',
      notes: '',
      error: {},
    };
  },
  methods: {
    addConversation() {
      const data = {
        name: this.name,
        model: this.model,
        notes: this.notes,
      };

      axios.post('/admin/api/conversation', data).then(
        (response) => {
          this.$router.push({ name: 'view-conversation', params: { id: response.data.data.id } });
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
