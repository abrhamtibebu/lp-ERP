<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-3xl font-bold text-[#8B4513]">Finished Goods</h1>
      <p class="text-gray-600 mt-1">Completed products ready for shipment</p>
    </div>

    <Card>
      <CardContent class="p-0">
        <div class="overflow-y-auto max-h-[calc(100vh-300px)]">
          <table class="w-full table-auto">
            <thead class="sticky top-0 bg-gray-50 z-10">
              <tr class="border-b">
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch ID</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed Date</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in finishedGoods" :key="item.id" class="hover:bg-gray-50">
                <td class="px-4 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-[#8B4513] to-[#6B3410] rounded-lg flex items-center justify-center text-white font-semibold">
                      {{ item.product?.product_name?.charAt(0).toUpperCase() || 'P' }}
                    </div>
                    <div>
                      <div class="font-medium">{{ item.product?.product_name || 'N/A' }}</div>
                      <div class="text-sm text-gray-500">{{ item.product?.sku || 'N/A' }}</div>
                      <div class="text-xs text-gray-400">{{ item.product?.color || '' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-4 text-sm text-gray-900">
                  {{ item.batch?.batch_id || 'N/A' }}
                </td>
                <td class="px-4 py-4">
                  <span class="font-semibold">{{ item.quantity }}</span>
                </td>
                <td class="px-4 py-4 text-sm text-gray-900">
                  {{ new Date(item.completed_at).toLocaleDateString() }}
                </td>
                <td class="px-4 py-4 text-sm">
                  <div class="flex gap-2">
                    <Button variant="ghost" size="sm" @click="openAdjustDialog(item)">Adjust</Button>
                    <Button variant="ghost" size="sm" @click="viewAdjustments(item)">Logs</Button>
                  </div>
                </td>
              </tr>
              <tr v-if="finishedGoods.length === 0">
                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                  No finished goods found
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardContent>
    </Card>

    <!-- Quantity Adjustment Dialog -->
    <Dialog v-model="adjustDialogOpen" title="Adjust Finished Goods Quantity" class="max-w-3xl">
      <form @submit.prevent="saveAdjustment" class="space-y-6">
        <!-- Product Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Package class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Product Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label class="flex items-center gap-2">
                <Package class="h-4 w-4 text-gray-500" />
                Product
              </Label>
              <Input :value="selectedItem?.product?.product_name || 'N/A'" disabled placeholder="Product name" />
            </div>
            <div class="space-y-2">
              <Label class="flex items-center gap-2">
                <Hash class="h-4 w-4 text-gray-500" />
                Current Quantity
              </Label>
              <Input :value="selectedItem ? selectedItem.quantity : ''" disabled placeholder="Current quantity" />
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
                <SelectItem value="add">Add Quantity (New Production)</SelectItem>
                <SelectItem value="deduct">Deduct Quantity (Export)</SelectItem>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="adjust_quantity" class="flex items-center gap-2">
                <Hash class="h-4 w-4 text-gray-500" />
                Quantity *
              </Label>
              <Input id="adjust_quantity" v-model.number="adjustForm.quantity" type="number" required min="1" placeholder="Enter quantity" />
            </div>
            <div class="space-y-2 md:col-span-2" v-if="adjustForm.adjustment_type === 'deduct'">
              <Label for="export_reference" class="flex items-center gap-2">
                <FileText class="h-4 w-4 text-gray-500" />
                Export Reference
              </Label>
              <Input id="export_reference" v-model="adjustForm.export_reference" placeholder="Enter export document number" />
            </div>
            <div class="space-y-2 md:col-span-2">
              <Label for="adjust_reason" class="flex items-center gap-2">
                <FileText class="h-4 w-4 text-gray-500" />
                Reason *
              </Label>
              <Input id="adjust_reason" v-model="adjustForm.reason" placeholder="Enter reason for adjustment" required />
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
                      <Plus class="h-3 w-3 mr-1" />
                      Added {{ adj.quantity }} units
                    </Badge>
                    <Badge :variant="adj.adjustment_type === 'deduct' ? 'default' : 'secondary'" class="bg-red-100 text-red-800" v-else>
                      <Minus class="h-3 w-3 mr-1" />
                      Deducted {{ adj.quantity }} units
                    </Badge>
                  </div>
                  <div class="text-sm text-gray-700 mb-1">{{ adj.reason || 'No reason provided' }}</div>
                  <div v-if="adj.export_reference" class="text-xs text-blue-600 mb-1">
                    Export Ref: {{ adj.export_reference }}
                  </div>
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
import { ref, onMounted } from 'vue';
import apiClient from '@/api/client';
import { Package, Plus, Minus, FileText, Hash, Tag } from 'lucide-vue-next';
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

const finishedGoods = ref([]);
const adjustDialogOpen = ref(false);
const logsDialogOpen = ref(false);
const selectedItem = ref(null);

const adjustForm = ref({
  adjustment_type: 'add',
  quantity: 0,
  reason: '',
  export_reference: '',
});

const loadFinishedGoods = async () => {
  try {
    const response = await apiClient.get('/finished-goods');
    finishedGoods.value = response.data || [];
  } catch (error) {
    console.error('Error loading finished goods:', error);
  }
};

function openAdjustDialog(item) {
  selectedItem.value = item;
  adjustForm.value = {
    adjustment_type: 'add',
    quantity: 0,
    reason: '',
    export_reference: '',
  };
  adjustDialogOpen.value = true;
}

async function viewAdjustments(item) {
  try {
    const response = await apiClient.get(`/finished-goods/${item.id}`);
    selectedItem.value = response.data;
    logsDialogOpen.value = true;
  } catch (error) {
    console.error('Error fetching adjustments:', error);
    selectedItem.value = item;
    logsDialogOpen.value = true;
  }
}

async function saveAdjustment() {
  if (!selectedItem.value || !adjustForm.value.quantity || adjustForm.value.quantity <= 0 || !adjustForm.value.reason) {
    toast.warning('Please fill in all required fields');
    return;
  }

  try {
    const response = await apiClient.post(`/finished-goods/${selectedItem.value.id}/adjust`, adjustForm.value);
    await loadFinishedGoods();
    adjustDialogOpen.value = false;
    toast.success('Quantity adjusted successfully');
    if (response.data.finishedGood) {
      const index = finishedGoods.value.findIndex(i => i.id === selectedItem.value.id);
      if (index !== -1) {
        finishedGoods.value[index] = response.data.finishedGood;
      }
    }
  } catch (error) {
    console.error('Error adjusting quantity:', error);
    toast.error('Error adjusting quantity', error.response?.data?.message || 'Error adjusting quantity');
  }
}

onMounted(() => {
  loadFinishedGoods();
});
</script>
