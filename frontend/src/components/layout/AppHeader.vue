<template>
  <header class="sticky top-0 z-40 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
    <div class="flex h-14 items-center px-4">
      <div class="flex flex-1 items-center space-x-4">
        <!-- Logo -->
        <div class="flex items-center space-x-2">
          <button
            @click="$emit('toggle-sidebar')"
            class="inline-flex items-center justify-center rounded-md p-2 text-muted-foreground hover:bg-accent hover:text-accent-foreground lg:hidden"
          >
            <Menu class="h-5 w-5" />
          </button>
          <router-link to="/" class="flex items-center space-x-2">
            <Package class="h-6 w-6 text-primary" />
            <span class="font-bold text-lg">Leather ERP</span>
          </router-link>
        </div>

        <!-- Search -->
        <div class="flex-1 max-w-lg">
          <div class="relative">
            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input
              v-model="searchQuery"
              placeholder="Search... (Ctrl+K)"
              class="pl-8 h-9"
              @keydown.ctrl.k.prevent="focusSearch"
            />
          </div>
        </div>
      </div>

      <div class="flex items-center space-x-4">
        <!-- Notifications -->
        <Button variant="ghost" size="icon" class="relative">
          <Bell class="h-5 w-5" />
          <span v-if="hasNotifications" class="absolute top-1 right-1 h-2 w-2 rounded-full bg-destructive"></span>
        </Button>

        <!-- User Menu -->
        <DropdownMenu align="end" class="w-56">
          <template #trigger>
            <Button variant="ghost" class="flex items-center space-x-2 h-9 px-2">
              <Avatar class="h-8 w-8">
                <AvatarFallback>{{ userInitials }}</AvatarFallback>
              </Avatar>
              <div class="hidden md:flex flex-col items-start">
                <span class="text-sm font-medium">{{ user?.name }}</span>
                <span class="text-xs text-muted-foreground">{{ user?.email }}</span>
              </div>
              <ChevronDown class="h-4 w-4" />
            </Button>
          </template>
          <DropdownMenuItem>
            <User class="mr-2 h-4 w-4" />
            <span>Profile</span>
          </DropdownMenuItem>
          <DropdownMenuItem>
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
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { Menu, Package, Search, Bell, ChevronDown, User, Settings, LogOut } from 'lucide-vue-next';
import Button from '../ui/Button.vue';
import Input from '../ui/Input.vue';
import Avatar from '../ui/Avatar.vue';
import AvatarFallback from '../ui/AvatarFallback.vue';
import DropdownMenu from '../ui/DropdownMenu.vue';
import DropdownMenuItem from '../ui/DropdownMenuItem.vue';
import Separator from '../ui/Separator.vue';

defineEmits(['toggle-sidebar']);

const router = useRouter();
const authStore = useAuthStore();

const searchQuery = ref('');
const hasNotifications = ref(false);

const user = computed(() => authStore.user);

const userInitials = computed(() => {
  if (!user.value?.name) return 'U';
  const names = user.value.name.split(' ');
  return names.map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

const focusSearch = () => {
  // Focus search input
  document.querySelector('input[placeholder*="Search"]')?.focus();
};

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>

