<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700">Total Orders</h3>
        <p class="text-3xl font-bold text-indigo-600 mt-2">{{ stats.orders || 0 }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700">Active Batches</h3>
        <p class="text-3xl font-bold text-green-600 mt-2">{{ stats.batches || 0 }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700">Finished Goods</h3>
        <p class="text-3xl font-bold text-blue-600 mt-2">{{ stats.finishedGoods || 0 }}</p>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-700 mb-4">Recent Activity</h3>
      <p class="text-gray-500">Activity feed will be displayed here</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '../api/client';

const stats = ref({
  orders: 0,
  batches: 0,
  finishedGoods: 0
});

onMounted(async () => {
  try {
    // Fetch dashboard stats
    // This would be implemented with actual API calls
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
  }
});
</script>

