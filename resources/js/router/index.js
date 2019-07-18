import Vue from 'vue';
import VueRouter from 'vue-router';

// Containers
import DefaultContainer from '@/containers/DefaultContainer';

import Home from '@/views/Home';
import WebchatSetting from '@/views/WebchatSetting';
import Conversation from '@/views/Conversation';
import MessageTemplate from '@/views/MessageTemplate';
import OutgoingIntent from '@/views/OutgoingIntent';
import ChatbotUsers from '@/views/ChatbotUsers';
import ConversationLog from '@/views/ConversationLog';
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
          path: 'chatbot-users',
          name: 'chatbot-users',
          component: ChatbotUsers,
        },
        {
          path: 'chatbot-users/:id',
          name: 'view-chatbot-user',
          component: ChatbotUsers,
          props: true,
        },
        {
          path: 'chatbot-users/:id/conversation-log',
          name: 'conversation-log',
          component: ConversationLog,
          props: true,
        },
        {
          path: 'conversations',
          name: 'conversations',
          component: Conversation,
        },
        {
          path: 'conversations/add',
          name: 'add-conversation',
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
        {
          path: 'outgoing-intents/',
          name: 'outgoing-intents',
          component: OutgoingIntent,
        },
        {
          path: 'outgoing-intents/add',
          name: 'add-outgoing-intent',
          component: OutgoingIntent,
        },
        {
          path: 'outgoing-intents/:id',
          name: 'view-outgoing-intent',
          component: OutgoingIntent,
          props: true,
        },
        {
          path: 'outgoing-intents/:id/edit',
          name: 'edit-outgoing-intent',
          component: OutgoingIntent,
          props: true,
        },
        {
          path: 'outgoing-intents/:outgoingIntent/message-templates',
          name: 'message-templates',
          component: MessageTemplate,
          props: true,
        },
        {
          path: 'outgoing-intents/:outgoingIntent/message-templates/add',
          name: 'add-message-template',
          component: MessageTemplate,
          props: true,
        },
        {
          path: 'outgoing-intents/:outgoingIntent/message-templates/:id',
          name: 'view-message-template',
          component: MessageTemplate,
          props: true,
        },
        {
          path: 'outgoing-intents/:outgoingIntent/message-templates/:id/edit',
          name: 'edit-message-template',
          component: MessageTemplate,
          props: true,
        },
        {
          path: 'users',
          name: 'users',
          component: User,
        },
        {
          path: 'users/add',
          name: 'add-user',
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
