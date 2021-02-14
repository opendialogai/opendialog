<template>
  <div class="app">
    <Header :navigationItems="navigationItems"/>
    <div class="app-body">
      <Sidebar :navigationItems="navigationItems" :user="user" :minimized="false"/>
      <main class="main">
        <div class="container-fluid mt-4">
          <router-view></router-view>
        </div>
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
        email: window.user.email,
        image: '/images/logo.svg'
      };
    }
  },
}
</script>
