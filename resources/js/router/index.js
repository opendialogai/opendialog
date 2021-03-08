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
            meta: {
                breadcrumbs: []
            }
        },
        {
          path: 'chatbot-users/:id',
          name: 'view-chatbot-user',
          component: ChatbotUsersView,
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
            path: 'conversations/:id/message-templates',
            name: 'conversation-message-templates',
            props: true,
            component: Conversation,
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
          component: OutgoingIntentView,
        },
        {
          path: 'outgoing-intents/add',
          name: 'add-outgoing-intent',
          component: OutgoingIntentView,
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
          component: OutgoingIntentView,
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
            component: OutgoingIntentView,
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
          component: MessageTemplateView,
          props: true,
        },
        {
          path: 'outgoing-intents/:outgoingIntent/message-templates/add',
          name: 'add-message-template',
          component: MessageTemplateView,
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
          component: MessageTemplateView,
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
          component: MessageTemplateView,
            props: true,
            meta: {
                breadcrumbs: [{
                    name: "Back to view outgoing intent",
                    routename: "view-outgoing-intent"
                }]
            }
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
          meta: {
              breadcrumbs: [{
                  name: "Back to Dynamic attributes",
                  routename: "dynamic-attributes"
              }]
          }
      },
      {
          path: 'dynamic-attributes/:id',
          name: 'view-dynamic-attribute',
          component: DynamicAttribute,
          props: true,
          meta: {
              breadcrumbs: [{
                  name: "Back to Dynamic attributes",
                  routename: "dynamic-attributes"
              }]
          }
      },
      {
          path: 'dynamic-attributes/:id/edit',
          name: 'edit-dynamic-attribute',
          component: DynamicAttribute,
          props: true,
          meta: {
              breadcrumbs: [{
                  name: "Back to dynamic attributes",
                  routename: "dynamic-attributes"
              },{
                  name: "Back to dynamic attribute",
                  routename: "view-dynamic-attribute"
              }]
          }
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
          component: UserView,
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
          component: UserView,
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
          component: RequestView,
        },
        {
          path: 'requests/:id',
          name: 'view-request',
          component: RequestView,
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
          component: GlobalContextView,
        },
        {
          path: 'global-contexts/add',
          name: 'add-global-context',
          component: GlobalContextView,
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
          component: GlobalContextView,
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
          component: GlobalContextView,
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
