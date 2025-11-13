<template>
  <div class="w-full h-full relative">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import {
  Chart as ChartJS,
  ArcElement,
  PieController,
  Tooltip,
  Legend
} from 'chart.js';
import { createPieChartOptions, defaultColors, withOpacity } from '@/lib/chartTheme';

ChartJS.register(
  ArcElement,
  PieController,
  Tooltip,
  Legend
);

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  options: {
    type: Object,
    default: () => ({})
  },
  borderWidth: {
    type: Number,
    default: 2
  }
});

const chartCanvas = ref(null);
let chartInstance = null;

const processedData = computed(() => {
  if (!props.data || !props.data.datasets) return props.data;

  return {
    ...props.data,
    datasets: props.data.datasets.map(dataset => {
      const backgroundColor = dataset.backgroundColor || defaultColors;
      const borderColor = dataset.borderColor || '#ffffff';
      
      return {
        ...dataset,
        backgroundColor: Array.isArray(backgroundColor) 
          ? backgroundColor 
          : defaultColors.slice(0, (props.data.labels || []).length),
        borderColor: borderColor,
        borderWidth: props.borderWidth,
        hoverBorderWidth: props.borderWidth + 2,
        hoverOffset: 4,
      };
    }),
  };
});

const defaultOptions = createPieChartOptions({
  cutout: '0%',
  ...props.options,
});

const initializeChart = () => {
  if (!chartCanvas.value || !processedData.value) return;

  chartInstance = new ChartJS(chartCanvas.value, {
    type: 'pie',
    data: processedData.value,
    options: defaultOptions,
  });
};

onMounted(() => {
  if (chartCanvas.value && props.data) {
    initializeChart();
  }
});

watch(() => props.data, (newData) => {
  if (!newData) return;
  
  if (!chartInstance && chartCanvas.value) {
    initializeChart();
  } else if (chartInstance) {
    chartInstance.data = processedData.value;
    chartInstance.update('active');
  }
}, { deep: true });

watch(() => props.borderWidth, () => {
  if (chartInstance) {
    initializeChart();
  }
});

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy();
    chartInstance = null;
  }
});
</script>




