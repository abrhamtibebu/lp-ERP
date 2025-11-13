<template>
  <header class="sticky top-0 z-40 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
    <div class="flex h-14 items-center px-2 sm:px-4">
      <div class="flex flex-1 items-center space-x-2 sm:space-x-4 min-w-0">
        <!-- Logo -->
        <div class="flex items-center space-x-1 sm:space-x-2 flex-shrink-0">
          <button
            @click="$emit('toggle-sidebar')"
            class="inline-flex items-center justify-center rounded-md p-2 text-muted-foreground hover:bg-accent hover:text-accent-foreground lg:hidden"
          >
            <Menu class="h-5 w-5" />
          </button>
          <router-link to="/" class="flex items-center space-x-1 sm:space-x-2">
            <img 
              src="https://www.parkerclay.com/cdn/shop/files/ParkerClay_logo.png?v=1632880083&width=255" 
              alt="Parker Clay" 
              class="h-5 w-auto sm:h-6 flex-shrink-0"
            />
            <span class="font-bold text-sm sm:text-lg whitespace-nowrap">ERP</span>
          </router-link>
        </div>

        <!-- Search - Hidden on small mobile, visible on larger screens -->
        <div class="hidden sm:flex flex-1 max-w-lg min-w-0">
          <div class="relative w-full">
            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input
              v-model="searchQuery"
              placeholder="Search... (Ctrl+K)"
              class="pl-8 h-9 w-full"
              @keydown.ctrl.k.prevent="focusSearch"
            />
          </div>
        </div>
      </div>

      <div class="flex items-center space-x-1 sm:space-x-4 flex-shrink-0">
        <!-- Notifications -->
        <DropdownMenu align="end" class="!min-w-[22rem] !max-w-[28rem] w-auto !p-0 !max-h-none">
          <template #trigger>
            <Button variant="ghost" size="icon" class="relative">
              <Bell class="h-5 w-5" />
              <span v-if="notificationCount > 0" class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-destructive text-white text-xs flex items-center justify-center font-medium border-2 border-white z-10">
                {{ notificationCount > 9 ? '9+' : notificationCount }}
              </span>
            </Button>
          </template>
          <!-- Header -->
          <div class="px-3 py-2.5 text-sm font-semibold text-gray-900 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <span>Notifications</span>
            <div class="flex items-center gap-2">
              <span v-if="notificationCount > 0" class="text-xs font-normal text-gray-500">
                {{ notificationCount }} {{ notificationCount === 1 ? 'notification' : 'notifications' }}
              </span>
              <Button
                variant="ghost"
                size="sm"
                @click.stop="handleManualRefresh"
                :disabled="isRefreshing || isLoadingNotifications"
                class="h-6 w-6 p-0"
                title="Refresh notifications"
              >
                <RefreshCw 
                  :class="[
                    'h-3 w-3 text-gray-500',
                    (isRefreshing || isLoadingNotifications) && 'animate-spin'
                  ]"
                />
              </Button>
            </div>
          </div>
          <!-- Loading State -->
          <div v-if="isLoadingNotifications" class="px-4 py-8 text-center text-sm text-gray-500">
            Loading notifications...
          </div>
          <!-- Empty State -->
          <div v-else-if="notificationCount === 0" class="px-4 py-8 text-center text-sm text-gray-500">
            <Bell class="h-8 w-8 mx-auto mb-2 text-gray-400" />
            <p>No notifications</p>
            <p class="text-xs text-gray-400 mt-1">You're all caught up!</p>
            <p class="text-xs text-gray-400 mt-2">Notifications update every 30 seconds</p>
          </div>
          <!-- Notifications List -->
          <div v-else class="max-h-[24rem] overflow-y-auto">
            <!-- Low Stock Alerts Section -->
            <div v-if="lowStockNotifications.length > 0" class="border-b border-gray-200">
              <div class="px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-50 uppercase tracking-wide">
                Low Stock Alerts ({{ lowStockNotifications.length }})
              </div>
              <DropdownMenuItem
                v-for="notification in lowStockNotifications"
                :key="notification.id"
                @click="handleNotificationClick(notification)"
                class="flex flex-col items-start gap-1.5 px-3 py-2.5 cursor-pointer hover:bg-orange-50"
              >
                <div class="flex items-center gap-2 w-full">
                  <AlertCircle class="h-4 w-4 text-orange-500 flex-shrink-0" />
                  <span class="font-medium text-sm text-gray-900 flex-1">{{ notification.message }}</span>
                  <span 
                    :class="[
                      'text-xs px-1.5 py-0.5 rounded font-medium',
                      notification.subtype === 'leather' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800'
                    ]"
                  >
                    {{ notification.subtype === 'leather' ? 'Leather' : 'Accessories' }}
                  </span>
                </div>
                <div class="text-xs text-gray-600 ml-6 w-full">
                  {{ notification.description }}
                </div>
                <div v-if="notification.data.supplier" class="text-xs text-gray-500 ml-6">
                  <span class="font-medium">Supplier:</span> {{ notification.data.supplier }}
                </div>
                <div v-if="notification.data.brand_make" class="text-xs text-gray-500 ml-6">
                  <span class="font-medium">Brand:</span> {{ notification.data.brand_make }}
                </div>
              </DropdownMenuItem>
            </div>
            
            <!-- Orders Section (if any) -->
            <div v-if="orderNotifications.length > 0" class="border-b border-gray-200">
              <div class="px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-50 uppercase tracking-wide">
                Orders ({{ orderNotifications.length }})
              </div>
              <DropdownMenuItem
                v-for="notification in orderNotifications"
                :key="notification.id"
                @click="handleNotificationClick(notification)"
                class="flex items-center gap-2 px-3 py-2.5 cursor-pointer hover:bg-blue-50"
              >
                <Package class="h-4 w-4 text-blue-500 flex-shrink-0" />
                <div class="flex-1">
                  <div class="font-medium text-sm text-gray-900">{{ notification.message }}</div>
                  <div class="text-xs text-gray-600">{{ notification.description }}</div>
                </div>
              </DropdownMenuItem>
            </div>
            
            <!-- Batches Section (if any) -->
            <div v-if="batchNotifications.length > 0" class="border-b border-gray-200">
              <div class="px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-50 uppercase tracking-wide">
                Batches ({{ batchNotifications.length }})
              </div>
              <DropdownMenuItem
                v-for="notification in batchNotifications"
                :key="notification.id"
                @click="handleNotificationClick(notification)"
                class="flex items-center gap-2 px-3 py-2.5 cursor-pointer hover:bg-purple-50"
              >
                <Package class="h-4 w-4 text-purple-500 flex-shrink-0" />
                <div class="flex-1">
                  <div class="font-medium text-sm text-gray-900">{{ notification.message }}</div>
                  <div class="text-xs text-gray-600">{{ notification.description }}</div>
                </div>
              </DropdownMenuItem>
            </div>
            
            <!-- System Notifications Section (if any) -->
            <div v-if="systemNotifications.length > 0" class="border-b border-gray-200">
              <div class="px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-50 uppercase tracking-wide">
                System ({{ systemNotifications.length }})
              </div>
              <DropdownMenuItem
                v-for="notification in systemNotifications"
                :key="notification.id"
                @click="handleNotificationClick(notification)"
                class="flex items-center gap-2 px-3 py-2.5 cursor-pointer hover:bg-gray-50"
              >
                <AlertCircle class="h-4 w-4 text-gray-500 flex-shrink-0" />
                <div class="flex-1">
                  <div class="font-medium text-sm text-gray-900">{{ notification.message }}</div>
                  <div class="text-xs text-gray-600">{{ notification.description }}</div>
                </div>
              </DropdownMenuItem>
            </div>
          </div>
          <!-- Footer -->
          <template v-if="notificationCount > 0">
            <Separator />
            <div class="flex flex-col gap-1 px-1">
              <DropdownMenuItem
                v-if="lowStockNotifications.length > 0"
                @click="navigateToInventory('leather')"
                class="text-center justify-center text-sm font-medium text-[#8B4513] hover:text-[#6B3410]"
              >
                View All Inventory
              </DropdownMenuItem>
            </div>
          </template>
        </DropdownMenu>

        <!-- User Menu -->
        <DropdownMenu align="end" class="w-56">
          <template #trigger>
            <Button variant="ghost" class="flex items-center space-x-1 sm:space-x-2 h-9 px-1 sm:px-2">
              <Avatar class="h-7 w-7 sm:h-8 sm:w-8 flex-shrink-0">
                <AvatarFallback>{{ userInitials }}</AvatarFallback>
              </Avatar>
              <div class="hidden md:flex flex-col items-start min-w-0">
                <span class="text-sm font-medium truncate">{{ user?.name }}</span>
                <span class="text-xs text-muted-foreground truncate">{{ user?.email }}</span>
              </div>
              <ChevronDown class="h-4 w-4 hidden sm:block flex-shrink-0" />
            </Button>
          </template>
          <DropdownMenuItem @click="router.push('/profile')">
            <User class="mr-2 h-4 w-4" />
            <span>Profile</span>
          </DropdownMenuItem>
          <DropdownMenuItem @click="router.push('/settings')">
            <Settings class="mr-2 h-4 w-4" />
            <span>Settings</span>
          </DropdownMenuItem>
          <Separator />
          <DropdownMenuItem @click="handleLogout" class="text-destructive">
            <LogOut class="mr-2 h-4 w-4" />
            <span>Logout</span>
          </DropdownMenuItem>
        </DropdownMenu>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { Menu, Search, Bell, ChevronDown, User, Settings, LogOut, AlertCircle, Package, RefreshCw } from 'lucide-vue-next';
import Button from '../ui/Button.vue';
import Input from '../ui/Input.vue';
import Avatar from '../ui/Avatar.vue';
import AvatarFallback from '../ui/AvatarFallback.vue';
import DropdownMenu from '../ui/DropdownMenu.vue';
import DropdownMenuItem from '../ui/DropdownMenuItem.vue';
import Separator from '../ui/Separator.vue';
import { useNotifications } from '@/composables/useNotifications';

defineEmits(['toggle-sidebar']);

const router = useRouter();
const authStore = useAuthStore();

const searchQuery = ref('');
const isRefreshing = ref(false);

// Use the notifications composable
const {
  notifications,
  isLoadingNotifications,
  isPollingActive,
  lowStockNotifications,
  orderNotifications,
  batchNotifications,
  systemNotifications,
  notificationCount,
  fetchNotifications,
  refreshNotifications,
  startPolling,
  stopPolling,
  pausePolling,
  resumePolling,
} = useNotifications();

// Expose refresh function globally for other components
if (typeof window !== 'undefined') {
  window.__notificationsRefresh = refreshNotifications;
}

const user = computed(() => authStore.user);

const userInitials = computed(() => {
  if (!user.value?.name) return 'U';
  const names = user.value.name.split(' ');
  return names.map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(num);
};

const handleNotificationClick = (notification) => {
  if (notification.action_url) {
    router.push(notification.action_url);
  }
};

const navigateToInventory = (type) => {
  if (type === 'leather') {
    router.push('/inventory/leather');
  } else if (type === 'accessories') {
    router.push('/inventory/accessories');
  }
};

const handleManualRefresh = async () => {
  isRefreshing.value = true;
  await refreshNotifications();
  isRefreshing.value = false;
};

const focusSearch = () => {
  // Focus search input
  document.querySelector('input[placeholder*="Search"]')?.focus();
};

const handleLogout = async () => {
  stopPolling();
  await authStore.logout();
  router.push('/login');
};

// Track last refresh time to avoid duplicate refreshes
let lastRefreshTime = 0;
const REFRESH_DEBOUNCE_MS = 1000; // 1 second debounce

// Handle window visibility change for better performance
const handleVisibilityChange = () => {
  if (document.hidden) {
    // Tab is hidden, pause polling to save resources
    pausePolling();
  } else {
    // Tab is visible, resume polling and refresh immediately (but debounced)
    resumePolling();
    const now = Date.now();
    if (now - lastRefreshTime > REFRESH_DEBOUNCE_MS) {
      lastRefreshTime = now;
      refreshNotifications();
    }
  }
};

// Handle window focus for immediate refresh when user returns
const handleWindowFocus = () => {
  // User returned to the window, refresh notifications (but debounced)
  const now = Date.now();
  if (now - lastRefreshTime > REFRESH_DEBOUNCE_MS) {
    lastRefreshTime = now;
    refreshNotifications();
  }
  // Ensure polling is active when window is focused
  if (!isPollingActive.value) {
    resumePolling();
  }
};

// Fetch notifications on mount and set up real-time polling
onMounted(() => {
  // Initial fetch
  fetchNotifications();
  
  // Start polling every 30 seconds for real-time updates
  startPolling(30000); // 30 seconds = 30000 ms
  
  // Listen to visibility changes
  document.addEventListener('visibilitychange', handleVisibilityChange);
  
  // Listen to window focus events for immediate refresh when user returns
  window.addEventListener('focus', handleWindowFocus);
});

// Clean up on unmount
onUnmounted(() => {
  stopPolling();
  document.removeEventListener('visibilitychange', handleVisibilityChange);
  window.removeEventListener('focus', handleWindowFocus);
  
  // Clean up global reference
  if (typeof window !== 'undefined') {
    delete window.__notificationsRefresh;
  }
});

// Watch for route changes to refresh notifications when navigating to inventory pages
watch(() => router.currentRoute.value.path, (newPath) => {
  // Refresh notifications when navigating to inventory-related pages
  if (newPath.includes('/inventory')) {
    refreshNotifications();
  }
});
</script>

