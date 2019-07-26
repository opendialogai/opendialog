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
          <div slot="input" slot-scope="picker" style="min-width: 250px;">
            {{ picker.startDate | date }} - {{ picker.endDate | date }}
          </div>
        </date-range-picker>
      </b-col>
    </b-row>

    <b-row>
      <line-chart-card name="Users" :start-date="startDate" :end-date="endDate" endpoint="/stats/users" :width="2"></line-chart-card>
      <line-chart-card name="Users" :start-date="startDate" :end-date="endDate" endpoint="/stats/users" :width="2"></line-chart-card>
    </b-row>
    <b-row>
      <single-number-card name="Cost" :start-date="startDate" :end-date="endDate" endpoint="/stats/cost" :width="3"></single-number-card>
      <single-number-card name="Cost" :start-date="startDate" :end-date="endDate" endpoint="/stats/cost" :width="3"></single-number-card>
      <single-number-card name="Cost" :start-date="startDate" :end-date="endDate" endpoint="/stats/cost" :width="3"></single-number-card>
    </b-row>
  </div>
</template>

<script>
import DateRangePicker from 'vue2-daterange-picker';

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
  methods: {
    updateDateRange() {
      this.startDate = moment(this.dateRange.startDate).format('YYYY-MM-DD');
      this.endDate = moment(this.dateRange.endDate).format('YYYY-MM-DD');
    },
  }
};
</script>
