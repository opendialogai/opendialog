<template>
  <div class="animated fadeIn">
    <b-row class="mb-4">
      <b-col>
        <date-range-picker
          :ranges="datePickerRanges"
          :auto-apply="true"
          v-model="dateRange"
          @update="updateDateRange"
        >
          <template v-slot:input="picker" style="min-width: 250px;">
            {{ picker.startDate | date }} - {{ picker.endDate | date }}
          </template>
        </date-range-picker>
      </b-col>
    </b-row>

    <template v-for="row in dashboardCards">
      <b-row>
        <template v-for="card in row">
          <template v-if="card.type == 'line-chart'">
            <line-chart-card :name="card.name" :start-date="startDate" :end-date="endDate" :endpoint="card.endpoint" :width="card.width"></line-chart-card>
          </template>
          <template v-else-if="card.type == 'single-number'">
            <single-number-card :name="card.name" :start-date="startDate" :end-date="endDate" :endpoint="card.endpoint" :width="card.width"></single-number-card>
          </template>
        </template>
      </b-row>
    </template>
  </div>
</template>

<script>
import DateRangePicker from 'vue2-daterange-picker';
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css';

import LineChartCard from '@/components/dashboard/LineChartCard';
import SingleNumberCard from '@/components/dashboard/SingleNumberCard';

const moment = require('moment');

export default {
  name: 'home',
  components: {
    DateRangePicker,
    LineChartCard,
    SingleNumberCard,
  },
  filters: {
    date: (value) => {
      if (value) {
        return moment(value).format('MMMM D, YYYY');
      }
    },
  },
  data() {
    return {
      dateRange: {
        startDate: moment().subtract(6, 'days'),
        endDate: moment(),
      },
      datePickerRanges: {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
      },
      startDate: moment().subtract(6, 'days').format('YYYY-MM-DD'),
      endDate: moment().format('YYYY-MM-DD'),
    };
  },
  computed: {
    dashboardCards() {
      return window.DashboardCards;
    },
  },
  created() {
    const dateRange = this.$cookies.get('filterDateRange');

    if (dateRange) {
      this.dateRange.startDate = dateRange.startDate;
      this.dateRange.endDate = dateRange.endDate;

      this.startDate = moment(dateRange.startDate).format('YYYY-MM-DD');
      this.endDate = moment(dateRange.endDate).format('YYYY-MM-DD');
    }
  },
  methods: {
    updateDateRange() {
      this.startDate = moment(this.dateRange.startDate).format('YYYY-MM-DD');
      this.endDate = moment(this.dateRange.endDate).format('YYYY-MM-DD');

      this.$cookies.set('filterDateRange', this.dateRange, 0);
    },
  }
};
</script>
