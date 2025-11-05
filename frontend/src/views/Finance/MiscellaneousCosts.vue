<template>
  <div class="space-y-6">
    <ActionBar title="Miscellaneous Costs" description="Manage miscellaneous costs and adjustments">
      <template #actions>
        <Button @click="openCreateDialog">
          <Plus class="mr-2 h-4 w-4" />
          Add Cost
        </Button>
      </template>
    </ActionBar>

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
    <Dialog v-model="dialogOpen" :title="dialogTitle" :description="dialogDescription">
      <form @submit.prevent="saveCost" class="space-y-4">
        <div class="space-y-2">
          <Label for="description">Description *</Label>
          <Input id="description" v-model="form.description" required />
        </div>
        <div class="space-y-2">
          <Label for="amount">Amount *</Label>
          <Input id="amount" type="number" step="0.01" v-model="form.amount" required />
        </div>
        <div class="space-y-2">
          <Label for="type">Type *</Label>
          <Select v-model="form.type">
            <SelectItem value="adjustment">Adjustment</SelectItem>
            <SelectItem value="other">Other</SelectItem>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="notes">Notes</Label>
          <Input id="notes" v-model="form.notes" />
        </div>
      </form>
      <template #footer>
        <Button type="button" variant="outline" @click="dialogOpen = false">Cancel</Button>
        <Button type="button" @click="saveCost">Save</Button>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Plus, Edit, Trash2 } from 'lucide-vue-next';
import apiClient from '@/api/client';
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
  if (confirm(`Delete ${cost.description}?`)) {
    try {
      await apiClient.delete(`/miscellaneous-costs/${cost.id}`);
      await loadCosts();
    } catch (error) {
      console.error('Error deleting cost:', error);
    }
  }
};

const saveCost = async () => {
  try {
    if (editingCost.value) {
      await apiClient.put(`/miscellaneous-costs/${editingCost.value.id}`, form.value);
    } else {
      await apiClient.post('/miscellaneous-costs', form.value);
    }
    dialogOpen.value = false;
    await loadCosts();
  } catch (error) {
    console.error('Error saving cost:', error);
    alert(error.response?.data?.message || 'Error saving cost');
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
