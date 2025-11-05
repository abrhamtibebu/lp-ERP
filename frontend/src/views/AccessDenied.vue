<template>
  <div class="min-h-[calc(100vh-8rem)] flex items-center justify-center">
    <Card class="max-w-md w-full">
      <CardContent class="pt-6">
        <div class="text-center space-y-4">
          <div class="mx-auto w-16 h-16 bg-destructive/10 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-destructive" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Access Denied</h1>
            <p class="text-gray-600 mb-4">
              You don't have permission to access this page.
            </p>
            <p v-if="requiredPermission" class="text-sm text-gray-500 mb-6">
              Required permission: <code class="bg-gray-100 px-2 py-1 rounded">{{ requiredPermission }}</code>
            </p>
            <Button @click="goToDashboard" class="w-full">
              Return to Dashboard
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import Card from '@/components/ui/Card.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Button from '@/components/ui/Button.vue';

const router = useRouter();
const route = useRoute();

const requiredPermission = computed(() => {
  // Get permission from query parameter (passed from router guard)
  return route.query.permission || null;
});

const goToDashboard = () => {
  router.push('/');
};
</script>

