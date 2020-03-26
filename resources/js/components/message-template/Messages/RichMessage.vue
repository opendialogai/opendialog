<template>
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

<script>
export default {
  name: 'rich-message',
  props: ['message'],
};
</script>
