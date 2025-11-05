<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold text-gray-800">Production Batches</h2>
    </div>
    
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stage</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="batch in batches" :key="batch.id">
            <td class="px-6 py-4 whitespace-nowrap">{{ batch.batch_id }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ batch.order?.product?.product_name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ batch.current_stage?.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ batch.current_quantity }} / {{ batch.total_quantity }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="statusClass(batch.status)">
                {{ batch.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <router-link :to="`/batches/${batch.id}`" class="text-indigo-600 hover:text-indigo-900">View</router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '../../api/client';

const batches = ref([]);

const statusClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'in_progress': 'bg-blue-100 text-blue-800',
    'completed': 'bg-green-100 text-green-800',
    'rework': 'bg-red-100 text-red-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

onMounted(async () => {
  try {
    const response = await apiClient.get('/batches');
    batches.value = response.data;
  } catch (error) {
    console.error('Error fetching batches:', error);
  }
});
</script>

