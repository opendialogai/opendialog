<template>
  <div class="hand-to-human-message">
    <div class="hand-to-human-message--title">---------- Hand To Human message ----------</div>
    <div v-for="data in message.data.data" :key="data.name">
      {{ data.name }}: {{ data.val }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'hand-to-human-message',
  props: ['message'],
};
</script>

<style lang="scss" scoped>
.message {
  .hand-to-human-message--title {
    color: #73818f;
  }
}
</style>
