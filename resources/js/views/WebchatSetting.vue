<template>
  <div>
    <WebchatSettings :id="id" />
  </div>
</template>

<script>
import WebchatSettings from '@/components/webchat-setting/WebchatSettings';

export default {
  name: 'webchat-setting',
  props: ['id'],
  components: {
    WebchatSettings,
  },
};
</script>
