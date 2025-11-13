<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold text-[#8B4513]">Settings</h1>
      <p class="text-gray-600 mt-1">Manage your application preferences and account settings</p>
    </div>

    <Tabs v-model="activeTab" class="w-full">
      <TabsList class="grid w-full grid-cols-4 lg:w-auto lg:inline-flex">
        <TabsTrigger value="preferences">Preferences</TabsTrigger>
        <TabsTrigger value="notifications">Notifications</TabsTrigger>
        <TabsTrigger value="account">Account</TabsTrigger>
        <TabsTrigger value="tenant" v-if="isAdmin">Tenant Settings</TabsTrigger>
      </TabsList>

      <!-- Preferences Tab -->
      <TabsContent value="preferences" class="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Appearance</CardTitle>
            <CardDescription>Customize the look and feel of the application</CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
            <div class="space-y-2">
              <Label for="theme">Theme</Label>
              <Select 
                v-model="userSettings.theme" 
                :placeholder="getThemeLabel(userSettings.theme)"
              >
                <SelectItem value="light">Light</SelectItem>
                <SelectItem value="dark">Dark</SelectItem>
                <SelectItem value="system">System</SelectItem>
              </Select>
              <p class="text-xs text-muted-foreground">
                Choose your preferred theme for the application
              </p>
            </div>
            <div class="space-y-2">
              <Label for="language">Language</Label>
              <Select 
                v-model="userSettings.language" 
                placeholder="English"
              >
                <SelectItem value="en">English</SelectItem>
              </Select>
              <p class="text-xs text-muted-foreground">
                Select your preferred language
              </p>
            </div>
            <div class="space-y-2">
              <Label for="itemsPerPage">Items Per Page</Label>
              <Select 
                v-model="userSettings.itemsPerPage" 
                :placeholder="`${userSettings.itemsPerPage} items`"
              >
                <SelectItem value="10">10 items</SelectItem>
                <SelectItem value="25">25 items</SelectItem>
                <SelectItem value="50">50 items</SelectItem>
                <SelectItem value="100">100 items</SelectItem>
              </Select>
              <p class="text-xs text-muted-foreground">
                Number of items to display per page in tables
              </p>
            </div>
            <div class="space-y-2">
              <Label for="autoRefresh">Auto-refresh Interval</Label>
              <Select 
                v-model="userSettings.autoRefresh" 
                :placeholder="getRefreshLabel(userSettings.autoRefresh)"
              >
                <SelectItem value="0">Disabled</SelectItem>
                <SelectItem value="30">30 seconds</SelectItem>
                <SelectItem value="60">1 minute</SelectItem>
                <SelectItem value="300">5 minutes</SelectItem>
                <SelectItem value="600">10 minutes</SelectItem>
              </Select>
              <p class="text-xs text-muted-foreground">
                Automatically refresh data at the specified interval
              </p>
            </div>
            <Button 
              @click="saveUserSettings" 
              :disabled="savingUserSettings"
              class="bg-[#8B4513] hover:bg-[#6B3410] text-white"
            >
              <span v-if="savingUserSettings">Saving...</span>
              <span v-else>Save Preferences</span>
            </Button>
          </CardContent>
        </Card>
      </TabsContent>

      <!-- Notifications Tab -->
      <TabsContent value="notifications" class="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Notification Preferences</CardTitle>
            <CardDescription>Manage how you receive notifications</CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
            <div class="flex items-center justify-between">
              <div class="space-y-0.5 flex-1">
                <Label>Email Notifications</Label>
                <p class="text-sm text-muted-foreground">
                  Receive email updates about important events
                </p>
              </div>
              <Switch 
                v-model="userSettings.emailNotifications" 
                aria-label="Enable email notifications"
              />
            </div>
            <Separator />
            <div class="flex items-center justify-between">
              <div class="space-y-0.5 flex-1">
                <Label>Order Updates</Label>
                <p class="text-sm text-muted-foreground">
                  Get notified about order status changes
                </p>
              </div>
              <Switch 
                v-model="userSettings.orderNotifications" 
                aria-label="Enable order notifications"
              />
            </div>
            <Separator />
            <div class="flex items-center justify-between">
              <div class="space-y-0.5 flex-1">
                <Label>Batch Updates</Label>
                <p class="text-sm text-muted-foreground">
                  Get notified about batch status changes
                </p>
              </div>
              <Switch 
                v-model="userSettings.batchNotifications" 
                aria-label="Enable batch notifications"
              />
            </div>
            <Button 
              @click="saveUserSettings" 
              :disabled="savingUserSettings"
              class="bg-[#8B4513] hover:bg-[#6B3410] text-white"
            >
              <span v-if="savingUserSettings">Saving...</span>
              <span v-else>Save Notification Preferences</span>
            </Button>
          </CardContent>
        </Card>
      </TabsContent>

      <!-- Account Tab -->
      <TabsContent value="account" class="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Data & Privacy</CardTitle>
            <CardDescription>Manage your data and privacy settings</CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
            <div class="space-y-2">
              <p class="text-sm text-muted-foreground">
                Your data is stored securely and only accessible to authorized personnel. 
                You can export your account data at any time.
              </p>
            </div>
            <div class="flex items-center justify-between">
              <div class="space-y-0.5 flex-1">
                <Label>Export Account Data</Label>
                <p class="text-sm text-muted-foreground">
                  Download a copy of your account data in JSON format
                </p>
              </div>
              <Button 
                variant="outline" 
                @click="exportData"
                :disabled="exportingData"
              >
                <span v-if="exportingData">Exporting...</span>
                <span v-else>Export Data</span>
              </Button>
            </div>
          </CardContent>
        </Card>
      </TabsContent>

      <!-- Tenant Settings Tab (Admin only) -->
      <TabsContent value="tenant" v-if="isAdmin" class="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Tenant Settings</CardTitle>
            <CardDescription>Configure organization-wide settings</CardDescription>
          </CardHeader>
          <CardContent class="space-y-6" v-if="tenantSettings">
            <div class="space-y-2">
              <Label for="leatherConsumptionMode">Leather Consumption Mode</Label>
              <Select 
                v-model="tenantSettings.leather_consumption_mode" 
                :placeholder="getConsumptionModeLabel(tenantSettings.leather_consumption_mode)"
              >
                <SelectItem value="formula">Formula-based</SelectItem>
                <SelectItem value="manual">Manual</SelectItem>
                <SelectItem value="hybrid">Hybrid</SelectItem>
              </Select>
              <p class="text-xs text-muted-foreground">
                Choose how leather consumption is calculated for production batches
              </p>
            </div>
            <Separator />
            <div class="space-y-2">
              <Label for="lowStockThresholdLeather">Low Stock Threshold (Leather) - sqft</Label>
              <Input
                id="lowStockThresholdLeather"
                type="number"
                :model-value="tenantSettings.low_stock_threshold_leather"
                @update:modelValue="handleTenantSettingChange('low_stock_threshold_leather', $event)"
                min="0"
                placeholder="100"
              />
              <p class="text-xs text-muted-foreground">
                Alert when leather inventory falls below this threshold
              </p>
            </div>
            <div class="space-y-2">
              <Label for="lowStockThresholdAccessories">Low Stock Threshold (Accessories)</Label>
              <Input
                id="lowStockThresholdAccessories"
                type="number"
                :model-value="tenantSettings.low_stock_threshold_accessories"
                @update:modelValue="handleTenantSettingChange('low_stock_threshold_accessories', $event)"
                min="0"
                placeholder="50"
              />
              <p class="text-xs text-muted-foreground">
                Alert when accessories inventory falls below this threshold
              </p>
            </div>
            <Separator />
            <div class="flex items-center justify-between">
              <div class="space-y-0.5 flex-1">
                <Label>Auto-generate Batch ID</Label>
                <p class="text-sm text-muted-foreground">
                  Automatically generate batch IDs when creating batches from orders
                </p>
              </div>
              <Switch 
                v-model="tenantSettings.auto_generate_batch_id" 
                aria-label="Enable auto-generate batch ID"
              />
            </div>
            <Separator />
            <div class="flex items-center justify-between">
              <div class="space-y-0.5 flex-1">
                <Label>Auto-create Invoice</Label>
                <p class="text-sm text-muted-foreground">
                  Automatically create commercial invoices when batches are created
                </p>
              </div>
              <Switch 
                v-model="tenantSettings.auto_create_invoice" 
                aria-label="Enable auto-create invoice"
              />
            </div>
            <Separator />
            <div class="space-y-2">
              <Label for="notificationEmail">Notification Email</Label>
              <Input
                id="notificationEmail"
                type="email"
                :model-value="tenantSettings.notification_email"
                @update:modelValue="handleTenantSettingChange('notification_email', $event)"
                placeholder="notifications@example.com"
              />
              <p class="text-xs text-muted-foreground">
                Email address for receiving system notifications
              </p>
            </div>
            <Button 
              @click="saveTenantSettings" 
              :disabled="savingTenantSettings"
              class="bg-[#8B4513] hover:bg-[#6B3410] text-white"
            >
              <span v-if="savingTenantSettings">Saving...</span>
              <span v-else>Save Tenant Settings</span>
            </Button>
          </CardContent>
        </Card>
      </TabsContent>
    </Tabs>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useAuthStore } from '@/stores/auth';
import apiClient from '@/api/client';
import Card from '@/components/ui/Card.vue';
import CardHeader from '@/components/ui/CardHeader.vue';
import CardTitle from '@/components/ui/CardTitle.vue';
import CardDescription from '@/components/ui/CardDescription.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Label from '@/components/ui/Label.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Select from '@/components/ui/Select.vue';
import SelectItem from '@/components/ui/SelectItem.vue';
import Switch from '@/components/ui/Switch.vue';
import Tabs from '@/components/ui/Tabs.vue';
import TabsList from '@/components/ui/TabsList.vue';
import TabsTrigger from '@/components/ui/TabsTrigger.vue';
import TabsContent from '@/components/ui/TabsContent.vue';
import Separator from '@/components/ui/Separator.vue';
import { useToast } from '@/composables/useToast';

const { toast } = useToast();
const authStore = useAuthStore();

const activeTab = ref('preferences');
const savingUserSettings = ref(false);
const savingTenantSettings = ref(false);
const exportingData = ref(false);
const loadingUserSettings = ref(false);
const loadingTenantSettings = ref(false);
const isInitialLoad = ref(true);

// User settings
const userSettings = ref({
  theme: 'light',
  language: 'en',
  emailNotifications: true,
  orderNotifications: true,
  batchNotifications: true,
  autoRefresh: 60,
  itemsPerPage: 25,
});

// Tenant settings (for Admin only)
const tenantSettings = ref(null);

// Check if user is Admin
const isAdmin = computed(() => {
  return authStore.hasRole('Admin');
});

// Load user settings from backend
const loadUserSettings = async () => {
  loadingUserSettings.value = true;
  isInitialLoad.value = true; // Prevent auto-save during initial load
  try {
    const response = await apiClient.get('/settings/user');
    if (response.data.preferences) {
      userSettings.value = {
        ...userSettings.value,
        ...response.data.preferences,
      };
    }
  } catch (error) {
    console.error('Error loading user settings:', error);
    toast.error('Error loading settings', error.response?.data?.message || 'Failed to load user settings');
  } finally {
    loadingUserSettings.value = false;
    // Allow auto-save after initial load completes
    setTimeout(() => {
      isInitialLoad.value = false;
    }, 500);
  }
};

// Load tenant settings from backend (Admin only)
const loadTenantSettings = async () => {
  if (!isAdmin.value) return;
  
  loadingTenantSettings.value = true;
  try {
    const response = await apiClient.get('/settings/tenant');
    if (response.data) {
      tenantSettings.value = {
        leather_consumption_mode: response.data.tenant?.leather_consumption_mode || 'formula',
        low_stock_threshold_leather: response.data.settings?.low_stock_threshold_leather || 100,
        low_stock_threshold_accessories: response.data.settings?.low_stock_threshold_accessories || 50,
        auto_generate_batch_id: response.data.settings?.auto_generate_batch_id ?? true,
        auto_create_invoice: response.data.settings?.auto_create_invoice ?? true,
        notification_email: response.data.settings?.notification_email || '',
      };
    }
  } catch (error) {
    console.error('Error loading tenant settings:', error);
    if (error.response?.status !== 403) {
      toast.error('Error loading tenant settings', error.response?.data?.message || 'Failed to load tenant settings');
    }
  } finally {
    loadingTenantSettings.value = false;
  }
};

// Save user settings to backend
const saveUserSettings = async () => {
  savingUserSettings.value = true;
  try {
    const payload = {
      theme: userSettings.value.theme,
      language: userSettings.value.language,
      email_notifications: userSettings.value.emailNotifications,
      order_notifications: userSettings.value.orderNotifications,
      batch_notifications: userSettings.value.batchNotifications,
      auto_refresh: parseInt(userSettings.value.autoRefresh) || 0,
      items_per_page: parseInt(userSettings.value.itemsPerPage) || 25,
    };

    await apiClient.put('/settings/user', payload);
    toast.success('Settings saved successfully!');
    
    // Also save to localStorage as backup
    localStorage.setItem('userSettings', JSON.stringify(userSettings.value));
  } catch (error) {
    console.error('Error saving user settings:', error);
    toast.error('Error saving settings', error.response?.data?.message || 'Failed to save user settings');
  } finally {
    savingUserSettings.value = false;
  }
};

// Save tenant settings to backend (Admin only)
const saveTenantSettings = async () => {
  if (!isAdmin.value || !tenantSettings.value) return;
  
  savingTenantSettings.value = true;
  try {
    const payload = {
      leather_consumption_mode: tenantSettings.value.leather_consumption_mode,
      low_stock_threshold_leather: parseInt(tenantSettings.value.low_stock_threshold_leather) || 100,
      low_stock_threshold_accessories: parseInt(tenantSettings.value.low_stock_threshold_accessories) || 50,
      auto_generate_batch_id: tenantSettings.value.auto_generate_batch_id ?? true,
      auto_create_invoice: tenantSettings.value.auto_create_invoice ?? true,
      notification_email: tenantSettings.value.notification_email || null,
    };

    await apiClient.put('/settings/tenant', payload);
    toast.success('Tenant settings saved successfully!');
  } catch (error) {
    console.error('Error saving tenant settings:', error);
    toast.error('Error saving tenant settings', error.response?.data?.message || 'Failed to save tenant settings');
  } finally {
    savingTenantSettings.value = false;
  }
};

// Watch for changes in user settings and auto-save (skip during initial load)
watch(
  () => userSettings.value,
  () => {
    // Skip auto-save during initial load
    if (isInitialLoad.value) return;
    
    // Auto-save after a short delay
    clearTimeout(window.settingsSaveTimeout);
    window.settingsSaveTimeout = setTimeout(() => {
      saveUserSettings();
    }, 1000);
  },
  { deep: true }
);

// Handle tenant setting change (for number inputs)
const handleTenantSettingChange = (key, value) => {
  if (!tenantSettings.value) return;
  // Convert string to number for numeric fields
  if (key === 'low_stock_threshold_leather' || key === 'low_stock_threshold_accessories') {
    tenantSettings.value[key] = parseInt(value) || 0;
  } else {
    tenantSettings.value[key] = value;
  }
};

// Export user data
const exportData = async () => {
  exportingData.value = true;
  try {
    const response = await apiClient.get('/settings/export-data');
    if (response.data.data) {
      // Create a blob from the JSON data
      const jsonData = JSON.stringify(response.data.data, null, 2);
      const blob = new Blob([jsonData], { type: 'application/json' });
      const url = URL.createObjectURL(blob);
      
      // Create a temporary link and click it to download
      const link = document.createElement('a');
      link.href = url;
      link.download = response.data.filename || `user_data_${Date.now()}.json`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
      
      toast.success('Data exported successfully!');
    }
  } catch (error) {
    console.error('Error exporting data:', error);
    toast.error('Error exporting data', error.response?.data?.message || 'Failed to export data');
  } finally {
    exportingData.value = false;
  }
};

// Helper functions
const getThemeLabel = (theme) => {
  const labels = {
    light: 'Light',
    dark: 'Dark',
    system: 'System',
  };
  return labels[theme] || 'Light';
};

const getRefreshLabel = (value) => {
  const labels = {
    0: 'Disabled',
    30: '30 seconds',
    60: '1 minute',
    300: '5 minutes',
    600: '10 minutes',
  };
  return labels[value] || '1 minute';
};

const getConsumptionModeLabel = (mode) => {
  const labels = {
    formula: 'Formula-based',
    manual: 'Manual',
    hybrid: 'Hybrid',
  };
  return labels[mode] || 'Formula-based';
};

// Load settings on mount
onMounted(async () => {
  await loadUserSettings();
  if (isAdmin.value) {
    await loadTenantSettings();
  }
});
</script>
