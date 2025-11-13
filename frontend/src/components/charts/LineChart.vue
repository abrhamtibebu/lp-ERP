<template>
  <div class="w-full h-full relative">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
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
import { createChartOptions, createGradientFromContext, withOpacity, chartColors, defaultColors } from '@/lib/chartTheme';

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
  },
  fill: {
    type: Boolean,
    default: true
  },
  showPoints: {
    type: Boolean,
    default: true
  },
  tension: {
    type: Number,
    default: 0.4
  }
});

const chartCanvas = ref(null);
let chartInstance = null;

const getDatasetDefaults = (dataset, index) => {
  const color = dataset.borderColor || dataset.backgroundColor || defaultColors[index % defaultColors.length];
  const borderColor = dataset.borderColor || color;
  
  return {
    borderColor: borderColor,
    borderWidth: 3,
    pointRadius: props.showPoints ? 4 : 0,
    pointHoverRadius: 6,
    pointBackgroundColor: borderColor,
    pointBorderColor: '#ffffff',
    pointBorderWidth: 2,
    pointHoverBackgroundColor: borderColor,
    pointHoverBorderColor: '#ffffff',
    pointHoverBorderWidth: 3,
    fill: props.fill,
    tension: props.tension,
    cubicInterpolationMode: 'monotone',
  };
};

const defaultOptions = createChartOptions('line', {
  elements: {
    point: {
      hoverRadius: 6,
      hoverBorderWidth: 3,
    },
    line: {
      borderJoinStyle: 'round',
      borderCapStyle: 'round',
    },
  },
  ...props.options,
});

onMounted(() => {
  if (chartCanvas.value && props.data) {
    initializeChart();
  }
});

const initializeChart = () => {
  if (!chartCanvas.value || !props.data) return;

  const ctx = chartCanvas.value.getContext('2d');
  const canvas = chartCanvas.value;
  
  // Create data with gradients
  const dataWithGradients = {
    ...props.data,
    datasets: props.data.datasets.map((dataset, index) => {
      const defaults = getDatasetDefaults(dataset, index);
      const color = defaults.borderColor;
      
      return {
        ...dataset,
        ...defaults,
        backgroundColor: props.fill 
          ? createGradientFromContext(ctx, withOpacity(color, 0.3), withOpacity(color, 0.05), canvas.width || 400, canvas.height || 300)
          : 'transparent',
      };
    }),
  };

  chartInstance = new ChartJS(chartCanvas.value, {
    type: 'line',
    data: dataWithGradients,
    options: defaultOptions,
  });
};

watch(() => props.data, (newData) => {
  if (!newData) return;
  
  if (!chartInstance && chartCanvas.value) {
    initializeChart();
  } else if (chartInstance && chartCanvas.value) {
    // Re-process data with gradients
    const ctx = chartCanvas.value.getContext('2d');
    const canvas = chartCanvas.value;
    const dataWithGradients = {
      ...newData,
      datasets: newData.datasets.map((dataset, index) => {
        const defaults = getDatasetDefaults(dataset, index);
        const color = defaults.borderColor;
        
        return {
          ...dataset,
          ...defaults,
          backgroundColor: props.fill 
            ? createGradientFromContext(ctx, withOpacity(color, 0.3), withOpacity(color, 0.05), canvas.width || 400, canvas.height || 300)
            : 'transparent',
        };
      }),
    };
    
    chartInstance.data = dataWithGradients;
    chartInstance.update('active');
  }
}, { deep: true });

watch(() => [props.fill, props.showPoints, props.tension], () => {
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




