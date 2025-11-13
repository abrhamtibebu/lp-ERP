<template>
  <div class="space-y-8">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-3xl font-bold text-gray-900">Reports & Analytics</h2>
        <p class="text-gray-500 mt-1">Comprehensive business insights and analytics</p>
        <p v-if="lastUpdated" class="text-xs text-gray-400 mt-1">
          Last updated: {{ formatDateTime(lastUpdated) }}
          <span v-if="isAutoRefresh" class="ml-2 text-green-600">● Auto-refresh active</span>
        </p>
      </div>
      <div class="flex gap-4 items-center">
        <div class="flex items-center gap-2">
          <Label for="timePeriod">Time Period:</Label>
          <Select id="timePeriod" v-model="timePeriod" @change="onTimePeriodChange" class="w-40">
            <SelectItem value="7">Last 7 Days</SelectItem>
            <SelectItem value="30">Last 30 Days</SelectItem>
            <SelectItem value="90">Last 90 Days</SelectItem>
          </Select>
        </div>
        <div class="flex items-center gap-2">
          <input 
            type="checkbox" 
            id="autoRefresh" 
            v-model="isAutoRefresh" 
            class="rounded"
          />
          <Label for="autoRefresh" class="cursor-pointer">Auto-refresh (30s)</Label>
        </div>
        <Button variant="outline" size="sm" @click="refreshAll">
          <RefreshCw class="h-4 w-4 mr-2" :class="{ 'animate-spin': isRefreshing }" />
          Refresh
        </Button>
      </div>
    </div>
    
    <!-- WIP Stage Tracker Chart -->
    <Card>
      <CardHeader>
        <CardTitle>WIP Stage Tracker</CardTitle>
        <CardDescription>Batches by production stage</CardDescription>
      </CardHeader>
      <CardContent>
        <div v-if="wipTrackerLoading" class="text-center py-12 text-gray-500">Loading WIP data...</div>
        <div v-else-if="wipStageChartData" class="h-80">
          <BarChart :data="wipStageChartData" :options="chartOptions" />
        </div>
        <div v-else class="text-center py-12 text-gray-500">No WIP data available</div>
      </CardContent>
    </Card>

    <!-- Inventory Levels Charts -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Leather Inventory Chart -->
      <Card>
        <CardHeader>
          <CardTitle>Leather Inventory Levels</CardTitle>
          <CardDescription>Available stock by leather type</CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="inventoryLoading" class="text-center py-12 text-gray-500">Loading...</div>
          <div v-else-if="leatherInventoryChartData" class="h-80">
            <BarChart :data="leatherInventoryChartData" :options="chartOptions" />
          </div>
          <div v-else class="text-center py-12 text-gray-500">No leather inventory data</div>
        </CardContent>
      </Card>

      <!-- Accessories Inventory Chart -->
      <Card>
        <CardHeader>
          <CardTitle>Accessories Inventory Levels</CardTitle>
          <CardDescription>Available stock by accessory type</CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="inventoryLoading" class="text-center py-12 text-gray-500">Loading...</div>
          <div v-else-if="accessoriesInventoryChartData" class="h-80">
            <BarChart :data="accessoriesInventoryChartData" :options="chartOptions" />
          </div>
          <div v-else class="text-center py-12 text-gray-500">No accessories inventory data</div>
        </CardContent>
      </Card>
    </div>

    <!-- Inventory Distribution Pie Chart -->
    <Card>
      <CardHeader>
        <CardTitle>Inventory Distribution</CardTitle>
        <CardDescription>Leather vs Accessories stock comparison</CardDescription>
      </CardHeader>
      <CardContent>
        <div v-if="inventoryLoading" class="text-center py-12 text-gray-500">Loading...</div>
        <div v-else-if="inventoryDistributionChartData" class="h-80">
          <DoughnutChart :data="inventoryDistributionChartData" />
        </div>
        <div v-else class="text-center py-12 text-gray-500">No inventory data available</div>
      </CardContent>
    </Card>

    <!-- Finished Goods Aging Chart -->
    <Card>
      <CardHeader>
        <CardTitle>Finished Goods Aging Analysis</CardTitle>
        <CardDescription>Products in inventory for 30+ days</CardDescription>
      </CardHeader>
      <CardContent>
        <div v-if="agingLoading" class="text-center py-12 text-gray-500">Loading aging data...</div>
        <div v-else-if="agingChartData" class="h-80">
          <BarChart :data="agingChartData" :options="chartOptions" />
        </div>
        <div v-else class="text-center py-12 text-gray-500">No aging data available</div>
      </CardContent>
    </Card>

    <!-- Batch Progress by Stage -->
    <Card>
      <CardHeader>
        <CardTitle>Batch Progress by Stage</CardTitle>
        <CardDescription>WIP inventory distribution across production stages</CardDescription>
      </CardHeader>
      <CardContent>
        <div v-if="wipTrackerLoading" class="text-center py-12 text-gray-500">Loading...</div>
        <div v-else-if="batchProgressChartData" class="h-80">
          <LineChart :data="batchProgressChartData" :options="chartOptions" />
        </div>
        <div v-else class="text-center py-12 text-gray-500">No batch progress data</div>
      </CardContent>
    </Card>

    <!-- Time-Based Trends -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Inventory Trends Over Time -->
      <Card>
        <CardHeader>
          <CardTitle>Inventory Trends</CardTitle>
          <CardDescription>Inventory changes over the last {{ timePeriod }} days</CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="trendsLoading" class="text-center py-12 text-gray-500">Loading trends...</div>
          <div v-else-if="inventoryTrendsChartData" class="h-80">
            <LineChart :data="inventoryTrendsChartData" :options="trendChartOptions" />
          </div>
          <div v-else class="text-center py-12 text-gray-500">No trend data available</div>
        </CardContent>
      </Card>

      <!-- Order Trends Over Time -->
      <Card>
        <CardHeader>
          <CardTitle>Order Trends</CardTitle>
          <CardDescription>Orders placed over the last {{ timePeriod }} days</CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="trendsLoading" class="text-center py-12 text-gray-500">Loading trends...</div>
          <div v-else-if="orderTrendsChartData" class="h-80">
            <LineChart :data="orderTrendsChartData" :options="trendChartOptions" />
          </div>
          <div v-else class="text-center py-12 text-gray-500">No order trend data</div>
        </CardContent>
      </Card>
    </div>

    <!-- Production Trends -->
    <Card>
      <CardHeader>
        <CardTitle>Production Stage Trends</CardTitle>
        <CardDescription>Production stage movements over the last {{ timePeriod }} days</CardDescription>
      </CardHeader>
      <CardContent>
        <div v-if="trendsLoading" class="text-center py-12 text-gray-500">Loading production trends...</div>
        <div v-else-if="productionTrendsChartData" class="h-80">
          <LineChart :data="productionTrendsChartData" :options="trendChartOptions" />
        </div>
        <div v-else class="text-center py-12 text-gray-500">No production trend data</div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { RefreshCw } from 'lucide-vue-next';
import apiClient from '@/api/client';
import Card from '@/components/ui/Card.vue';
import CardHeader from '@/components/ui/CardHeader.vue';
import CardTitle from '@/components/ui/CardTitle.vue';
import CardDescription from '@/components/ui/CardDescription.vue';
import CardContent from '@/components/ui/CardContent.vue';
import BarChart from '@/components/charts/BarChart.vue';
import LineChart from '@/components/charts/LineChart.vue';
import DoughnutChart from '@/components/charts/DoughnutChart.vue';
import Button from '@/components/ui/Button.vue';
import Label from '@/components/ui/Label.vue';
import Select from '@/components/ui/Select.vue';
import SelectItem from '@/components/ui/SelectItem.vue';
import { chartColors } from '@/lib/chartTheme';

const wipTrackerData = ref([]);
const wipTrackerLoading = ref(false);
const inventoryData = ref({ leather: [], accessories: [] });
const inventoryLoading = ref(false);
const agingData = ref([]);
const agingLoading = ref(false);
const trendsData = ref({});
const trendsLoading = ref(false);
const productionTrendsData = ref({});
const timePeriod = ref('7');
const isAutoRefresh = ref(true);
const isRefreshing = ref(false);
const lastUpdated = ref(null);
const refreshInterval = ref(null);

// Chart options are now handled by the theme system in chart components
const chartOptions = {};
const trendChartOptions = {};

// WIP Stage Distribution Chart
const wipStageChartData = computed(() => {
  if (!wipTrackerData.value.length) return null;

  const stageCounts = {};
  wipTrackerData.value.forEach(batch => {
    const stageName = batch.currentStage?.name || batch.current_stage?.stage_name || 'Unknown';
    stageCounts[stageName] = (stageCounts[stageName] || 0) + 1;
  });

  return {
    labels: Object.keys(stageCounts),
    datasets: [{
      label: 'Batches',
      data: Object.values(stageCounts),
      backgroundColor: chartColors.info.main,
      borderColor: chartColors.info.main,
    }]
  };
});

// Leather Inventory Chart
const leatherInventoryChartData = computed(() => {
  if (!inventoryData.value.leather?.length) return null;

  const names = inventoryData.value.leather.map(item => item.leather_name);
  const available = inventoryData.value.leather.map(item => parseFloat(item.available) || 0);

  return {
    labels: names,
    datasets: [{
      label: 'Available (sqft)',
      data: available,
      backgroundColor: chartColors.primary.main,
      borderColor: chartColors.primary.main,
    }]
  };
});

// Accessories Inventory Chart
const accessoriesInventoryChartData = computed(() => {
  if (!inventoryData.value.accessories?.length) return null;

  const names = inventoryData.value.accessories.map(item => item.name);
  const available = inventoryData.value.accessories.map(item => parseFloat(item.available) || 0);

  return {
    labels: names,
    datasets: [{
      label: 'Available',
      data: available,
      backgroundColor: chartColors.success.main,
      borderColor: chartColors.success.main,
    }]
  };
});

// Inventory Distribution Chart
const inventoryDistributionChartData = computed(() => {
  const leatherTotal = inventoryData.value.leather?.reduce((sum, item) => sum + (parseFloat(item.available) || 0), 0) || 0;
  const accessoriesTotal = inventoryData.value.accessories?.reduce((sum, item) => sum + (parseFloat(item.available) || 0), 0) || 0;

  if (leatherTotal === 0 && accessoriesTotal === 0) return null;

  return {
    labels: ['Leather', 'Accessories'],
    datasets: [{
      data: [leatherTotal, accessoriesTotal],
      backgroundColor: [
        chartColors.primary.main,
        chartColors.success.main
      ],
      borderColor: '#ffffff',
    }]
  };
});

// Finished Goods Aging Chart
const agingChartData = computed(() => {
  if (!agingData.value.length) return null;

  const productGroups = {};
  agingData.value.forEach(item => {
    const productName = item.product?.product_name || 'Unknown';
    productGroups[productName] = (productGroups[productName] || 0) + 1;
  });

  return {
    labels: Object.keys(productGroups),
    datasets: [{
      label: 'Items Over 30 Days',
      data: Object.values(productGroups),
      backgroundColor: chartColors.warning.main,
      borderColor: chartColors.warning.main,
    }]
  };
});

// Batch Progress by Stage Chart
const batchProgressChartData = computed(() => {
  if (!wipTrackerData.value.length) return null;

  const stageQuantities = {};
  wipTrackerData.value.forEach(batch => {
    batch.wipInventories?.forEach(wip => {
      const stageName = wip.stage?.name || 'Unknown';
      stageQuantities[stageName] = (stageQuantities[stageName] || 0) + (wip.quantity || 0);
    });
  });

  if (Object.keys(stageQuantities).length === 0) return null;

  return {
    labels: Object.keys(stageQuantities),
    datasets: [{
      label: 'Quantity in Stage',
      data: Object.values(stageQuantities),
      borderColor: chartColors.purple.main,
      backgroundColor: chartColors.purple.main,
      fill: true,
    }]
  };
});

async function loadWipTracker() {
  try {
    wipTrackerLoading.value = true;
    const response = await apiClient.get('/reports/wip-tracker');
    wipTrackerData.value = response.data || [];
  } catch (error) {
    console.error('Error loading WIP tracker:', error);
  } finally {
    wipTrackerLoading.value = false;
  }
}

async function loadInventoryLevels() {
  try {
    inventoryLoading.value = true;
    const response = await apiClient.get('/reports/inventory-levels');
    inventoryData.value = response.data || { leather: [], accessories: [] };
  } catch (error) {
    console.error('Error loading inventory levels:', error);
  } finally {
    inventoryLoading.value = false;
  }
}

async function loadFinishedGoodsAging() {
  try {
    agingLoading.value = true;
    const response = await apiClient.get('/reports/finished-goods-aging');
    agingData.value = response.data || [];
  } catch (error) {
    console.error('Error loading finished goods aging:', error);
  } finally {
    agingLoading.value = false;
  }
}

async function loadInventoryTrends() {
  try {
    trendsLoading.value = true;
    const response = await apiClient.get(`/reports/inventory-trends?days=${timePeriod.value}`);
    trendsData.value = response.data || {};
  } catch (error) {
    console.error('Error loading inventory trends:', error);
  } finally {
    trendsLoading.value = false;
  }
}

async function loadProductionTrends() {
  try {
    const response = await apiClient.get(`/reports/production-trends?days=${timePeriod.value}`);
    productionTrendsData.value = response.data || {};
  } catch (error) {
    console.error('Error loading production trends:', error);
  }
}

// Inventory Trends Chart Data
const inventoryTrendsChartData = computed(() => {
  if (!trendsData.value.leather_trends || !trendsData.value.accessories_trends) return null;
  
  // Combine dates from both trends
  const allDates = new Set();
  trendsData.value.leather_trends.forEach(t => allDates.add(t.date));
  trendsData.value.accessories_trends.forEach(t => allDates.add(t.date));
  
  const sortedDates = Array.from(allDates).sort();
  
  const leatherData = sortedDates.map(date => {
    const trend = trendsData.value.leather_trends.find(t => t.date === date);
    return trend ? parseFloat(trend.net_change || 0) : 0;
  });
  
  const accessoriesData = sortedDates.map(date => {
    const trend = trendsData.value.accessories_trends.find(t => t.date === date);
    return trend ? parseFloat(trend.net_change || 0) : 0;
  });
  
  return {
    labels: sortedDates.map(d => new Date(d).toLocaleDateString()),
    datasets: [
      {
        label: 'Leather (sqft)',
        data: leatherData,
        borderColor: chartColors.primary.main,
        backgroundColor: chartColors.primary.main,
        fill: true,
      },
      {
        label: 'Accessories',
        data: accessoriesData,
        borderColor: chartColors.success.main,
        backgroundColor: chartColors.success.main,
        fill: true,
      }
    ]
  };
});

// Order Trends Chart Data
const orderTrendsChartData = computed(() => {
  if (!trendsData.value.order_trends || trendsData.value.order_trends.length === 0) return null;
  
  const dates = trendsData.value.order_trends.map(t => new Date(t.date).toLocaleDateString());
  const counts = trendsData.value.order_trends.map(t => t.count || 0);
  const quantities = trendsData.value.order_trends.map(t => t.total_quantity || 0);
  
  return {
    labels: dates,
    datasets: [
      {
        label: 'Orders Count',
        data: counts,
        borderColor: chartColors.info.main,
        backgroundColor: chartColors.info.main,
        fill: true,
        yAxisID: 'y'
      },
      {
        label: 'Total Quantity',
        data: quantities,
        borderColor: chartColors.purple.main,
        backgroundColor: chartColors.purple.main,
        fill: true,
        yAxisID: 'y1'
      }
    ]
  };
});

// Production Trends Chart Data
const productionTrendsChartData = computed(() => {
  if (!productionTrendsData.value.stage_trends || productionTrendsData.value.stage_trends.length === 0) return null;
  
  // Group by stage name
  const stages = [...new Set(productionTrendsData.value.stage_trends.map(t => t.stage_name))];
  const dates = [...new Set(productionTrendsData.value.stage_trends.map(t => t.date))].sort();
  
  const stageColors = [
    chartColors.info.main,
    chartColors.success.main,
    chartColors.warning.main,
    chartColors.purple.main,
    chartColors.orange.main,
    chartColors.teal.main,
    chartColors.secondary.main,
    chartColors.pink.main,
  ];
  
  const datasets = stages.map((stage, index) => {
    const color = stageColors[index % stageColors.length];
    
    const data = dates.map(date => {
      const trend = productionTrendsData.value.stage_trends.find(
        t => t.date === date && t.stage_name === stage
      );
      return trend ? (trend.movement_count || 0) : 0;
    });
    
    return {
      label: stage,
      data: data,
      borderColor: color,
      backgroundColor: color,
      fill: false,
    };
  });
  
  return {
    labels: dates.map(d => new Date(d).toLocaleDateString()),
    datasets: datasets
  };
});

const formatDateTime = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleString();
};

const onTimePeriodChange = () => {
  loadInventoryTrends();
  loadProductionTrends();
};

const refreshAll = async () => {
  isRefreshing.value = true;
  lastUpdated.value = new Date();
  await Promise.all([
    loadWipTracker(),
    loadInventoryLevels(),
    loadFinishedGoodsAging(),
    loadInventoryTrends(),
    loadProductionTrends(),
  ]);
  isRefreshing.value = false;
};

const startAutoRefresh = () => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value);
  }
  if (isAutoRefresh.value) {
    refreshInterval.value = setInterval(() => {
      if (isAutoRefresh.value) {
        refreshAll();
      }
    }, 30000); // 30 seconds
  }
};

const stopAutoRefresh = () => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value);
    refreshInterval.value = null;
  }
};

// Watch auto-refresh toggle
watch(isAutoRefresh, (enabled) => {
  if (enabled) {
    startAutoRefresh();
  } else {
    stopAutoRefresh();
  }
});

onMounted(() => {
  refreshAll();
  startAutoRefresh();
});

onUnmounted(() => {
  stopAutoRefresh();
});
</script>
