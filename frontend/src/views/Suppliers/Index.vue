<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#8B4513]">Suppliers</h1>
        <p class="text-gray-600 mt-1">Manage your supplier network</p>
      </div>
      <Button @click="dialogOpen = true" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
        <Plus class="h-4 w-4 mr-2" />
        Add Supplier
      </Button>
    </div>

    <Card>
      <CardContent class="p-0">
        <DataTable :data="suppliers" :columns="columns">
          <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-[#8B4513] to-[#6B3410] rounded-lg flex items-center justify-center text-white font-semibold">
                {{ row.name.charAt(0).toUpperCase() }}
              </div>
              <span class="font-medium">{{ row.name }}</span>
            </div>
          </template>
          <template #cell-products_supplied="{ row }">
            <Badge variant="secondary">{{ row.products_supplied || 'N/A' }}</Badge>
          </template>
          <template #rowActions="{ row }">
            <DropdownMenu>
              <template #trigger>
                <Button variant="ghost" class="h-8 w-8 p-0">
                  <span class="sr-only">Open menu</span>
                  <MoreHorizontal class="h-4 w-4" />
                </Button>
              </template>
              <DropdownMenuItem @click="editSupplier(row)">Edit</DropdownMenuItem>
              <DropdownMenuItem @click="deleteSupplier(row.id)" class="text-destructive">Delete</DropdownMenuItem>
            </DropdownMenu>
          </template>
        </DataTable>
      </CardContent>
    </Card>

    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Supplier' : 'Add New Supplier'">
      <form @submit.prevent="saveSupplier" class="space-y-4">
        <div class="space-y-2">
          <Label for="name">Name</Label>
          <Input id="name" v-model="form.name" required />
        </div>
        <div class="space-y-2">
          <Label for="tin_number">TIN Number</Label>
          <Input id="tin_number" v-model="form.tin_number" />
        </div>
        <div class="space-y-2">
          <Label for="products_supplied">Products Supplied</Label>
          <Input id="products_supplied" v-model="form.products_supplied" />
        </div>
        <div class="space-y-2">
          <Label for="contact_info">Contact Info</Label>
          <Input id="contact_info" v-model="form.contact_info" />
        </div>
      </form>
      <template #footer>
        <Button type="button" variant="outline" @click="dialogOpen = false">Cancel</Button>
        <Button type="button" @click="saveSupplier" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">Save</Button>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '@/api/client';
import { MoreHorizontal, Plus } from 'lucide-vue-next';
import Card from '@/components/ui/Card.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Button from '@/components/ui/Button.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Dialog from '@/components/ui/Dialog.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Badge from '@/components/ui/Badge.vue';
import DropdownMenu from '@/components/ui/DropdownMenu.vue';
import DropdownMenuItem from '@/components/ui/DropdownMenuItem.vue';

const suppliers = ref([]);
const dialogOpen = ref(false);
const isEditing = ref(false);
const form = ref({
  id: null,
  name: '',
  tin_number: '',
  products_supplied: '',
  contact_info: '',
});

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'tin_number', label: 'TIN Number', sortable: true },
  { key: 'products_supplied', label: 'Products Supplied', sortable: true },
  { key: 'contact_info', label: 'Contact Info', sortable: false },
];

async function fetchSuppliers() {
  try {
    const response = await apiClient.get('/suppliers');
    suppliers.value = response.data;
  } catch (error) {
    console.error('Error fetching suppliers:', error);
  }
}

function editSupplier(supplier) {
  isEditing.value = true;
  form.value = { ...supplier };
  dialogOpen.value = true;
}

async function saveSupplier() {
  try {
    if (isEditing.value) {
      await apiClient.put(`/suppliers/${form.value.id}`, form.value);
    } else {
      await apiClient.post('/suppliers', form.value);
    }
    await fetchSuppliers();
    dialogOpen.value = false;
    resetForm();
  } catch (error) {
    console.error('Error saving supplier:', error);
  }
}

async function deleteSupplier(id) {
  if (confirm('Are you sure you want to delete this supplier?')) {
    try {
      await apiClient.delete(`/suppliers/${id}`);
      await fetchSuppliers();
    } catch (error) {
      console.error('Error deleting supplier:', error);
    }
  }
}

function resetForm() {
  form.value = {
    id: null,
    name: '',
    tin_number: '',
    products_supplied: '',
    contact_info: '',
  };
  isEditing.value = false;
}

onMounted(fetchSuppliers);
</script>
