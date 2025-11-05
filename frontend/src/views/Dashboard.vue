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
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
      <Card class="col-span-4">
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

      <Card class="col-span-3">
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
    </div>

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
import { ref, onMounted } from 'vue';
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

async function fetchDashboardData() {
  try {
    // Fetch orders
    const ordersResponse = await apiClient.get('/orders');
    const orders = ordersResponse.data || [];
    stats.value[0].value = orders.length.toString();
    recentOrders.value = orders.slice(0, 5).map(order => ({
      order_number: `#${order.id}`,
      product_name: order.product?.product_name || 'N/A',
      quantity: order.quantity,
      status: order.status,
      created_at: order.created_at,
    }));

    // Fetch batches
    const batchesResponse = await apiClient.get('/batches');
    const batches = batchesResponse.data || [];
    const activeBatchesList = batches.filter(b => b.status === 'in_progress' || b.status === 'in_production');
    stats.value[1].value = activeBatchesList.length.toString();
    activeBatches.value = activeBatchesList.slice(0, 5).map(batch => ({
      batch_id: batch.batch_id,
      product_name: batch.order?.product?.product_name || 'N/A',
      current_quantity: batch.current_quantity,
      total_quantity: batch.total_quantity,
      current_stage: batch.currentStage?.stage_name || batch.current_stage?.stage_name || 'N/A',
    }));
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
