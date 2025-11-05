<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-3xl font-bold text-gray-900">Production Batches</h2>
        <p class="text-gray-500 mt-1">Track and monitor production batches</p>
      </div>
    </div>
    
    <!-- Batches Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="batch in batches" :key="batch.id" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h3 class="text-lg font-bold text-gray-900 mb-1">{{ batch.batch_id }}</h3>
            <p class="text-sm text-gray-500">{{ batch.order?.product?.product_name || 'N/A' }}</p>
          </div>
          <span class="px-3 py-1 text-xs font-semibold rounded-full" :class="statusClass(batch.status)">
            {{ batch.status }}
          </span>
        </div>
        
        <div class="space-y-3 mb-4">
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Current Stage</span>
            <span class="text-sm font-semibold text-gray-900">{{ batch.current_stage?.stage_name || batch.currentStage?.stage_name || 'N/A' }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Progress</span>
            <span class="text-sm font-semibold text-gray-900">
              {{ batch.current_quantity }} / {{ batch.total_quantity }}
            </span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div 
              class="bg-gradient-to-r from-indigo-600 to-purple-600 h-2 rounded-full transition-all duration-300"
              :style="{ width: `${(batch.current_quantity / batch.total_quantity) * 100}%` }"
            ></div>
          </div>
        </div>
        
        <router-link
          :to="`/production/batches/${batch.id}`"
          class="block w-full px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:shadow-lg font-medium text-center transition-all duration-200"
        >
          View Details
        </router-link>
      </div>
      
      <div v-if="batches.length === 0" class="col-span-full">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-12 text-center">
          <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
          </svg>
          <p class="text-gray-500 text-lg">No batches found</p>
          <p class="text-gray-400 text-sm mt-2">Create an order to generate batches</p>
        </div>
      </div>
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
