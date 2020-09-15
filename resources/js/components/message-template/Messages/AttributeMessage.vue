<template>
  <div class="cta-message">
    <div class="cta-message--title">--------------- Attribute message ---------------</div>
    <div>{{ message.data }}</div>
  </div>
</template>

<script>
export default {
  name: 'attribute-message',
  props: ['message'],
};
</script>
