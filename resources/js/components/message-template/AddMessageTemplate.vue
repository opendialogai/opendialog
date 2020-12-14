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
        <codemirror v-model="conditions" :options="cmConditionsOptions" :class="(error.field == 'conditions') ? 'is-invalid' : ''" class="collapse-codemirror"/>
      </b-form-group>

      <b-card header="Message Builder">
        <b-form-group>
          <label>Message Template</label>
          <div>
            <b-button v-for="messageType in listMessageTypes" :key="messageType" variant="outline-primary" class="mr-2 mt-2" @click="addMarkup(messageType)">
              {{ messageType }}
            </b-button>
          </div>
        </b-form-group>
      </b-card>

      <b-form-group>
        <label>Message Mark-up</label>
        <codemirror ref="messageMarkup" v-model="message_markup" :options="cmMarkupOptions" :class="(error.field == 'message_markup') ? 'is-invalid' : ''" class="collapse-codemirror"/>
      </b-form-group>

      <b-card header="Message Preview">
        <MessageBuilder v-if="previewData" :message="previewData" v-model="previewData" v-on:errorEmit="errorEmitCatcher"/>
      </b-card>

      <b-btn variant="primary" @click="addMessageTemplate">Create</b-btn>
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

import XmlCodemirror from '@/mixins/XmlCodemirror';
import MessageBuilder from './MessageBuilder';
import MessageTypes from '@/mixins/MessageTypes';

export default {
  name: 'add-message-template',
  props: ['outgoingIntent'],
  mixins: [XmlCodemirror, MessageTypes],
  components: {
    MessageBuilder,
    codemirror,
  },
  data() {
    return {
      previewData: {
        message_markup: ''
      },
      cmConditionsOptions: {
        tabSize: 2,
        mode: 'text/yaml',
        theme: 'dracula',
        lineNumbers: true,
        line: true,
      },
      name: '',
      conditions: '',
      message_markup: '<message>\n</message>',
      error: {},
      messageTypes: ''
    };
  },
  computed: {
    listMessageTypes: () => {
      const options = [];
      MessageTypes.methods.getMessageTypes().forEach((form) => {
        options.push(form.type);
      });
      return options;
    },
  },
  watch: {
    message_markup: {
      handler (val) {
        this.previewData.message_markup = val;
      },
      deep: true,
    },
  },
  methods: {
    addMarkup(messageType) {
      if (messageType) {
        const messageTypeConfig = MessageTypes.methods.getMessageTypes().find(messageConfig => messageConfig.type === messageType);

        if (this.message_markup.includes('</message>')) {
          var xml = messageTypeConfig.xml.split('\n');
          xml.splice(0, 1);
          xml.splice(-1, 1);

          let line = 0;
          const rows = this.message_markup.split('\n');
          rows.forEach((row, i) => {
            if (row.includes('</message>')) {
              rows.splice(i, 0, ...xml);
              line = Math.min(i + 10, rows.length - 1);
            }
          });
          this.message_markup = rows.join('\n');

          setTimeout(() => {
            this.$refs.messageMarkup.cminstance.scrollIntoView({ line });
          }, 100);
        } else {
          this.message_markup = messageTypeConfig.xml;
        }
      }
    },
    addMessageTemplate() {
      const data = {
        name: this.name,
        conditions: this.conditions,
        message_markup: this.message_markup,
      };

      axios.post('/admin/api/outgoing-intent/' + this.outgoingIntent + '/message-templates', data).then(
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
