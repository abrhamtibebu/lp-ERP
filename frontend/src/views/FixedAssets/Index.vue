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
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b bg-gray-50">
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Year</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Value</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Value</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Depreciation</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="asset in filteredAssets" :key="asset.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ asset.description }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :class="getCategoryClass(asset)">
                      {{ getCategory(asset) }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ getPurchaseYear(asset) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${{ formatCurrency(getPurchaseValue(asset)) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${{ formatCurrency(getCurrentValue(asset)) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ asset.depreciation || 0 }}% p.a.
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge class="bg-green-100 text-green-800">Active</Badge>
                  </td>
                </tr>
                <tr v-if="filteredAssets.length === 0">
                  <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
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
    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Asset' : 'Add New Asset'">
      <form @submit.prevent="saveAsset" class="space-y-4">
        <div class="space-y-2">
          <Label for="description">Description</Label>
          <Input id="description" v-model="form.description" required />
        </div>
        <div class="space-y-2">
          <Label for="purchase_year">Purchase Year</Label>
          <Input id="purchase_year" v-model="form.purchase_year" type="date" />
        </div>
        <div class="space-y-2">
          <Label for="depreciation">Depreciation Rate (% p.a.)</Label>
          <Input id="depreciation" v-model.number="form.depreciation" type="number" step="0.01" required min="0" max="100" />
        </div>
        <div class="space-y-2">
          <Label for="notes">Notes</Label>
          <Input id="notes" v-model="form.notes" />
        </div>
      </form>
      <template #footer>
        <Button type="button" variant="outline" @click="dialogOpen = false">Cancel</Button>
        <Button type="button" @click="saveAsset" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">Save</Button>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Plus, Search } from 'lucide-vue-next';
import apiClient from '@/api/client';
import Card from '@/components/ui/Card.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Button from '@/components/ui/Button.vue';
import Badge from '@/components/ui/Badge.vue';
import Dialog from '@/components/ui/Dialog.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';

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
  purchase_year: '',
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
  // Simple categorization based on description keywords
  const desc = (asset.description || '').toLowerCase();
  if (desc.includes('cutting') || desc.includes('sewing') || desc.includes('machine')) {
    return 'Production Equipment';
  } else if (desc.includes('quality') || desc.includes('inspection')) {
    return 'Quality Control';
  } else if (desc.includes('forklift') || desc.includes('warehouse') || desc.includes('logistics')) {
    return 'Logistics';
  } else {
    return 'Office Equipment';
  }
};

const getCategoryClass = (asset) => {
  const category = getCategory(asset);
  const classes = {
    'Production Equipment': 'bg-orange-100 text-orange-800',
    'Quality Control': 'bg-green-100 text-green-800',
    'Logistics': 'bg-blue-100 text-blue-800',
    'Office Equipment': 'bg-gray-100 text-gray-800',
  };
  return classes[category] || 'bg-gray-100 text-gray-800';
};

const getPurchaseYear = (asset) => {
  if (!asset.purchase_year) return 'N/A';
  const date = new Date(asset.purchase_year);
  return date.getFullYear().toString();
};

const getPurchaseValue = (asset) => {
  // Estimate purchase value (in real system, this would be stored)
  return 50000;
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
    purchase_year: asset.purchase_year ? new Date(asset.purchase_year).toISOString().split('T')[0] : '',
    depreciation: asset.depreciation || 0,
    notes: asset.notes || '',
  };
  dialogOpen.value = true;
}

async function saveAsset() {
  try {
    if (isEditing.value) {
      await apiClient.put(`/fixed-assets/${form.value.id}`, form.value);
    } else {
      await apiClient.post('/fixed-assets', form.value);
    }
    await fetchAssets();
    dialogOpen.value = false;
    resetForm();
  } catch (error) {
    console.error('Error saving asset:', error);
    alert(error.response?.data?.message || 'Error saving asset');
  }
}

function resetForm() {
  form.value = {
    id: null,
    description: '',
    purchase_year: '',
    depreciation: 0,
    notes: '',
  };
  isEditing.value = false;
}

onMounted(() => {
  fetchAssets();
});
</script>
