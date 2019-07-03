<template>
  <div class="mt-4">
    <template v-if="currentRoute == 'webchat-setting'">
      <h3>Webchat settings</h3>

      <ul class="list-group">
        <li class="list-group-item pt-1 pb-0">
          <router-link :to="{ name: 'webchat-setting' }">
            General
          </router-link>
        </li>
        <li class="list-group-item pt-1 pb-0">
          <router-link :to="{ name: 'webchat-setting' }">
            Colours
          </router-link>
        </li>
        <li class="list-group-item pt-1 pb-0">
          <router-link :to="{ name: 'webchat-setting' }">
            Comments
          </router-link>
        </li>
      </ul>
    </template>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'sidebar',
  data() {
    return {
      webchatSettings: [],
    };
  },
  computed: {
    currentRoute() {
      return this.$route.name;
    },
  },
  mounted() {
    if (this.currentRoute == 'webchat-setting') {
      axios.get('/admin/api/webchat-setting').then(
        (response) => {
          this.webchatSettings = response.data;
        },
      );
    }
  },
};
</script>

<style lang="scss" scoped>
.list-group-item {
  background: no-repeat;
  border: none;

  a {
    color: #000;
  }
}
</style>
