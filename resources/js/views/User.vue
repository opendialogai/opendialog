<template>
  <div>
    <template v-if="currentRouteName == 'view-user'">
      <User :id="id" />
    </template>
    <template v-else-if="currentRouteName == 'edit-user'">
      <EditUser :id="id" />
    </template>
    <template v-else>
      <Users />
    </template>
  </div>
</template>

<script>
import User from '@/components/user/User';
import Users from '@/components/user/Users';
import EditUser from '@/components/user/EditUser';

export default {
  name: 'user',
  props: ['id'],
  components: {
    User,
    Users,
    EditUser,
  },
  computed: {
    currentRouteName() {
      return this.$route.name;
    },
  },
};
</script>
