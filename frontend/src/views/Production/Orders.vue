<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#8B4513]">Orders</h1>
      </div>
      <Button @click="dialogOpen = true" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
        <Plus class="h-4 w-4 mr-2" />
        Create New Order
      </Button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-gray-900">{{ stats.total || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">All time</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-[#8B4513]">{{ stats.in_production || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">Active batches</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-orange-600">{{ stats.pending || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">Awaiting start</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-green-600">{{ stats.completed || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">This month</div>
        </CardContent>
      </Card>
    </div>

    <!-- Order List -->
    <div class="space-y-4">
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-900">Order List</h2>
        <div class="relative w-64">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
          <Input
            v-model="searchQuery"
            placeholder="Search orders..."
            class="pl-10"
          />
        </div>
      </div>

      <Card>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b bg-gray-50">
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch ID</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ getOrderId(order.id) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ order.product?.product_name || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge variant="secondary" class="bg-gray-100 text-gray-700">
                      {{ order.sku }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ order.quantity }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ order.color || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(order.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge v-if="getBatchId(order)" variant="secondary" class="bg-gray-100 text-gray-700">
                      {{ getBatchId(order) }}
                    </Badge>
                    <span v-else class="text-sm text-gray-400">-</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :class="getStatusClass(order)">
                      {{ getStatusLabel(order) }}
                    </Badge>
                  </td>
                </tr>
                <tr v-if="filteredOrders.length === 0">
                  <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                    No orders found
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Create/Edit Order Dialog -->
    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Order' : 'Create New Order'">
      <form @submit.prevent="saveOrder" class="space-y-4">
        <div class="space-y-2">
          <Label for="product_id">Product</Label>
          <Select v-model="form.product_id">
            <SelectItem value="">Select a product</SelectItem>
            <SelectItem v-for="product in products" :key="product.id" :value="String(product.id)">
              {{ product.product_name }} - {{ product.color }}
            </SelectItem>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="quantity">Quantity</Label>
          <Input id="quantity" v-model.number="form.quantity" type="number" required min="1" />
        </div>
        <div class="space-y-2">
          <Label for="color">Color</Label>
          <Input id="color" v-model="form.color" />
        </div>
        <div class="space-y-2">
          <Label for="sku">SKU</Label>
          <Input id="sku" v-model="form.sku" required />
        </div>
        <div class="space-y-2">
          <Label for="notes">Notes</Label>
          <Input id="notes" v-model="form.notes" />
        </div>
      </form>
      <template #footer>
        <Button type="button" variant="outline" @click="dialogOpen = false">Cancel</Button>
        <Button type="button" @click="saveOrder" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">Save</Button>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Plus, Search } from 'lucide-vue-next';
import apiClient from '@/api/client';
import Card from '@/components/ui/Card.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Button from '@/components/ui/Button.vue';
import Badge from '@/components/ui/Badge.vue';
import Dialog from '@/components/ui/Dialog.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Select from '@/components/ui/Select.vue';
import SelectItem from '@/components/ui/SelectItem.vue';

const orders = ref([]);
const products = ref([]);
const stats = ref({
  total: 0,
  in_production: 0,
  pending: 0,
  completed: 0,
});
const dialogOpen = ref(false);
const isEditing = ref(false);
const searchQuery = ref('');

const form = ref({
  id: null,
  product_id: '',
  quantity: 0,
  color: '',
  sku: '',
  notes: '',
});

const filteredOrders = computed(() => {
  if (!searchQuery.value) return orders.value;
  
  const query = searchQuery.value.toLowerCase();
  return orders.value.filter(order => {
    const orderId = getOrderId(order.id).toLowerCase();
    const productName = (order.product?.product_name || '').toLowerCase();
    const sku = (order.sku || '').toLowerCase();
    const batchId = (getBatchId(order) || '').toLowerCase();
    
    return orderId.includes(query) ||
           productName.includes(query) ||
           sku.includes(query) ||
           batchId.includes(query);
  });
});

const getOrderId = (id) => {
  return `ORD-${String(id).padStart(4, '0')}`;
};

const getBatchId = (order) => {
  if (order.batches && order.batches.length > 0) {
    return order.batches[0].batch_id || `BATCH-${order.batches[0].id}`;
  }
  return null;
};

const getStatusLabel = (order) => {
  // Check if order is in quality check stage
  if (order.batches && order.batches.length > 0) {
    const batch = order.batches[0];
    if (batch.currentStage && batch.currentStage.name === 'Quality Inspection') {
      return 'Quality Check';
    }
  }
  
  // Map status values
  const statusMap = {
    'pending': 'Pending',
    'in_production': 'In Production',
    'completed': 'Completed',
    'cancelled': 'Cancelled',
  };
  
  return statusMap[order.status] || order.status;
};

const getStatusClass = (order) => {
  const label = getStatusLabel(order);
  
  if (label === 'In Production') {
    return 'bg-blue-100 text-blue-800';
  } else if (label === 'Quality Check') {
    return 'bg-orange-100 text-orange-800';
  } else if (label === 'Pending') {
    return 'bg-gray-100 text-gray-700';
  } else if (label === 'Completed') {
    return 'bg-green-100 text-green-800';
  }
  
  return 'bg-gray-100 text-gray-700';
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toISOString().split('T')[0];
};

async function fetchOrders() {
  try {
    const response = await apiClient.get('/orders');
    orders.value = response.data.orders || response.data || [];
    stats.value = response.data.stats || {
      total: orders.value.length,
      in_production: orders.value.filter(o => o.status === 'in_production').length,
      pending: orders.value.filter(o => o.status === 'pending').length,
      completed: orders.value.filter(o => o.status === 'completed').length,
    };
  } catch (error) {
    console.error('Error fetching orders:', error);
    // Fallback: calculate stats from orders
    stats.value = {
      total: orders.value.length,
      in_production: orders.value.filter(o => o.status === 'in_production').length,
      pending: orders.value.filter(o => o.status === 'pending').length,
      completed: orders.value.filter(o => o.status === 'completed').length,
    };
  }
}

async function fetchProducts() {
  try {
    const response = await apiClient.get('/products');
    products.value = response.data || [];
  } catch (error) {
    console.error('Error fetching products:', error);
  }
}

function editOrder(order) {
  isEditing.value = true;
  form.value = {
    id: order.id,
    product_id: String(order.product_id),
    quantity: order.quantity,
    color: order.color || '',
    sku: order.sku,
    notes: order.notes || '',
  };
  dialogOpen.value = true;
}

async function saveOrder() {
  try {
    const payload = {
      ...form.value,
      product_id: parseInt(form.value.product_id),
    };
    delete payload.id;
    
    if (isEditing.value) {
      await apiClient.put(`/orders/${form.value.id}`, payload);
    } else {
      await apiClient.post('/orders', payload);
    }
    await fetchOrders();
    dialogOpen.value = false;
    resetForm();
  } catch (error) {
    console.error('Error saving order:', error);
    alert(error.response?.data?.message || 'Error saving order');
  }
}

function resetForm() {
  form.value = {
    id: null,
    product_id: '',
    quantity: 0,
    color: '',
    sku: '',
    notes: '',
  };
  isEditing.value = false;
}

onMounted(() => {
  fetchOrders();
  fetchProducts();
});
</script>
