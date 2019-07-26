<template>
  <b-col :sm="12 / width">
    <b-card no-body>
      <template v-if="data">
        <b-card-body>
          <div class="text-value-sm text-info">{{ data.value }}</div>
          <div class="text-muted text-uppercase font-weight-bold">{{ name }}</div>
        </b-card-body>
      </template>
      <template v-else>
        <b-card-body class="px-0 py-1">
          <div class="text-center w-10 py-4">
            <b-spinner />
          </div>
        </b-card-body>
      </template>
    </b-card>
  </b-col>
</template>

<script>
export default {
  name: 'single-number',
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
      data: null,
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
      this.data = null;

      axios.get(this.endpoint + this.query).then(
        (response) => {
          this.data = response.data;
        },
      );
    },
  },
};
</script>
