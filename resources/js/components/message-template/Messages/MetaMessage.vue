<template>
  <div class="meta-message">
    <div class="meta-message--title">----------------- Meta message -----------------</div>
    <div class="meta-message--data" v-for="data in message.data.datas">
      <span class="meta-message--data-name text-danger">{{ data.name }}:</span>
      <span class="meta-message--data-value">{{ data.value }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'meta-message',
  props: ['message'],
};
</script>

<style lang="scss" scoped>
.message {
  .meta-message--title {
    color: #73818f;
  }
}
</style>
