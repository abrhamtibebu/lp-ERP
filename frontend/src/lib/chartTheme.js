/**
 * Modern Chart Theme Configuration
 * Provides elegant, consistent styling for all charts across the application
 */

// Modern color palette with gradients
export const chartColors = {
  primary: {
    main: '#2563EB',
    light: '#3B82F6',
    dark: '#1D4ED8',
    gradient: ['#2563EB', '#3B82F6', '#60A5FA'],
  },
  secondary: {
    main: '#4A90E2',
    light: '#6BA3E8',
    dark: '#2E6CB8',
    gradient: ['#4A90E2', '#6BA3E8', '#87C0F0'],
  },
  success: {
    main: '#10B981',
    light: '#34D399',
    dark: '#059669',
    gradient: ['#10B981', '#34D399', '#6EE7B7'],
  },
  warning: {
    main: '#F59E0B',
    light: '#FBBF24',
    dark: '#D97706',
    gradient: ['#F59E0B', '#FBBF24', '#FCD34D'],
  },
  danger: {
    main: '#EF4444',
    light: '#F87171',
    dark: '#DC2626',
    gradient: ['#EF4444', '#F87171', '#FCA5A5'],
  },
  info: {
    main: '#3B82F6',
    light: '#60A5FA',
    dark: '#2563EB',
    gradient: ['#3B82F6', '#60A5FA', '#93C5FD'],
  },
  purple: {
    main: '#8B5CF6',
    light: '#A78BFA',
    dark: '#7C3AED',
    gradient: ['#8B5CF6', '#A78BFA', '#C4B5FD'],
  },
  teal: {
    main: '#14B8A6',
    light: '#5EEAD4',
    dark: '#0D9488',
    gradient: ['#14B8A6', '#5EEAD4', '#99F6E4'],
  },
  orange: {
    main: '#F97316',
    light: '#FB923C',
    dark: '#EA580C',
    gradient: ['#F97316', '#FB923C', '#FDBA74'],
  },
  pink: {
    main: '#EC4899',
    light: '#F472B6',
    dark: '#DB2777',
    gradient: ['#EC4899', '#F472B6', '#F9A8D4'],
  },
};

// Default color set for charts
export const defaultColors = [
  chartColors.primary.main,
  chartColors.secondary.main,
  chartColors.success.main,
  chartColors.warning.main,
  chartColors.info.main,
  chartColors.purple.main,
  chartColors.teal.main,
  chartColors.orange.main,
];

// Helper function to create gradient - use this in Chart.js plugins
export const createGradient = (chart, colorStart, colorEnd, datasetIndex = 0, direction = 'vertical') => {
  const ctx = chart.ctx;
  if (!ctx || !chart.chartArea) {
    return colorStart;
  }
  
  const chartArea = chart.chartArea;
  let gradient;
  
  if (direction === 'vertical') {
    gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
  } else {
    gradient = ctx.createLinearGradient(chartArea.left, 0, chartArea.right, 0);
  }
  
  gradient.addColorStop(0, colorStart);
  gradient.addColorStop(1, colorEnd);
  return gradient;
};

// Helper to create gradient from context directly (for initial setup)
export const createGradientFromContext = (ctx, colorStart, colorEnd, width = 400, height = 300, direction = 'vertical') => {
  if (!ctx) {
    return colorStart;
  }
  
  const gradient = direction === 'vertical'
    ? ctx.createLinearGradient(0, 0, 0, height)
    : ctx.createLinearGradient(0, 0, width, 0);
  
  gradient.addColorStop(0, colorStart);
  gradient.addColorStop(1, colorEnd);
  return gradient;
};

// Helper function to get color with opacity
export const withOpacity = (color, opacity) => {
  // Convert hex to rgba
  const hex = color.replace('#', '');
  const r = parseInt(hex.substring(0, 2), 16);
  const g = parseInt(hex.substring(2, 4), 16);
  const b = parseInt(hex.substring(4, 6), 16);
  return `rgba(${r}, ${g}, ${b}, ${opacity})`;
};

// Detect if dark mode is active
export const isDarkMode = () => {
  if (typeof window === 'undefined') return false;
  return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
};

// Modern chart options factory
export const createChartOptions = (type = 'line', customOptions = {}) => {
  const darkMode = isDarkMode();
  const textColor = darkMode ? '#E5E7EB' : '#374151';
  const gridColor = darkMode ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
  const borderColor = darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

  const baseOptions = {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
      duration: 1200,
      easing: 'easeInOutQuart',
    },
    interaction: {
      mode: 'index',
      intersect: false,
    },
    plugins: {
      legend: {
        display: true,
        position: 'top',
        labels: {
          color: textColor,
          font: {
            family: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
            size: 12,
            weight: 500,
          },
          padding: 15,
          usePointStyle: true,
          pointStyle: 'circle',
          boxWidth: 12,
          boxHeight: 12,
        },
      },
      tooltip: {
        enabled: true,
        backgroundColor: darkMode ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
        titleColor: textColor,
        bodyColor: textColor,
        borderColor: borderColor,
        borderWidth: 1,
        padding: 12,
        cornerRadius: 8,
        displayColors: true,
        titleFont: {
          family: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
          size: 13,
          weight: 600,
        },
        bodyFont: {
          family: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
          size: 12,
          weight: 400,
        },
        boxPadding: 6,
        usePointStyle: true,
        callbacks: {
          label: function(context) {
            let label = context.dataset.label || '';
            if (label) {
              label += ': ';
            }
            if (context.parsed.y !== null) {
              label += new Intl.NumberFormat('en-US').format(context.parsed.y);
            } else if (context.parsed !== null) {
              label += new Intl.NumberFormat('en-US').format(context.parsed);
            }
            return label;
          },
        },
      },
    },
  };

  // Type-specific options
  if (type === 'line' || type === 'bar') {
    baseOptions.scales = {
      x: {
        display: true,
        grid: {
          display: true,
          color: gridColor,
          drawBorder: false,
          lineWidth: 1,
        },
        ticks: {
          color: darkMode ? 'rgba(229, 231, 235, 0.7)' : 'rgba(107, 114, 128, 0.7)',
          font: {
            family: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
            size: 11,
            weight: 400,
          },
          padding: 8,
        },
        border: {
          display: false,
        },
      },
      y: {
        display: true,
        beginAtZero: true,
        grid: {
          display: true,
          color: gridColor,
          drawBorder: false,
          lineWidth: 1,
        },
        ticks: {
          color: darkMode ? 'rgba(229, 231, 235, 0.7)' : 'rgba(107, 114, 128, 0.7)',
          font: {
            family: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
            size: 11,
            weight: 400,
          },
          padding: 8,
          callback: function(value) {
            return new Intl.NumberFormat('en-US', {
              notation: 'compact',
              maximumFractionDigits: 1,
            }).format(value);
          },
        },
        border: {
          display: false,
        },
      },
    };
  }

  // Merge with custom options
  return deepMerge(baseOptions, customOptions);
};

// Pie/Doughnut specific options
export const createPieChartOptions = (customOptions = {}) => {
  const darkMode = isDarkMode();
  const textColor = darkMode ? '#E5E7EB' : '#374151';

  const baseOptions = {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
      animateRotate: true,
      animateScale: true,
      duration: 1200,
      easing: 'easeInOutQuart',
    },
    plugins: {
      legend: {
        display: true,
        position: 'right',
        labels: {
          color: textColor,
          font: {
            family: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
            size: 12,
            weight: 500,
          },
          padding: 15,
          usePointStyle: true,
          pointStyle: 'circle',
          boxWidth: 12,
          boxHeight: 12,
        },
      },
      tooltip: {
        enabled: true,
        backgroundColor: darkMode ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
        titleColor: textColor,
        bodyColor: textColor,
        borderColor: darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
        borderWidth: 1,
        padding: 12,
        cornerRadius: 8,
        displayColors: true,
        titleFont: {
          family: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
          size: 13,
          weight: 600,
        },
        bodyFont: {
          family: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
          size: 12,
          weight: 400,
        },
        boxPadding: 6,
        usePointStyle: true,
        callbacks: {
          label: function(context) {
            const label = context.label || '';
            const value = context.parsed || 0;
            const total = context.dataset.data.reduce((a, b) => a + b, 0);
            const percentage = ((value / total) * 100).toFixed(1);
            return `${label}: ${new Intl.NumberFormat('en-US').format(value)} (${percentage}%)`;
          },
        },
      },
    },
  };

  return deepMerge(baseOptions, customOptions);
};

// Deep merge utility
function deepMerge(target, source) {
  const output = { ...target };
  if (isObject(target) && isObject(source)) {
    Object.keys(source).forEach(key => {
      if (isObject(source[key])) {
        if (!(key in target)) {
          Object.assign(output, { [key]: source[key] });
        } else {
          output[key] = deepMerge(target[key], source[key]);
        }
      } else {
        Object.assign(output, { [key]: source[key] });
      }
    });
  }
  return output;
}

function isObject(item) {
  return item && typeof item === 'object' && !Array.isArray(item);
}

// Export default theme
export default {
  colors: chartColors,
  defaultColors,
  createGradient,
  withOpacity,
  createChartOptions,
  createPieChartOptions,
  isDarkMode,
};

