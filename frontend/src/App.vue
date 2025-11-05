<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from './stores/auth';

const authStore = useAuthStore();

onMounted(async () => {
  // Check if user is already authenticated (this is a fallback, router guard should handle it)
  if (authStore.token && !authStore.user) {
    await authStore.checkAuth();
  }
});
</script>

<style>
#app {
  min-height: 100vh;
}
</style>

