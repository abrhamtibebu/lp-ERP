<template>
  <div class="space-y-6">
    <ActionBar title="Commercial Invoices" description="Manage commercial invoices and documentation" @add-new="dialogOpen = true" />

    <Card>
      <CardContent class="p-0">
        <DataTable :data="invoices" :columns="columns">
          <template #cell-invoice_number="{ row }">
            <span class="font-medium">{{ row.invoice_number }}</span>
          </template>
          <template #cell-order="{ row }">
            <span>{{ row.order?.product?.product_name || 'N/A' }}</span>
          </template>
          <template #cell-batch="{ row }">
            <Badge v-if="row.batch" variant="outline">{{ row.batch.batch_id }}</Badge>
            <span v-else class="text-gray-400">-</span>
          </template>
          <template #cell-total_amount="{ row }">
            <span class="font-semibold text-green-600">${{ parseFloat(row.total_amount).toLocaleString() }}</span>
          </template>
          <template #cell-invoice_date="{ row }">
            <span>{{ new Date(row.invoice_date).toLocaleDateString() }}</span>
          </template>
          <template #rowActions="{ row }">
            <DropdownMenu>
              <template #trigger>
                <Button variant="ghost" class="h-8 w-8 p-0">
                  <span class="sr-only">Open menu</span>
                  <MoreHorizontal class="h-4 w-4" />
                </Button>
              </template>
              <DropdownMenuItem @click="viewInvoice(row)">View Details</DropdownMenuItem>
              <DropdownMenuItem @click="downloadInvoice(row)">Download PDF</DropdownMenuItem>
              <DropdownMenuItem @click="editInvoice(row)">Edit</DropdownMenuItem>
              <DropdownMenuItem @click="deleteInvoice(row.id)" class="text-destructive">Delete</DropdownMenuItem>
            </DropdownMenu>
          </template>
        </DataTable>
      </CardContent>
    </Card>

    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Invoice' : 'Create New Invoice'">
      <form @submit.prevent="saveInvoice" class="space-y-4">
        <div class="space-y-2">
          <Label for="order_id">Order</Label>
          <Select v-model="form.order_id">
            <SelectItem v-for="order in orders" :key="order.id" :value="order.id.toString()">
              {{ order.product?.product_name }} - Qty: {{ order.quantity }}
            </SelectItem>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="batch_id">Batch (Optional)</Label>
          <Select v-model="form.batch_id">
            <SelectItem value="">None</SelectItem>
            <SelectItem v-for="batch in batches" :key="batch.id" :value="batch.id.toString()">
              {{ batch.batch_id }}
            </SelectItem>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="total_amount">Total Amount (Auto-calculated)</Label>
          <Input id="total_amount" v-model.number="form.total_amount" type="number" step="0.01" disabled />
          <p class="text-xs text-muted-foreground">Amount will be calculated from product details</p>
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
        <Button type="button" @click="saveInvoice">Save</Button>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '@/api/client';
import { MoreHorizontal } from 'lucide-vue-next';
import ActionBar from '@/components/layout/ActionBar.vue';
import Card from '@/components/ui/Card.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Button from '@/components/ui/Button.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Dialog from '@/components/ui/Dialog.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Select from '@/components/ui/Select.vue';
import SelectItem from '@/components/ui/SelectItem.vue';
import Badge from '@/components/ui/Badge.vue';
import DropdownMenu from '@/components/ui/DropdownMenu.vue';
import DropdownMenuItem from '@/components/ui/DropdownMenuItem.vue';

const invoices = ref([]);
const orders = ref([]);
const batches = ref([]);
const dialogOpen = ref(false);
const isEditing = ref(false);
const form = ref({
  id: null,
  order_id: '',
  batch_id: '',
  total_amount: 0,
  invoice_date: new Date().toISOString().split('T')[0],
  notes: '',
});

const columns = [
  { key: 'invoice_number', label: 'Invoice Number', sortable: true },
  { key: 'order', label: 'Order', sortable: true },
  { key: 'batch', label: 'Batch', sortable: true },
  { key: 'total_amount', label: 'Amount', sortable: true },
  { key: 'invoice_date', label: 'Date', sortable: true },
];

async function fetchInvoices() {
  try {
    const response = await apiClient.get('/commercial-invoices');
    invoices.value = response.data;
  } catch (error) {
    console.error('Error fetching invoices:', error);
  }
}

async function fetchOrders() {
  try {
    const response = await apiClient.get('/orders');
    orders.value = response.data.filter(o => o.status === 'completed');
  } catch (error) {
    console.error('Error fetching orders:', error);
  }
}

async function fetchBatches() {
  try {
    const response = await apiClient.get('/batches');
    batches.value = response.data.filter(b => b.status === 'completed');
  } catch (error) {
    console.error('Error fetching batches:', error);
  }
}

function editInvoice(invoice) {
  isEditing.value = true;
  form.value = {
    id: invoice.id,
    order_id: invoice.order_id?.toString() || '',
    batch_id: invoice.batch_id?.toString() || '',
    total_amount: invoice.total_amount,
    invoice_date: new Date(invoice.invoice_date).toISOString().split('T')[0],
    notes: invoice.notes || '',
  };
  dialogOpen.value = true;
}

function viewInvoice(invoice) {
  console.log('View invoice:', invoice);
}

function downloadInvoice(invoice) {
  console.log('Download invoice:', invoice);
}

async function saveInvoice() {
  try {
    let payload;
    
    if (isEditing.value) {
      payload = {
        order_id: form.value.order_id ? parseInt(form.value.order_id) : null,
        batch_id: form.value.batch_id ? parseInt(form.value.batch_id) : null,
        total_amount: form.value.total_amount,
        invoice_date: form.value.invoice_date,
        notes: form.value.notes,
      };
      await apiClient.put(`/commercial-invoices/${form.value.id}`, payload);
    } else {
      // For new invoices, we need product_details array
      const selectedOrder = orders.value.find(o => o.id.toString() === form.value.order_id);
      if (!selectedOrder) {
        alert('Please select an order');
        return;
      }
      
      payload = {
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
    }
    await fetchInvoices();
    dialogOpen.value = false;
    resetForm();
  } catch (error) {
    console.error('Error saving invoice:', error);
    alert('Error saving invoice: ' + (error.response?.data?.message || error.message));
  }
}

async function deleteInvoice(id) {
  if (confirm('Are you sure you want to delete this invoice?')) {
    try {
      await apiClient.delete(`/commercial-invoices/${id}`);
      await fetchInvoices();
    } catch (error) {
      console.error('Error deleting invoice:', error);
    }
  }
}

function resetForm() {
  form.value = {
    id: null,
    order_id: '',
    batch_id: '',
    total_amount: 0,
    invoice_date: new Date().toISOString().split('T')[0],
    notes: '',
  };
  isEditing.value = false;
}

onMounted(() => {
  fetchInvoices();
  fetchOrders();
  fetchBatches();
});
</script>
