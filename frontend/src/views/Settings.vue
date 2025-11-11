<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold text-[#8B4513]">Settings</h1>
      <p class="text-gray-600 mt-1">Manage your application preferences</p>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
      <!-- Appearance Settings -->
      <Card>
        <CardHeader>
          <CardTitle>Appearance</CardTitle>
          <CardDescription>Customize the look and feel of the application</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="space-y-2">
            <Label>Theme</Label>
            <Select v-model="settings.theme" :placeholder="getThemeLabel(settings.theme)">
              <SelectItem value="light">Light</SelectItem>
              <SelectItem value="dark">Dark</SelectItem>
              <SelectItem value="system">System</SelectItem>
            </Select>
          </div>
          <div class="space-y-2">
            <Label>Language</Label>
            <Select v-model="settings.language" placeholder="English">
              <SelectItem value="en">English</SelectItem>
            </Select>
          </div>
          <Button @click="saveSettings" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            Save Preferences
          </Button>
        </CardContent>
      </Card>

      <!-- Notification Settings -->
      <Card>
        <CardHeader>
          <CardTitle>Notifications</CardTitle>
          <CardDescription>Manage your notification preferences</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="flex items-center justify-between">
            <div class="space-y-0.5">
              <Label>Email Notifications</Label>
              <p class="text-sm text-muted-foreground">Receive email updates</p>
            </div>
            <input
              type="checkbox"
              v-model="settings.emailNotifications"
              class="h-4 w-4 rounded border-gray-300"
            />
          </div>
          <div class="flex items-center justify-between">
            <div class="space-y-0.5">
              <Label>Order Updates</Label>
              <p class="text-sm text-muted-foreground">Get notified about order changes</p>
            </div>
            <input
              type="checkbox"
              v-model="settings.orderNotifications"
              class="h-4 w-4 rounded border-gray-300"
            />
          </div>
          <div class="flex items-center justify-between">
            <div class="space-y-0.5">
              <Label>Batch Updates</Label>
              <p class="text-sm text-muted-foreground">Get notified about batch status changes</p>
            </div>
            <input
              type="checkbox"
              v-model="settings.batchNotifications"
              class="h-4 w-4 rounded border-gray-300"
            />
          </div>
          <Button @click="saveSettings" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            Save Preferences
          </Button>
        </CardContent>
      </Card>

      <!-- Account Settings -->
      <Card>
        <CardHeader>
          <CardTitle>Account</CardTitle>
          <CardDescription>Manage your account settings</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="space-y-2">
            <Label>Auto-refresh Interval</Label>
            <Select v-model="settings.autoRefresh" :placeholder="getRefreshLabel(settings.autoRefresh)">
              <SelectItem value="30">30 seconds</SelectItem>
              <SelectItem value="60">1 minute</SelectItem>
              <SelectItem value="300">5 minutes</SelectItem>
              <SelectItem value="0">Disabled</SelectItem>
            </Select>
          </div>
          <div class="space-y-2">
            <Label>Items Per Page</Label>
            <Select v-model="settings.itemsPerPage" :placeholder="`${settings.itemsPerPage} items`">
              <SelectItem value="10">10</SelectItem>
              <SelectItem value="25">25</SelectItem>
              <SelectItem value="50">50</SelectItem>
              <SelectItem value="100">100</SelectItem>
            </Select>
          </div>
          <Button @click="saveSettings" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            Save Preferences
          </Button>
        </CardContent>
      </Card>

      <!-- Data & Privacy -->
      <Card>
        <CardHeader>
          <CardTitle>Data & Privacy</CardTitle>
          <CardDescription>Manage your data preferences</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="space-y-2">
            <p class="text-sm text-muted-foreground">
              Your data is stored securely and only accessible to authorized personnel.
            </p>
          </div>
          <div class="flex items-center justify-between">
            <div class="space-y-0.5">
              <Label>Data Export</Label>
              <p class="text-sm text-muted-foreground">Export your account data</p>
            </div>
            <Button variant="outline" size="sm" @click="exportData">
              Export Data
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Card from '@/components/ui/Card.vue';
import CardHeader from '@/components/ui/CardHeader.vue';
import CardTitle from '@/components/ui/CardTitle.vue';
import CardDescription from '@/components/ui/CardDescription.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Label from '@/components/ui/Label.vue';
import Button from '@/components/ui/Button.vue';
import Select from '@/components/ui/Select.vue';
import SelectItem from '@/components/ui/SelectItem.vue';

const settings = ref({
  theme: 'light',
  language: 'en',
  emailNotifications: true,
  orderNotifications: true,
  batchNotifications: true,
  autoRefresh: '60',
  itemsPerPage: '25'
});

onMounted(() => {
  // Load settings from localStorage
  const savedSettings = localStorage.getItem('userSettings');
  if (savedSettings) {
    try {
      settings.value = { ...settings.value, ...JSON.parse(savedSettings) };
    } catch (e) {
      console.error('Failed to load settings:', e);
    }
  }
});

const getThemeLabel = (theme) => {
  const labels = {
    light: 'Light',
    dark: 'Dark',
    system: 'System'
  };
  return labels[theme] || 'Light';
};

const getRefreshLabel = (value) => {
  const labels = {
    '30': '30 seconds',
    '60': '1 minute',
    '300': '5 minutes',
    '0': 'Disabled'
  };
  return labels[value] || '1 minute';
};

const saveSettings = () => {
  localStorage.setItem('userSettings', JSON.stringify(settings.value));
  // You could also send to backend API here
  alert('Settings saved successfully!');
};

const exportData = () => {
  // Implement data export functionality
  alert('Data export feature coming soon!');
};
</script>

