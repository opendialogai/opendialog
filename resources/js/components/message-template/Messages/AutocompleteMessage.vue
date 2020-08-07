<template>
  <div class="autocomplete-message">
    <input />
    <button class="btn btn-default btn-primary">Submit</button>
    <div>{{ message.data.title }}</div>
  </div>
</template>

<script>
export default {
  name: 'autocomplete-message',
  props: ['message'],
};
</script>
