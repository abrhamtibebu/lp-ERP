<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-3xl font-bold text-[#8B4513]">Revenues</h1>
      <p class="text-gray-600 mt-1">Track revenue from commercial invoices</p>
    </div>

    <Card>
      <CardContent class="p-0">
        <DataTable :data="revenues" :columns="columns">
          <template #cell-description="{ row }">
            <span class="font-medium">{{ row.description }}</span>
          </template>
          <template #cell-amount="{ row }">
            <span class="font-semibold text-green-600">${{ parseFloat(row.amount).toLocaleString() }}</span>
          </template>
          <template #cell-revenue_date="{ row }">
            <span>{{ new Date(row.revenue_date).toLocaleDateString() }}</span>
          </template>
          <template #cell-commercial_invoice_id="{ row }">
            <Badge variant="secondary">{{ row.commercial_invoice?.invoice_number || 'N/A' }}</Badge>
          </template>
        </DataTable>
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '@/api/client';
import Card from '@/components/ui/Card.vue';
import CardContent from '@/components/ui/CardContent.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Badge from '@/components/ui/Badge.vue';

const revenues = ref([]);

const columns = [
  { key: 'description', label: 'Description', sortable: true },
  { key: 'amount', label: 'Amount', sortable: true },
  { key: 'revenue_date', label: 'Date', sortable: true },
  { key: 'commercial_invoice_id', label: 'Invoice', sortable: true },
];

async function fetchRevenues() {
  try {
    const response = await apiClient.get('/revenues');
    revenues.value = response.data;
  } catch (error) {
    console.error('Error fetching revenues:', error);
  }
}

onMounted(fetchRevenues);
</script>
