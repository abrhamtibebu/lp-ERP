<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#8B4513]">Logistics</h1>
        <p class="text-gray-600 mt-1">Commercial invoices and shipping</p>
      </div>
      <Button @click="dialogOpen = true" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
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
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b bg-gray-50">
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice ID</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shipping Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="invoice in filteredInvoices" :key="invoice.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ invoice.invoice_number || `CI-${invoice.id}` }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">
                        {{ getProductName(invoice) }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ getProductSku(invoice) }}
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ getQuantity(invoice) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${{ formatCurrency(invoice.total_amount || 0) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ getDestination(invoice) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(invoice.invoice_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :class="getStatusClass(invoice)">
                      {{ getStatus(invoice) }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Button 
                      variant="ghost" 
                      size="sm"
                      @click="downloadPDF(invoice)"
                      class="text-[#8B4513] hover:text-[#6B3410]"
                    >
                      <Download class="h-4 w-4 mr-1" />
                      PDF
                    </Button>
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

    <!-- Generate Invoice Dialog -->
    <Dialog v-model="dialogOpen" title="Generate Invoice">
      <form @submit.prevent="saveInvoice" class="space-y-4">
        <div class="space-y-2">
          <Label for="order_id">Order</Label>
          <Select v-model="form.order_id">
            <SelectItem value="">Select an order</SelectItem>
            <SelectItem v-for="order in orders" :key="order.id" :value="String(order.id)">
              {{ order.product?.product_name }} - Qty: {{ order.quantity }}
            </SelectItem>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="batch_id">Batch (Optional)</Label>
          <Select v-model="form.batch_id">
            <SelectItem value="">None</SelectItem>
            <SelectItem v-for="batch in batches" :key="batch.id" :value="String(batch.id)">
              {{ batch.batch_id }}
            </SelectItem>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="total_amount">Total Amount (Auto-calculated)</Label>
          <Input id="total_amount" v-model.number="form.total_amount" type="number" step="0.01" disabled />
          <p class="text-xs text-gray-500">Amount is calculated from Product Cost (set in Finance → Product Costs module). Product costs cannot be edited here.</p>
        </div>
        <div class="space-y-2">
          <Label for="invoice_date">Invoice Date</Label>
          <Input id="invoice_date" v-model="form.invoice_date" type="date" required />
        </div>
        <div class="space-y-2">
          <Label for="notes">Notes</Label>
          <Input id="notes" v-model="form.notes" />
        </div>
      </form>
      <template #footer>
        <Button type="button" variant="outline" @click="dialogOpen = false">Cancel</Button>
        <Button type="button" @click="saveInvoice" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">Generate</Button>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Plus, Search, Download } from 'lucide-vue-next';
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
const searchQuery = ref('');

const form = ref({
  order_id: '',
  batch_id: '',
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
    orders.value = ordersData.filter(o => o.status === 'completed' || o.status === 'in_production');
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

async function saveInvoice() {
  try {
    const selectedOrder = orders.value.find(o => o.id.toString() === form.value.order_id);
    if (!selectedOrder) {
      alert('Please select an order');
      return;
    }
    
    const payload = {
      order_id: parseInt(form.value.order_id),
      batch_id: form.value.batch_id ? parseInt(form.value.batch_id) : null,
      product_details: [
        {
          product_name: selectedOrder.product?.product_name,
          color: selectedOrder.color,
          sku: selectedOrder.sku,
          quantity: selectedOrder.quantity,
          price: selectedOrder.product?.unit_price || 0,
        }
      ],
      invoice_date: form.value.invoice_date,
      notes: form.value.notes,
    };
    
    await apiClient.post('/commercial-invoices', payload);
    await fetchInvoices();
    dialogOpen.value = false;
    resetForm();
  } catch (error) {
    console.error('Error saving invoice:', error);
    alert(error.response?.data?.message || 'Error generating invoice');
  }
}

function downloadPDF(invoice) {
  // Download PDF functionality
  window.open(`/api/commercial-invoices/${invoice.id}/pdf`, '_blank');
}

function resetForm() {
  form.value = {
    order_id: '',
    batch_id: '',
    total_amount: 0,
    invoice_date: new Date().toISOString().split('T')[0],
    notes: '',
  };
}

onMounted(() => {
  fetchInvoices();
  fetchOrders();
  fetchBatches();
});
</script>
