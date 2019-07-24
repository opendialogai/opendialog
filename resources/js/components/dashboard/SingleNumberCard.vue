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
        <div class="text-center w-10 py-3">
          <b-spinner />
        </div>
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
  mounted() {
    axios.get(this.endpoint).then(
      (response) => {
        this.data = response.data;
      },
    );
  },
};
</script>
