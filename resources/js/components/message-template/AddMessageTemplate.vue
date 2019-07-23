<template>
  <div>
    <h2 class="mb-3">Message Template</h2>

    <div class="alert alert-danger" role="alert" v-if="error.message">
      <span>{{ error.message }}</span>
      <button type="button" class="close" @click="error.message = ''">
        <span>&times;</span>
      </button>
    </div>

    <b-card header="Add Message Template">
      <b-form-group>
        <label>Name</label>
        <b-form-input type="text" v-model="name" :class="(error.field == 'name') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-form-group>
        <label>Conditions</label>
        <codemirror v-model="conditions" :options="cmConditionsOptions" :class="(error.field == 'conditions') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-form-group>
        <label>Message Mark-up</label>
        <codemirror v-model="message_markup" :options="cmMarkupOptions" :class="(error.field == 'message_markup') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-btn variant="primary" @click="addMessageTemplate">Create</b-btn>
    </b-card>
  </div>
</template>

<script>
import { codemirror } from 'vue-codemirror';
import 'codemirror/mode/yaml/yaml';
import 'codemirror/mode/xml/xml';
import 'codemirror/lib/codemirror.css';
import 'codemirror/theme/dracula.css';

export default {
  name: 'add-message-template',
  props: ['outgoingIntent'],
  components: {
    codemirror,
  },
  data() {
    return {
      cmConditionsOptions: {
        tabSize: 4,
        mode: 'text/yaml',
        theme: 'dracula',
        lineNumbers: true,
        line: true,
      },
      cmMarkupOptions: {
        tabSize: 4,
        mode: 'application/xml',
        theme: 'dracula',
        lineNumbers: true,
        line: true,
      },
      name: '',
      conditions: '',
      message_markup: '',
      error: {},
    };
  },
  methods: {
    addMessageTemplate() {
      const data = {
        name: this.name,
        conditions: this.conditions,
        message_markup: this.message_markup,
      };

      axios.post('/admin/api/outgoing-intents/' + this.outgoingIntent + '/message-templates', data).then(
        (response) => {
          this.$router.push({ name: 'view-message-template', params: { outgoingIntent: this.outgoingIntent, id: response.data.data.id } });
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
