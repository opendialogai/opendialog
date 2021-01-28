<template>
  <div>
    <Breadcrumbs />
    <WebchatSettings :id="id" />
  </div>
</template>

<script>

export default {
  name: 'webchat-setting',
  props: ['id'],
};
</script>