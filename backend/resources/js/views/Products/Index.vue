<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold text-gray-800">Products</h2>
      <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
        Add Product
      </button>
    </div>
    
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="product in products" :key="product.id">
            <td class="px-6 py-4 whitespace-nowrap">{{ product.product_name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ product.sku }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ product.color }}</td>
            <td class="px-6 py-4 whitespace-nowrap">${{ product.unit_price }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button class="text-indigo-600 hover:text-indigo-900">Edit</button>
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

const products = ref([]);

onMounted(async () => {
  try {
    const response = await apiClient.get('/products');
    products.value = response.data;
  } catch (error) {
    console.error('Error fetching products:', error);
  }
});
</script>

