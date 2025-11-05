<template>
  <nav class="flex items-center space-x-2 px-4 py-2 text-sm text-muted-foreground">
    <router-link to="/" class="hover:text-foreground">Home</router-link>
    <ChevronRight class="h-4 w-4" />
    <template v-for="(crumb, index) in breadcrumbs" :key="crumb.path">
      <router-link
        v-if="index < breadcrumbs.length - 1 && crumb.isClickable"
        :to="crumb.path"
        class="hover:text-foreground"
      >
        {{ crumb.name }}
      </router-link>
      <span v-else-if="index < breadcrumbs.length - 1" class="text-muted-foreground">
        {{ crumb.name }}
      </span>
      <span v-else class="text-foreground font-medium">{{ crumb.name }}</span>
      <ChevronRight v-if="index < breadcrumbs.length - 1" class="h-4 w-4" />
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

