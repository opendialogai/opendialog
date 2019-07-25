<template>
  <b-col :sm="12 / width">
    <b-card no-body class="bg-primary">
      <template v-if="chartData">
        <b-card-body class="pb-0">
          <h4>{{ chartData.total }}</h4>
          <p class="mb-0">{{ name }}</p>
        </b-card-body>
        <LineChart class="px-3" :data="chartData" :chart-id="chartId" :height="104"></LineChart>
      </template>
      <template v-else>
        <b-card-body class="px-0 py-4">
          <div class="text-center w-10 py-5">
            <b-spinner />
          </div>
        </b-card-body>
      </template>
    </b-card>
  </b-col>
</template>

<script>
import shortid from 'shortid';

import LineChart from '@/components/charts/LineChart';

export default {
  name: 'line-chart',
  components: {
    LineChart,
  },
  props: {
    name: {
      type: String,
      required: true,
    },
    endpoint: {
      type: String,
      required: true,
    },
    startDate: {
      type: String,
      default: '',
    },
    endDate: {
      type: String,
      default: '',
    },
    width: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      chartData: null,
      chartId: shortid.generate(),
    };
  },
  computed: {
    query() {
      let query = '?';
      if (this.startDate) {
        query = query + 'startdate=' + this.startDate + '&';
      }
      if (this.endDate) {
        query = query + 'enddate=' + this.endDate;
      }
      return query;
    },
  },
  watch: {
    query() {
      this.fetchData();
    },
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      this.chartData = null;

      axios.get(this.endpoint + this.query).then(
        (response) => {
          this.chartData = response.data;
        },
      );
    },
  },
};
</script>
