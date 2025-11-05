<template>
  <div class="space-y-6">
    <ActionBar title="Finished Goods" description="Completed products ready for shipment" />

    <Card>
      <CardContent class="p-0">
        <DataTable :data="finishedGoods" :columns="columns">
          <template #cell-product="{ row }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center text-white font-semibold">
                {{ row.product?.product_name?.charAt(0).toUpperCase() || 'P' }}
              </div>
              <div>
                <div class="font-medium">{{ row.product?.product_name || 'N/A' }}</div>
                <div class="text-sm text-gray-500">{{ row.product?.sku || 'N/A' }}</div>
              </div>
            </div>
          </template>
          <template #cell-quantity="{ row }">
            <span class="font-semibold">{{ row.quantity }}</span>
          </template>
          <template #cell-completed_at="{ row }">
            <span>{{ new Date(row.completed_at).toLocaleDateString() }}</span>
          </template>
        </DataTable>
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '@/api/client';
import ActionBar from '@/components/layout/ActionBar.vue';
import Card from '@/components/ui/Card.vue';
import CardContent from '@/components/ui/CardContent.vue';
import DataTable from '@/components/ui/DataTable.vue';

const finishedGoods = ref([]);

const columns = [
  { key: 'product', label: 'Product', sortable: true },
  { key: 'quantity', label: 'Quantity', sortable: true },
  { key: 'completed_at', label: 'Completed Date', sortable: true },
  { key: 'batch_id', label: 'Batch ID', sortable: true },
];

const loadFinishedGoods = async () => {
  try {
    const response = await apiClient.get('/finished-goods');
    finishedGoods.value = response.data || [];
  } catch (error) {
    console.error('Error loading finished goods:', error);
  }
};

onMounted(() => {
  loadFinishedGoods();
});
</script>
