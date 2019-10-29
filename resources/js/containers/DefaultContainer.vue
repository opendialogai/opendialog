<template>
  <div class="app">
    <AppHeader fixed>
      <SidebarToggler class="d-lg-none" display="md" mobile />
      <b-link class="navbar-brand" to="/admin">
        <img class="navbar-brand-full" src="/images/logo.svg" width="89" height="25" alt="Opendialog Logo">
        <span class="navbar-brand-full">Opendialog</span>
        <img class="navbar-brand-minimized" src="/images/logo.svg" width="30" height="30" alt="Opendialog Logo">
      </b-link>
      <SidebarToggler class="d-md-down-none" display="lg" :defaultOpen=true />
      <b-navbar-nav class="d-md-down-none">
        <b-nav-item v-for="item in navigationItems" class="px-3" :key="item.url" :to="item.url">
          {{ item.title }}
        </b-nav-item>
      </b-navbar-nav>
      <b-navbar-nav class="ml-auto">
        <DefaultHeaderDropdownAccnt/>
      </b-navbar-nav>
    </AppHeader>
    <div class="app-body">
      <AppSidebar fixed>
        <SidebarHeader/>
        <SidebarForm/>
        <SidebarNav :navItems="nav"></SidebarNav>
        <SidebarFooter/>
        <SidebarMinimizer/>
      </AppSidebar>
      <main class="main">
        <div class="container-fluid mt-4">
          <router-view></router-view>
        </div>
      </main>
    </div>
    <TheFooter>
      <!--footer-->
      <div>
        <a href="https://opendialog.ai">OpenDialog</a>
        <span class="ml-1">&copy; 2019 Greenshoot Labs.</span>
      </div>
    </TheFooter>
  </div>
</template>

<script>
import { Header as AppHeader, SidebarToggler, Sidebar as AppSidebar, SidebarFooter, SidebarForm, SidebarHeader, SidebarMinimizer, SidebarNav, Aside as AppAside, Footer as TheFooter } from '@coreui/vue';
import DefaultHeaderDropdownAccnt from './DefaultHeaderDropdownAccnt';

export default {
  name: 'DefaultContainer',
  components: {
    AppHeader,
    AppSidebar,
    AppAside,
    TheFooter,
    DefaultHeaderDropdownAccnt,
    SidebarForm,
    SidebarFooter,
    SidebarToggler,
    SidebarHeader,
    SidebarNav,
    SidebarMinimizer,
  },
  data () {
    return {
      nav: [],
    };
  },
  computed: {
    navigationItems() {
      return window.NavigationItems;
    },
  },
  created() {
    this.buildSidebarMenu();
  },
  methods: {
    buildSidebarMenu() {
      this.asyncForEach(this.navigationItems, async (item) => {
        const navigationItem = {
          name: item.title,
          url: item.url,
          icon: item.icon,
        };

        if (item.children) {
          if (Array.isArray(item.children)) {
            navigationItem.children = item.children;
          } else {
            navigationItem.children = await this.getChildren(item.children);
          }
        }

<<<<<<< HEAD
      this.nav = [
        {
          name: 'Message Editor',
          url: '/admin/outgoing-intents',
          icon: 'icon-list',
        },
        {
          name: 'Chatbot Users',
          url: '/admin/chatbot-users',
          icon: 'icon-layers'
        },
        {
          name: 'Users',
          url: '/admin/users',
          icon: 'icon-people'
        },
        {
          name: 'Webchat settings',
          url: '/admin/webchat-setting',
          icon: 'icon-settings',
          children: [
            {
              name: 'General',
              url: '/admin/webchat-setting/' + generalId,
            },
            {
              name: 'Colours',
              url: '/admin/webchat-setting/' + coloursId,
            },
            {
              name: 'Comments',
              url: '/admin/webchat-setting/' + commentsId,
            },
            {
              name: 'History',
              url: '/admin/webchat-setting/' + historyId,
            },
          ],
        },
        {
          name: 'Conversations',
          url: '/admin/conversations',
          icon: 'icon-speech',
          children: conversations,
        },
        {
          name: 'Test Bot',
          url: '/admin/demo',
          icon: 'icon-control-play',
        },
      ];
    },
    async getConversations() {
      const promise = axios.get('/admin/api/conversation').then(
        (response) => {
          const conversations = [];

          response.data.data.forEach((conversation) => {
            conversations.push({
              name: conversation.name,
              url: '/admin/conversations/' + conversation.id,
            });
          });

          return conversations;
        },
      );

      return await Promise.resolve(promise);
=======
        this.nav.push(navigationItem);
      });
>>>>>>> develop
    },
    async getChildren(url) {
      const promise = axios.get(url).then(
        (response) => {
          return response.data;
        },
      );

      return await Promise.resolve(promise);
    },
    async asyncForEach(array, callback) {
      for (let index = 0; index < array.length; index++) {
        await callback(array[index], index, array);
      }
    },
  },
};
</script>
