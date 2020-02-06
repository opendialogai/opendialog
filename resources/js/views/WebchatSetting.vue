<template>
  <div>
      <Breadcrumbs />
    <WebchatSettings :id="id" />
  </div>
</template>

<script>
import WebchatSettings from '@/components/webchat-setting/WebchatSettings';
import Breadcrumbs from "@/components/breadcrumbs/Breadcrumbs";

export default {
  name: 'webchat-setting',
  props: ['id'],
  components: {
    Breadcrumbs,
    WebchatSettings,
  },
};
</script>
