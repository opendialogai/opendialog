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
        <codemirror ref="messageMarkup" v-model="messageTemplate.message_markup" :options="cmMarkupOptions" :class="(error.field == 'message_markup') ? 'is-invalid' : ''" class="collapse-codemirror"/>
      </b-form-group>

      <b-card header="Message Preview">
         <MessageBuilder v-if="previewData" :message="previewData" v-model="previewData" v-on:errorEmit="errorEmitCatcher"/>
      </b-card>

      <b-btn variant="primary" @click="saveMessageTemplate">Save</b-btn>
      <b-btn v-if="this.$route.query.conversationId" variant="danger" @click="cancel">Cancel</b-btn>
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
  name: 'edit-message-template',
  props: ['outgoingIntent', 'id'],
  mixins: [XmlCodemirror, MessageTypes],
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
      nextState: {}
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
  mounted() {
    axios.get('/admin/api/outgoing-intents/' + this.outgoingIntent + '/message-templates/' + this.id).then(
      (response) => {
        this.messageTemplate = response.data.data;
        this.previewData = this.messageTemplate;

        if (!this.$route.query.conversationId) {
          this.nextState = {
            name: 'view-message-template',
            params: {
              outgoingIntent: this.outgoingIntent, id: this.messageTemplate.id
            }
          };
        } else {
          this.nextState = {
            name: 'conversation-message-templates',
            params: {
              id: this.$route.query.conversationId
            }
          };
        }
      },
    );
  },
  methods: {
    addMarkup(messageType) {
      if (messageType) {
        const messageTypeConfig = MessageTypes.methods.getMessageTypes().find(messageConfig => messageConfig.type === messageType);

        if (this.messageTemplate.message_markup.includes('</message>')) {
          var xml = messageTypeConfig.xml.split('\n');
          xml.splice(0, 1);
          xml.splice(-1, 1);

          let line = 0;
          const rows = this.messageTemplate.message_markup.split('\n');
          rows.forEach((row, i) => {
            if (row.includes('</message>')) {
              rows.splice(i, 0, ...xml);
              line = Math.min(i + 10, rows.length - 1);
            }
          });
          this.messageTemplate.message_markup = rows.join('\n');

          setTimeout(() => {
            this.$refs.messageMarkup.cminstance.scrollIntoView({ line });
          }, 100);
        } else {
          this.messageTemplate.message_markup = messageTypeConfig.xml;
        }
      }
    },
    saveMessageTemplate() {
      this.error = {};

      const data = {
        name: this.messageTemplate.name,
        conditions: this.messageTemplate.conditions,
        message_markup: this.messageTemplate.message_markup,
      };

      axios.patch('/admin/api/outgoing-intents/' + this.outgoingIntent + '/message-templates/' + this.id, data).then(
        (response) => {
          this.$router.push(this.nextState);
        },
      ).catch(
        (error) => {
          if (error.response.status === 400) {
            this.error = error.response.data;
          }
        },
      );
    },
    cancel() {
      this.$router.push(this.nextState);
    },
    errorEmitCatcher(error) {
      this.error = {};
      if (error) {
        this.error.field = 'message_markup';
      }
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
