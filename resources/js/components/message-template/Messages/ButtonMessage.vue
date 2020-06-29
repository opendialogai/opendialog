<template>
  <div class="button-message">
    <div v-html="message.data.text"></div>
    <div class="buttons" v-for="button in message.data.buttons">
      <button class="btn btn-default btn-primary mt-1 mr-2">{{ button.text }}</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'button-message',
  props: ['message'],
};
</script>
