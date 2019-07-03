<template>
  <div class="container">
    <AppHeader/>
    <div class="row">
      <Sidebar class="col-2" />
      <main class="col-8">
        <div class="container-fluid">
          <router-view />
        </div>
      </main>
      <AppAside class="col-2" />
    </div>
    <AppFooter/>
  </div>
</template>

<script>
import AppHeader from '@/components/Header';
import Sidebar from '@/components/Sidebar';
import AppAside from '@/components/Aside';
import AppFooter from '@/components/Footer';

export default {
  name: 'home',
  components: {
    AppHeader,
    Sidebar,
    AppAside,
    AppFooter,
  },
};
</script>
