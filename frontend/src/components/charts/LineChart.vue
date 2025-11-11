<template>
  <div class="w-full">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  LineController,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  LineController,
  Title,
  Tooltip,
  Legend,
  Filler
);

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  options: {
    type: Object,
    default: () => ({})
  }
});

const chartCanvas = ref(null);
let chartInstance = null;

const defaultOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
    },
    tooltip: {
      mode: 'index',
      intersect: false,
    }
  },
  scales: {
    x: {
      display: true,
      grid: {
        display: false
      }
    },
    y: {
      display: true,
      beginAtZero: true,
      grid: {
        color: 'rgba(0, 0, 0, 0.05)'
      }
    }
  }
};

onMounted(() => {
  if (chartCanvas.value && props.data) {
    chartInstance = new ChartJS(chartCanvas.value, {
      type: 'line',
      data: props.data,
      options: { ...defaultOptions, ...props.options }
    });
  }
});

watch(() => props.data, (newData) => {
  if (!newData) return;
  
  if (!chartInstance && chartCanvas.value) {
    // Create chart if it doesn't exist yet
    chartInstance = new ChartJS(chartCanvas.value, {
      type: 'line',
      data: newData,
      options: { ...defaultOptions, ...props.options }
    });
  } else if (chartInstance) {
    // Update existing chart
    chartInstance.data = newData;
    chartInstance.update();
  }
}, { deep: true });

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy();
  }
});
</script>




