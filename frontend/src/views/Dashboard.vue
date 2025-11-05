<template>
  <div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <Card v-for="stat in stats" :key="stat.title">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">{{ stat.title }}</CardTitle>
          <component :is="stat.icon" class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stat.value }}</div>
          <p v-if="stat.change" class="text-xs text-muted-foreground">
            <span :class="stat.change > 0 ? 'text-green-600' : 'text-red-600'">
              {{ stat.change > 0 ? '+' : '' }}{{ stat.change }}%
            </span>
            from last month
          </p>
        </CardContent>
      </Card>
    </div>

    <!-- Charts and Tables -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <!-- Orders Over Time Chart -->
      <Card class="col-span-2">
        <CardHeader>
          <CardTitle>Orders Over Time</CardTitle>
          <CardDescription>Order trends for the last 7 days</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="h-64">
            <LineChart v-if="ordersChartData" :data="ordersChartData" :options="chartOptions" />
          </div>
        </CardContent>
      </Card>

      <!-- Batch Status Distribution -->
      <Card>
        <CardHeader>
          <CardTitle>Batch Status</CardTitle>
          <CardDescription>Distribution by status</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="h-64">
            <DoughnutChart v-if="batchStatusChartData" :data="batchStatusChartData" />
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Additional Charts Row -->
    <div class="grid gap-4 md:grid-cols-2">
      <!-- Inventory Levels Chart -->
      <Card>
        <CardHeader>
          <CardTitle>Inventory Levels</CardTitle>
          <CardDescription>Leather and Accessories stock</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="h-64">
            <BarChart v-if="inventoryChartData" :data="inventoryChartData" :options="chartOptions" />
          </div>
        </CardContent>
      </Card>

      <!-- Order Status Distribution -->
      <Card>
        <CardHeader>
          <CardTitle>Order Status Distribution</CardTitle>
          <CardDescription>Orders by status</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="h-64">
            <PieChart v-if="orderStatusChartData" :data="orderStatusChartData" />
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Recent Orders Table -->
    <Card>
      <CardHeader>
        <CardTitle>Recent Orders</CardTitle>
        <CardDescription>Latest production orders</CardDescription>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Order #</TableHead>
              <TableHead>Product</TableHead>
              <TableHead>Quantity</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Date</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="order in recentOrders" :key="order.id">
              <TableCell class="font-medium">{{ order.order_number }}</TableCell>
              <TableCell>{{ order.product_name }}</TableCell>
              <TableCell>{{ order.quantity }}</TableCell>
              <TableCell>
                <Badge :variant="getStatusVariant(order.status)">
                  {{ order.status }}
                </Badge>
              </TableCell>
              <TableCell>{{ formatDate(order.created_at) }}</TableCell>
            </TableRow>
            <TableRow v-if="recentOrders.length === 0">
              <TableCell colspan="5" class="text-center text-muted-foreground py-8">
                No orders found
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>

    <!-- Active Batches List -->
    <Card>
      <CardHeader>
        <CardTitle>Active Batches</CardTitle>
        <CardDescription>Production in progress</CardDescription>
      </CardHeader>
      <CardContent>
        <div class="space-y-4">
          <div
            v-for="batch in activeBatches"
            :key="batch.id"
            class="flex items-center justify-between space-x-4"
          >
            <div class="flex-1 space-y-1">
              <p class="text-sm font-medium">{{ batch.batch_id }}</p>
              <p class="text-xs text-muted-foreground">{{ batch.product_name }}</p>
              <div class="w-full bg-secondary rounded-full h-2">
                <div
                  class="bg-primary h-2 rounded-full transition-all"
                  :style="{ width: `${(batch.current_quantity / batch.total_quantity) * 100}%` }"
                ></div>
              </div>
            </div>
            <Badge variant="outline">{{ batch.current_stage }}</Badge>
          </div>
          <p v-if="activeBatches.length === 0" class="text-center text-muted-foreground py-4">
            No active batches
          </p>
        </div>
      </CardContent>
    </Card>

    <!-- Quick Actions -->
    <Card>
      <CardHeader>
        <CardTitle>Quick Actions</CardTitle>
        <CardDescription>Common tasks and shortcuts</CardDescription>
      </CardHeader>
      <CardContent>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Button
            v-for="action in quickActions"
            :key="action.label"
            variant="outline"
            class="h-24 flex-col"
            @click="handleQuickAction(action)"
          >
            <component :is="action.icon" class="h-6 w-6 mb-2" />
            <span>{{ action.label }}</span>
          </Button>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { Plus, Package, ShoppingCart, TrendingUp, FileText } from 'lucide-vue-next';
import Card from '@/components/ui/Card.vue';
import CardHeader from '@/components/ui/CardHeader.vue';
import CardTitle from '@/components/ui/CardTitle.vue';
import CardDescription from '@/components/ui/CardDescription.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Badge from '@/components/ui/Badge.vue';
import Button from '@/components/ui/Button.vue';
import Table from '@/components/ui/Table.vue';
import TableHeader from '@/components/ui/TableHeader.vue';
import TableBody from '@/components/ui/TableBody.vue';
import TableRow from '@/components/ui/TableRow.vue';
import TableHead from '@/components/ui/TableHead.vue';
import TableCell from '@/components/ui/TableCell.vue';
import LineChart from '@/components/charts/LineChart.vue';
import BarChart from '@/components/charts/BarChart.vue';
import PieChart from '@/components/charts/PieChart.vue';
import DoughnutChart from '@/components/charts/DoughnutChart.vue';
import apiClient from '@/api/client';

const router = useRouter();

const stats = ref([
  { title: 'Total Orders', value: '0', icon: ShoppingCart, change: null },
  { title: 'Active Batches', value: '0', icon: Package, change: null },
  { title: 'Finished Goods', value: '0', icon: TrendingUp, change: 12 },
  { title: 'Pending Tasks', value: '0', icon: FileText, change: -5 },
]);

const recentOrders = ref([]);
const activeBatches = ref([]);
const allOrders = ref([]);
const allBatches = ref([]);
const inventoryData = ref({ leather: [], accessories: [] });

const chartOptions = {
  plugins: {
    legend: {
      display: true,
    }
  }
};

// Orders over time chart data
const ordersChartData = computed(() => {
  if (!allOrders.value.length) return null;

  const last7Days = Array.from({ length: 7 }, (_, i) => {
    const date = new Date();
    date.setDate(date.getDate() - (6 - i));
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  });

  const ordersByDate = {};
  last7Days.forEach(date => {
    ordersByDate[date] = 0;
  });

  allOrders.value.forEach(order => {
    const orderDate = new Date(order.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    if (ordersByDate.hasOwnProperty(orderDate)) {
      ordersByDate[orderDate]++;
    }
  });

  return {
    labels: last7Days,
    datasets: [{
      label: 'Orders',
      data: last7Days.map(date => ordersByDate[date]),
      borderColor: 'rgb(99, 102, 241)',
      backgroundColor: 'rgba(99, 102, 241, 0.1)',
      fill: true,
      tension: 0.4
    }]
  };
});

// Batch status distribution chart
const batchStatusChartData = computed(() => {
  if (!allBatches.value.length) return null;

  const statusCounts = {};
  allBatches.value.forEach(batch => {
    const status = batch.status || 'unknown';
    statusCounts[status] = (statusCounts[status] || 0) + 1;
  });

  const colors = {
    pending: 'rgb(234, 179, 8)',
    in_progress: 'rgb(59, 130, 246)',
    in_production: 'rgb(59, 130, 246)',
    completed: 'rgb(34, 197, 94)',
    on_hold: 'rgb(239, 68, 68)',
    rework: 'rgb(239, 68, 68)',
    unknown: 'rgb(156, 163, 175)'
  };

  return {
    labels: Object.keys(statusCounts),
    datasets: [{
      data: Object.values(statusCounts),
      backgroundColor: Object.keys(statusCounts).map(status => colors[status] || colors.unknown),
      borderWidth: 2,
      borderColor: '#fff'
    }]
  };
});

// Inventory levels chart
const inventoryChartData = computed(() => {
  if (!inventoryData.value.leather?.length && !inventoryData.value.accessories?.length) return null;

  const leatherNames = inventoryData.value.leather?.map(item => item.leather_name) || [];
  const leatherAvailable = inventoryData.value.leather?.map(item => parseFloat(item.available) || 0) || [];
  const accessoryNames = inventoryData.value.accessories?.map(item => item.name) || [];
  const accessoryAvailable = inventoryData.value.accessories?.map(item => parseFloat(item.available) || 0) || [];

  return {
    labels: [...leatherNames.slice(0, 5), ...accessoryNames.slice(0, 5)],
    datasets: [
      {
        label: 'Leather (sqft)',
        data: [...leatherAvailable.slice(0, 5), ...Array(accessoryNames.length > 5 ? 5 : accessoryNames.length).fill(0)],
        backgroundColor: 'rgba(99, 102, 241, 0.6)',
      },
      {
        label: 'Accessories',
        data: [...Array(leatherNames.length > 5 ? 5 : leatherNames.length).fill(0), ...accessoryAvailable.slice(0, 5)],
        backgroundColor: 'rgba(34, 197, 94, 0.6)',
      }
    ]
  };
});

// Order status distribution chart
const orderStatusChartData = computed(() => {
  if (!allOrders.value.length) return null;

  const statusCounts = {};
  allOrders.value.forEach(order => {
    const status = order.status || 'unknown';
    statusCounts[status] = (statusCounts[status] || 0) + 1;
  });

  const colors = {
    pending: 'rgb(234, 179, 8)',
    in_production: 'rgb(59, 130, 246)',
    completed: 'rgb(34, 197, 94)',
    cancelled: 'rgb(239, 68, 68)',
    unknown: 'rgb(156, 163, 175)'
  };

  return {
    labels: Object.keys(statusCounts),
    datasets: [{
      data: Object.values(statusCounts),
      backgroundColor: Object.keys(statusCounts).map(status => colors[status] || colors.unknown),
      borderWidth: 2,
      borderColor: '#fff'
    }]
  };
});

async function fetchDashboardData() {
  try {
    // Fetch orders
    const ordersResponse = await apiClient.get('/orders');
    const orders = ordersResponse.data || [];
    allOrders.value = orders;
    stats.value[0].value = orders.length.toString();
    recentOrders.value = orders.slice(0, 5).map(order => ({
      id: order.id,
      order_number: `#${order.id}`,
      product_name: order.product?.product_name || 'N/A',
      quantity: order.quantity,
      status: order.status,
      created_at: order.created_at,
    }));

    // Fetch batches
    const batchesResponse = await apiClient.get('/batches');
    const batches = batchesResponse.data || [];
    allBatches.value = batches;
    const activeBatchesList = batches.filter(b => b.status === 'in_progress' || b.status === 'in_production');
    stats.value[1].value = activeBatchesList.length.toString();
    activeBatches.value = activeBatchesList.slice(0, 5).map(batch => ({
      id: batch.id,
      batch_id: batch.batch_id,
      product_name: batch.order?.product?.product_name || 'N/A',
      current_quantity: batch.current_quantity,
      total_quantity: batch.total_quantity,
      current_stage: batch.currentStage?.name || batch.currentStage?.stage_name || batch.current_stage?.stage_name || 'N/A',
    }));

    // Fetch inventory levels for chart
    try {
      const inventoryResponse = await apiClient.get('/reports/inventory-levels');
      inventoryData.value = inventoryResponse.data || { leather: [], accessories: [] };
    } catch (error) {
      console.error('Error fetching inventory levels:', error);
    }
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
  }
}

const quickActions = [
  { label: 'New Order', icon: Plus, action: '/production/orders' },
  { label: 'Add Product', icon: Package, action: '/products' },
  { label: 'Add Employee', icon: Plus, action: '/employees' },
  { label: 'View Reports', icon: FileText, action: '/reports' },
];

const getStatusVariant = (status) => {
  const variants = {
    pending: 'secondary',
    'in_progress': 'default',
    completed: 'default',
  };
  return variants[status] || 'secondary';
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString();
};

const handleQuickAction = (action) => {
  router.push(action.action);
};

onMounted(() => {
  fetchDashboardData();
});
</script>
