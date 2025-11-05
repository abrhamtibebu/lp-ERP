<template>
  <div class="space-y-8">
    <div>
      <h2 class="text-3xl font-bold text-gray-900">Reports & Analytics</h2>
      <p class="text-gray-500 mt-1">Comprehensive business insights and analytics</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center gap-4 mb-6">
          <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-900">WIP Stage Tracker</h3>
            <p class="text-sm text-gray-500">Real-time batch tracking</p>
          </div>
        </div>
        <p class="text-gray-600 mb-6">View work-in-progress status by batch and production stage with detailed analytics.</p>
        <button class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all duration-200">
          View Report
        </button>
      </div>
      
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center gap-4 mb-6">
          <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-900">Inventory Levels</h3>
            <p class="text-sm text-gray-500">Stock monitoring</p>
          </div>
        </div>
        <p class="text-gray-600 mb-6">Current stock levels for leather and accessories with low stock alerts.</p>
        <button class="w-full px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all duration-200">
          View Report
        </button>
      </div>
      
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center gap-4 mb-6">
          <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-900">Consumption Variance</h3>
            <p class="text-sm text-gray-500">Formula vs actual</p>
          </div>
        </div>
        <p class="text-gray-600 mb-6">Compare actual leather consumption vs formula-based calculations.</p>
        <button class="w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all duration-200">
          View Report
        </button>
      </div>
      
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center gap-4 mb-6">
          <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl flex items-center justify-center shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-900">Cost Breakdown</h3>
            <p class="text-sm text-gray-500">Financial analysis</p>
          </div>
        </div>
        <p class="text-gray-600 mb-6">Detailed cost analysis per product and batch with profit margins.</p>
        <button class="w-full px-6 py-3 bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all duration-200">
          View Report
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
// Reports view
</script>
