import Vue from 'vue';
import VueRouter from 'vue-router';

// Containers
import DefaultContainer from '@/containers/DefaultContainer';

import Home from '@/views/Home';
import WebchatSetting from '@/views/WebchatSetting';
import User from '@/views/User';

Vue.use(VueRouter);

const router = new VueRouter({
  mode: 'history',
  routes: [
    {
      path: '/admin',
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
        {
          path: 'users',
          name: 'users',
          component: User,
        },
        {
          path: 'users/:id',
          name: 'view-user',
          component: User,
          props: true,
        },
        {
          path: 'users/:id/edit',
          name: 'edit-user',
          component: User,
          props: true,
        },
      ],
    },
  ],
});

export default router;
