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
import Request from '@/views/Request';
import GlobalContext from '@/views/GlobalContext';
import Warning from '@/views/Warning';
import WebchatDemo from '@/views/WebchatDemo';

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
            meta: {
                breadcrumbs: []
            }
        },
        {
          path: 'chatbot-users/:id',
          name: 'view-chatbot-user',
          component: ChatbotUsers,
          props: true,
            meta: {
                breadcrumbs: [{
                    name: "Back to users",
                    routename: "chatbot-users"
                }]
            }
        },
        {
          path: 'chatbot-users/:id/conversation-log',
          name: 'conversation-log',
          component: ConversationLog,
          props: true,
            meta: {
                breadcrumbs: [{
                    name: "Back to users",
                    routename: "chatbot-users"
                },{
                    name: "Back to user",
                    routename: "view-chatbot-user"
                }]
            }
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
            meta: {
                breadcrumbs: [{
                    name: "Back to conversations",
                    routename: "conversations"
                }]
            }
        },
        {
          path: 'conversations/:id',
          name: 'view-conversation',
          component: Conversation,
          props: true,
            meta: {
                breadcrumbs: [{
                    name: "Back to conversations",
                    routename: "conversations"
                }]
            }
        },
        {
          path: 'conversations/:id/edit',
          name: 'edit-conversation',
          component: Conversation,
          props: true,
            meta: {
                breadcrumbs: [{
                    name: "Back to conversations",
                    routename: "conversations"
                },{
                    name: "Back to conversation",
                    routename: "view-conversation"
                }]
            }
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
            meta: {
                breadcrumbs: [{
                    name: "Back to outgoing intents",
                    routename: "outgoing-intents"
                }]
            }
        },
        {
          path: 'outgoing-intents/:id',
          name: 'view-outgoing-intent',
          component: OutgoingIntent,
          props: true,
            meta: {
                breadcrumbs: [{
                    name: "Back to outgoing intents",
                    routename: "outgoing-intents"
                }]
            }
        },
        {
            path: 'outgoing-intents/:id/edit',
            name: 'edit-outgoing-intent',
            component: OutgoingIntent,
            props: true,
            meta: {
                breadcrumbs: [{
                    name: "Back to outgoing intents",
                    routename: "outgoing-intents"
                },{
                    name: "Back to outgoing intent",
                    routename: "view-outgoing-intent"
                }]
            }
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
            meta: {
                breadcrumbs: [{
                    name: "Back to outgoing intents",
                    routename: "outgoing-intents"
                }]
            }
        },
        {
          path: 'outgoing-intents/:outgoingIntent/message-templates/:id',
          name: 'view-message-template',
          component: MessageTemplate,
          props: true,
            meta: {
                breadcrumbs: [{
                    name: "Back to view outgoing intent",
                    routename: "view-outgoing-intent"
                }]
            }
        },
        {
          path: 'outgoing-intents/:outgoingIntent/message-templates/:id/edit',
          name: 'edit-message-template',
          component: MessageTemplate,
            props: true,
            meta: {
                breadcrumbs: [{
                    name: "Back to view outgoing intent",
                    routename: "view-outgoing-intent"
                }]
            }
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
            meta: {
                breadcrumbs: [{
                    name: "Back to users",
                    routename: "users"
                }]
            }
        },
        {
          path: 'users/:id',
          name: 'view-user',
          component: User,
          props: true,
            meta: {
                breadcrumbs: [{
                    name: "Back to users",
                    routename: "users"
                }]
            }
        },
        {
          path: 'users/:id/edit',
          name: 'edit-user',
          component: User,
          props: true,
            meta: {
                breadcrumbs: [{
                    name: "Back to users",
                    routename: "users"
                },{
                    name: "Back to user",
                    routename: "view-user"
                }]
            }
        },
        {
          path: 'requests',
          name: 'requests',
          component: Request,
        },
        {
          path: 'requests/:id',
          name: 'view-request',
          component: Request,
          props: true,
            meta: {
                breadcrumbs: [{
                    name: "Back to requests",
                    routename: "requests"
                }]
            }
        },
        {
          path: 'global-contexts',
          name: 'global-contexts',
          component: GlobalContext,
        },
        {
          path: 'global-contexts/add',
          name: 'add-global-context',
          component: GlobalContext,
            meta: {
                breadcrumbs: [{
                    name: "Back to global contexts",
                    routename: "global-contexts"
                }]
            }
        },
        {
          path: 'global-contexts/:id',
          name: 'view-global-context',
          component: GlobalContext,
          props: true,
            meta: {
                breadcrumbs: [{
                    name: "Back to global contexts",
                    routename: "global-contexts"
                }]
            }
        },
        {
          path: 'global-contexts/:id/edit',
          name: 'edit-global-context',
          component: GlobalContext,
          props: true,
            meta: {
                breadcrumbs: [{
                    name: "Back to global contexts",
                    routename: "add-global-context"
                },{
                    name: "Back to global context",
                    routename: "edit-global-context"
                }]
            }
        },
        {
          path: 'warnings',
          name: 'warnings',
          component: Warning,
        },
        {
          path: 'warnings/:id',
          name: 'view-warning',
          component: Warning,
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
