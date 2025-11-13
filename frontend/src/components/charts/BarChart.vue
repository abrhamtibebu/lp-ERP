<template>
  <div class="w-full h-full relative">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  BarController,
  Title,
  Tooltip,
  Legend
} from 'chart.js';
import { createChartOptions, createGradientFromContext, withOpacity, defaultColors } from '@/lib/chartTheme';

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  BarController,
  Title,
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
  borderRadius: {
    type: Number,
    default: 8
  },
  useGradient: {
    type: Boolean,
    default: true
  }
});

const chartCanvas = ref(null);
let chartInstance = null;

const getDatasetDefaults = (dataset, index) => {
  const color = dataset.backgroundColor || dataset.borderColor || defaultColors[index % defaultColors.length];
  const borderColor = dataset.borderColor || color;
  
  return {
    backgroundColor: color,
    borderColor: borderColor,
    borderWidth: 0,
    borderRadius: props.borderRadius,
    borderSkipped: false,
  };
};

const defaultOptions = createChartOptions('bar', {
  elements: {
    bar: {
      borderRadius: props.borderRadius,
    },
  },
  ...props.options,
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
      const color = defaults.backgroundColor;
      
      return {
        ...dataset,
        ...defaults,
        backgroundColor: props.useGradient && dataset.backgroundColor
          ? (Array.isArray(dataset.backgroundColor)
              ? dataset.backgroundColor.map(c => {
                  const baseColor = typeof c === 'string' ? c : color;
                  return createGradientFromContext(ctx, baseColor, withOpacity(baseColor, 0.7), canvas.width || 400, canvas.height || 300, 'vertical');
                })
              : createGradientFromContext(ctx, color, withOpacity(color, 0.7), canvas.width || 400, canvas.height || 300, 'vertical'))
          : color,
      };
    }),
  };

  chartInstance = new ChartJS(chartCanvas.value, {
    type: 'bar',
    data: dataWithGradients,
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
  } else if (chartInstance && chartCanvas.value) {
    // Re-process data with gradients
    const ctx = chartCanvas.value.getContext('2d');
    const canvas = chartCanvas.value;
    const dataWithGradients = {
      ...newData,
      datasets: newData.datasets.map((dataset, index) => {
        const defaults = getDatasetDefaults(dataset, index);
        const color = defaults.backgroundColor;
        
        return {
          ...dataset,
          ...defaults,
          backgroundColor: props.useGradient && dataset.backgroundColor
            ? (Array.isArray(dataset.backgroundColor)
                ? dataset.backgroundColor.map(c => {
                    const baseColor = typeof c === 'string' ? c : color;
                    return createGradientFromContext(ctx, baseColor, withOpacity(baseColor, 0.7), canvas.width || 400, canvas.height || 300, 'vertical');
                  })
                : createGradientFromContext(ctx, color, withOpacity(color, 0.7), canvas.width || 400, canvas.height || 300, 'vertical'))
            : color,
        };
      }),
    };
    
    chartInstance.data = dataWithGradients;
    chartInstance.update('active');
  }
}, { deep: true });

watch(() => [props.borderRadius, props.useGradient], () => {
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




