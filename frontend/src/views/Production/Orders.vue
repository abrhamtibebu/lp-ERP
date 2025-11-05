<template>
  <div class="space-y-6">
    <ActionBar title="Production Orders" description="Manage production orders and batches" @add-new="dialogOpen = true" />

    <Card>
      <CardContent class="p-0">
        <DataTable :data="orders" :columns="columns">
          <template #cell-product="{ row }">
            <div class="flex items-center gap-3">
              <span class="font-medium">{{ row.product?.product_name || 'N/A' }}</span>
              <Badge variant="outline">{{ row.sku }}</Badge>
            </div>
          </template>
          <template #cell-status="{ row }">
            <Badge :variant="getStatusVariant(row.status)">
              {{ row.status }}
            </Badge>
          </template>
          <template #cell-quantity="{ row }">
            <span class="font-medium">{{ row.quantity }}</span>
          </template>
          <template #rowActions="{ row }">
            <DropdownMenu>
              <template #trigger>
                <Button variant="ghost" class="h-8 w-8 p-0">
                  <span class="sr-only">Open menu</span>
                  <MoreHorizontal class="h-4 w-4" />
                </Button>
              </template>
              <DropdownMenuItem @click="viewOrder(row)">View Details</DropdownMenuItem>
              <DropdownMenuItem v-if="row.status === 'pending'" @click="createBatch(row)">Create Batch</DropdownMenuItem>
              <DropdownMenuItem @click="editOrder(row)">Edit</DropdownMenuItem>
              <DropdownMenuItem @click="deleteOrder(row.id)" class="text-destructive">Delete</DropdownMenuItem>
            </DropdownMenu>
          </template>
        </DataTable>
      </CardContent>
    </Card>

    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Order' : 'Create New Order'">
      <form @submit.prevent="saveOrder" class="space-y-4">
        <div class="space-y-2">
          <Label for="product_id">Product</Label>
          <Select v-model="form.product_id">
            <SelectItem v-for="product in products" :key="product.id" :value="product.id.toString()">
              {{ product.product_name }} - {{ product.color }}
            </SelectItem>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="quantity">Quantity</Label>
          <Input id="quantity" v-model.number="form.quantity" type="number" required />
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
        <Button type="button" @click="saveOrder">Save</Button>
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

const orders = ref([]);
const products = ref([]);
const dialogOpen = ref(false);
const isEditing = ref(false);
const form = ref({
  id: null,
  product_id: '',
  quantity: 0,
  color: '',
  sku: '',
  notes: '',
});

const columns = [
  { key: 'product', label: 'Product', sortable: true },
  { key: 'quantity', label: 'Quantity', sortable: true },
  { key: 'color', label: 'Color', sortable: true },
  { key: 'status', label: 'Status', sortable: true },
];

function getStatusVariant(status) {
  const variants = {
    pending: 'outline',
    in_production: 'default',
    completed: 'secondary',
    cancelled: 'destructive',
  };
  return variants[status] || 'default';
}

async function fetchOrders() {
  try {
    const response = await apiClient.get('/orders');
    orders.value = response.data;
  } catch (error) {
    console.error('Error fetching orders:', error);
  }
}

async function fetchProducts() {
  try {
    const response = await apiClient.get('/products');
    products.value = response.data;
  } catch (error) {
    console.error('Error fetching products:', error);
  }
}

function editOrder(order) {
  isEditing.value = true;
  form.value = {
    id: order.id,
    product_id: order.product_id.toString(),
    quantity: order.quantity,
    color: order.color,
    sku: order.sku,
    notes: order.notes || '',
  };
  dialogOpen.value = true;
}

function viewOrder(order) {
  // Navigate to order detail page
  console.log('View order:', order);
}

async function createBatch(order) {
  try {
    await apiClient.post(`/orders/${order.id}/create-batch`);
    await fetchOrders();
    alert('Batch created successfully!');
  } catch (error) {
    console.error('Error creating batch:', error);
    alert('Failed to create batch');
  }
}

async function saveOrder() {
  try {
    const payload = {
      ...form.value,
      product_id: parseInt(form.value.product_id),
    };
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
  }
}

async function deleteOrder(id) {
  if (confirm('Are you sure you want to delete this order?')) {
    try {
      await apiClient.delete(`/orders/${id}`);
      await fetchOrders();
    } catch (error) {
      console.error('Error deleting order:', error);
    }
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
