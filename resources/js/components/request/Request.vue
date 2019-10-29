<template>
  <div v-if="requestLog">
    <h2 class="mb-3">Request</h2>

    <b-card header="Request log">
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Request id</b-col>
        <b-col cols="10">{{ requestLog.request_id }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Url</b-col>
        <b-col cols="10">{{ requestLog.url }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Query params</b-col>
        <b-col cols="10">{{ requestLog.query_params }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Method</b-col>
        <b-col cols="10">{{ requestLog.method }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Source ip</b-col>
        <b-col cols="10">{{ requestLog.source_ip }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Time</b-col>
        <b-col cols="10">{{ requestLog.microtime }}</b-col>
      </b-row>
      <b-row>
        <b-col class="font-weight-bold" cols="2">Raw request</b-col>
        <b-col cols="10">
          <prism language="json" :code="jsonPretty(requestLog.raw_request)"></prism>
        </b-col>
      </b-row>
    </b-card>

    <b-card header="Response log" v-if="requestLog.response_log">
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Request id</b-col>
        <b-col cols="10">{{ requestLog.response_log.request_id }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Http status</b-col>
        <b-col cols="10">{{ requestLog.response_log.http_status }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Headers</b-col>
        <b-col cols="10"><pre>{{ requestLog.response_log.headers }}</pre></b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Raw response</b-col>
        <b-col cols="10">
          <prism language="json" :code="jsonPretty(requestLog.response_log.raw_response)"></prism>
        </b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Request length</b-col>
        <b-col cols="10">{{ requestLog.response_log.request_length }}</b-col>
      </b-row>
      <b-row>
        <b-col class="font-weight-bold" cols="2">Memory usage</b-col>
        <b-col cols="10">{{ requestLog.response_log.memory_usage }}</b-col>
      </b-row>
    </b-card>
  </div>
</template>

<script>
import Prism from 'vue-prismjs';
import 'prismjs/themes/prism.css';

export default {
  name: 'request',
  props: ['id'],
  components: {
    Prism,
  },
  data() {
    return {
      requestLog: null,
    };
  },
  watch: {
    '$route' () {
      this.fetchRequest();
    },
  },
  mounted() {
    this.fetchRequest();
  },
  methods: {
    fetchRequest() {
      this.request = null;

      axios.get('/admin/api/requests/' + this.id).then(
        (response) => {
          this.requestLog = response.data.data;
        },
      );
    },
    jsonPretty(jsonString) {
      return JSON.stringify(JSON.parse(jsonString), null, 2);
    },
  },
};
</script>
