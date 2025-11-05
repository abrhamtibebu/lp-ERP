import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import apiClient from '../api/client';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const token = ref(localStorage.getItem('token') || null);

  const isAuthenticated = computed(() => !!token.value && !!user.value);

  const hasRole = (roleName) => {
    if (!user.value?.roles) return false;
    return user.value.roles.some(role => role.name === roleName);
  };

  const hasPermission = (permissionName) => {
    // GM has all permissions
    if (hasRole('GM')) return true;
    
    // Check if user has the permission through their roles
    // This would need to be implemented based on your permission structure
    return false;
  };

  async function login(email, password) {
    try {
      const response = await apiClient.post('/login', { email, password });
      token.value = response.data.token;
      user.value = response.data.user;
      localStorage.setItem('token', token.value);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  async function logout() {
    try {
      await apiClient.post('/logout');
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      token.value = null;
      user.value = null;
      localStorage.removeItem('token');
    }
  }

  async function checkAuth() {
    if (!token.value) return;
    
    try {
      const response = await apiClient.get('/user');
      user.value = response.data.user;
    } catch (error) {
      token.value = null;
      user.value = null;
      localStorage.removeItem('token');
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    hasRole,
    hasPermission,
    login,
    logout,
    checkAuth
  };
});

