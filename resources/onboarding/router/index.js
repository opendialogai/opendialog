import Vue from 'vue';
import VueRouter from 'vue-router';

import Home from '../views/Home';

Vue.use(VueRouter);

const router = new VueRouter({
  mode: 'history',
  routes: [
    {
      path: '/onboarding',
      name: 'home',
      component: Home,
    },
    {
      path: '/onboarding/:id',
      name: 'landing',
      component: Home,
      props: true,
    },
  ],
});

export default router;
