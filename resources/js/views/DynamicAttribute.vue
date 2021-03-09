<template>
  <div>
    <TopMenu :back-button="{url: '/admin', label: 'Dashboard'}"/>

    <template v-if="currentRouteName == 'view-dynamic-attribute'">
      <DynamicAttribute :id="id" />
    </template>
    <template v-else-if="currentRouteName == 'edit-dynamic-attribute'">
      <EditDynamicAttribute :id="id" />
    </template>
    <template v-else-if="currentRouteName == 'add-dynamic-attribute'">
      <AddDynamicAttribute />
    </template>
    <template v-else>
      <DynamicAttributes />
    </template>
  </div>
</template>

<script>
import EditDynamicAttribute from "@/components/dynamic-attribute/EditDynamicAttribute";
import AddDynamicAttribute from "@/components/dynamic-attribute/AddDynamicAttribute";
import DynamicAttributes from "@/components/dynamic-attribute/DynamicAttributes";
import DynamicAttribute from "@/components/dynamic-attribute/DynamicAttribute";

export default {
  name: 'dynamic-attribute',
  props: ['id'],
  components: {
    DynamicAttributes,
    AddDynamicAttribute,
    EditDynamicAttribute,
    DynamicAttribute
  },
  computed: {
    currentRouteName() {
      return this.$route.name;
    },
  },
};
</script>
