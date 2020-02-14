<template>
  <div>
      <Breadcrumbs/>
    <template v-if="currentRouteName == 'view-user'">
      <User :id="id" />
    </template>
    <template v-else-if="currentRouteName == 'edit-user'">
      <EditUser :id="id" />
    </template>
    <template v-else-if="currentRouteName == 'add-user'">
      <AddUser />
    </template>
    <template v-else>
      <Users />
    </template>
  </div>
</template>

<script>
import AddUser from '@/components/user/AddUser';
import User from '@/components/user/User';
import Users from '@/components/user/Users';
import EditUser from '@/components/user/EditUser';
import Breadcrumbs from "@/components/breadcrumbs/Breadcrumbs";

export default {
  name: 'user',
  props: ['id'],
  components: {
    Breadcrumbs,
    AddUser,
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
