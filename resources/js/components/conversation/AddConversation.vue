<template>
  <div>
    <div class="alert alert-danger" role="alert" v-if="errorMessage">
      <span>{{ errorMessage }}</span>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>

    <b-card header="Add Conversation">
      <b-form-group>
        <label>Name</label>
        <b-form-input type="text" v-model="name" />
      </b-form-group>

      <b-form-group>
        <label>Model</label>
        <codemirror v-model="model" :options="cmOptions" />
      </b-form-group>

      <b-form-group>
        <label>Notes</label>
        <b-form-textarea v-model="notes" />
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
      errorMessage: '',
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
