import Vue from 'vue';
import VueRouter from 'vue-router';

// Containers
import DefaultContainer from '@/containers/DefaultContainer';

import Home from '@/views/Home';
import WebchatSetting from '@/views/WebchatSetting';

Vue.use(VueRouter);

const router = new VueRouter({
  mode: 'history',
  routes: [
    {
      path: '/od-admin',
      component: DefaultContainer,
      children: [
        {
          path: '/',
          name: 'home',
          component: Home,
        },
        {
          path: 'webchat-setting',
          name: 'webchat-setting',
          component: WebchatSetting,
        },
        {
          path: 'webchat-setting/:id',
          component: WebchatSetting,
          props: true,
        },
      ],
    },
  ],
});

export default router;
