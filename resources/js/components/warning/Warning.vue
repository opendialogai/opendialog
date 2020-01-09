<template>
  <div v-if="warning">
    <h2 class="mb-3">Warning</h2>

    <b-card header="General">
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Request ID</b-col>
        <b-col cols="10">{{ warning.request_id }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">User ID</b-col>
        <b-col cols="10">{{ warning.user_id }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Message</b-col>
        <b-col cols="10">{{ warning.message }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Context</b-col>
        <b-col cols="10">
          <prism v-if="warning.context.length !== 0" language="json" :code="toJson(warning.context)"></prism>
        </b-col>
      </b-row>
      <b-row>
        <b-col class="font-weight-bold" cols="2">Created at</b-col>
        <b-col cols="10">{{ warning.created_at }}</b-col>
      </b-row>
    </b-card>
  </div>
</template>

<script>
import Prism from 'vue-prismjs';
import 'prismjs/themes/prism.css';

export default {
  name: 'warning',
  props: ['id'],
  components: {
    Prism,
  },
  data() {
    return {
      warning: null,
    };
  },
  mounted() {
    axios.get('/admin/api/warnings/' + this.id).then(
      (response) => {
        this.warning = response.data.data;
      },
    );
  },
  methods: {
    toJson(object) {
      return JSON.stringify(object, null, 2);
    },
  },
};
</script>
