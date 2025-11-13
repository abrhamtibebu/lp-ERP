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
          <div class="overflow-y-auto max-h-[calc(100vh-300px)]">
            <table class="w-full table-auto">
              <thead class="sticky top-0 bg-gray-50 z-10">
                <tr class="border-b">
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch ID</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-gray-50">
                  <td class="px-4 py-4 text-sm font-medium text-gray-900">
                    {{ getOrderId(order.id) }}
                  </td>
                  <td class="px-4 py-4">
                    <Badge variant="outline" class="bg-blue-50 text-blue-700">
                      {{ getOrderTypeLabel(order.order_type) }}
                    </Badge>
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ order.product?.product_name || 'N/A' }}
                  </td>
                  <td class="px-4 py-4">
                    <Badge variant="secondary" class="bg-gray-100 text-gray-700">
                      {{ order.sku || 'N/A' }}
                    </Badge>
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ order.quantity }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ order.color || 'N/A' }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ formatDate(order.created_at) }}
                  </td>
                  <td class="px-4 py-4">
                    <Badge v-if="getBatchId(order)" variant="secondary" class="bg-gray-100 text-gray-700">
                      {{ getBatchId(order) }}
                    </Badge>
                    <span v-else class="text-sm text-gray-400">-</span>
                  </td>
                  <td class="px-4 py-4">
                    <Badge :class="getStatusClass(order)">
                      {{ getStatusLabel(order) }}
                    </Badge>
                  </td>
                  <td class="px-4 py-4">
                    <div class="flex items-center gap-2 flex-wrap">
                      <Button
                        v-if="canCreateBatch(order)"
                        @click="createBatchFromOrder(order)"
                        size="sm"
                        class="bg-[#8B4513] hover:bg-[#6B3410] text-white text-xs"
                      >
                        <Play class="h-3 w-3 mr-1" />
                        Create Batch
                      </Button>
                      <Button
                        variant="ghost"
                        size="sm"
                        @click="editOrder(order)"
                        class="text-xs"
                      >
                        <Edit class="h-3 w-3" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="sm"
                        @click="deleteOrder(order.id)"
                        class="text-xs text-red-600 hover:text-red-700"
                      >
                        <Trash2 class="h-3 w-3" />
                      </Button>
                      <span v-if="getBatchId(order) && !canCreateBatch(order)" class="text-xs text-gray-500">
                        Batch exists
                      </span>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredOrders.length === 0">
                  <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500">
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
    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Order' : 'Create New Order'" class="max-w-3xl">
      <form @submit.prevent="saveOrder" class="space-y-6">
        <!-- Order Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <ShoppingCart class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Order Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="order_type" class="flex items-center gap-2">
                <Tag class="h-4 w-4 text-gray-500" />
                Order Type *
              </Label>
              <Select v-model="form.order_type" placeholder="Select order type" required>
                <SelectItem value="">Select order type</SelectItem>
                <SelectItem value="online_order">Online Order</SelectItem>
                <SelectItem value="corporate_order">Corporate Order</SelectItem>
                <SelectItem value="sample">Sample</SelectItem>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="quantity" class="flex items-center gap-2">
                <Package class="h-4 w-4 text-gray-500" />
                Quantity *
              </Label>
              <Input id="quantity" v-model.number="form.quantity" type="number" required min="1" placeholder="Enter quantity" />
            </div>
          </div>
        </div>

        <!-- Product Details Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Package class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Product Details</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2 md:col-span-2">
              <Label for="product_id" class="flex items-center gap-2">
                <Package class="h-4 w-4 text-gray-500" />
                Product *
              </Label>
              <Select v-model="form.product_id" @change="onProductChange" placeholder="Select a product" required>
                <SelectItem value="">Select a product</SelectItem>
                <SelectItem v-for="product in products" :key="product.id" :value="String(product.id)">
                  {{ product.product_name }} - {{ product.color || 'N/A' }}
                </SelectItem>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="color" class="flex items-center gap-2">
                <Palette class="h-4 w-4 text-gray-500" />
                Color
              </Label>
              <Input id="color" v-model="form.color" :disabled="!!form.product_id" placeholder="Auto-populated from product" />
              <p class="text-xs text-gray-500">Auto-populated from product</p>
            </div>
            <div class="space-y-2">
              <Label for="sku" class="flex items-center gap-2">
                <Hash class="h-4 w-4 text-gray-500" />
                SKU
              </Label>
              <Input id="sku" v-model="form.sku" :disabled="!!form.product_id" placeholder="Auto-populated from product" />
              <p class="text-xs text-gray-500">Auto-populated from product</p>
            </div>
          </div>
        </div>

        <!-- Additional Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <FileText class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Additional Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2 md:col-span-2">
              <Label for="notes" class="flex items-center gap-2">
                <FileText class="h-4 w-4 text-gray-500" />
                Notes
              </Label>
              <textarea
                id="notes"
                v-model="form.notes"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-[#8B4513]"
                placeholder="Enter order notes or special instructions..."
              ></textarea>
            </div>
          </div>
        </div>
      </form>
      <template #footer>
        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="dialogOpen = false">
            Cancel
          </Button>
          <Button type="button" @click="saveOrder" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            {{ isEditing ? 'Update Order' : 'Create Order' }}
          </Button>
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Plus, Search, ShoppingCart, Package, Tag, Hash, Palette, FileText, Play, Edit, Trash2 } from 'lucide-vue-next';
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
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const { toast } = useToast();
const { confirm } = useConfirm();

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
  order_type: '',
  product_id: '',
  quantity: 0,
  color: '',
  sku: '',
  notes: '',
});

const onProductChange = () => {
  if (form.value.product_id) {
    const selectedProduct = products.value.find(p => String(p.id) === form.value.product_id);
    if (selectedProduct) {
      form.value.color = selectedProduct.color || '';
      form.value.sku = selectedProduct.sku || '';
    }
  } else {
    form.value.color = '';
    form.value.sku = '';
  }
};

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

const getOrderTypeLabel = (type) => {
  const typeMap = {
    'online_order': 'Online',
    'corporate_order': 'Corporate',
    'sample': 'Sample',
  };
  return typeMap[type] || 'N/A';
};

const canCreateBatch = (order) => {
  // Can create batch if order is pending and doesn't have a batch yet
  return order.status === 'pending' && !getBatchId(order);
};

const createBatchFromOrder = async (order) => {
  if (!canCreateBatch(order)) {
    toast.warning('Cannot create batch for this order');
    return;
  }

  try {
    const response = await apiClient.post(`/orders/${order.id}/create-batch`);
    toast.success('Batch created successfully');
    // Refresh orders to show the new batch
    await fetchOrders();
  } catch (error) {
    console.error('Error creating batch:', error);
    const errorMessage = error.response?.data?.message || 'Error creating batch';
    toast.error('Error creating batch', errorMessage);
  }
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
    order_type: order.order_type || '',
    product_id: String(order.product_id),
    quantity: order.quantity,
    color: order.color || '',
    sku: order.sku || '',
    notes: order.notes || '',
  };
  dialogOpen.value = true;
}

async function saveOrder() {
  // Validation
  if (!form.value.order_type || form.value.order_type.trim() === '') {
    toast.warning('Please select order type');
    return;
  }
  
  if (!form.value.product_id) {
    toast.warning('Please select a product');
    return;
  }
  
  if (!form.value.quantity || form.value.quantity <= 0) {
    toast.warning('Please enter a valid quantity (greater than 0)');
    return;
  }

  try {
    const payload = {
      ...form.value,
      product_id: parseInt(form.value.product_id),
      quantity: parseInt(form.value.quantity),
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
    toast.success(isEditing.value ? 'Order updated successfully' : 'Order created successfully');
  } catch (error) {
    console.error('Error saving order:', error);
    const errorMessage = error.response?.data?.message || error.response?.data?.errors
      ? (error.response.data.errors ? JSON.stringify(error.response.data.errors) : error.response.data.message)
      : 'Error saving order';
    toast.error('Error saving order', errorMessage);
  }
}

function resetForm() {
  form.value = {
    id: null,
    order_type: '',
    product_id: '',
    quantity: 0,
    color: '',
    sku: '',
    notes: '',
  };
  isEditing.value = false;
}

async function deleteOrder(id) {
  const confirmed = await confirm({
    title: 'Delete Order',
    message: 'Are you sure you want to delete this order? This action cannot be undone.',
    type: 'danger'
  });

  if (!confirmed) return;

  try {
    await apiClient.delete(`/orders/${id}`);
    await fetchOrders();
    toast.success('Order deleted successfully');
  } catch (error) {
    console.error('Error deleting order:', error);
    const errorMessage = error.response?.data?.message || 'Error deleting order';
    toast.error('Error deleting order', errorMessage);
  }
}

onMounted(() => {
  fetchOrders();
  fetchProducts();
});
</script>
