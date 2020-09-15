<template>
  <div class="long-text-message">
    <div class="long-text-message--title">-------------- Long Text message --------------</div>
    <div v-if="message.data.initial_text" class="long-text-message--text">
      <b>Initial text:</b> <span>{{ message.data.initial_text }}</span>
    </div>
    <div v-if="message.data.placeholder" class="long-text-message--placeholder">
      <b>Placeholder:</b> <span>{{ message.data.placeholder }}</span>
    </div>
    <div v-if="message.data.confirmation_text" class="long-text-message--confirmation-text">
      <b>Confirmation text:</b> <span>{{ message.data.confirmation_text }}</span>
    </div>
    <div v-if="message.data.character_limit" class="long-text-message--character-limit">
      <b>Character limit:</b> <span>{{ message.data.character_limit }}</span>
    </div>
    <div v-if="message.data.submit_text" class="long-text-message--submit-text">
      <b>Submit text:</b> <span>{{ message.data.submit_text }}</span>
    </div>
    <div v-if="message.data.callback" class="long-text-message--callback">
      <b>Callback:</b> <span>{{ message.data.callback }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'long-text-message',
  props: ['message'],
};
</script>

<style lang="scss" scoped>
.message {
  .long-text-message--title {
    color: #73818f;
  }
}
</style>
