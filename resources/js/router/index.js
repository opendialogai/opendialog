import Vue from 'vue';
import VueRouter from 'vue-router';
// Containers
import DefaultContainer from '@/containers/DefaultContainer';

import Home from '@opendialogai/opendialog-design-system-pkg/src/components/Views/Home';
import WebchatSettingView from '@opendialogai/opendialog-design-system-pkg/src/components/Views/WebchatSettingView';
import Conversation from '@/views/Conversation';
import MessageTemplateView from '@opendialogai/opendialog-design-system-pkg/src/components/Views/MessageTemplateView';
import OutgoingIntentView from '@opendialogai/opendialog-design-system-pkg/src/components/Views/OutgoingIntentView';
import ChatbotUsersView from '@opendialogai/opendialog-design-system-pkg/src/components/Views/ChatbotUsersView';
import UserView from '@opendialogai/opendialog-design-system-pkg/src/components/Views/UserView';
import RequestView from '@opendialogai/opendialog-design-system-pkg/src/components/Views/RequestView';
import GlobalContextView from '@opendialogai/opendialog-design-system-pkg/src/components/Views/GlobalContextView';
import WarningView from '@opendialogai/opendialog-design-system-pkg/src/components/Views/WarningView';
import WebchatDemo from '@opendialogai/opendialog-design-system-pkg/src/components/Views/WebchatDemo';
import ConversationLog from '@opendialogai/opendialog-design-system-pkg/src/components/Views/ConversationLog'
import DynamicAttribute from '@/views/DynamicAttribute'
import Scenarios from '@opendialogai/opendialog-design-system-pkg/src/components/ConversationBuilder/Scenarios/Scenarios'

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
          path: 'conversation-builder/scenarios',
          name: 'scenarios',
          component: Scenarios
        },
        {
          path: 'webchat-setting',
          name: 'webchat-setting',
          component: WebchatSettingView,
        },
        {
          path: 'webchat-setting/:id',
          component: WebchatSettingView,
          props: true,
        },
        {
          path: 'chatbot-users',
          name: 'chatbot-users',
          component: ChatbotUsersView,
        },
        {
          path: 'chatbot-users/:id',
          name: 'view-chatbot-user',
          component: ChatbotUsersView,
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
            path: 'conversations/archive',
            name: 'conversations-archive',
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
            path: 'conversations/:id/message-templates',
            name: 'conversation-message-templates',
            props: true,
            component: Conversation,
        },
        {
          path: 'outgoing-intents/',
          name: 'outgoing-intents',
          component: OutgoingIntentView,
        },
        {
          path: 'outgoing-intents/add',
          name: 'add-outgoing-intent',
          component: OutgoingIntentView,
        },
        {
          path: 'outgoing-intents/:id',
          name: 'view-outgoing-intent',
          component: OutgoingIntentView,
          props: true,
        },
        {
            path: 'outgoing-intents/:id/edit',
            name: 'edit-outgoing-intent',
            component: OutgoingIntentView,
            props: true,
        },
          {
          path: 'outgoing-intents/:outgoingIntent/message-templates',
          name: 'message-templates',
          component: MessageTemplateView,
          props: true,
        },
        {
          path: 'outgoing-intents/:outgoingIntent/message-templates/add',
          name: 'add-message-template',
          component: MessageTemplateView,
          props: true,
        },
        {
          path: 'outgoing-intents/:outgoingIntent/message-templates/:id',
          name: 'view-message-template',
          component: MessageTemplateView,
          props: true,
        },
        {
          path: 'outgoing-intents/:outgoingIntent/message-templates/:id/edit',
          name: 'edit-message-template',
          component: MessageTemplateView,
          props: true,
        },
      {
          path: 'dynamic-attributes',
          name: 'dynamic-attributes',
          component: DynamicAttribute
      },
      {
          path: 'dynamic-attributes/add',
          name: 'add-dynamic-attribute',
          component: DynamicAttribute,
      },
      {
          path: 'dynamic-attributes/:id',
          name: 'view-dynamic-attribute',
          component: DynamicAttribute,
          props: true,
      },
      {
          path: 'dynamic-attributes/:id/edit',
          name: 'edit-dynamic-attribute',
          component: DynamicAttribute,
          props: true,
      },
        {
          path: 'users',
          name: 'users',
          component: UserView,
        },
        {
          path: 'users/add',
          name: 'add-user',
          component: UserView,
        },
        {
          path: 'users/:id',
          name: 'view-user',
          component: UserView,
          props: true,
        },
        {
          path: 'users/:id/edit',
          name: 'edit-user',
          component: UserView,
          props: true,
        },
        {
          path: 'requests',
          name: 'requests',
          component: RequestView,
        },
        {
          path: 'requests/:id',
          name: 'view-request',
          component: RequestView,
          props: true,
        },
        {
          path: 'global-contexts',
          name: 'global-contexts',
          component: GlobalContextView,
        },
        {
          path: 'global-contexts/add',
          name: 'add-global-context',
          component: GlobalContextView,
        },
        {
          path: 'global-contexts/:id',
          name: 'view-global-context',
          component: GlobalContextView,
          props: true,
        },
        {
          path: 'global-contexts/:id/edit',
          name: 'edit-global-context',
          component: GlobalContextView,
          props: true,
        },
        {
          path: 'warnings',
          name: 'warnings',
          component: WarningView,
        },
        {
          path: 'warnings/:id',
          name: 'view-warning',
          component: WarningView,
          props: true,
        },
        {
          path: 'demo',
          name: 'webchat-demo',
          component: WebchatDemo,
        }
      ],
    },
  ],
});

export default router;
