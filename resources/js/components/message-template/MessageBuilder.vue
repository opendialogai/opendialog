<template>
  <div>
    <div class="message mb-4" v-for="message in messages">
      <template v-if="message.type == 'empty-message'">
        <EmptyMessage :message="message" />
      </template>
      <template v-if="message.type == 'hand-to-human-message'">
        <HandToHumanMessage :message="message" />
      </template>
      <template v-if="message.type == 'cta-message'">
        <CtaMessage :message="message" />
      </template>
      <template v-if="message.type == 'text-message'">
        <TextMessage :message="message" />
      </template>
      <template v-if="message.type == 'button-message'">
        <ButtonMessage :message="message" />
      </template>
      <template v-if="message.type == 'image-message'">
        <ImageMessage :message="message" />
      </template>
      <template v-if="message.type == 'rich-message' || message.type == 'fp-rich-message'">
        <RichMessage :message="message" />
      </template>
      <template v-if="message.type == 'form-message' || message.type == 'fp-form-message'">
        <FormMessage :message="message" />
      </template>
      <template v-if="message.type == 'long-text-message'">
        <LongTextMessage :message="message" />
      </template>
      <template v-if="message.type == 'meta-message'">
        <MetaMessage :message="message" />
      </template>
      <template v-if="message.type == 'list-message'">
        <div class="list-message">
          <slider
            :direction="message.data.view_type"
            :pagination-visible="true"
            :pagination-clickable="true"
            :drag-enable="false"
          >
            <div v-for="(item, idx) in message.data.items" :key="idx">
              <TextMessage v-if="item.type === 'text-message'" :message="item" />
              <ButtonMessage v-else-if="item.type === 'button-message'" :message="item" />
              <ImageMessage v-else-if="item.type === 'image-message'" :message="item" />
              <RichMessage v-else-if="item.type === 'rich-message'" :message="item" />
            </div>
          </slider>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import xmldoc from 'xmldoc';

import Slider from 'vue-plain-slider';

import ButtonMessage from './Messages/ButtonMessage';
import CtaMessage from './Messages/CtaMessage';
import EmptyMessage from './Messages/EmptyMessage';
import FormMessage from './Messages/FormMessage';
import HandToHumanMessage from './Messages/HandToHumanMessage';
import ImageMessage from './Messages/ImageMessage';
import LongTextMessage from './Messages/LongTextMessage';
import MetaMessage from './Messages/MetaMessage';
import RichMessage from './Messages/RichMessage';
import TextMessage from './Messages/TextMessage';

export default {
  name: 'message-builder',
  components: {
    ButtonMessage,
    CtaMessage,
    EmptyMessage,
    FormMessage,
    HandToHumanMessage,
    ImageMessage,
    LongTextMessage,
    MetaMessage,
    RichMessage,
    TextMessage,
    Slider,
  },
  props: ['message'],
  data() {
    return {
      messages: [],
    };
  },
  mounted() {
    var document = new xmldoc.XmlDocument(this.message.message_markup);
    document.children.forEach((msg) => {
      if (msg.type === 'element') {
        const message = this.parseMessage(msg);
        this.messages.push(message);
      }
    });
  },
  methods: {
    parseMessage(msg) {
      const message = {
        type: msg.name,
        data: {},
      };

      switch (message.type) {
        case 'text-message':
          let text = '';
          msg.children.forEach((child) => {
            if (child.type === 'element') {
              text += ' <a target="_blank" href="' + child.childNamed('url').val.trim() + '">' + child.childNamed('text').val.trim() + '</a>';
            } else if (child.type === 'text') {
              text += ' ' + child.text.trim();
            }
          });
          message.data = text.trim();
          break;

        case 'button-message':
          let buttons = [];
          msg.childrenNamed('button').forEach((button) => {
            buttons.push({
              text: (button.childNamed('text')) ? button.childNamed('text').val.trim() : '',
            });
          });

          message.data.text = (msg.childNamed('text')) ? msg.childNamed('text').val.trim() : '';
          message.data.buttons = buttons;
          break;

        case 'image-message':
          message.data.src = (msg.childNamed('src')) ? msg.childNamed('src').val.trim() : '';
          message.data.link = (msg.childNamed('link')) ? msg.childNamed('link').val.trim() : '';
          break;

        case 'cta-message':
          message.data.text = msg.val;
          break;

        case 'hand-to-human-message':
          let data = [];
          msg.childrenNamed('data').forEach((d) => {
            data.push({
              name: d.attr.name,
              val: d.val,
            });
          });
          message.data.data = data;
          break;

        case 'fp-rich-message':
        case 'rich-message':
          message.data.title = (msg.childNamed('title')) ? msg.childNamed('title').val.trim() : '';
          message.data.subtitle = (msg.childNamed('subtitle')) ? msg.childNamed('subtitle').val.trim() : '';
          message.data.text = (msg.childNamed('text')) ? msg.childNamed('text').val.trim() : '';
          message.data.button = {
            text: (msg.childNamed('button')) ? msg.childNamed('button').childNamed('text').val.trim() : '',
          };
          message.data.image = {
            src: (msg.childNamed('image')) ? msg.childNamed('image').childNamed('src').val.trim() : '',
            url: (msg.childNamed('image')) ? msg.childNamed('image').childNamed('url').val.trim() : '',
          };
          break;

        case 'fp-form-message':
        case 'form-message':
          let elements = [];
          msg.childrenNamed('element').forEach((element) => {
            const elementType = element.childNamed('element_type').val.trim();
            let options = [];

            if (elementType == 'radio' || elementType == 'auto_complete_select') {
              element.childNamed('options').childrenNamed('option').forEach((option) => {
                options.push({
                  key: (option.childNamed('key')) ? option.childNamed('key').val.trim() : '',
                  value: (option.childNamed('value')) ? option.childNamed('value').val.trim() : '',
                });
              });
            }

            elements.push({
              element_type: elementType,
              display: (element.childNamed('display')) ? element.childNamed('display').val.trim() : '',
              default_value: (element.childNamed('default_value')) ? element.childNamed('default_value').val.trim() : '',
              options,
            });
          });

          message.data.text = (msg.childNamed('text')) ? msg.childNamed('text').val.trim() : '';
          message.data.submit_text = (msg.childNamed('submit_text')) ? msg.childNamed('submit_text').val.trim() : '';
          message.data.elements = elements;
          break;

        case 'long-text-message':
          message.data.submit_text = (msg.childNamed('submit_text')) ? msg.childNamed('submit_text').val.trim() : '';
          message.data.initial_text = (msg.childNamed('initial_text')) ? msg.childNamed('initial_text').val.trim() : '';
          message.data.placeholder = (msg.childNamed('placeholder')) ? msg.childNamed('placeholder').val.trim() : '';
          message.data.confirmation_text = (msg.childNamed('confirmation_text')) ? msg.childNamed('confirmation_text').val.trim() : '';
          message.data.character_limit = (msg.childNamed('character_limit')) ? msg.childNamed('character_limit').val.trim() : '';
          break;

        case 'list-message':
          let items = [];
          msg.childrenNamed('item').forEach((item) => {
            item.children.forEach((children) => {
              if (children.type === 'element') {
                const i = this.parseMessage(children);
                items.push(i);
              }
            });
          });

          message.data.view_type = msg.attr['view-type'];
          message.data.items = items;
          break;

        case 'meta-message':
          let datas = [];
          msg.childrenNamed('data').forEach((data) => {
            datas.push({
              name: data.attr.name,
              value: data.val.trim(),
            });
          });

          message.data.datas = datas;
          break;
      }

      return message;
    },
  },
};
</script>

<style lang="scss" scoped>
.message {
  .list-message,
  .long-text-message,
  .cta-message,
  .empty-message,
  .hand-to-human-message,
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

  .slider {
    padding-bottom: 30px;
  }
}
</style>
