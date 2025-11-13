<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#8B4513]">Miscellaneous Costs</h1>
        <p class="text-gray-600 mt-1">Manage miscellaneous costs and adjustments</p>
      </div>
      <Button @click="openCreateDialog" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
        <Plus class="mr-2 h-4 w-4" />
        Add Cost
      </Button>
    </div>

    <Card>
      <CardContent class="p-0">
        <DataTable :data="costs" :columns="columns">
          <template #cell-amount="{ row }">
            <span class="font-semibold">${{ parseFloat(row.amount).toLocaleString() }}</span>
          </template>
          <template #cell-type="{ row }">
            <Badge :variant="row.type === 'adjustment' ? 'warning' : 'secondary'">
              {{ row.type }}
            </Badge>
          </template>
          <template #rowActions="{ row }">
            <div class="flex items-center space-x-2">
              <Button variant="ghost" size="sm" @click="editCost(row)">
                <Edit class="h-4 w-4" />
              </Button>
              <Button variant="ghost" size="sm" @click="deleteCost(row)">
                <Trash2 class="h-4 w-4 text-destructive" />
              </Button>
            </div>
          </template>
        </DataTable>
      </CardContent>
    </Card>

    <!-- Create/Edit Dialog -->
    <Dialog v-model="dialogOpen" :title="dialogTitle" :description="dialogDescription" class="max-w-3xl">
      <form @submit.prevent="saveCost" class="space-y-6">
        <!-- Cost Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <DollarSign class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Cost Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2 md:col-span-2">
              <Label for="description" class="flex items-center gap-2">
                <FileText class="h-4 w-4 text-gray-500" />
                Description *
              </Label>
              <Input id="description" v-model="form.description" placeholder="Enter cost description" required />
            </div>
            <div class="space-y-2">
              <Label for="amount" class="flex items-center gap-2">
                <DollarSign class="h-4 w-4 text-gray-500" />
                Amount ($) *
              </Label>
              <Input id="amount" type="number" step="0.01" v-model="form.amount" required placeholder="0.00" min="0" />
            </div>
            <div class="space-y-2">
              <Label for="type" class="flex items-center gap-2">
                <Tag class="h-4 w-4 text-gray-500" />
                Type *
              </Label>
              <Select v-model="form.type" placeholder="Select type">
                <SelectItem value="adjustment">Adjustment</SelectItem>
                <SelectItem value="other">Other</SelectItem>
              </Select>
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
          <Button type="button" @click="saveCost" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            {{ editingCost ? 'Update Cost' : 'Create Cost' }}
          </Button>
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Plus, Edit, Trash2, DollarSign, FileText, Tag } from 'lucide-vue-next';
import apiClient from '@/api/client';
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
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const { toast } = useToast();
const { confirm } = useConfirm();

const costs = ref([]);
const dialogOpen = ref(false);
const editingCost = ref(null);

const form = ref({
  description: '',
  amount: '',
  type: 'other',
  notes: '',
});

const dialogTitle = computed(() => editingCost.value ? 'Edit Miscellaneous Cost' : 'Add Miscellaneous Cost');
const dialogDescription = computed(() => editingCost.value ? 'Update cost information' : 'Create a new miscellaneous cost');

const columns = [
  { key: 'description', label: 'Description', sortable: true },
  { key: 'amount', label: 'Amount', sortable: true },
  { key: 'type', label: 'Type', sortable: true },
  { key: 'notes', label: 'Notes', sortable: false },
];

const openCreateDialog = () => {
  editingCost.value = null;
  form.value = { description: '', amount: '', type: 'other', notes: '' };
  dialogOpen.value = true;
};

const editCost = (cost) => {
  editingCost.value = cost;
  form.value = { ...cost };
  dialogOpen.value = true;
};

const deleteCost = async (cost) => {
  const confirmed = await confirm(
    `Are you sure you want to delete ${cost.description}?`,
    'Delete Cost',
    'danger'
  );
  
  if (!confirmed) return;

  try {
    await apiClient.delete(`/miscellaneous-costs/${cost.id}`);
    await loadCosts();
    toast.success('Cost deleted successfully');
  } catch (error) {
    console.error('Error deleting cost:', error);
    toast.error('Error deleting cost', error.response?.data?.message || 'Error deleting cost');
  }
};

const saveCost = async () => {
  // Validation
  if (!form.value.description || form.value.description.trim() === '') {
    toast.warning('Please enter description');
    return;
  }
  
  if (!form.value.amount || form.value.amount <= 0) {
    toast.warning('Please enter a valid amount (greater than 0)');
    return;
  }
  
  if (!form.value.type || form.value.type.trim() === '') {
    toast.warning('Please select type');
    return;
  }

  try {
    const payload = {
      ...form.value,
      amount: parseFloat(form.value.amount),
    };
    
    if (editingCost.value) {
      await apiClient.put(`/miscellaneous-costs/${editingCost.value.id}`, payload);
    } else {
      await apiClient.post('/miscellaneous-costs', payload);
    }
    dialogOpen.value = false;
    await loadCosts();
    toast.success(editingCost.value ? 'Cost updated successfully' : 'Cost created successfully');
  } catch (error) {
    console.error('Error saving cost:', error);
    const errorMessage = error.response?.data?.message || error.response?.data?.errors
      ? (error.response.data.errors ? JSON.stringify(error.response.data.errors) : error.response.data.message)
      : 'Error saving cost';
    toast.error('Error saving cost', errorMessage);
  }
};

const loadCosts = async () => {
  try {
    const response = await apiClient.get('/miscellaneous-costs');
    costs.value = response.data?.data || response.data || [];
  } catch (error) {
    console.error('Error loading costs:', error);
  }
};

onMounted(() => {
  loadCosts();
});
</script>
