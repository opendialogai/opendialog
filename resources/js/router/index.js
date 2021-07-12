import Vue from 'vue'
import VueRouter from 'vue-router'
// Containers
import DefaultContainer from '@/containers/DefaultContainer'

import Home from '@opendialogai/opendialog-design-system-pkg/src/components/Views/Home'
import WebchatSettingView from '@opendialogai/opendialog-design-system-pkg/src/components/Views/WebchatSettingView'
import MessageEditor from '@opendialogai/opendialog-design-system-pkg/src/components/Views/MessageEditor'
import ChatbotUsersView from '@opendialogai/opendialog-design-system-pkg/src/components/Views/ChatbotUsersView'
import UserView from '@opendialogai/opendialog-design-system-pkg/src/components/Views/UserView'
import RequestView from '@opendialogai/opendialog-design-system-pkg/src/components/Views/RequestView'
import GlobalContextView from '@opendialogai/opendialog-design-system-pkg/src/components/Views/GlobalContextView'
import WarningView from '@opendialogai/opendialog-design-system-pkg/src/components/Views/WarningView'
import WebchatDemo from '@opendialogai/opendialog-design-system-pkg/src/components/Views/WebchatDemo'
import ConversationLog from '@opendialogai/opendialog-design-system-pkg/src/components/Views/ConversationLog'
import DynamicAttribute from '@/views/DynamicAttribute'
import Scenarios
  from '@opendialogai/opendialog-design-system-pkg/src/components/ConversationBuilder/Scenarios/Scenarios'
import ConversationBuilder
  from '@opendialogai/opendialog-design-system-pkg/src/components/ConversationBuilder/Wrapper/ConversationBuilder'
import Interpreters
  from '@opendialogai/opendialog-design-system-pkg/src/components/Interpreters/Interpreters'
import ConfigureInterpreter
  from '@opendialogai/opendialog-design-system-pkg/src/components/Interpreters/ConfigureInterpreter'
import MapInterpreter
from '@opendialogai/opendialog-design-system-pkg/src/components/Interpreters/MapInterpreter'
import EditInterpreter
from '@opendialogai/opendialog-design-system-pkg/src/components/Interpreters/EditInterpreter'
import Actions
from '@opendialogai/opendialog-design-system-pkg/src/components/Actions/Actions'
import ConfigureAction
from '@opendialogai/opendialog-design-system-pkg/src/components/Actions/ConfigureAction'

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
          meta: {
            title: 'Dashboard',
          },
        },
        {
          path: 'conversation-builder/scenarios',
          name: 'scenarios',
          component: Scenarios,
          props: route => ({ newScenario: route.query.newScenario === "true" }),
          meta: {
              title: 'Scenarios',
          },
        },
        {
          path: 'conversation-builder/*',
          name: 'conversation-builder',
          component: ConversationBuilder,
          props: route => ({ newScenario: route.query.newScenario }),
          meta: {
              title: 'Conversation Designer',
          },
        },
        {
          path: 'actions',
          name: 'actions',
          component: Actions,
          meta: {
              title: 'Actions',
          },
        },
        {
          path: 'actions/configure/:id',
          name: 'configure-action',
          component: ConfigureAction,
          meta: {
              title: 'Conversation Designer',
          },
        },
        {
          path: 'interpreters',
          name: 'interpreters',
          component: Interpreters,
          meta: {
              title: 'Interpreters',
          },
        },
        {
          path: 'message-editor',
          name: 'message-editor',
          component: MessageEditor,
          meta: {
            title: 'Message Editor',
          },
        },
        {
          path: 'interpreters/configure/new',
          name: 'configure-interpreter',
          component: ConfigureInterpreter,
          meta: {
              title: 'Configure Interpreter',
          },
        },
        {
          path: 'interpreters/configure/:id',
          name: 'edit-interpreter',
          component: EditInterpreter,
          meta: {
              title: 'Configure Interpreter',
          },
        },
        {
          path: 'interpreters/mapping/:id',
          name: 'map-interpreter',
          component: MapInterpreter,
          meta: {
              title: 'Configure Interpreter',
          },
        },
        {
          path: 'webchat-setting',
          name: 'webchat-setting',
          component: WebchatSettingView,
          meta: {
              title: 'Interface Settings',
          },
        },
        {
          path: 'chatbot-users',
          name: 'chatbot-users',
          component: ChatbotUsersView,
          meta: {
              title: 'Chatbot Users',
          },
        },
        {
          path: 'chatbot-users/:id',
          name: 'view-chatbot-user',
          component: ChatbotUsersView,
          meta: {
              title: 'Chatbot Users',
          },
          props: true,
        },
        {
          path: 'chatbot-users/:id/conversation-log',
          name: 'conversation-log',
          component: ConversationLog,
          meta: {
              title: 'Conversation Log',
          },
          props: true,
        },
        {
          path: 'dynamic-attributes',
          name: 'dynamic-attributes',
          component: DynamicAttribute,
          meta: {
              title: 'Dynamic Attributes',
          },
        },
        {
          path: 'dynamic-attributes/add',
          name: 'add-dynamic-attribute',
          component: DynamicAttribute,
          meta: {
              title: 'Dynamic Attributes',
          },
        },
        {
          path: 'dynamic-attributes/:id',
          name: 'view-dynamic-attribute',
          component: DynamicAttribute,
          meta: {
              title: 'Dynamic Attributes',
          },
          props: true,
      },
      {
          path: 'dynamic-attributes/:id/edit',
          name: 'edit-dynamic-attribute',
          component: DynamicAttribute,
          meta: {
              title: 'Dynamic Attributes',
          },
          props: true,
      },
        {
          path: 'users',
          name: 'users',
          component: UserView,
          meta: {
              title: 'Users',
          },
        },
        {
          path: 'users/add',
          name: 'add-user',
          component: UserView,
          meta: {
              title: 'Add User',
          },
        },
        {
          path: 'users/:id',
          name: 'view-user',
          component: UserView,
          meta: {
              title: 'Account',
          },
          props: true,
        },
        {
          path: 'users/:id/edit',
          name: 'edit-user',
          component: UserView,
          meta: {
              title: 'Account',
          },
          props: true,
        },
        {
          path: 'requests',
          name: 'requests',
          component: RequestView,
          meta: {
              title: 'Requests',
          },
        },
        {
          path: 'requests/:id',
          name: 'view-request',
          component: RequestView,
          meta: {
              title: 'Requests',
          },
          props: true,
        },
        {
          path: 'global-contexts',
          name: 'global-contexts',
          component: GlobalContextView,
          meta: {
              title: 'Global Contexts',
          },
        },
        {
          path: 'global-contexts/add',
          name: 'add-global-context',
          component: GlobalContextView,
          meta: {
              title: 'Global Contexts',
          },
        },
        {
          path: 'global-contexts/:id',
          name: 'view-global-context',
          component: GlobalContextView,
          meta: {
              title: 'Global Contexts',
          },
          props: true,
        },
        {
          path: 'global-contexts/:id/edit',
          name: 'edit-global-context',
          component: GlobalContextView,
          meta: {
              title: 'Global Contexts',
          },
          props: true,
        },
        {
          path: 'warnings',
          name: 'warnings',
          component: WarningView,
          meta: {
              title: 'Warnings',
          },
        },
        {
          path: 'warnings/:id',
          name: 'view-warning',
          component: WarningView,
          meta: {
              title: 'Warnings',
          },
          props: true,
        },
        {
          path: 'demo',
          name: 'webchat-demo',
          component: WebchatDemo,
          meta: {
              title: 'Preview',
          },
        }
      ],
    },
  ],
});

export default router;
