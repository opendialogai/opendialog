<template>
  <div class="autocomplete-message">
    <div>{{ message.data.title }}</div>
    <input />
    <button class="btn btn-default btn-primary">{{ message.data.submit_text}}</button>
  </div>
</template>

<script>
export default {
  name: 'autocomplete-message',
  props: ['message'],
};
</script>
