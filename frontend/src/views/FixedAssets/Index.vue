<template>
  <div class="space-y-6">
    <ActionBar title="Fixed Assets" description="Manage fixed assets and depreciation" @add-new="dialogOpen = true" />

    <Card>
      <CardContent class="p-0">
        <DataTable :data="assets" :columns="columns">
          <template #cell-description="{ row }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center text-white font-semibold">
                {{ row.description.charAt(0).toUpperCase() }}
              </div>
              <span class="font-medium">{{ row.description }}</span>
            </div>
          </template>
          <template #cell-depreciation="{ row }">
            <span class="font-semibold">${{ parseFloat(row.depreciation).toLocaleString() }}</span>
          </template>
          <template #cell-purchase_year="{ row }">
            <span>{{ new Date(row.purchase_year).getFullYear() }}</span>
          </template>
          <template #rowActions="{ row }">
            <DropdownMenu>
              <template #trigger>
                <Button variant="ghost" class="h-8 w-8 p-0">
                  <span class="sr-only">Open menu</span>
                  <MoreHorizontal class="h-4 w-4" />
                </Button>
              </template>
              <DropdownMenuItem @click="editAsset(row)">Edit</DropdownMenuItem>
              <DropdownMenuItem @click="deleteAsset(row.id)" class="text-destructive">Delete</DropdownMenuItem>
            </DropdownMenu>
          </template>
        </DataTable>
      </CardContent>
    </Card>

    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Asset' : 'Add New Asset'">
      <form @submit.prevent="saveAsset" class="space-y-4">
        <div class="space-y-2">
          <Label for="description">Description</Label>
          <Input id="description" v-model="form.description" required />
        </div>
        <div class="space-y-2">
          <Label for="purchase_year">Purchase Year</Label>
          <Input id="purchase_year" v-model="form.purchase_year" type="date" required />
        </div>
        <div class="space-y-2">
          <Label for="depreciation">Depreciation Amount</Label>
          <Input id="depreciation" v-model.number="form.depreciation" type="number" step="0.01" required />
        </div>
        <div class="space-y-2">
          <Label for="notes">Notes</Label>
          <Input id="notes" v-model="form.notes" />
        </div>
      </form>
      <template #footer>
        <Button type="button" variant="outline" @click="dialogOpen = false">Cancel</Button>
        <Button type="button" @click="saveAsset">Save</Button>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '@/api/client';
import { MoreHorizontal } from 'lucide-vue-next';
import ActionBar from '@/components/layout/ActionBar.vue';
import Card from '@/components/ui/Card.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Button from '@/components/ui/Button.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Dialog from '@/components/ui/Dialog.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import DropdownMenu from '@/components/ui/DropdownMenu.vue';
import DropdownMenuItem from '@/components/ui/DropdownMenuItem.vue';

const assets = ref([]);
const dialogOpen = ref(false);
const isEditing = ref(false);
const form = ref({
  id: null,
  description: '',
  purchase_year: '',
  depreciation: 0,
  notes: '',
});

const columns = [
  { key: 'description', label: 'Description', sortable: true },
  { key: 'purchase_year', label: 'Purchase Year', sortable: true },
  { key: 'depreciation', label: 'Depreciation', sortable: true },
];

async function fetchAssets() {
  try {
    const response = await apiClient.get('/fixed-assets');
    assets.value = response.data;
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
    depreciation: asset.depreciation,
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
  }
}

async function deleteAsset(id) {
  if (confirm('Are you sure you want to delete this asset?')) {
    try {
      await apiClient.delete(`/fixed-assets/${id}`);
      await fetchAssets();
    } catch (error) {
      console.error('Error deleting asset:', error);
    }
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

onMounted(fetchAssets);
</script>
