<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-3xl font-bold text-[#8B4513]">Product Costs</h1>
      <p class="text-gray-600 mt-1">Manage and lock product costs for production planning</p>
    </div>

    <Card>
      <CardContent class="p-0">
        <DataTable :data="productCosts" :columns="columns">
          <template #cell-product="{ row }">
            <div class="flex items-center gap-3">
              <span class="font-medium">{{ row.product?.product_name || 'N/A' }}</span>
              <Badge variant="outline">{{ row.product?.sku || '' }}</Badge>
            </div>
          </template>
          <template #cell-cost="{ row }">
            <span class="font-semibold">${{ parseFloat(row.cost).toLocaleString() }}</span>
          </template>
          <template #cell-is_locked="{ row }">
            <Badge :variant="row.is_locked ? 'default' : 'outline'">
              {{ row.is_locked ? 'Locked' : 'Unlocked' }}
            </Badge>
          </template>
          <template #cell-locked_at="{ row }">
            <span v-if="row.locked_at">{{ new Date(row.locked_at).toLocaleDateString() }}</span>
            <span v-else class="text-gray-400">-</span>
          </template>
          <template #rowActions="{ row }">
            <div class="flex gap-2">
              <Button variant="ghost" size="sm" @click="editCost(row)">
                <Edit class="h-4 w-4" />
              </Button>
              <Button 
                v-if="!row.is_locked" 
                variant="ghost" 
                size="sm" 
                @click="lockCost(row)" 
                :disabled="locking[row.id]"
                class="text-blue-600 hover:text-blue-700"
              >
                <Loader2 v-if="locking[row.id]" class="h-4 w-4 animate-spin" />
                <Lock v-else class="h-4 w-4" />
              </Button>
              <Button 
                v-if="row.is_locked" 
                variant="ghost" 
                size="sm" 
                @click="unlockCost(row)" 
                :disabled="unlocking[row.id]"
                class="text-green-600 hover:text-green-700"
              >
                <Loader2 v-if="unlocking[row.id]" class="h-4 w-4 animate-spin" />
                <Unlock v-else class="h-4 w-4" />
              </Button>
              <Button 
                variant="ghost" 
                size="sm" 
                @click="deleteCost(row.id)" 
                :disabled="deleting[row.id]"
                class="text-red-600 hover:text-red-700"
              >
                <Loader2 v-if="deleting[row.id]" class="h-4 w-4 animate-spin" />
                <Trash2 v-else class="h-4 w-4" />
              </Button>
            </div>
          </template>
        </DataTable>
      </CardContent>
    </Card>

    <Dialog v-model="dialogOpen" title="Edit Product Cost" class="max-w-3xl">
      <form @submit.prevent="saveCost" class="space-y-6">
        <!-- Cost Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <DollarSign class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Cost Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="space-y-2">
              <Label for="cost" class="flex items-center gap-2">
                <DollarSign class="h-4 w-4 text-gray-500" />
                Cost ($) *
              </Label>
              <Input id="cost" v-model.number="form.cost" type="number" step="0.01" required placeholder="0.00" min="0" />
            </div>
          </div>
        </div>

        <!-- Additional Information Section -->
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
                placeholder="Enter notes or comments..."
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
          <Button type="button" @click="saveCost" :disabled="saving" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            <Loader2 v-if="saving" class="h-4 w-4 mr-2 animate-spin" />
            {{ saving ? 'Updating...' : 'Update Cost' }}
          </Button>
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '@/api/client';
import { Edit, Trash2, Lock, Unlock, DollarSign, FileText, Loader2 } from 'lucide-vue-next';
import Card from '@/components/ui/Card.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Button from '@/components/ui/Button.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Dialog from '@/components/ui/Dialog.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Badge from '@/components/ui/Badge.vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const { toast } = useToast();
const { confirm } = useConfirm();

const saving = ref(false);
const locking = ref({});
const unlocking = ref({});
const deleting = ref({});

const productCosts = ref([]);
const dialogOpen = ref(false);
const editingCost = ref(null);
const form = ref({
  cost: 0,
  notes: '',
});

const columns = [
  { key: 'product', label: 'Product', sortable: true },
  { key: 'cost', label: 'Cost', sortable: true },
  { key: 'is_locked', label: 'Status', sortable: true },
  { key: 'locked_at', label: 'Locked At', sortable: true },
];

async function fetchProductCosts() {
  try {
    const response = await apiClient.get('/product-costs');
    productCosts.value = response.data;
  } catch (error) {
    console.error('Error fetching product costs:', error);
  }
}

function editCost(cost) {
  editingCost.value = cost;
  form.value = {
    cost: cost.cost,
    notes: cost.notes || '',
  };
  dialogOpen.value = true;
}

async function saveCost() {
  try {
    saving.value = true;
    await apiClient.put(`/product-costs/${editingCost.value.id}`, form.value);
    await fetchProductCosts();
    dialogOpen.value = false;
    editingCost.value = null;
    toast.success('Product cost updated successfully');
  } catch (error) {
    console.error('Error saving cost:', error);
    toast.error('Error saving cost', error.response?.data?.message || 'Error saving cost');
  } finally {
    saving.value = false;
  }
}

async function lockCost(cost) {
  try {
    locking.value[cost.id] = true;
    await apiClient.put(`/product-costs/${cost.id}`, { is_locked: true });
    await fetchProductCosts();
    toast.success('Cost locked successfully');
  } catch (error) {
    console.error('Error locking cost:', error);
    toast.error('Error locking cost', error.response?.data?.message || 'Error locking cost');
  } finally {
    locking.value[cost.id] = false;
  }
}

async function unlockCost(cost) {
  try {
    unlocking.value[cost.id] = true;
    await apiClient.put(`/product-costs/${cost.id}`, { is_locked: false });
    await fetchProductCosts();
    toast.success('Cost unlocked successfully');
  } catch (error) {
    console.error('Error unlocking cost:', error);
    toast.error('Error unlocking cost', error.response?.data?.message || 'Error unlocking cost');
  } finally {
    unlocking.value[cost.id] = false;
  }
}

async function deleteCost(id) {
  const confirmed = await confirm({
    title: 'Delete Product Cost',
    message: 'Are you sure you want to delete this product cost? This action cannot be undone.',
    type: 'danger'
  });

  if (!confirmed) return;

  try {
    deleting.value[id] = true;
    await apiClient.delete(`/product-costs/${id}`);
    await fetchProductCosts();
    toast.success('Product cost deleted successfully');
  } catch (error) {
    console.error('Error deleting product cost:', error);
    const errorMessage = error.response?.data?.message || 'Error deleting product cost';
    toast.error('Error deleting product cost', errorMessage);
  } finally {
    deleting.value[id] = false;
  }
}

onMounted(fetchProductCosts);
</script>
