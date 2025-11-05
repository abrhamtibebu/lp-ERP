<template>
  <div class="space-y-6">
    <ActionBar title="Product Costs" description="Manage and lock product costs for production planning" />

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
            <DropdownMenu>
              <template #trigger>
                <Button variant="ghost" class="h-8 w-8 p-0">
                  <span class="sr-only">Open menu</span>
                  <MoreHorizontal class="h-4 w-4" />
                </Button>
              </template>
              <DropdownMenuItem @click="editCost(row)">Edit Cost</DropdownMenuItem>
              <DropdownMenuItem v-if="!row.is_locked" @click="lockCost(row)">Lock Cost</DropdownMenuItem>
              <DropdownMenuItem v-if="row.is_locked" @click="unlockCost(row)">Unlock Cost</DropdownMenuItem>
            </DropdownMenu>
          </template>
        </DataTable>
      </CardContent>
    </Card>

    <Dialog v-model="dialogOpen" title="Edit Product Cost">
      <form @submit.prevent="saveCost" class="space-y-4">
        <div class="space-y-2">
          <Label for="cost">Cost</Label>
          <Input id="cost" v-model.number="form.cost" type="number" step="0.01" required />
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
import Badge from '@/components/ui/Badge.vue';
import DropdownMenu from '@/components/ui/DropdownMenu.vue';
import DropdownMenuItem from '@/components/ui/DropdownMenuItem.vue';

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
    await apiClient.put(`/product-costs/${editingCost.value.id}`, form.value);
    await fetchProductCosts();
    dialogOpen.value = false;
    editingCost.value = null;
  } catch (error) {
    console.error('Error saving cost:', error);
  }
}

async function lockCost(cost) {
  try {
    await apiClient.put(`/product-costs/${cost.id}`, { is_locked: true });
    await fetchProductCosts();
  } catch (error) {
    console.error('Error locking cost:', error);
  }
}

async function unlockCost(cost) {
  try {
    await apiClient.put(`/product-costs/${cost.id}`, { is_locked: false });
    await fetchProductCosts();
  } catch (error) {
    console.error('Error unlocking cost:', error);
  }
}

onMounted(fetchProductCosts);
</script>
