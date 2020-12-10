<template>
  <div class="animated fadeIn">
    <div class="alert alert-danger" role="alert" v-if="errorMessage">
      <span>{{ errorMessage }}</span>
      <button type="button" class="close" @click="errorMessage = ''">
        <span>&times;</span>
      </button>
    </div>

    <div class="alert alert-success" role="alert" v-if="successMessage">
      <span>{{ successMessage }}</span>
      <button type="button" class="close" @click="successMessage = ''">
        <span>&times;</span>
      </button>
    </div>

    <b-row class="mb-4">
      <b-col>
        <input ref="file" type="file" hidden multiple @change="specificationUpload"/>

        <b-btn v-if="!importingSpecification" variant="primary" @click="specificationExport">Specification download</b-btn>
        <b-btn v-if="!importingSpecification" variant="primary" class="mr-2" @click="specificationImport">Specification upload</b-btn>
        <b-btn v-if="importingSpecification" variant="primary">
          <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
          Uploading ...
        </b-btn>
      </b-col>
    </b-row>
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
      successMessage: '',
      errorMessage: '',
      importingSpecification: false,
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
    specificationImport() {
      this.$refs.file.click();
    },
    specificationUpload(event) {
      this.errorMessage = '';
      this.successMessage = '';
      this.importingSpecification = true;

      const formData = new FormData();

      event.target.files.forEach((file, i) => {
        formData.append('file' + (i + 1), file);
      });

      axios.post('/admin/api/specification-import', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      }).then((response) => {
        if (response.status == 200) {
          this.successMessage = 'Specification updated.';
        } else {
          this.errorMessage = 'Sorry, I wasn\'t able to update the specification.';
        }

        this.$refs.file.value = null;
        this.importingSpecification = false;
      }).catch(e => {
        if (e.response.data) {
          this.errorMessage = e.response.data.message;
        } else {
          this.errorMessage = 'Sorry, I wasn\'t able to update the specification.';
        }

        this.$refs.file.value = null;
        this.importingSpecification = false;
      });
    },
    specificationExport() {
      axios.get('/admin/api/specification-export', { responseType: 'blob' }).then(
        (response) => {
          const url = window.URL.createObjectURL(response.data);
          const link = document.createElement('a');
          link.href = url;
          link.setAttribute('download', `specification.zip`);
          document.body.appendChild(link);
          link.click();
        },
      );
    },
  }
};
</script>
