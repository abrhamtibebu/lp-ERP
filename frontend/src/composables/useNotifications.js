import { ref, computed } from 'vue';
import apiClient from '@/api/client';

// Global notification state (singleton pattern)
const notifications = ref([]);
const isLoadingNotifications = ref(false);
let notificationRefreshInterval = null;
const isPollingActive = ref(false);
let pollInterval = 30000; // Default 30 seconds

/**
 * Composable for managing notifications
 * Provides real-time notification updates with polling and event-based refresh
 */
export function useNotifications() {
  /**
   * Fetch notifications from the API
   */
  const fetchNotifications = async (silent = false) => {
    try {
      if (!silent) {
        isLoadingNotifications.value = true;
      }
      const response = await apiClient.get('/notifications');
      notifications.value = response.data.notifications || [];
      return notifications.value;
    } catch (error) {
      console.error('Error fetching notifications:', error);
      notifications.value = [];
      return [];
    } finally {
      if (!silent) {
        isLoadingNotifications.value = false;
      }
    }
  };

  /**
   * Start polling for notifications
   * @param {number} interval - Polling interval in milliseconds (default: 30000 = 30 seconds)
   */
  const startPolling = (interval = 30000) => {
    if (notificationRefreshInterval) {
      clearInterval(notificationRefreshInterval);
    }
    
    pollInterval = interval;
    isPollingActive.value = true;
    notificationRefreshInterval = setInterval(() => {
      if (isPollingActive.value && !document.hidden) {
        fetchNotifications(true); // Silent fetch during polling
      }
    }, interval);
  };

  /**
   * Stop polling for notifications
   */
  const stopPolling = () => {
    if (notificationRefreshInterval) {
      clearInterval(notificationRefreshInterval);
      notificationRefreshInterval = null;
    }
    isPollingActive.value = false;
  };

  /**
   * Refresh notifications manually
   * Can be called from anywhere in the app
   */
  const refreshNotifications = async () => {
    await fetchNotifications();
  };

  /**
   * Pause polling (e.g., when tab is hidden)
   */
  const pausePolling = () => {
    isPollingActive.value = false;
  };

  /**
   * Resume polling (e.g., when tab is visible)
   */
  const resumePolling = () => {
    isPollingActive.value = true;
    // Immediately fetch when resuming
    fetchNotifications(true);
  };

  /**
   * Computed properties for different notification types
   */
  const lowStockNotifications = computed(() => {
    return notifications.value.filter(n => n.type === 'low_stock');
  });

  const orderNotifications = computed(() => {
    return notifications.value.filter(n => n.category === 'orders');
  });

  const batchNotifications = computed(() => {
    return notifications.value.filter(n => n.category === 'batches');
  });

  const systemNotifications = computed(() => {
    return notifications.value.filter(n => n.category === 'system');
  });

  const notificationCount = computed(() => {
    return notifications.value.length;
  });

  return {
    // State
    notifications,
    isLoadingNotifications,
    isPollingActive,
    
    // Computed
    lowStockNotifications,
    orderNotifications,
    batchNotifications,
    systemNotifications,
    notificationCount,
    
    // Methods
    fetchNotifications,
    refreshNotifications,
    startPolling,
    stopPolling,
    pausePolling,
    resumePolling,
  };
}

// Export a global refresh function that can be called from anywhere
export const refreshNotificationsGlobal = () => {
  if (typeof window !== 'undefined' && window.__notificationsRefresh) {
    window.__notificationsRefresh();
  }
};

