<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 w-64 bg-gray-800 text-white">
      <div class="flex flex-col h-full">
        <div class="flex items-center justify-center h-16 bg-gray-900">
          <h1 class="text-xl font-bold">ERP</h1>
        </div>
        <nav class="flex-1 px-4 py-4 space-y-2">
          <router-link
            v-for="item in menuItems"
            :key="item.path"
            :to="item.path"
            class="flex items-center px-4 py-2 text-gray-300 rounded-lg hover:bg-gray-700"
            :class="{ 'bg-gray-700': $route.path === item.path }"
          >
            <span>{{ item.name }}</span>
          </router-link>
        </nav>
        <div class="p-4 border-t border-gray-700">
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-400">{{ user?.name }}</span>
            <button
              @click="handleLogout"
              class="text-sm text-red-400 hover:text-red-300"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="ml-64">
      <header class="bg-white shadow-sm">
        <div class="px-6 py-4">
          <h2 class="text-2xl font-semibold text-gray-800">{{ pageTitle }}</h2>
        </div>
      </header>
      <main class="p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const user = computed(() => authStore.user);

const menuItems = computed(() => {
  const items = [
    { name: 'Dashboard', path: '/' },
    { name: 'Employees', path: '/employees' },
    { name: 'Suppliers', path: '/suppliers' },
    { name: 'Fixed Assets', path: '/fixed-assets' },
    { name: 'Leather Inventory', path: '/leather-inventory' },
    { name: 'Accessories Inventory', path: '/accessories-inventory' },
    { name: 'Products', path: '/products' },
    { name: 'Orders', path: '/orders' },
    { name: 'Batches', path: '/batches' },
    { name: 'Product Costs', path: '/product-costs' },
    { name: 'Expenses', path: '/expenses' },
    { name: 'Revenues', path: '/revenues' },
    { name: 'Commercial Invoices', path: '/commercial-invoices' },
    { name: 'Reports', path: '/reports' }
  ];
  
  // Filter menu items based on permissions
  return items.filter(item => {
    if (item.path === '/product-costs' && !authStore.hasRole('Finance') && !authStore.hasRole('Admin')) {
      return false;
    }
    return true;
  });
});

const pageTitle = computed(() => {
  const item = menuItems.value.find(i => i.path === route.path);
  return item?.name || 'Dashboard';
});

async function handleLogout() {
  await authStore.logout();
  router.push('/login');
}
</script>

