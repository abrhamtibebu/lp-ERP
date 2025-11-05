<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#8B4513]">Accessories</h1>
        <p class="text-gray-600 mt-1">Manage accessory inventory</p>
      </div>
      <Button @click="dialogOpen = true" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
        <Plus class="h-4 w-4 mr-2" />
        Add Accessory
      </Button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-gray-900">{{ stats.total_items || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">Accessory types</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-orange-600">{{ stats.low_stock_items || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">Need reordering</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-gray-900">{{ stats.recent_imports || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">This month</div>
        </CardContent>
      </Card>
    </div>

    <!-- Accessory Inventory -->
    <div class="space-y-4">
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-900">Accessory Inventory</h2>
        <div class="relative w-64">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
          <Input
            v-model="searchQuery"
            placeholder="Search accessories..."
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
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Import Invoice</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted By</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received By</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in filteredAccessories" :key="item.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ item.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <span class="text-sm text-gray-900">
                        {{ item.quantity }} {{ item.unit || 'pcs' }}
                      </span>
                      <AlertCircle v-if="isLowStock(item)" class="h-4 w-4 text-yellow-500" />
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ item.import_invoice_number || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ item.submitted_by?.name || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ item.received_by?.name || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :class="getStatusClass(item)">
                      {{ getStatus(item) }}
                    </Badge>
                  </td>
                </tr>
                <tr v-if="filteredAccessories.length === 0">
                  <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                    No accessories found
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Add/Edit Dialog -->
    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Accessory' : 'Add New Accessory'">
      <form @submit.prevent="saveAccessory" class="space-y-4">
        <div class="space-y-2">
          <Label for="name">Name *</Label>
          <Input id="name" v-model="form.name" required />
          <p v-if="errors.name" class="text-xs text-red-600">{{ errors.name }}</p>
        </div>
        <div class="space-y-2">
          <Label for="quantity">Quantity *</Label>
          <Input id="quantity" v-model.number="form.quantity" type="number" step="0.01" required min="0" />
          <p v-if="errors.quantity" class="text-xs text-red-600">{{ errors.quantity }}</p>
        </div>
        <div class="space-y-2">
          <Label for="unit">Unit</Label>
          <Select v-model="form.unit">
            <SelectItem value="pieces">pieces</SelectItem>
            <SelectItem value="spools">spools</SelectItem>
            <SelectItem value="bottles">bottles</SelectItem>
            <SelectItem value="sets">sets</SelectItem>
            <SelectItem value="kg">kg</SelectItem>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="import_invoice_number">Import Invoice Number</Label>
          <Input id="import_invoice_number" v-model="form.import_invoice_number" />
        </div>
        <div class="space-y-2">
          <Label for="submitted_by">Submitted By *</Label>
          <Select v-model="form.submitted_by" required>
            <SelectItem value="">Select user</SelectItem>
            <SelectItem v-for="user in users" :key="user.id" :value="String(user.id)">
              {{ user.name }}
            </SelectItem>
          </Select>
          <p v-if="errors.submitted_by" class="text-xs text-red-600">{{ errors.submitted_by }}</p>
        </div>
        <div class="space-y-2">
          <Label for="received_by">Received By *</Label>
          <Select v-model="form.received_by" required>
            <SelectItem value="">Select user</SelectItem>
            <SelectItem v-for="user in users" :key="user.id" :value="String(user.id)">
              {{ user.name }}
            </SelectItem>
          </Select>
          <p v-if="errors.received_by" class="text-xs text-red-600">{{ errors.received_by }}</p>
        </div>
      </form>
      <template #footer>
        <Button type="button" variant="outline" @click="dialogOpen = false">Cancel</Button>
        <Button type="button" @click="saveAccessory" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">Save</Button>
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

const accessories = ref([]);
const users = ref([]);
const stats = ref({
  total_items: 0,
  low_stock_items: 0,
  recent_imports: 0,
});
const dialogOpen = ref(false);
const isEditing = ref(false);
const searchQuery = ref('');
const errors = ref({});

const form = ref({
  id: null,
  name: '',
  quantity: 0,
  unit: 'pieces',
  import_invoice_number: '',
  submitted_by: '',
  received_by: '',
});

const lowStockThreshold = 500;

const filteredAccessories = computed(() => {
  if (!searchQuery.value) return accessories.value;
  
  const query = searchQuery.value.toLowerCase();
  return accessories.value.filter(item => {
    const name = (item.name || '').toLowerCase();
    const invoice = (item.import_invoice_number || '').toLowerCase();
    
    return name.includes(query) || invoice.includes(query);
  });
});

const isLowStock = (item) => {
  return item.quantity < lowStockThreshold;
};

const getStatus = (item) => {
  return isLowStock(item) ? 'Low Stock' : 'In Stock';
};

const getStatusClass = (item) => {
  return isLowStock(item) 
    ? 'bg-orange-100 text-orange-800' 
    : 'bg-green-100 text-green-800';
};

async function fetchAccessories() {
  try {
    const response = await apiClient.get('/accessories-inventory');
    accessories.value = response.data.inventory || response.data || [];
    stats.value = response.data.stats || {
      total_items: accessories.value.length,
      low_stock_items: accessories.value.filter(isLowStock).length,
      recent_imports: 0,
    };
  } catch (error) {
    console.error('Error fetching accessories:', error);
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

function editAccessory(accessory) {
  isEditing.value = true;
  form.value = {
    id: accessory.id,
    name: accessory.name,
    quantity: accessory.quantity,
    unit: accessory.unit || 'pieces',
    import_invoice_number: accessory.import_invoice_number || '',
    submitted_by: String(accessory.submitted_by || ''),
    received_by: String(accessory.received_by || ''),
  };
  dialogOpen.value = true;
}

async function saveAccessory() {
  // Clear previous errors
  errors.value = {};
  
  // Client-side validation
  if (!form.value.name || form.value.name.trim() === '') {
    errors.value.name = 'Name is required';
    return;
  }
  
  if (!form.value.quantity || form.value.quantity <= 0) {
    errors.value.quantity = 'Quantity must be greater than 0';
    return;
  }
  
  if (!form.value.submitted_by || form.value.submitted_by === '') {
    errors.value.submitted_by = 'Submitted By is required';
    return;
  }
  
  if (!form.value.received_by || form.value.received_by === '') {
    errors.value.received_by = 'Received By is required';
    return;
  }
  
  try {
    const payload = {
      name: form.value.name.trim(),
      quantity: parseFloat(form.value.quantity),
      unit: form.value.unit || 'pieces',
      import_invoice_number: form.value.import_invoice_number || null,
      submitted_by: parseInt(form.value.submitted_by),
      received_by: parseInt(form.value.received_by),
    };
    
    if (isEditing.value) {
      await apiClient.put(`/accessories-inventory/${form.value.id}`, payload);
    } else {
      await apiClient.post('/accessories-inventory', payload);
    }
    await fetchAccessories();
    dialogOpen.value = false;
    resetForm();
  } catch (error) {
    console.error('Error saving accessory:', error);
    
    // Handle validation errors from backend
    if (error.response?.status === 422) {
      const backendErrors = error.response.data.errors || {};
      errors.value = {};
      
      // Map backend errors to form errors
      Object.keys(backendErrors).forEach(key => {
        if (backendErrors[key] && backendErrors[key].length > 0) {
          errors.value[key] = backendErrors[key][0];
        }
      });
      
      // Show alert for general errors
      if (error.response.data.message) {
        alert(error.response.data.message);
      }
    } else {
      alert(error.response?.data?.message || 'Error saving accessory');
    }
  }
}

function resetForm() {
  form.value = {
    id: null,
    name: '',
    quantity: 0,
    unit: 'pieces',
    import_invoice_number: '',
    submitted_by: '',
    received_by: '',
  };
  isEditing.value = false;
  errors.value = {};
}

onMounted(() => {
  fetchAccessories();
  fetchUsers();
});
</script>
