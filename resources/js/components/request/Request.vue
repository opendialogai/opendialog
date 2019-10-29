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
      <b-row>
        <b-col class="font-weight-bold" cols="2">Time</b-col>
        <b-col cols="10">{{ requestLog.microtime }}</b-col>
      </b-row>
    </b-card>

    <b-card header="Response log" v-if="responseLog">
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Request id</b-col>
        <b-col cols="10">{{ responseLog.request_id }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Http status</b-col>
        <b-col cols="10">{{ responseLog.http_status }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Headers</b-col>
        <b-col cols="10">{{ responseLog.headers }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Raw response</b-col>
        <b-col cols="10">{{ responseLog.raw_response }}</b-col>
      </b-row>
      <b-row class="border-bottom mb-2 pb-2">
        <b-col class="font-weight-bold" cols="2">Request length</b-col>
        <b-col cols="10">{{ responseLog.request_length }}</b-col>
      </b-row>
      <b-row>
        <b-col class="font-weight-bold" cols="2">Memory usage</b-col>
        <b-col cols="10">{{ responseLog.memory_usage }}</b-col>
      </b-row>
    </b-card>
  </div>
</template>

<script>
export default {
  name: 'request',
  props: ['id'],
  data() {
    return {
      requestLog: null,
      responseLog: null,
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
          this.requestLog = response.data.requestLog;
          this.responseLog = response.data.responseLog;
        },
      );
    },
  },
};
</script>
