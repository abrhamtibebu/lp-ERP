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
          <div class="overflow-y-auto max-h-[calc(100vh-300px)]">
            <table class="w-full table-auto">
              <thead class="sticky top-0 bg-gray-50 z-10">
                <tr class="border-b">
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Import Invoice</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted By</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received By</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in filteredAccessories" :key="item.id" class="hover:bg-gray-50">
                  <td class="px-4 py-4 text-sm font-medium text-gray-900">
                    {{ item.name }}
                  </td>
                  <td class="px-4 py-4">
                    <div class="flex items-center gap-2">
                      <span class="text-sm text-gray-900">
                        {{ item.quantity }} {{ item.unit || 'pcs' }}
                      </span>
                      <AlertCircle v-if="isLowStock(item)" class="h-4 w-4 text-orange-500" title="Low Stock" />
                    </div>
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ item.import_invoice_number || 'N/A' }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ item.submitted_by?.name || 'N/A' }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ item.received_by?.name || 'N/A' }}
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
                <tr v-if="filteredAccessories.length === 0">
                  <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
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
    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Accessory' : 'Add New Accessory'" class="max-w-3xl">
      <form @submit.prevent="saveAccessory" class="space-y-6">
        <!-- Accessory Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Package class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Accessory Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2 md:col-span-2">
              <Label for="name" class="flex items-center gap-2">
                <Package class="h-4 w-4 text-gray-500" />
                Name *
              </Label>
              <Input id="name" v-model="form.name" placeholder="Enter accessory name" required />
              <p v-if="errors.name" class="text-xs text-red-600">{{ errors.name }}</p>
            </div>
            <div class="space-y-2">
              <Label for="quantity" class="flex items-center gap-2">
                <Scale class="h-4 w-4 text-gray-500" />
                Quantity *
              </Label>
              <Input id="quantity" v-model.number="form.quantity" type="number" step="0.01" required min="0" placeholder="0.00" />
              <p v-if="errors.quantity" class="text-xs text-red-600">{{ errors.quantity }}</p>
            </div>
            <div class="space-y-2">
              <Label for="unit" class="flex items-center gap-2">
                <Tag class="h-4 w-4 text-gray-500" />
                Unit
              </Label>
              <Select v-model="form.unit" placeholder="Select unit">
                <SelectItem value="pieces">pieces</SelectItem>
                <SelectItem value="spools">spools</SelectItem>
                <SelectItem value="bottles">bottles</SelectItem>
                <SelectItem value="sets">sets</SelectItem>
                <SelectItem value="kg">kg</SelectItem>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="low_stock_threshold" class="flex items-center gap-2">
                <AlertCircle class="h-4 w-4 text-gray-500" />
                Low Stock Threshold
              </Label>
              <Input id="low_stock_threshold" v-model.number="form.low_stock_threshold" type="number" step="0.01" min="0" placeholder="0.00" />
              <p class="text-xs text-gray-500">Alert when quantity falls below this threshold</p>
            </div>
          </div>
        </div>

        <!-- Document Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <FileText class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Document Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="import_invoice_number" class="flex items-center gap-2">
                <Hash class="h-4 w-4 text-gray-500" />
                Import Invoice Number
              </Label>
              <Input id="import_invoice_number" v-model="form.import_invoice_number" placeholder="Enter import invoice number" />
            </div>
            <div class="space-y-2 md:col-span-2">
              <Label for="file" class="flex items-center gap-2">
                <Upload class="h-4 w-4 text-gray-500" />
                File Upload
              </Label>
              <div class="flex items-center justify-center w-full">
                <label
                  for="file"
                  class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors"
                >
                  <div class="flex flex-col items-center justify-center pt-3 pb-4">
                    <Upload class="w-6 h-6 mb-1 text-gray-500" />
                    <p class="mb-1 text-xs text-gray-500">
                      <span class="font-semibold">Click to upload</span> or drag and drop
                    </p>
                    <p class="text-xs text-gray-500">PDF, DOC, DOCX (MAX. 10MB)</p>
                  </div>
                  <input
                    id="file"
                    type="file"
                    class="hidden"
                    @change="handleFileChange"
                    accept=".pdf,.doc,.docx"
                  />
                </label>
              </div>
              <p v-if="form.file_path && !form.file" class="text-xs text-blue-600 mt-1">Current file: {{ form.file_path }}</p>
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
              <p v-if="errors.submitted_by" class="text-xs text-red-600">{{ errors.submitted_by }}</p>
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
              <p v-if="errors.received_by" class="text-xs text-red-600">{{ errors.received_by }}</p>
            </div>
          </div>
        </div>
      </form>
      <template #footer>
        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="dialogOpen = false">
            Cancel
          </Button>
          <Button type="button" @click="saveAccessory" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            {{ isEditing ? 'Update Accessory' : 'Add Accessory' }}
          </Button>
        </div>
      </template>
    </Dialog>

    <!-- Quantity Adjustment Dialog -->
    <Dialog v-model="adjustDialogOpen" title="Adjust Accessory Quantity" class="max-w-3xl">
      <form @submit.prevent="saveAdjustment" class="space-y-6">
        <!-- Accessory Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Package class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Accessory Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label class="flex items-center gap-2">
                <Package class="h-4 w-4 text-gray-500" />
                Accessory Item
              </Label>
              <Input :value="selectedItem?.name" disabled placeholder="Accessory name" />
            </div>
            <div class="space-y-2">
              <Label class="flex items-center gap-2">
                <Scale class="h-4 w-4 text-gray-500" />
                Current Quantity
              </Label>
              <Input :value="selectedItem ? selectedItem.quantity + ' ' + (selectedItem.unit || 'pcs') : ''" disabled placeholder="Current quantity" />
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
                <Hash class="h-4 w-4 text-gray-500" />
                Quantity *
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
                      Added {{ formatNumber(adj.quantity) }} {{ selectedItem?.unit || 'pcs' }}
                    </Badge>
                    <Badge :variant="adj.adjustment_type === 'deduct' ? 'default' : 'secondary'" class="bg-red-100 text-red-800" v-else>
                      Deducted {{ formatNumber(adj.quantity) }} {{ selectedItem?.unit || 'pcs' }}
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
import { Plus, Search, AlertCircle, Package, Scale, Tag, Hash, FileText, Upload, User, Calendar } from 'lucide-vue-next';
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

const accessories = ref([]);
const users = ref([]);
const stats = ref({
  total_items: 0,
  low_stock_items: 0,
  recent_imports: 0,
});
const dialogOpen = ref(false);
const isEditing = ref(false);
const adjustDialogOpen = ref(false);
const logsDialogOpen = ref(false);
const searchQuery = ref('');
const errors = ref({});
const selectedItem = ref(null);

const form = ref({
  id: null,
  name: '',
  quantity: 0,
  low_stock_threshold: null,
  unit: 'pieces',
  import_invoice_number: '',
  file: null,
  file_path: null,
  submitted_by: '',
  received_by: '',
});

const adjustForm = ref({
  adjustment_type: 'add',
  quantity: 0,
  notes: '',
});

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
  const threshold = item.low_stock_threshold ?? 500; // Default threshold if not set
  return item.quantity < threshold;
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
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
};

const handleFileChange = (event) => {
  form.value.file = event.target.files[0] || null;
};

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
    const response = await apiClient.get(`/accessories-inventory/${item.id}`);
    selectedItem.value = response.data;
    logsDialogOpen.value = true;
  } catch (error) {
    console.error('Error fetching adjustments:', error);
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
    const response = await apiClient.post(`/accessories-inventory/${selectedItem.value.id}/adjust`, adjustForm.value);
    await fetchAccessories();
    adjustDialogOpen.value = false;
    toast.success('Quantity adjusted successfully');
    if (response.data.inventory) {
      const index = accessories.value.findIndex(i => i.id === selectedItem.value.id);
      if (index !== -1) {
        accessories.value[index] = response.data.inventory;
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

async function fetchAccessories() {
  try {
    const response = await apiClient.get('/accessories-inventory');
    accessories.value = response.data.inventory || response.data || [];
    stats.value = response.data.stats || {
      total_items: new Set(accessories.value.map(item => item.name)).size,
      low_stock_items: accessories.value.filter(item => isLowStock(item)).length,
      recent_imports: accessories.value.filter(item => {
        const created = new Date(item.created_at);
        const now = new Date();
        return created.getMonth() === now.getMonth() && created.getFullYear() === now.getFullYear();
      }).length,
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
    low_stock_threshold: accessory.low_stock_threshold || null,
    unit: accessory.unit || 'pieces',
    import_invoice_number: accessory.import_invoice_number || '',
    file: null,
    file_path: accessory.file_path || null,
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
    const formData = new FormData();
    formData.append('name', form.value.name.trim());
    formData.append('quantity', parseFloat(form.value.quantity));
    if (form.value.low_stock_threshold !== null) {
      formData.append('low_stock_threshold', parseFloat(form.value.low_stock_threshold));
    }
    formData.append('unit', form.value.unit || 'pieces');
    if (form.value.import_invoice_number) {
      formData.append('import_invoice_number', form.value.import_invoice_number);
    }
    if (form.value.file) {
      formData.append('file', form.value.file);
    }
    formData.append('submitted_by', parseInt(form.value.submitted_by));
    formData.append('received_by', parseInt(form.value.received_by));
    
    if (isEditing.value) {
      await apiClient.put(`/accessories-inventory/${form.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    } else {
      await apiClient.post('/accessories-inventory', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    }
    await fetchAccessories();
    dialogOpen.value = false;
    resetForm();
    toast.success(isEditing.value ? 'Accessory updated successfully' : 'Accessory created successfully');
    
    // Refresh notifications immediately after inventory change
    if (typeof window !== 'undefined' && window.__notificationsRefresh) {
      window.__notificationsRefresh();
    }
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
      
      // Show toast for general errors
      if (error.response.data.message) {
        toast.error('Error saving accessory', error.response.data.message);
      }
    } else {
      toast.error('Error saving accessory', error.response?.data?.message || 'Error saving accessory');
    }
  }
}

function resetForm() {
  form.value = {
    id: null,
    name: '',
    quantity: 0,
    low_stock_threshold: null,
    unit: 'pieces',
    import_invoice_number: '',
    file: null,
    file_path: null,
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
