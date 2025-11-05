<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#8B4513]">Leather Inventory</h1>
        <p class="text-gray-600 mt-1">Manage raw leather stock and supplies.</p>
      </div>
      <Button @click="dialogOpen = true" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
        <Plus class="h-4 w-4 mr-2" />
        Add Leather Stock
      </Button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <Card>
        <CardContent class="p-6">
          <div class="text-sm text-gray-600 mb-1">Total Stock</div>
          <div class="text-3xl font-bold text-gray-900">{{ formatNumber(stats.total_stock) }} sq.ft</div>
          <div class="text-sm text-gray-600 mt-1">Across {{ stats.unique_types || 0 }} leather types</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="text-sm text-gray-600 mb-1">Low Stock Items</div>
          <div class="text-3xl font-bold text-orange-600">{{ stats.low_stock_items || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">Below threshold levels</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="text-sm text-gray-600 mb-1">Active Suppliers</div>
          <div class="text-3xl font-bold text-gray-900">{{ stats.active_suppliers || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">Verified partners</div>
        </CardContent>
      </Card>
    </div>

    <!-- Leather Stock -->
    <div class="space-y-4">
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-900">Leather Stock</h2>
        <div class="relative w-64">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
          <Input
            v-model="searchQuery"
            placeholder="Search leather..."
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
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leather Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity (sq.ft)</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in filteredInventory" :key="item.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ item.leather_name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ item.brand_make || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ item.supplier?.name || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <span class="text-sm text-gray-900">{{ formatNumber(getAvailableQuantity(item)) }}</span>
                      <AlertCircle v-if="isLowStock(item)" class="h-4 w-4 text-orange-500" />
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(item.purchase_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :class="getStatusClass(item)">
                      {{ getStatus(item) }}
                    </Badge>
                  </td>
                </tr>
                <tr v-if="filteredInventory.length === 0">
                  <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                    No leather inventory found
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Add/Edit Dialog -->
    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Leather Stock' : 'Add Leather Stock'">
      <form @submit.prevent="saveLeather" class="space-y-4">
        <div class="space-y-2">
          <Label for="leather_name">Leather Name</Label>
          <Input id="leather_name" v-model="form.leather_name" required />
        </div>
        <div class="space-y-2">
          <Label for="brand_make">Brand/Make</Label>
          <Input id="brand_make" v-model="form.brand_make" />
        </div>
        <div class="space-y-2">
          <Label for="supplier_id">Supplier</Label>
          <Select v-model="form.supplier_id">
            <SelectItem value="">Select supplier</SelectItem>
            <SelectItem v-for="supplier in suppliers" :key="supplier.id" :value="String(supplier.id)">
              {{ supplier.name }}
            </SelectItem>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="quantity_sqft">Quantity (sq.ft)</Label>
          <Input id="quantity_sqft" v-model.number="form.quantity_sqft" type="number" step="0.01" required min="0" />
        </div>
        <div class="space-y-2">
          <Label for="purchase_date">Purchase Date</Label>
          <Input id="purchase_date" v-model="form.purchase_date" type="date" required />
        </div>
        <div class="space-y-2">
          <Label for="submitted_by">Submitted By</Label>
          <Select v-model="form.submitted_by">
            <SelectItem value="">Select user</SelectItem>
            <SelectItem v-for="user in users" :key="user.id" :value="String(user.id)">
              {{ user.name }}
            </SelectItem>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="received_by">Received By</Label>
          <Select v-model="form.received_by">
            <SelectItem value="">Select user</SelectItem>
            <SelectItem v-for="user in users" :key="user.id" :value="String(user.id)">
              {{ user.name }}
            </SelectItem>
          </Select>
        </div>
      </form>
      <template #footer>
        <Button type="button" variant="outline" @click="dialogOpen = false">Cancel</Button>
        <Button type="button" @click="saveLeather" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">Save</Button>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Plus, Search, AlertCircle } from 'lucide-vue-next';
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

const inventory = ref([]);
const suppliers = ref([]);
const users = ref([]);
const stats = ref({
  total_stock: 0,
  unique_types: 0,
  low_stock_items: 0,
  active_suppliers: 0,
});
const dialogOpen = ref(false);
const isEditing = ref(false);
const searchQuery = ref('');

const form = ref({
  id: null,
  leather_name: '',
  brand_make: '',
  supplier_id: '',
  quantity_sqft: 0,
  purchase_date: new Date().toISOString().split('T')[0],
  submitted_by: '',
  received_by: '',
});

const lowStockThreshold = 500; // sqft

const filteredInventory = computed(() => {
  if (!searchQuery.value) return inventory.value;
  
  const query = searchQuery.value.toLowerCase();
  return inventory.value.filter(item => {
    const name = (item.leather_name || '').toLowerCase();
    const brand = (item.brand_make || '').toLowerCase();
    const supplier = (item.supplier?.name || '').toLowerCase();
    
    return name.includes(query) || brand.includes(query) || supplier.includes(query);
  });
});

const getAvailableQuantity = (item) => {
  return item.quantity_sqft - (item.consumption_reduction || 0);
};

const isLowStock = (item) => {
  return getAvailableQuantity(item) < lowStockThreshold;
};

const getStatus = (item) => {
  return isLowStock(item) ? 'Low Stock' : 'In Stock';
};

const getStatusClass = (item) => {
  return isLowStock(item) 
    ? 'bg-orange-100 text-orange-800' 
    : 'bg-green-100 text-green-800';
};

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num);
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toISOString().split('T')[0];
};

async function fetchInventory() {
  try {
    const response = await apiClient.get('/leather-inventory');
    inventory.value = response.data.inventory || response.data || [];
    stats.value = response.data.stats || {
      total_stock: inventory.value.reduce((sum, item) => sum + getAvailableQuantity(item), 0),
      unique_types: inventory.value.length,
      low_stock_items: 0,
      active_suppliers: 0,
    };
  } catch (error) {
    console.error('Error fetching leather inventory:', error);
  }
}

async function fetchSuppliers() {
  try {
    const response = await apiClient.get('/suppliers');
    suppliers.value = response.data || [];
  } catch (error) {
    console.error('Error fetching suppliers:', error);
  }
}

async function fetchUsers() {
  try {
    const response = await apiClient.get('/users');
    users.value = response.data || [];
  } catch (error) {
    console.error('Error fetching users:', error);
  }
}

function editLeather(item) {
  isEditing.value = true;
  form.value = {
    id: item.id,
    leather_name: item.leather_name,
    brand_make: item.brand_make || '',
    supplier_id: String(item.supplier_id || ''),
    quantity_sqft: item.quantity_sqft,
    purchase_date: item.purchase_date ? formatDate(item.purchase_date) : new Date().toISOString().split('T')[0],
    submitted_by: String(item.submitted_by || ''),
    received_by: String(item.received_by || ''),
  };
  dialogOpen.value = true;
}

async function saveLeather() {
  try {
    const payload = {
      leather_name: form.value.leather_name,
      brand_make: form.value.brand_make,
      supplier_id: form.value.supplier_id ? parseInt(form.value.supplier_id) : null,
      quantity_sqft: form.value.quantity_sqft,
      purchase_date: form.value.purchase_date,
      submitted_by: form.value.submitted_by ? parseInt(form.value.submitted_by) : null,
      received_by: form.value.received_by ? parseInt(form.value.received_by) : null,
    };
    
    if (isEditing.value) {
      await apiClient.put(`/leather-inventory/${form.value.id}`, payload);
    } else {
      await apiClient.post('/leather-inventory', payload);
    }
    await fetchInventory();
    dialogOpen.value = false;
    resetForm();
  } catch (error) {
    console.error('Error saving leather:', error);
    alert(error.response?.data?.message || 'Error saving leather');
  }
}

function resetForm() {
  form.value = {
    id: null,
    leather_name: '',
    brand_make: '',
    supplier_id: '',
    quantity_sqft: 0,
    purchase_date: new Date().toISOString().split('T')[0],
    submitted_by: '',
    received_by: '',
  };
  isEditing.value = false;
}

onMounted(() => {
  fetchInventory();
  fetchSuppliers();
  fetchUsers();
});
</script>
