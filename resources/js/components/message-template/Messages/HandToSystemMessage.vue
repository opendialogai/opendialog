<template>
  <div class="hand-to-system-message">
    <div class="hand-to-system-message--title">---------- Hand To System message ----------</div>
    <div>System: {{ message.data.system }}</div>
    <div>Elements:</div>
    <div class="ml-4" v-for="data in message.data.data" :key="data.name">
      <span class="text-danger">{{ data.name }}:</span>
      <span>{{ data.val }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'hand-to-system-message',
  props: ['message'],
};
</script>

<style lang="scss" scoped>
.message {
  .hand-to-system-message--title {
    color: #73818f;
  }
}
</style>
