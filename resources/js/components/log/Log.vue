<template>
  <div v-if="log">
    <h2 class="mb-3">Log</h2>

    <b-card header="General">
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Request ID</b-col>
        <b-col cols="10">{{ log.request_id }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">User ID</b-col>
        <b-col cols="10">{{ log.user_id }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Message</b-col>
        <b-col cols="10">{{ log.message }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Context</b-col>
        <b-col cols="10">
          <prism v-if="log.context.length !== 0" language="json" :code="toJson(log.context)"></prism>
        </b-col>
      </b-row>
      <b-row>
        <b-col class="font-weight-bold" cols="2">Created at</b-col>
        <b-col cols="10">{{ log.created_at }}</b-col>
      </b-row>
    </b-card>
  </div>
</template>

<script>
import Prism from 'vue-prismjs';
import 'prismjs/themes/prism.css';

export default {
  name: 'log',
  props: ['id'],
  components: {
    Prism,
  },
  data() {
    return {
      log: null,
    };
  },
  mounted() {
    axios.get('/admin/api/logs/' + this.id).then(
      (response) => {
        this.log = response.data.data;
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
