<template>
  <div v-if="messageTemplate">
    <h2 class="mb-3">Message Template</h2>

    <div class="alert alert-danger" role="alert" v-if="error.message">
      <span>{{ error.message }}</span>
      <button type="button" class="close" @click="error.message = ''">
        <span>&times;</span>
      </button>
    </div>

    <b-card header="Edit Message Template">
      <b-form-group>
        <label>Name</label>
        <b-form-input type="text" v-model="messageTemplate.name" :class="(error.field == 'name') ? 'is-invalid' : ''" />
      </b-form-group>

      <b-form-group>
        <label>Conditions</label>
        <codemirror v-model="messageTemplate.conditions" :options="cmConditionsOptions" :class="(error.field == 'conditions') ? 'is-invalid' : ''" class ="collapse-codemirror"/>
      </b-form-group>

      <b-form-group>
        <label>Message Mark-up</label>
        <codemirror v-model="messageTemplate.message_markup" :options="cmMarkupOptions" :class="(error.field == 'message_markup') ? 'is-invalid' : ''" class="collapse-codemirror"/>
      </b-form-group>

      <b-card header="Message Preview">
         <MessageBuilder v-if="previewData" :message="previewData" v-model="previewData" v-on:errorEmit="errorEmitCatcher"/>
      </b-card>

      <b-btn variant="primary" @click="saveMessageTemplate">Save</b-btn>
    </b-card>

  </div>
</template>

<script>
import { codemirror } from 'vue-codemirror';
import 'codemirror/addon/edit/closetag';
import 'codemirror/addon/edit/matchtags';
import 'codemirror/mode/yaml/yaml';
import 'codemirror/mode/xml/xml';
import 'codemirror/lib/codemirror.css';
import 'codemirror/theme/dracula.css';
import MessageBuilder from "./MessageBuilder";

import XmlCodemirror from '@/mixins/XmlCodemirror';

export default {
  name: 'edit-message-template',
  props: ['outgoingIntent', 'id'],
  mixins: [XmlCodemirror],
  components: {
    MessageBuilder,
    codemirror,
  },
  data() {
    return {
      previewData: {},
      cmConditionsOptions: {
        tabSize: 2,
        mode: 'text/yaml',
        theme: 'dracula',
        lineNumbers: true,
        line: true
      },
      messageTemplate: null,
      error: {},
    };
  },
  mounted() {
    axios.get('/admin/api/outgoing-intents/' + this.outgoingIntent + '/message-templates/' + this.id).then(
      (response) => {
        this.messageTemplate = response.data.data;
        this.previewData = this.messageTemplate;
      },
    );
  },
  methods: {
    saveMessageTemplate() {
      this.error = {};

      const data = {
        name: this.messageTemplate.name,
        conditions: this.messageTemplate.conditions,
        message_markup: this.messageTemplate.message_markup,
      };

      axios.patch('/admin/api/outgoing-intents/' + this.outgoingIntent + '/message-templates/' + this.id, data).then(
        (response) => {
          this.$router.push({ name: 'view-message-template', params: { outgoingIntent: this.outgoingIntent, id: this.messageTemplate.id } });
        },
      ).catch(
        (error) => {
          if (error.response.status === 400) {
            this.error = error.response.data;
          }
        },
      );
    },
    errorEmitCatcher(error) {
      this.error = {};
      if (error) {
        this.error.field = 'message_markup';
      }
    }
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
