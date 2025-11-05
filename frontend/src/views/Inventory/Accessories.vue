<template>
  <div class="space-y-6">
    <ActionBar title="Accessories Inventory" description="Manage accessories and hardware inventory" @add-new="dialogOpen = true" />

    <Card>
      <CardContent class="p-0">
        <DataTable :data="accessories" :columns="columns">
          <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center text-white font-semibold">
                {{ row.name.charAt(0).toUpperCase() }}
              </div>
              <span class="font-medium">{{ row.name }}</span>
            </div>
          </template>
          <template #cell-quantity="{ row }">
            <span class="font-semibold">{{ row.quantity }} {{ row.unit }}</span>
          </template>
          <template #rowActions="{ row }">
            <DropdownMenu>
              <template #trigger>
                <Button variant="ghost" class="h-8 w-8 p-0">
                  <span class="sr-only">Open menu</span>
                  <MoreHorizontal class="h-4 w-4" />
                </Button>
              </template>
              <DropdownMenuItem @click="editAccessory(row)">Edit</DropdownMenuItem>
              <DropdownMenuItem @click="deleteAccessory(row.id)" class="text-destructive">Delete</DropdownMenuItem>
            </DropdownMenu>
          </template>
        </DataTable>
      </CardContent>
    </Card>

    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Accessory' : 'Add New Accessory'">
      <form @submit.prevent="saveAccessory" class="space-y-4">
        <div class="space-y-2">
          <Label for="name">Name</Label>
          <Input id="name" v-model="form.name" required />
        </div>
        <div class="space-y-2">
          <Label for="quantity">Quantity</Label>
          <Input id="quantity" v-model.number="form.quantity" type="number" step="0.01" required />
        </div>
        <div class="space-y-2">
          <Label for="unit">Unit</Label>
          <Input id="unit" v-model="form.unit" placeholder="pcs, kg, etc." />
        </div>
        <div class="space-y-2">
          <Label for="import_invoice_number">Import Invoice Number</Label>
          <Input id="import_invoice_number" v-model="form.import_invoice_number" />
        </div>
      </form>
      <template #footer>
        <Button type="button" variant="outline" @click="dialogOpen = false">Cancel</Button>
        <Button type="button" @click="saveAccessory">Save</Button>
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

const accessories = ref([]);
const dialogOpen = ref(false);
const isEditing = ref(false);
const form = ref({
  id: null,
  name: '',
  quantity: 0,
  unit: 'pcs',
  import_invoice_number: '',
});

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'quantity', label: 'Quantity', sortable: true },
  { key: 'unit', label: 'Unit', sortable: true },
  { key: 'import_invoice_number', label: 'Invoice Number', sortable: true },
];

async function fetchAccessories() {
  try {
    const response = await apiClient.get('/accessories-inventory');
    accessories.value = response.data;
  } catch (error) {
    console.error('Error fetching accessories:', error);
  }
}

function editAccessory(accessory) {
  isEditing.value = true;
  form.value = { ...accessory };
  dialogOpen.value = true;
}

async function saveAccessory() {
  try {
    if (isEditing.value) {
      await apiClient.put(`/accessories-inventory/${form.value.id}`, form.value);
    } else {
      await apiClient.post('/accessories-inventory', form.value);
    }
    await fetchAccessories();
    dialogOpen.value = false;
    resetForm();
  } catch (error) {
    console.error('Error saving accessory:', error);
  }
}

async function deleteAccessory(id) {
  if (confirm('Are you sure you want to delete this accessory?')) {
    try {
      await apiClient.delete(`/accessories-inventory/${id}`);
      await fetchAccessories();
    } catch (error) {
      console.error('Error deleting accessory:', error);
    }
  }
}

function resetForm() {
  form.value = {
    id: null,
    name: '',
    quantity: 0,
    unit: 'pcs',
    import_invoice_number: '',
  };
  isEditing.value = false;
}

onMounted(fetchAccessories);
</script>
