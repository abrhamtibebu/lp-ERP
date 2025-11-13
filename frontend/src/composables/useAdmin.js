import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

/**
 * Composable to check if current user is Admin
 * Admin has full access to edit/delete all records
 */
export function useAdmin() {
  const authStore = useAuthStore();

  const isAdmin = computed(() => {
    return authStore.hasRole('Admin');
  });

  /**
   * Check if user can manage a resource
   * Admin can always manage, otherwise checks permission
   */
  const canManage = (permission) => {
    if (isAdmin.value) return true;
    return authStore.hasPermission(permission);
  };

  /**
   * Check if user can view a resource
   * Admin can always view, otherwise checks permission
   */
  const canView = (permission) => {
    if (isAdmin.value) return true;
    return authStore.hasPermission(permission);
  };

  return {
    isAdmin,
    canManage,
    canView
  };
}

