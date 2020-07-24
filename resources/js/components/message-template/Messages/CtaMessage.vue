<template>
  <div class="cta-message">
    <div class="cta-message--title">----------------- CTA message -----------------</div>
    <div>{{ message.data.text }}</div>
  </div>
</template>

<script>
export default {
  name: 'cta-message',
  props: ['message'],
};
</script>

<style lang="scss" scoped>
.message {
  .cta-message--title {
    color: #73818f;
  }
}
</style>
