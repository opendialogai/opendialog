<template>
  <div class="image-message">
    <template v-if="message.data.link">
      <a :href="message.data.link">
        <img :src="message.data.src" />
      </a>
    </template>
    <template v-else>
      <img :src="message.data.src" />
    </template>
  </div>
</template>

<script>
export default {
  name: 'image-message',
  props: ['message'],
};
</script>
