<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#8B4513]">Logistics</h1>
        <p class="text-gray-600 mt-1">Commercial invoices and shipping</p>
      </div>
      <Button @click="openCreateDialog" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
        <Plus class="h-4 w-4 mr-2" />
        Generate Invoice
      </Button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-gray-900">{{ stats.total_shipments || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">This month</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-gray-900">${{ formatCurrency(stats.total_value || 0) }}</div>
          <div class="text-sm text-gray-600 mt-1">Shipped goods</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-orange-600">{{ stats.pending || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">Awaiting dispatch</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-green-600">{{ stats.delivered || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">Successfully delivered</div>
        </CardContent>
      </Card>
    </div>

    <!-- Commercial Invoices -->
    <div class="space-y-4">
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-900">Commercial Invoices</h2>
        <div class="relative w-64">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
          <Input
            v-model="searchQuery"
            placeholder="Search invoices..."
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
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice ID</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order/Batch</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Date</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="invoice in filteredInvoices" :key="invoice.id" class="hover:bg-gray-50">
                  <td class="px-4 py-4 text-sm font-medium text-gray-900">
                    {{ invoice.invoice_number || `CI-${invoice.id}` }}
                  </td>
                  <td class="px-4 py-4">
                    <div class="text-sm">
                      <div class="text-gray-900">Order: {{ invoice.order ? `ORD-${String(invoice.order.id).padStart(4, '0')}` : 'N/A' }}</div>
                      <div v-if="invoice.batch" class="text-xs text-gray-500">Batch: {{ invoice.batch.batch_id }}</div>
                    </div>
                  </td>
                  <td class="px-4 py-4">
                    <div>
                      <div class="text-sm font-medium text-gray-900">
                        {{ getProductName(invoice) }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ getProductSku(invoice) }}
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ getQuantity(invoice) }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    ${{ formatCurrency(invoice.total_amount || 0) }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ formatDate(invoice.invoice_date) }}
                  </td>
                  <td class="px-4 py-4">
                    <Badge :class="getStatusClass(invoice)">
                      {{ getStatus(invoice) }}
                    </Badge>
                  </td>
                  <td class="px-4 py-4 text-sm">
                    <div class="flex gap-2">
                      <Button 
                        variant="ghost" 
                        size="sm"
                        @click="editInvoice(invoice)"
                      >
                        <Edit class="h-4 w-4" />
                      </Button>
                      <Button 
                        variant="ghost" 
                        size="sm"
                        @click="downloadPDF(invoice)"
                      >
                        <Download class="h-4 w-4" />
                      </Button>
                      <Button 
                        variant="ghost" 
                        size="sm"
                        @click="deleteInvoice(invoice.id)"
                        class="text-red-600 hover:text-red-700"
                      >
                        <Trash2 class="h-4 w-4" />
                      </Button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredInvoices.length === 0">
                  <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                    No invoices found
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Generate/Edit Invoice Dialog -->
    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Invoice' : 'Generate Invoice'" class="max-w-3xl">
      <form @submit.prevent="saveInvoice" class="space-y-6">
        <!-- Order Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <ShoppingCart class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Order Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2 md:col-span-2">
              <Label for="order_id" class="flex items-center gap-2">
                <ShoppingCart class="h-4 w-4 text-gray-500" />
                Order {{ isEditing ? '' : '*' }}
              </Label>
              <Select 
                v-if="!isEditing" 
                id="order_id" 
                v-model="form.order_id" 
                @change="onOrderChange"
                :required="!isEditing"
                placeholder="Select an order"
              >
                <SelectItem value="">Select an order</SelectItem>
                <SelectItem v-for="order in orders" :key="order.id" :value="String(order.id)">
                  {{ order.product?.product_name }} - Qty: {{ order.quantity }} - {{ order.order_type || 'N/A' }}
                </SelectItem>
              </Select>
              <Input 
                v-else
                :value="form.order_id ? `ORD-${String(form.order_id).padStart(4, '0')}` : 'N/A'" 
                disabled 
                placeholder="Invoice is linked to this order"
              />
              <p class="text-xs text-gray-500">{{ isEditing ? 'Invoice is linked to this order' : 'Select an order to generate invoice' }}</p>
            </div>
            <div class="space-y-2">
              <Label for="batch_id" class="flex items-center gap-2">
                <Package class="h-4 w-4 text-gray-500" />
                Batch (Optional)
              </Label>
              <Select id="batch_id" v-model="form.batch_id" placeholder="Select batch">
                <SelectItem value="">None</SelectItem>
                <SelectItem 
                  v-for="batch in filteredBatches" 
                  :key="batch.id" 
                  :value="String(batch.id)"
                >
                  {{ batch.batch_id }}
                </SelectItem>
              </Select>
              <p class="text-xs text-gray-500" v-if="form.order_id && !isEditing">
                Showing batches for selected order
              </p>
            </div>
            <div class="space-y-2">
              <Label for="invoice_date" class="flex items-center gap-2">
                <Calendar class="h-4 w-4 text-gray-500" />
                Invoice Date *
              </Label>
              <Input id="invoice_date" v-model="form.invoice_date" type="date" required />
            </div>
          </div>
        </div>

        <!-- Product Details Section -->
        <div class="space-y-4" v-if="form.product_details && form.product_details.length > 0">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Package class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Product Details</h3>
          </div>
          <div class="border rounded-lg p-4 bg-gray-50 space-y-3">
            <div v-for="(product, index) in form.product_details" :key="index" class="border-b border-gray-200 last:border-b-0 pb-3 last:pb-0">
              <div class="flex items-center justify-between">
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900">{{ product.product_name || 'N/A' }}</div>
                  <div class="text-xs text-gray-600 mt-1">
                    <span class="inline-flex items-center gap-1">
                      <Hash class="h-3 w-3" />
                      SKU: {{ product.sku || 'N/A' }}
                    </span>
                    <span class="mx-2">•</span>
                    <span>Color: {{ product.color || 'N/A' }}</span>
                    <span class="mx-2">•</span>
                    <span>Qty: {{ product.quantity || 0 }}</span>
                  </div>
                  <div class="text-xs font-semibold text-green-600 mt-1">
                    Price: ${{ formatCurrency(product.price || 0) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          <p class="text-xs text-gray-500">Product details are auto-populated from the selected order</p>
        </div>

        <!-- Invoice Details Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <FileText class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Invoice Details</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="total_amount" class="flex items-center gap-2">
                <DollarSign class="h-4 w-4 text-gray-500" />
                Total Amount ($) *
              </Label>
              <Input id="total_amount" v-model.number="form.total_amount" type="number" step="0.01" required min="0" placeholder="0.00" />
              <p class="text-xs text-gray-500">You can edit the total amount if needed</p>
            </div>
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
                placeholder="Enter invoice notes or special instructions..."
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
          <Button type="button" @click="saveInvoice" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            {{ isEditing ? 'Update Invoice' : 'Generate Invoice' }}
          </Button>
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Plus, Search, Download, Edit, FileText, ShoppingCart, Package, Calendar, DollarSign, Hash, Trash2 } from 'lucide-vue-next';
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

const invoices = ref([]);
const orders = ref([]);
const batches = ref([]);
const stats = ref({
  total_shipments: 0,
  total_value: 0,
  pending: 0,
  delivered: 0,
});
const dialogOpen = ref(false);
const isEditing = ref(false);
const searchQuery = ref('');

const form = ref({
  id: null,
  order_id: null,
  batch_id: null,
  product_details: [],
  total_amount: 0,
  invoice_date: new Date().toISOString().split('T')[0],
  notes: '',
});

const filteredInvoices = computed(() => {
  if (!searchQuery.value) return invoices.value;
  
  const query = searchQuery.value.toLowerCase();
  return invoices.value.filter(invoice => {
    const invoiceId = (invoice.invoice_number || '').toLowerCase();
    const productName = (getProductName(invoice) || '').toLowerCase();
    
    return invoiceId.includes(query) || productName.includes(query);
  });
});

const filteredBatches = computed(() => {
  // When editing, show all batches so user can change if needed
  if (isEditing.value) {
    return batches.value;
  }
  
  // When creating, filter by selected order
  if (!form.value.order_id) {
    // If no order selected, show all batches
    return batches.value;
  }
  
  // Filter batches for the selected order
  // First check if order has batches loaded
  const selectedOrder = orders.value.find(o => String(o.id) === String(form.value.order_id));
  if (selectedOrder && selectedOrder.batches && selectedOrder.batches.length > 0) {
    return selectedOrder.batches;
  }
  
  // Otherwise filter from batches list
  return batches.value.filter(batch => String(batch.order_id) === String(form.value.order_id));
});

const getProductName = (invoice) => {
  if (invoice.product_details && invoice.product_details.length > 0) {
    return invoice.product_details[0].product_name || 'N/A';
  }
  return invoice.order?.product?.product_name || 'N/A';
};

const getProductSku = (invoice) => {
  if (invoice.product_details && invoice.product_details.length > 0) {
    return invoice.product_details[0].sku || '';
  }
  return invoice.order?.sku || '';
};

const getQuantity = (invoice) => {
  if (invoice.product_details && invoice.product_details.length > 0) {
    return invoice.product_details[0].quantity || 0;
  }
  return invoice.order?.quantity || 0;
};

const getDestination = (invoice) => {
  // In a real system, this would come from shipping details
  // For now, returning placeholder or from notes
  return invoice.order?.notes || 'N/A';
};

const getStatus = (invoice) => {
  // Check order status to determine shipping status
  if (invoice.order?.status === 'completed') {
    return 'Delivered';
  } else if (invoice.order?.status === 'in_production') {
    return 'In Transit';
  } else {
    return 'Pending';
  }
};

const getStatusClass = (invoice) => {
  const status = getStatus(invoice);
  const classes = {
    'Delivered': 'bg-green-100 text-green-800',
    'In Transit': 'bg-gray-100 text-gray-800',
    'Shipped': 'bg-blue-100 text-blue-800',
    'Pending': 'bg-orange-100 text-orange-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toISOString().split('T')[0];
};

async function fetchInvoices() {
  try {
    const response = await apiClient.get('/commercial-invoices');
    invoices.value = response.data.invoices || response.data || [];
    stats.value = response.data.stats || {
      total_shipments: invoices.value.length,
      total_value: invoices.value.reduce((sum, inv) => sum + (inv.total_amount || 0), 0),
      pending: invoices.value.filter(inv => getStatus(inv) === 'Pending').length,
      delivered: invoices.value.filter(inv => getStatus(inv) === 'Delivered').length,
    };
  } catch (error) {
    console.error('Error fetching invoices:', error);
  }
}

async function fetchOrders() {
  try {
    const response = await apiClient.get('/orders');
    const ordersData = response.data.orders || response.data || [];
    // Include all orders that can have invoices (completed, in_production, or any status)
    // Filter out cancelled orders if they exist
    orders.value = ordersData.filter(o => o.status !== 'cancelled');
  } catch (error) {
    console.error('Error fetching orders:', error);
  }
}

async function fetchBatches() {
  try {
    const response = await apiClient.get('/batches');
    batches.value = response.data || [];
  } catch (error) {
    console.error('Error fetching batches:', error);
  }
}

function editInvoice(invoice) {
  isEditing.value = true;
  form.value = {
    id: invoice.id,
    order_id: invoice.order_id,
    batch_id: invoice.batch_id ? String(invoice.batch_id) : null,
    product_details: invoice.product_details || (invoice.order ? [{
      product_id: invoice.order.product_id,
      product_name: invoice.order.product?.product_name || 'N/A',
      color: invoice.order.color || '',
      sku: invoice.order.sku || '',
      quantity: invoice.order.quantity || 0,
      price: invoice.order.product?.unit_price || 0,
    }] : []),
    total_amount: invoice.total_amount || 0,
    invoice_date: invoice.invoice_date ? new Date(invoice.invoice_date).toISOString().split('T')[0] : new Date().toISOString().split('T')[0],
    notes: invoice.notes || '',
  };
  dialogOpen.value = true;
}

function getBatchId(batchId) {
  if (!batchId) return 'N/A';
  const batch = batches.value.find(b => b.id === batchId || String(b.id) === String(batchId));
  return batch ? batch.batch_id : 'N/A';
}

async function onOrderChange() {
  if (!form.value.order_id) {
    form.value.product_details = [];
    form.value.total_amount = 0;
    form.value.batch_id = '';
    return;
  }
  
  const selectedOrder = orders.value.find(o => String(o.id) === String(form.value.order_id));
  if (selectedOrder) {
    form.value.product_details = [
      {
        product_id: selectedOrder.product_id,
        product_name: selectedOrder.product?.product_name || 'N/A',
        color: selectedOrder.color || '',
        sku: selectedOrder.sku || '',
        quantity: selectedOrder.quantity || 0,
        price: selectedOrder.product?.unit_price || 0,
      }
    ];
    // Calculate total amount
    form.value.total_amount = (selectedOrder.product?.unit_price || 0) * (selectedOrder.quantity || 0);
    
    // Try to find associated batch if exists
    // Orders can have multiple batches, so find the most recent one
    if (selectedOrder.batches && selectedOrder.batches.length > 0) {
      // Sort by created_at descending and take the first one
      const sortedBatches = [...selectedOrder.batches].sort((a, b) => {
        const dateA = new Date(a.created_at || 0);
        const dateB = new Date(b.created_at || 0);
        return dateB - dateA;
      });
      form.value.batch_id = String(sortedBatches[0].id);
    } else {
      // Fetch batches for this order if not loaded
      try {
        const batchResponse = await apiClient.get(`/batches`);
        const orderBatches = (batchResponse.data || []).filter(b => String(b.order_id) === String(form.value.order_id));
        if (orderBatches.length > 0) {
          const sortedBatches = orderBatches.sort((a, b) => {
            const dateA = new Date(a.created_at || 0);
            const dateB = new Date(b.created_at || 0);
            return dateB - dateA;
          });
          form.value.batch_id = String(sortedBatches[0].id);
        }
      } catch (error) {
        console.error('Error fetching batches for order:', error);
      }
    }
  }
}

async function saveInvoice() {
  try {
    if (!isEditing.value && !form.value.order_id) {
      toast.warning('Please select an order to generate invoice');
      return;
    }
    
    const payload = {
      order_id: form.value.order_id ? parseInt(form.value.order_id) : null,
      batch_id: form.value.batch_id ? parseInt(form.value.batch_id) : null,
      product_details: form.value.product_details,
      total_amount: form.value.total_amount,
      invoice_date: form.value.invoice_date,
      notes: form.value.notes,
    };
    
    if (isEditing.value) {
      await apiClient.put(`/commercial-invoices/${form.value.id}`, payload);
    } else {
      // Ensure product_details are set
      if (!payload.product_details || payload.product_details.length === 0) {
        const selectedOrder = orders.value.find(o => String(o.id) === String(form.value.order_id));
        if (selectedOrder) {
          payload.product_details = [
            {
              product_id: selectedOrder.product_id,
              product_name: selectedOrder.product?.product_name || 'N/A',
              color: selectedOrder.color || '',
              sku: selectedOrder.sku || '',
              quantity: selectedOrder.quantity || 0,
              price: selectedOrder.product?.unit_price || 0,
            }
          ];
        }
      }
      
      await apiClient.post('/commercial-invoices', payload);
    }
    
    await fetchInvoices();
    dialogOpen.value = false;
    resetForm();
    toast.success(isEditing.value ? 'Invoice updated successfully' : 'Invoice created successfully');
  } catch (error) {
    console.error('Error saving invoice:', error);
    const errorMessage = error.response?.data?.message || error.response?.data?.errors
      ? JSON.stringify(error.response.data.errors || error.response.data.message)
      : 'Error saving invoice';
    toast.error('Error saving invoice', errorMessage);
  }
}

function downloadPDF(invoice) {
  // Download PDF functionality
  window.open(`/api/commercial-invoices/${invoice.id}/pdf`, '_blank');
}

function openCreateDialog() {
  isEditing.value = false;
  resetForm();
  dialogOpen.value = true;
}

function resetForm() {
  form.value = {
    id: null,
    order_id: '',
    batch_id: '',
    product_details: [],
    total_amount: 0,
    invoice_date: new Date().toISOString().split('T')[0],
    notes: '',
  };
  isEditing.value = false;
}

async function deleteInvoice(id) {
  const confirmed = await confirm({
    title: 'Delete Invoice',
    message: 'Are you sure you want to delete this commercial invoice? This action cannot be undone.',
    type: 'danger'
  });

  if (!confirmed) return;

  try {
    await apiClient.delete(`/commercial-invoices/${id}`);
    await fetchInvoices();
    toast.success('Invoice deleted successfully');
  } catch (error) {
    console.error('Error deleting invoice:', error);
    const errorMessage = error.response?.data?.message || 'Error deleting invoice';
    toast.error('Error deleting invoice', errorMessage);
  }
}

onMounted(() => {
  fetchInvoices();
  fetchOrders();
  fetchBatches();
});
</script>
