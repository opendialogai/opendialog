<template>
  <div class="text-message" v-html="message.data"></div>
</template>

<script>
export default {
  name: 'text-message',
  props: ['message'],
};
</script>
