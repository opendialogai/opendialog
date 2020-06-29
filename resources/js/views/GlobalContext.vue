<template>
  <div>
    <Breadcrumbs />
    <template v-if="currentRouteName == 'view-global-context'">
      <GlobalContext :id="id" />
    </template>
    <template v-else-if="currentRouteName == 'edit-global-context'">
      <EditGlobalContext :id="id" />
    </template>
    <template v-else-if="currentRouteName == 'add-global-context'">
      <AddGlobalContext />
    </template>
    <template v-else>
      <GlobalContexts />
    </template>
  </div>
</template>

<script>
import AddGlobalContext from '@/components/global-context/AddGlobalContext';
import GlobalContext from '@/components/global-context/GlobalContext';
import GlobalContexts from '@/components/global-context/GlobalContexts';
import EditGlobalContext from '@/components/global-context/EditGlobalContext';
import Breadcrumbs from "@/components/breadcrumbs/Breadcrumbs";

export default {
  name: 'global-context',
  props: ['id'],
  components: {
    Breadcrumbs,
    AddGlobalContext,
    GlobalContext,
    GlobalContexts,
    EditGlobalContext,
  },
  computed: {
    currentRouteName() {
      return this.$route.name;
    },
  },
};
</script>
