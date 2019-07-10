import Vue from 'vue';
import VueRouter from 'vue-router';

// Containers
import DefaultContainer from '@/containers/DefaultContainer';

import Home from '@/views/Home';
import WebchatSetting from '@/views/WebchatSetting';
import Conversation from '@/views/Conversation';

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
          path: 'conversations',
          name: 'conversations',
          component: Conversation,
        },
        {
          path: 'conversations/:id',
          name: 'view-conversation',
          component: Conversation,
          props: true,
        },
        {
          path: 'conversations/:id/edit',
          name: 'edit-conversation',
          component: Conversation,
          props: true,
        },
      ],
    },
  ],
});

export default router;
