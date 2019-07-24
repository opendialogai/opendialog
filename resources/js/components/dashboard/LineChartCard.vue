<template>
  <b-col :sm="12 / width">
    <b-card no-body class="bg-primary">
      <template v-if="chartData">
        <b-card-body class="pb-0">
          <h4>{{ chartData.total }}</h4>
          <p class="mb-0">{{ name }}</p>
        </b-card-body>
        <LineChart class="px-3" :data="chartData" chart-id="ciao" :height="120"></LineChart>
      </template>
      <template v-else>
        <div class="text-center w-10 py-5">
          <b-spinner />
        </div>
      </template>
    </b-card>
  </b-col>
</template>

<script>
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
    };
  },
  mounted() {
    axios.get(this.endpoint).then(
      (response) => {
        this.chartData = response.data;
      },
    );
  },
};
</script>
