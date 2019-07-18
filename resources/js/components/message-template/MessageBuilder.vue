<template>
  <div>
    <div class="message mb-4" v-for="message in messages">
      <template v-if="message.type == 'text-message'">
        <div class="text-message" v-html="message.data"></div>
      </template>
      <template v-if="message.type == 'button-message'">
        <div class="button-message">
          <div v-html="message.data.text"></div>
          <div class="buttons" v-for="button in message.data.buttons">
            <button class="btn btn-default btn-primary mt-1 mr-2">{{ button.text }}</button>
          </div>
        </div>
      </template>
      <template v-if="message.type == 'image-message'">
        <div class="image-message">
          <template v-if="message.data.link">
            <a :href="message.data.link">
              <img :src="message.data.src" />
            </a>
          </template>
          <template v-else>
            <img :src="message.data.src" />
          </template>
        </div>
      </template>
      <template v-if="message.type == 'rich-message'">
        <div class="rich-message">
          <div class="rich-message--title mb-1" v-if="message.data.title">{{ message.data.title }}</div>
          <div class="rich-message--subtitle mb-2" v-if="message.data.subtitle">{{ message.data.subtitle }}</div>
          <div class="rich-message--text" v-html="message.data.text"></div>

          <div class="rich-message--image mt-2 mb-1" v-if="message.data.image.src">
            <template v-if="message.data.image.url">
              <a :href="message.data.image.url">
                <img :src="message.data.image.src" />
              </a>
            </template>
            <template v-else>
              <img :src="message.data.image.src" />
            </template>
          </div>

          <div class="buttons" v-if="message.data.button.text">
            <button class="btn btn-default btn-primary mt-1 mr-2">{{ message.data.button.text }}</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import xmldoc from 'xmldoc';

export default {
  name: 'message-builder',
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
        const message = {
          type: msg.name,
          data: {},
        };

        switch (message.type) {
          case 'text-message':
            message.data = msg.val.trim();
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

          case 'rich-message':
            message.data.title = (msg.childNamed('title')) ? msg.childNamed('title').val.trim() : '';
            message.data.subtitle = (msg.childNamed('subtitle')) ? msg.childNamed('subtitle').val.trim() : ''(string);
            message.data.text = (msg.childNamed('text')) ? msg.childNamed('text').val.trim() : ''(string);
            message.data.button = {
              text: (msg.childNamed('button')) ? msg.childNamed('button').childNamed('text').val.trim() : '',
            };
            message.data.image = {
              src: (msg.childNamed('image')) ? msg.childNamed('image').childNamed('src').val.trim() : '',
              url: (msg.childNamed('image')) ? msg.childNamed('image').childNamed('url').val.trim() : '',
            };
            break;
        }

        this.messages.push(message);
      }
    });
  },
};
</script>

<style lang="scss" scoped>
.message {
  .text-message,
  .button-message,
  .image-message,
  .rich-message {
    border-radius: 6px;
    padding: 7px 10px;
    background: #eaeaea;
    max-width: 300px;
  }
}
</style>
