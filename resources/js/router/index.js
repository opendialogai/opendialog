import Vue from 'vue';
import VueRouter from 'vue-router';

import Home from '@/components/Home';
import WebchatSetting from '@/components/WebchatSetting';

Vue.use(VueRouter);

const router = new VueRouter({
  mode: 'history',
  routes: [
    {
      path: '/od-admin',
      name: 'home',
      component: Home,
    },
    {
      path: '/od-admin/webchat-setting',
      name: 'webchat-setting',
      component: WebchatSetting,
    },
    {
      path: '/od-admin/webchat-setting/:id',
      component: WebchatSetting,
      props: true,
    },
  ],
});

export default router;
