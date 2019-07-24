<script>
import { Line } from 'vue-chartjs';
import { CustomTooltips } from '@coreui/coreui-plugin-chartjs-custom-tooltips';
import { getStyle } from '@coreui/coreui/dist/js/coreui-utilities';

export default {
  extends: Line,
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  mounted() {
    const brandPrimary = getStyle('--primary') || '#20a8d8'

    this.renderChart({
      labels: this.data.labels,
      datasets: [
        {
          backgroundColor: brandPrimary,
          borderColor: 'rgba(255,255,255,.55)',
          data: this.data.values,
        },
      ],
    }, {
      tooltips: {
        enabled: false,
        custom: CustomTooltips
      },
      maintainAspectRatio: false,
      legend: {
        display: false
      },
      scales: {
        xAxes: [
          {
            gridLines: {
              color: 'transparent',
              zeroLineColor: 'transparent',
            },
            ticks: {
              fontSize: 2,
              fontColor: 'transparent',
            },
          },
        ],
        yAxes: [
          {
            display: false,
            ticks: {
              display: false,
              min: Math.min.apply(Math, this.data.values) - 5,
              max: Math.max.apply(Math, this.data.values) + 5
            },
          },
        ],
      },
      elements: {
        line: {
          borderWidth: 1,
        },
        point: {
          radius: 4,
          hitRadius: 10,
          hoverRadius: 4,
        },
      },
    });
  },
};
</script>
