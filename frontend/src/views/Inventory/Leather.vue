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
          <div class="overflow-y-auto max-h-[calc(100vh-300px)]">
            <table class="w-full table-auto">
              <thead class="sticky top-0 bg-gray-50 z-10">
                <tr class="border-b">
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leather Name</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity (sq.ft)</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Date</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in filteredInventory" :key="item.id" class="hover:bg-gray-50">
                  <td class="px-4 py-4 text-sm font-medium text-gray-900">
                    {{ item.leather_name }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ item.brand_make || 'N/A' }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ item.supplier?.name || 'N/A' }}
                  </td>
                  <td class="px-4 py-4">
                    <div class="flex items-center gap-2">
                      <span class="text-sm text-gray-900">{{ formatNumber(getAvailableQuantity(item)) }}</span>
                      <AlertCircle v-if="isLowStock(item)" class="h-4 w-4 text-orange-500" title="Low Stock" />
                    </div>
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ formatDate(item.purchase_date) }}
                  </td>
                  <td class="px-4 py-4">
                    <Badge :class="getStatusClass(item)">
                      {{ getStatus(item) }}
                    </Badge>
                  </td>
                  <td class="px-4 py-4 text-sm">
                    <div class="flex gap-2">
                      <Button variant="ghost" size="sm" @click="openAdjustDialog(item)">Adjust</Button>
                      <Button variant="ghost" size="sm" @click="viewAdjustments(item)">Logs</Button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredInventory.length === 0">
                  <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
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
    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Leather Stock' : 'Add Leather Stock'" class="max-w-3xl">
      <form @submit.prevent="saveLeather" class="space-y-6">
        <!-- Leather Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Package class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Leather Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="leather_name" class="flex items-center gap-2">
                <Package class="h-4 w-4 text-gray-500" />
                Leather Name *
              </Label>
              <Input id="leather_name" v-model="form.leather_name" placeholder="Enter leather name" required />
            </div>
            <div class="space-y-2">
              <Label for="brand_make" class="flex items-center gap-2">
                <Tag class="h-4 w-4 text-gray-500" />
                Brand/Make
              </Label>
              <Input id="brand_make" v-model="form.brand_make" placeholder="Enter brand or make" />
            </div>
            <div class="space-y-2">
              <Label for="supplier_id" class="flex items-center gap-2">
                <Building2 class="h-4 w-4 text-gray-500" />
                Supplier *
              </Label>
              <Select v-model="form.supplier_id" placeholder="Select supplier" required>
                <SelectItem value="">Select supplier</SelectItem>
                <SelectItem v-for="supplier in suppliers" :key="supplier.id" :value="String(supplier.id)">
                  {{ supplier.name }}
                </SelectItem>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="purchase_date" class="flex items-center gap-2">
                <Calendar class="h-4 w-4 text-gray-500" />
                Purchase Date *
              </Label>
              <Input id="purchase_date" v-model="form.purchase_date" type="date" required />
            </div>
          </div>
        </div>

        <!-- Quantity Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Scale class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Quantity Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="quantity_sqft" class="flex items-center gap-2">
                <Scale class="h-4 w-4 text-gray-500" />
                Quantity (sq.ft) *
              </Label>
              <Input id="quantity_sqft" v-model.number="form.quantity_sqft" type="number" step="0.01" required min="0" placeholder="0.00" />
            </div>
            <div class="space-y-2">
              <Label for="low_stock_threshold" class="flex items-center gap-2">
                <AlertCircle class="h-4 w-4 text-gray-500" />
                Low Stock Threshold (sq.ft)
              </Label>
              <Input id="low_stock_threshold" v-model.number="form.low_stock_threshold" type="number" step="0.01" min="0" placeholder="0.00" />
              <p class="text-xs text-gray-500">Alert when available quantity falls below this threshold</p>
            </div>
          </div>
        </div>

        <!-- Personnel Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <User class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Personnel</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="submitted_by" class="flex items-center gap-2">
                <User class="h-4 w-4 text-gray-500" />
                Submitted By *
              </Label>
              <Select v-model="form.submitted_by" placeholder="Select user" required>
                <SelectItem value="">Select user</SelectItem>
                <SelectItem v-for="user in users" :key="user.id" :value="String(user.id)">
                  {{ user.name }}
                </SelectItem>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="received_by" class="flex items-center gap-2">
                <User class="h-4 w-4 text-gray-500" />
                Received By *
              </Label>
              <Select v-model="form.received_by" placeholder="Select user" required>
                <SelectItem value="">Select user</SelectItem>
                <SelectItem v-for="user in users" :key="user.id" :value="String(user.id)">
                  {{ user.name }}
                </SelectItem>
              </Select>
            </div>
          </div>
        </div>
      </form>
      <template #footer>
        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="dialogOpen = false">
            Cancel
          </Button>
          <Button type="button" @click="saveLeather" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            {{ isEditing ? 'Update Leather Stock' : 'Add Leather Stock' }}
          </Button>
        </div>
      </template>
    </Dialog>

    <!-- Quantity Adjustment Dialog -->
    <Dialog v-model="adjustDialogOpen" title="Adjust Leather Quantity" class="max-w-3xl">
      <form @submit.prevent="saveAdjustment" class="space-y-6">
        <!-- Leather Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Package class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Leather Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label class="flex items-center gap-2">
                <Package class="h-4 w-4 text-gray-500" />
                Leather Item
              </Label>
              <Input :value="selectedItem?.leather_name" disabled placeholder="Leather name" />
            </div>
            <div class="space-y-2">
              <Label class="flex items-center gap-2">
                <Scale class="h-4 w-4 text-gray-500" />
                Current Available Quantity
              </Label>
              <Input :value="selectedItem ? formatNumber(getAvailableQuantity(selectedItem)) + ' sq.ft' : ''" disabled placeholder="Current quantity" />
            </div>
          </div>
        </div>

        <!-- Adjustment Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Tag class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Adjustment Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="adjustment_type" class="flex items-center gap-2">
                <Tag class="h-4 w-4 text-gray-500" />
                Adjustment Type *
              </Label>
              <Select v-model="adjustForm.adjustment_type" placeholder="Select adjustment type" required>
                <SelectItem value="add">Add Quantity</SelectItem>
                <SelectItem value="deduct">Deduct Quantity</SelectItem>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="adjust_quantity" class="flex items-center gap-2">
                <Scale class="h-4 w-4 text-gray-500" />
                Quantity (sq.ft) *
              </Label>
              <Input id="adjust_quantity" v-model.number="adjustForm.quantity" type="number" step="0.01" required min="0.01" placeholder="0.00" />
            </div>
            <div class="space-y-2 md:col-span-2">
              <Label for="adjust_notes" class="flex items-center gap-2">
                <FileText class="h-4 w-4 text-gray-500" />
                Notes
              </Label>
              <textarea
                id="adjust_notes"
                v-model="adjustForm.notes"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-[#8B4513]"
                placeholder="Enter reason for adjustment..."
              ></textarea>
            </div>
          </div>
        </div>
      </form>
      <template #footer>
        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="adjustDialogOpen = false">
            Cancel
          </Button>
          <Button type="button" @click="saveAdjustment" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            Save Adjustment
          </Button>
        </div>
      </template>
    </Dialog>

    <!-- Adjustments Log Dialog -->
    <Dialog v-model="logsDialogOpen" title="Adjustment Logs" class="max-w-3xl">
      <div class="space-y-6">
        <div v-if="selectedItem?.adjustments && selectedItem.adjustments.length > 0" class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <FileText class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Adjustment History</h3>
          </div>
          <div class="space-y-3">
            <div v-for="adj in selectedItem.adjustments" :key="adj.id" class="border border-gray-200 rounded-lg p-4 bg-gray-50">
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2">
                    <Badge :variant="adj.adjustment_type === 'add' ? 'default' : 'secondary'" class="bg-green-100 text-green-800" v-if="adj.adjustment_type === 'add'">
                      Added {{ formatNumber(adj.quantity) }} sq.ft
                    </Badge>
                    <Badge :variant="adj.adjustment_type === 'deduct' ? 'default' : 'secondary'" class="bg-red-100 text-red-800" v-else>
                      Deducted {{ formatNumber(adj.quantity) }} sq.ft
                    </Badge>
                  </div>
                  <div class="text-sm text-gray-700 mb-1">{{ adj.notes || 'No notes' }}</div>
                  <div class="text-xs text-gray-500">By: {{ adj.adjusted_by?.name || 'Unknown' }}</div>
                </div>
                <div class="text-xs text-gray-500 whitespace-nowrap ml-4">
                  {{ new Date(adj.adjusted_at).toLocaleString() }}
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center text-gray-500 py-8">
          <FileText class="h-12 w-12 mx-auto mb-2 text-gray-400" />
          <p>No adjustments recorded</p>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end">
          <Button type="button" variant="outline" @click="logsDialogOpen = false">
            Close
          </Button>
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Plus, Search, AlertCircle, Package, Building2, Calendar, User, Tag, Hash, FileText, Scale } from 'lucide-vue-next';
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

const { toast } = useToast();

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
const adjustDialogOpen = ref(false);
const logsDialogOpen = ref(false);
const searchQuery = ref('');
const selectedItem = ref(null);

const form = ref({
  id: null,
  leather_name: '',
  brand_make: '',
  supplier_id: '',
  quantity_sqft: 0,
  low_stock_threshold: null,
  purchase_date: new Date().toISOString().split('T')[0],
  submitted_by: '',
  received_by: '',
});

const adjustForm = ref({
  adjustment_type: 'add',
  quantity: 0,
  notes: '',
});

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
  const available = getAvailableQuantity(item);
  const threshold = item.low_stock_threshold ?? 500; // Default threshold if not set
  return available < threshold;
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
      low_stock_items: inventory.value.filter(item => isLowStock(item)).length,
      active_suppliers: new Set(inventory.value.map(item => item.supplier_id).filter(Boolean)).size,
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
    low_stock_threshold: item.low_stock_threshold || null,
    purchase_date: item.purchase_date ? formatDate(item.purchase_date) : new Date().toISOString().split('T')[0],
    submitted_by: String(item.submitted_by || ''),
    received_by: String(item.received_by || ''),
  };
  dialogOpen.value = true;
}

function openAdjustDialog(item) {
  selectedItem.value = item;
  adjustForm.value = {
    adjustment_type: 'add',
    quantity: 0,
    notes: '',
  };
  adjustDialogOpen.value = true;
}

async function viewAdjustments(item) {
  try {
    // Fetch full item details with adjustments
    const response = await apiClient.get(`/leather-inventory/${item.id}`);
    selectedItem.value = response.data;
    logsDialogOpen.value = true;
  } catch (error) {
    console.error('Error fetching adjustments:', error);
    // Fallback to item data if API call fails
    selectedItem.value = item;
    logsDialogOpen.value = true;
  }
}

async function saveAdjustment() {
  if (!selectedItem.value || !adjustForm.value.quantity || adjustForm.value.quantity <= 0) {
    toast.warning('Please enter a valid quantity');
    return;
  }

  try {
    const response = await apiClient.post(`/leather-inventory/${selectedItem.value.id}/adjust`, adjustForm.value);
    await fetchInventory();
    adjustDialogOpen.value = false;
    toast.success('Quantity adjusted successfully');
    // Update selectedItem with new data
    if (response.data.inventory) {
      const index = inventory.value.findIndex(i => i.id === selectedItem.value.id);
      if (index !== -1) {
        inventory.value[index] = response.data.inventory;
      }
    }
    
    // Refresh notifications immediately after inventory adjustment
    if (typeof window !== 'undefined' && window.__notificationsRefresh) {
      window.__notificationsRefresh();
    }
  } catch (error) {
    console.error('Error adjusting quantity:', error);
    toast.error('Error adjusting quantity', error.response?.data?.message || 'Error adjusting quantity');
  }
}

async function saveLeather() {
  // Validation
  if (!form.value.leather_name || form.value.leather_name.trim() === '') {
    toast.warning('Please enter leather name');
    return;
  }
  
  if (!form.value.quantity_sqft || form.value.quantity_sqft <= 0) {
    toast.warning('Please enter a valid quantity (greater than 0)');
    return;
  }
  
  if (!form.value.purchase_date) {
    toast.warning('Please enter purchase date');
    return;
  }

  try {
    const payload = {
      leather_name: form.value.leather_name.trim(),
      brand_make: form.value.brand_make || null,
      supplier_id: form.value.supplier_id ? parseInt(form.value.supplier_id) : null,
      quantity_sqft: parseFloat(form.value.quantity_sqft),
      low_stock_threshold: form.value.low_stock_threshold ? parseFloat(form.value.low_stock_threshold) : null,
      purchase_date: form.value.purchase_date,
      submitted_by: form.value.submitted_by ? parseInt(form.value.submitted_by) : null,
      received_by: form.value.received_by ? parseInt(form.value.received_by) : null,
      consumption_reduction: 0, // Default to 0 if not provided
    };
    
    if (isEditing.value) {
      await apiClient.put(`/leather-inventory/${form.value.id}`, payload);
    } else {
      await apiClient.post('/leather-inventory', payload);
    }
    await fetchInventory();
    dialogOpen.value = false;
    resetForm();
    toast.success(isEditing.value ? 'Leather updated successfully' : 'Leather created successfully');
    
    // Refresh notifications immediately after inventory change
    if (typeof window !== 'undefined' && window.__notificationsRefresh) {
      window.__notificationsRefresh();
    }
  } catch (error) {
    console.error('Error saving leather:', error);
    const errorMessage = error.response?.data?.message || error.response?.data?.errors
      ? (error.response.data.errors ? JSON.stringify(error.response.data.errors) : error.response.data.message)
      : 'Error saving leather';
    toast.error('Error saving leather', errorMessage);
  }
}

function resetForm() {
  form.value = {
    id: null,
    leather_name: '',
    brand_make: '',
    supplier_id: '',
    quantity_sqft: 0,
    low_stock_threshold: null,
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
