<template>
  <div class="space-y-8">
    <div>
      <h2 class="text-3xl font-bold text-gray-900">Reports & Analytics</h2>
      <p class="text-gray-500 mt-1">Comprehensive business insights and analytics</p>
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
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import apiClient from '@/api/client';
import Card from '@/components/ui/Card.vue';
import CardHeader from '@/components/ui/CardHeader.vue';
import CardTitle from '@/components/ui/CardTitle.vue';
import CardDescription from '@/components/ui/CardDescription.vue';
import CardContent from '@/components/ui/CardContent.vue';
import BarChart from '@/components/charts/BarChart.vue';
import LineChart from '@/components/charts/LineChart.vue';
import DoughnutChart from '@/components/charts/DoughnutChart.vue';

const wipTrackerData = ref([]);
const wipTrackerLoading = ref(false);
const inventoryData = ref({ leather: [], accessories: [] });
const inventoryLoading = ref(false);
const agingData = ref([]);
const agingLoading = ref(false);

const chartOptions = {
  plugins: {
    legend: {
      display: true,
    }
  }
};

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
      backgroundColor: 'rgba(59, 130, 246, 0.6)',
      borderColor: 'rgb(59, 130, 246)',
      borderWidth: 1
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
      backgroundColor: 'rgba(99, 102, 241, 0.6)',
      borderColor: 'rgb(99, 102, 241)',
      borderWidth: 1
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
      backgroundColor: 'rgba(34, 197, 94, 0.6)',
      borderColor: 'rgb(34, 197, 94)',
      borderWidth: 1
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
        'rgba(99, 102, 241, 0.6)',
        'rgba(34, 197, 94, 0.6)'
      ],
      borderColor: [
        'rgb(99, 102, 241)',
        'rgb(34, 197, 94)'
      ],
      borderWidth: 2
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
      backgroundColor: 'rgba(239, 68, 68, 0.6)',
      borderColor: 'rgb(239, 68, 68)',
      borderWidth: 1
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
      borderColor: 'rgb(168, 85, 247)',
      backgroundColor: 'rgba(168, 85, 247, 0.1)',
      fill: true,
      tension: 0.4
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

onMounted(() => {
  loadWipTracker();
  loadInventoryLevels();
  loadFinishedGoodsAging();
});
</script>
