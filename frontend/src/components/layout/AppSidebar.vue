<template>
  <aside
    :class="[
      'fixed left-0 top-14 z-30 h-[calc(100vh-3.5rem)] w-64 border-r bg-background transition-transform duration-300',
      isCollapsed ? '-translate-x-full lg:translate-x-0 lg:w-16' : 'translate-x-0',
      isMobile && !isOpen ? '-translate-x-full' : 'translate-x-0'
    ]"
  >
    <nav class="flex h-full flex-col p-4 space-y-2">
      <!-- Main Navigation -->
      <div class="space-y-1">
        <router-link
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          class="flex items-center space-x-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
          :class="{ 'bg-accent text-accent-foreground': $route.path === item.path || ($route.path.startsWith(item.path) && item.path !== '/') }"
        >
          <component :is="item.icon" class="h-5 w-5 flex-shrink-0" />
          <span v-if="!isCollapsed" class="flex-1">{{ item.name }}</span>
        </router-link>
      </div>

      <!-- Separator -->
      <Separator class="my-4" />

      <!-- Module Groups -->
      <div v-for="group in menuGroups" :key="group.name" class="space-y-1">
        <div v-if="!isCollapsed" class="px-3 py-2 text-xs font-semibold text-muted-foreground uppercase">
          {{ group.name }}
        </div>
        <router-link
          v-for="item in group.items"
          :key="item.path"
          :to="item.path"
          class="flex items-center space-x-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
          :class="{ 'bg-accent text-accent-foreground': $route.path === item.path }"
        >
          <component :is="item.icon" class="h-5 w-5 flex-shrink-0" />
          <span v-if="!isCollapsed" class="flex-1">{{ item.name }}</span>
        </router-link>
      </div>
    </nav>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import {
  LayoutDashboard,
  Users,
  Truck,
  Building2,
  Package,
  Box,
  ShoppingCart,
  ClipboardList,
  DollarSign,
  FileText,
  BarChart3,
  Folder,
} from 'lucide-vue-next';
import Separator from '../ui/Separator.vue';

const props = defineProps({
  isCollapsed: {
    type: Boolean,
    default: false,
  },
  isOpen: {
    type: Boolean,
    default: true,
  },
  isMobile: {
    type: Boolean,
    default: false,
  },
});

const route = useRoute();

const menuItems = computed(() => [
  { name: 'Dashboard', path: '/', icon: LayoutDashboard },
]);

const menuGroups = computed(() => [
  {
    name: 'Core',
    items: [
      { name: 'Employees', path: '/employees', icon: Users },
      { name: 'Suppliers', path: '/suppliers', icon: Truck },
      { name: 'Fixed Assets', path: '/fixed-assets', icon: Building2 },
    ],
  },
  {
    name: 'Inventory',
    items: [
      { name: 'Leather', path: '/inventory/leather', icon: Package },
      { name: 'Accessories', path: '/inventory/accessories', icon: Box },
      { name: 'Products', path: '/products', icon: ShoppingCart },
    ],
  },
  {
    name: 'Production',
    items: [
      { name: 'Orders', path: '/production/orders', icon: ClipboardList },
      { name: 'Batches', path: '/production/batches', icon: Folder },
    ],
  },
  {
    name: 'Finance',
    items: [
      { name: 'Product Costs', path: '/finance/product-costs', icon: DollarSign },
      { name: 'Expenses', path: '/finance/expenses', icon: FileText },
      { name: 'Revenues', path: '/finance/revenues', icon: DollarSign },
    ],
  },
  {
    name: 'Reports',
    items: [
      { name: 'Analytics', path: '/reports', icon: BarChart3 },
    ],
  },
]);
</script>

