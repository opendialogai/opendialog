<template>
  <div class="empty-message">
    <div class="empty-message--title">---------------- Emtpy message ----------------</div>
  </div>
</template>

<script>
export default {
  name: 'emtpy-message',
  props: ['message'],
};
</script>

<style lang="scss" scoped>
.message {
  .empty-message--title {
    color: #73818f;
  }
}
</style>
