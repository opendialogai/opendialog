<template>
  <div>
    <b-card header="Conversation Log">
      <div class="d-inline-block mb-2 w-100">
        <div class="float-left font-weight-bolder">Bot</div>
        <div class="float-right font-weight-bolder">User</div>
      </div>

      <div class="message d-inline-block mb-4 w-100" v-for="message in messages">
        <div :class="(message.author == 'them') ? 'float-left them' : 'float-right me'">
          <template v-if="message.type == 'text'">
            <div class="text-message" v-html="message.message"></div>
          </template>
          <template v-if="message.type == 'button'">
            <div class="button-message">
              <div v-html="message.data.text"></div>
              <div class="buttons">
                <button v-for="button in message.data.buttons" class="btn btn-default btn-primary mt-1 mr-2">{{ button.text }}</button>
              </div>
            </div>
          </template>
          <template v-if="message.type == 'button_response'">
            <div class="button-response-message">
              <div class="btn btn-default btn-secondary" v-html="message.data.text"></div>
            </div>
          </template>
          <template v-if="message.type == 'image'">
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
          <template v-if="message.type == 'rich'">
            <div class="rich-message">
              <div class="rich-message--title mb-1" v-if="message.data.title">{{ message.data.title }}</div>
              <div class="rich-message--subtitle mb-2" v-if="message.data.subtitle">{{ message.data.subtitle }}</div>
              <div class="rich-message--text" v-html="message.data.text"></div>

              <div class="rich-message--image mt-2 mb-1" v-if="message.data.image && message.data.image.src">
                <template v-if="message.data.image.url">
                  <a :href="message.data.image.url">
                    <img :src="message.data.image.src" />
                  </a>
                </template>
                <template v-else>
                  <img :src="message.data.image.src" />
                </template>
              </div>

              <div class="buttons" v-if="message.data.button && message.data.button.text">
                <button class="btn btn-default btn-primary mt-1 mr-2">{{ message.data.button.text }}</button>
              </div>
            </div>
          </template>
          <template v-if="message.type == 'list'">
            <div class="list-message">
            </div>
          </template>
          <div class="time font-xs text-muted mt-1">{{ message.created_at }}</div>
        </div>
      </div>
    </b-card>
  </div>
</template>

<script>
export default {
  name: 'conversation-log',
  props: ['id'],
  data() {
    return {
      messages: [],
    };
  },
  mounted() {
    axios.get('/admin/api/chatbot-user/' + this.id + '/messages').then(
      (response) => {
        response.data.data.forEach(message => {
          if (message.author !== this.id) {
            message.author = 'them';
          }
          this.messages.push(message);
        });
      },
    );
  },
};
</script>

<style lang="scss" scoped>
.message {
  .list-message,
  .button-response-message,
  .text-message,
  .button-message,
  .image-message,
  .rich-message {
    border-radius: 6px;
    padding: 7px 10px;
    background: #eaeaea;
    max-width: 300px;
  }

  .me {
    .button-response-message,
    .text-message {
      background: #4e8cff;
      color: white;
    }
  }
}
</style>
