<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-3xl font-bold text-gray-900">Leather Inventory</h2>
        <p class="text-gray-500 mt-1">Track leather stock and consumption</p>
      </div>
      <button class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Add Leather
      </button>
    </div>
    
    <!-- Inventory Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="item in inventory" :key="item.id" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-start justify-between mb-4">
          <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center text-white text-2xl font-bold shadow-lg">
            {{ item.leather_name?.charAt(0).toUpperCase() || 'L' }}
          </div>
          <div class="text-right">
            <p class="text-2xl font-bold text-gray-900">{{ (item.quantity_sqft - item.consumption_reduction).toFixed(2) }}</p>
            <p class="text-xs text-gray-500">sqft available</p>
          </div>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ item.leather_name }}</h3>
        <div class="space-y-2 text-sm">
          <div class="flex items-center gap-2 text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ item.brand_make || 'N/A' }}</span>
          </div>
          <div class="flex items-center gap-2 text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ item.supplier?.name || 'N/A' }}</span>
          </div>
          <div class="flex items-center gap-2 text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span>{{ new Date(item.purchase_date).toLocaleDateString() }}</span>
          </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600">Total Quantity</span>
            <span class="font-semibold text-gray-900">{{ item.quantity_sqft }} sqft</span>
          </div>
        </div>
      </div>
      
      <div v-if="inventory.length === 0" class="col-span-full">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-12 text-center">
          <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
          </svg>
          <p class="text-gray-500 text-lg">No leather inventory found</p>
          <p class="text-gray-400 text-sm mt-2">Add leather to track your stock</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '../../api/client';

const inventory = ref([]);

onMounted(async () => {
  try {
    const response = await apiClient.get('/leather-inventory');
    inventory.value = response.data;
  } catch (error) {
    console.error('Error fetching leather inventory:', error);
  }
});
</script>
