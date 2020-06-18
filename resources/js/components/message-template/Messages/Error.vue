<template>
  <div class="error" v-html="message.data"></div>
</template>

<script>
export default {
  name: 'error',
  props: ['message'],
};
</script>
