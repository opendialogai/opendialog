<template>
  <div>
    <TopMenu :back-button="{url: '/admin', label: 'Dashboard'}"/>
    <div class="container-fluid mt-4">
      <template v-if="currentRouteName == 'view-dynamic-attribute'">
        <DynamicAttribute :id="id"/>
      </template>
      <template v-else-if="currentRouteName == 'edit-dynamic-attribute'">
        <EditDynamicAttribute :id="id"/>
      </template>
      <template v-else-if="currentRouteName == 'add-dynamic-attribute'">
        <AddDynamicAttribute/>
      </template>
      <template v-else>
        <DynamicAttributes/>
      </template>
    </div>
  </div>
</template>

<script>
import EditDynamicAttribute from '@/components/dynamic-attribute/EditDynamicAttribute'
import AddDynamicAttribute from '@/components/dynamic-attribute/AddDynamicAttribute'
import DynamicAttributes from '@/components/dynamic-attribute/DynamicAttributes'
import DynamicAttribute from '@/components/dynamic-attribute/DynamicAttribute'
import TopMenu from '@opendialogai/opendialog-design-system-pkg/src/components/Layout/TopMenu'

export default {
  name: 'dynamic-attribute',
  props: ['id'],
  components: {
    DynamicAttributes,
    AddDynamicAttribute,
    EditDynamicAttribute,
    DynamicAttribute,
    TopMenu
  },
  computed: {
    currentRouteName () {
      return this.$route.name
    },
  },
}
</script>
