<template>
  <div class="app">
    <div class="app-body">
      <Sidebar :navigationItems="navigationItems" :user="user" :minimized="false"/>
      <main class="main">
          <router-view></router-view>
      </main>
    </div>
  </div>
</template>

<script>

export default {
  name: 'DefaultContainer',
  components: {},
  computed: {
    navigationItems () {
      return window.NavigationItems
    },
    user() {
      return {
        name: window.user.name,
        id: window.user.id,
        email: window.user.email,
        image: '/images/logo.svg'
      };
    }
  },
}
</script>
