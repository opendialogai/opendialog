<template>
  <AppHeaderDropdown right no-caret>
    <template slot="header">
      <img
        src="/images/logo.svg"
        class="img-avatar" />
    </template>
    <template slot="dropdown">
      <b-dropdown-item @click="logout"><i class="fa fa-lock" /> Logout</b-dropdown-item>
    </template>
  </AppHeaderDropdown>
</template>

<script>
import { HeaderDropdown as AppHeaderDropdown } from '@coreui/vue';

export default {
  name: 'DefaultHeaderDropdownAccnt',
  components: {
    AppHeaderDropdown,
  },
  methods: {
    logout() {
      axios.post('/logout').then(() => {
        this.$router.push('/login');
      }).catch(() => {
        location.reload();
      });
    },
  },
};
</script>
