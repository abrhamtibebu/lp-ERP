<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#8B4513]">Fixed Assets</h1>
        <p class="text-gray-600 mt-1">Track equipment and machinery</p>
      </div>
      <Button @click="dialogOpen = true" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
        <Plus class="h-4 w-4 mr-2" />
        Add Asset
      </Button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-gray-900">{{ stats.total_assets || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">Registered items</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-gray-900">${{ formatCurrency(stats.current_value || 0) }}</div>
          <div class="text-sm text-gray-600 mt-1">After depreciation</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-orange-600">${{ formatCurrency(stats.total_depreciation || 0) }}</div>
          <div class="text-sm text-gray-600 mt-1">Cumulative</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-green-600">{{ stats.active_assets || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">In operation</div>
        </CardContent>
      </Card>
    </div>

    <!-- Asset Register -->
    <div class="space-y-4">
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-900">Asset Register</h2>
        <div class="relative w-64">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
          <Input
            v-model="searchQuery"
            placeholder="Search assets..."
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
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Year</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Value</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Value</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Depreciation</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="asset in filteredAssets" :key="asset.id" class="hover:bg-gray-50">
                  <td class="px-4 py-4 text-sm font-medium text-gray-900">
                    {{ asset.description }}
                  </td>
                  <td class="px-4 py-4">
                    <Badge :class="getCategoryClass(asset)">
                      {{ getCategory(asset) }}
                    </Badge>
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ getPurchaseYear(asset) }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    ${{ formatCurrency(getPurchaseValue(asset)) }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    ${{ formatCurrency(getCurrentValue(asset)) }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ asset.depreciation || 0 }}% p.a.
                  </td>
                  <td class="px-4 py-4">
                    <Badge class="bg-green-100 text-green-800">Active</Badge>
                  </td>
                  <td class="px-4 py-4 text-sm">
                    <div class="flex gap-2">
                      <Button variant="ghost" size="sm" @click="editAsset(asset)">
                        <Edit class="h-4 w-4" />
                      </Button>
                      <Button 
                        variant="ghost" 
                        size="sm" 
                        @click="deleteAsset(asset.id)" 
                        :disabled="deleting[asset.id]"
                        class="text-red-600 hover:text-red-700"
                      >
                        <Loader2 v-if="deleting[asset.id]" class="h-4 w-4 animate-spin" />
                        <Trash2 v-else class="h-4 w-4" />
                      </Button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredAssets.length === 0">
                  <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                    No assets found
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Add/Edit Dialog -->
    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Asset' : 'Add New Asset'" class="max-w-3xl">
      <form @submit.prevent="saveAsset" class="space-y-6">
        <!-- Asset Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Package class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Asset Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2 md:col-span-2">
              <Label for="description" class="flex items-center gap-2">
                <Package class="h-4 w-4 text-gray-500" />
                Description *
              </Label>
              <Input id="description" v-model="form.description" placeholder="Enter asset description" required />
            </div>
            <div class="space-y-2">
              <Label for="category" class="flex items-center gap-2">
                <Tag class="h-4 w-4 text-gray-500" />
                Category *
              </Label>
              <Select v-model="form.category" placeholder="Select category">
                <SelectItem value="">Select category</SelectItem>
                <SelectItem value="Production Equipment">Production Equipment</SelectItem>
                <SelectItem value="Office Equipment">Office Equipment</SelectItem>
                <SelectItem value="Quality Control">Quality Control</SelectItem>
                <SelectItem value="Logistics">Logistics</SelectItem>
                <SelectItem value="Maintenance Equipment">Maintenance Equipment</SelectItem>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="purchase_year" class="flex items-center gap-2">
                <Calendar class="h-4 w-4 text-gray-500" />
                Purchase Date *
              </Label>
              <Input id="purchase_year" v-model="form.purchase_year" type="date" placeholder="Select purchase date" required />
            </div>
            <div class="space-y-2">
              <Label for="purchase_value" class="flex items-center gap-2">
                <DollarSign class="h-4 w-4 text-gray-500" />
                Purchase Value ($)
              </Label>
              <Input id="purchase_value" v-model.number="form.purchase_value" type="number" step="0.01" min="0" placeholder="0.00" />
              <p class="text-xs text-gray-500">Enter the original purchase price</p>
            </div>
          </div>
        </div>

        <!-- Depreciation Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Percent class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Depreciation</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="depreciation" class="flex items-center gap-2">
                <Percent class="h-4 w-4 text-gray-500" />
                Depreciation Rate (% p.a.) *
              </Label>
              <Input id="depreciation" v-model.number="form.depreciation" type="number" step="0.01" required min="0" max="100" placeholder="0.00" />
              <p class="text-xs text-gray-500">Enter annual depreciation rate (0-100%)</p>
            </div>
          </div>
        </div>

        <!-- Additional Notes Section -->
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
                placeholder="Enter additional notes or comments..."
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
          <Button type="button" @click="saveAsset" :disabled="saving" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            <Loader2 v-if="saving" class="h-4 w-4 mr-2 animate-spin" />
            {{ saving ? (isEditing ? 'Updating...' : 'Creating...') : (isEditing ? 'Update Asset' : 'Create Asset') }}
          </Button>
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Plus, Search, Building2, Tag, Calendar, Percent, FileText, Package, DollarSign, Edit, Trash2, Loader2 } from 'lucide-vue-next';
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

const saving = ref(false);
const deleting = ref({});

const assets = ref([]);
const stats = ref({
  total_assets: 0,
  current_value: 0,
  total_depreciation: 0,
  active_assets: 0,
});
const dialogOpen = ref(false);
const isEditing = ref(false);
const searchQuery = ref('');

const form = ref({
  id: null,
  description: '',
  category: '',
  purchase_year: '',
  purchase_value: null,
  depreciation: 0,
  notes: '',
});

const filteredAssets = computed(() => {
  if (!searchQuery.value) return assets.value;
  
  const query = searchQuery.value.toLowerCase();
  return assets.value.filter(asset => {
    const description = (asset.description || '').toLowerCase();
    return description.includes(query);
  });
});

const getCategory = (asset) => {
  return asset.category || 'Uncategorized';
};

const getCategoryClass = (asset) => {
  const category = getCategory(asset);
  const classes = {
    'Production Equipment': 'bg-orange-100 text-orange-800',
    'Quality Control': 'bg-green-100 text-green-800',
    'Logistics': 'bg-blue-100 text-blue-800',
    'Office Equipment': 'bg-gray-100 text-gray-800',
    'Maintenance Equipment': 'bg-purple-100 text-purple-800',
    'Uncategorized': 'bg-gray-100 text-gray-800',
  };
  return classes[category] || 'bg-gray-100 text-gray-800';
};

const getPurchaseYear = (asset) => {
  if (!asset.purchase_year) return 'N/A';
  const date = new Date(asset.purchase_year);
  return date.getFullYear().toString();
};

const getPurchaseValue = (asset) => {
  return asset.purchase_value || 0;
};

const getCurrentValue = (asset) => {
  if (!asset.purchase_year || !asset.depreciation) {
    return getPurchaseValue(asset);
  }
  
  const yearsSincePurchase = new Date().getFullYear() - new Date(asset.purchase_year).getFullYear();
  const depreciationRate = (asset.depreciation || 0) / 100;
  const purchaseValue = getPurchaseValue(asset);
  const currentValue = purchaseValue * Math.pow(1 - depreciationRate, Math.max(yearsSincePurchase, 0));
  
  return Math.max(currentValue, 0);
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

async function fetchAssets() {
  try {
    const response = await apiClient.get('/fixed-assets');
    assets.value = response.data.assets || response.data || [];
    stats.value = response.data.stats || {
      total_assets: assets.value.length,
      current_value: 0,
      total_depreciation: 0,
      active_assets: assets.value.length,
    };
  } catch (error) {
    console.error('Error fetching assets:', error);
  }
}

function editAsset(asset) {
  isEditing.value = true;
  form.value = {
    id: asset.id,
    description: asset.description,
    category: asset.category || '',
    purchase_year: asset.purchase_year ? new Date(asset.purchase_year).toISOString().split('T')[0] : '',
    purchase_value: asset.purchase_value || null,
    depreciation: asset.depreciation || 0,
    notes: asset.notes || '',
  };
  dialogOpen.value = true;
}

async function saveAsset() {
  // Validation
  if (!form.value.description || form.value.description.trim() === '') {
    toast.warning('Please enter asset description');
    return;
  }
  
  if (!form.value.category || form.value.category.trim() === '') {
    toast.warning('Please select category');
    return;
  }
  
  if (!form.value.purchase_year) {
    toast.warning('Please enter purchase year');
    return;
  }
  
  if (form.value.depreciation < 0 || form.value.depreciation > 100) {
    toast.warning('Depreciation rate must be between 0 and 100');
    return;
  }

  try {
    saving.value = true;
    if (isEditing.value) {
      await apiClient.put(`/fixed-assets/${form.value.id}`, form.value);
    } else {
      await apiClient.post('/fixed-assets', form.value);
    }
    await fetchAssets();
    dialogOpen.value = false;
    resetForm();
    toast.success(isEditing.value ? 'Asset updated successfully' : 'Asset created successfully');
  } catch (error) {
    console.error('Error saving asset:', error);
    const errorMessage = error.response?.data?.message || error.response?.data?.errors
      ? (error.response.data.errors ? JSON.stringify(error.response.data.errors) : error.response.data.message)
      : 'Error saving asset';
    toast.error('Error saving asset', errorMessage);
  } finally {
    saving.value = false;
  }
}

function resetForm() {
  form.value = {
    id: null,
    description: '',
    category: '',
    purchase_year: '',
    purchase_value: null,
    depreciation: 0,
    notes: '',
  };
  isEditing.value = false;
}

async function deleteAsset(id) {
  const confirmed = await confirm({
    title: 'Delete Asset',
    message: 'Are you sure you want to delete this asset? This action cannot be undone.',
    type: 'danger'
  });

  if (!confirmed) return;

  try {
    deleting.value[id] = true;
    await apiClient.delete(`/fixed-assets/${id}`);
    await fetchAssets();
    toast.success('Asset deleted successfully');
  } catch (error) {
    console.error('Error deleting asset:', error);
    const errorMessage = error.response?.data?.message || 'Error deleting asset';
    toast.error('Error deleting asset', errorMessage);
  } finally {
    deleting.value[id] = false;
  }
}

onMounted(() => {
  fetchAssets();
});
</script>
