<template>
  <div>
    <div class="message mb-4" v-for="message in messages">
      <template v-if="message.type === 'empty-message'">
        <EmptyMessage :message="message" />
      </template>
      <template v-if="message.type === 'hand-to-system-message'">
        <HandToSystemMessage :message="message" />
      </template>
      <template v-if="message.type === 'cta-message'">
        <CtaMessage :message="message" />
      </template>
      <template v-if="message.type === 'text-message'">
        <TextMessage :message="message" />
      </template>
      <template v-if="message.type === 'attribute-message'">
        <AttributeMessage :message="message" />
      </template>
      <template v-if="message.type === 'button-message'">
        <ButtonMessage :message="message" />
      </template>
      <template v-if="message.type === 'image-message'">
        <ImageMessage :message="message" />
      </template>
      <template v-if="message.type === 'rich-message' || message.type === 'fp-rich-message'">
        <RichMessage :message="message" />
      </template>
      <template v-if="message.type === 'form-message' || message.type === 'fp-form-message'">
        <FormMessage :message="message" />
      </template>
      <template v-if="message.type === 'long-text-message'">
        <LongTextMessage :message="message" />
      </template>
      <template v-if="message.type === 'meta-message'">
        <MetaMessage :message="message" />
      </template>
      <template v-if="message.type === 'autocomplete-message'">
        <AutocompleteMessage :message="message" />
      </template>
      <template v-if="message.type === 'error'">
        <Error :message="message" />
      </template>
      <template v-if="message.type === 'list-message'">
        <div class="list-message" :class="message.data.view_type">
          <template v-if="message.data.view_type === 'list'">
            <div class="list-message--item" v-for="(item, idx) in message.data.items" :key="idx">
              <TextMessage v-if="item.type === 'text-message'" :message="item" />
              <ButtonMessage v-else-if="item.type === 'button-message'" :message="item" />
              <ImageMessage v-else-if="item.type === 'image-message'" :message="item" />
              <RichMessage v-else-if="item.type === 'rich-message'" :message="item" />
            </div>
          </template>
          <template v-else>
            <slider
              :direction="message.data.view_type"
              :pagination-visible="true"
              :pagination-clickable="true"
              :drag-enable="false"
            >
              <div class="list-message--item" v-for="(item, idx) in message.data.items" :key="idx">
                <TextMessage v-if="item.type === 'text-message'" :message="item" />
                <ButtonMessage v-else-if="item.type === 'button-message'" :message="item" />
                <ImageMessage v-else-if="item.type === 'image-message'" :message="item" />
                <RichMessage v-else-if="item.type === 'rich-message'" :message="item" />
              </div>
            </slider>
          </template>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import xmldoc from 'xmldoc';

import Slider from 'vue-plain-slider';

import AutocompleteMessage from './Messages/AutocompleteMessage';
import ButtonMessage from './Messages/ButtonMessage';
import CtaMessage from './Messages/CtaMessage';
import EmptyMessage from './Messages/EmptyMessage';
import FormMessage from './Messages/FormMessage';
import HandToSystemMessage from './Messages/HandToSystemMessage';
import ImageMessage from './Messages/ImageMessage';
import LongTextMessage from './Messages/LongTextMessage';
import MetaMessage from './Messages/MetaMessage';
import RichMessage from './Messages/RichMessage';
import TextMessage from './Messages/TextMessage';
import MessageTypes from "@/mixins/MessageTypes";
import Error from "./Messages/Error";
import AttributeMessage from "./Messages/AttributeMessage";

export default {
  name: 'message-builder',
  components: {
    AttributeMessage,
    AutocompleteMessage,
    Error,
    ButtonMessage,
    CtaMessage,
    EmptyMessage,
    FormMessage,
    HandToSystemMessage,
    ImageMessage,
    LongTextMessage,
    MetaMessage,
    RichMessage,
    TextMessage,
    Slider,
  },
  props: ['message'],
  mixins: [MessageTypes],
  data() {
    return {
      watchMessage: this.message,
      messages: [],
    };
  },
  watch: {
    watchMessage: {
      handler (val) {
        this.messages = [];
        const parser = new DOMParser();
        const doc = parser.parseFromString(val.message_markup, 'application/xml');
        if (doc.getElementsByTagName('parsererror').length > 0) {
          const error = doc.getElementsByTagName('parsererror')[0].getElementsByTagName('div')[0].innerHTML;
          this.$emit('errorEmit', error);
          this.messages.push(
            {
              type: 'error',
              data:  `Validation error: ${error}`
            }
          );
        } else {
          this.$emit('errorEmit', '');
          const document = new xmldoc.XmlDocument(val.message_markup);
          this.parseDocumentForMessage(document)
        }
      },
      deep: true,
    },
  },
  mounted() {
    if (this.watchMessage.message_markup) {
      var document = new xmldoc.XmlDocument(this.watchMessage.message_markup);
      this.parseDocumentForMessage(document)
    }
  },
  methods: {
    parseDocumentForMessage(document) {
      document.children.forEach((msg) => {
        if (msg.type === 'element') {
          const message = this.parseMessage(msg);
          this.messages.push(message);
        }
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.message {
  .autocomplete-message,
  .list-message,
  .long-text-message,
  .cta-message,
  .empty-message,
  .hand-to-system-message,
  .text-message,
  .button-message,
  .image-message,
  .form-message,
  .meta-message,
  .rich-message {
    border-radius: 6px;
    padding: 7px 10px;
    background: #eaeaea;
    max-width: 300px;
  }

  .list-message {
    .text-message,
    .button-message,
    .image-message,
    .rich-message {
      padding-left: 0;
      padding-right: 0;
    }

    &.list {
      .list-message--item {
        border-bottom: 1px solid #c3c3c3;
        &:last-child {
          border-bottom: none;
        }
      }
    }
  }

  .slider {
    padding-bottom: 30px;
  }
}
</style>
