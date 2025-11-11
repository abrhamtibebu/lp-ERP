<template>
  <nav class="flex items-center space-x-1 sm:space-x-2 px-2 sm:px-4 py-2 text-xs sm:text-sm text-muted-foreground overflow-x-auto">
    <router-link to="/" class="hover:text-foreground whitespace-nowrap">Home</router-link>
    <ChevronRight class="h-3 w-3 sm:h-4 sm:w-4 flex-shrink-0" />
    <template v-for="(crumb, index) in breadcrumbs" :key="crumb.path">
      <router-link
        v-if="index < breadcrumbs.length - 1 && crumb.isClickable"
        :to="crumb.path"
        class="hover:text-foreground whitespace-nowrap truncate max-w-[100px] sm:max-w-none"
      >
        {{ crumb.name }}
      </router-link>
      <span v-else-if="index < breadcrumbs.length - 1" class="text-muted-foreground whitespace-nowrap truncate max-w-[100px] sm:max-w-none">
        {{ crumb.name }}
      </span>
      <span v-else class="text-foreground font-medium whitespace-nowrap truncate">{{ crumb.name }}</span>
      <ChevronRight v-if="index < breadcrumbs.length - 1" class="h-3 w-3 sm:h-4 sm:w-4 flex-shrink-0" />
    </template>
  </nav>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ChevronRight } from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();

// Define valid routes that should be clickable
const validRoutes = ['/', '/employees', '/suppliers', '/fixed-assets', 
  '/inventory/leather', '/inventory/accessories', '/products',
  '/production/orders', '/production/batches',
  '/finance/product-costs', '/finance/expenses', '/finance/revenues',
  '/commercial-invoices', '/reports'];

const breadcrumbs = computed(() => {
  const paths = route.path.split('/').filter(Boolean);
  const crumbs = [];

  paths.forEach((path, index) => {
    const fullPath = '/' + paths.slice(0, index + 1).join('/');
    const name = path
      .split('-')
      .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
    
    // Only make clickable if it's a valid route or the current route
    const isClickable = validRoutes.includes(fullPath) || fullPath === route.path;
    crumbs.push({ name, path: fullPath, isClickable });
  });

  return crumbs;
});
</script>

