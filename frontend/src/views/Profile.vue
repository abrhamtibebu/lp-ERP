<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold text-[#8B4513]">My Profile</h1>
      <p class="text-gray-600 mt-1">View and manage your profile information</p>
    </div>

    <div class="grid gap-6 md:grid-cols-3">
      <!-- Profile Card -->
      <Card class="md:col-span-1">
        <CardContent class="pt-6">
          <div class="flex flex-col items-center text-center space-y-4">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-[#8B4513] to-[#6B3410] flex items-center justify-center text-white text-3xl font-bold shadow-lg">
              {{ userInitials }}
            </div>
            <div>
              <h2 class="text-xl font-bold">{{ user?.name || 'N/A' }}</h2>
              <p class="text-sm text-muted-foreground">{{ user?.email || 'N/A' }}</p>
            </div>
            <div class="w-full space-y-2">
              <Badge v-for="role in user?.roles || []" :key="role.id" variant="secondary" class="mr-1">
                {{ role.name }}
              </Badge>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Profile Information -->
      <Card class="md:col-span-2">
        <CardHeader>
          <div class="flex items-center justify-between">
            <div>
              <CardTitle>Profile Information</CardTitle>
              <CardDescription>Your personal and professional details</CardDescription>
            </div>
            <Button
              v-if="!isEditing"
              variant="outline"
              size="sm"
              @click="startEditing"
            >
              <Edit class="mr-2 h-4 w-4" />
              Edit Profile
            </Button>
            <div v-else class="flex gap-2">
              <Button
                variant="outline"
                size="sm"
                @click="cancelEditing"
                :disabled="profileLoading"
              >
                Cancel
              </Button>
              <Button
                size="sm"
                @click="handleUpdateProfile"
                :disabled="profileLoading"
                class="bg-[#8B4513] hover:bg-[#6B3410] text-white"
              >
                <span v-if="profileLoading">Saving...</span>
                <span v-else>Save Changes</span>
              </Button>
            </div>
          </div>
        </CardHeader>
        <CardContent class="space-y-6">
          <form @submit.prevent="handleUpdateProfile">
            <div class="grid gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="name">Full Name *</Label>
                <Input
                  id="name"
                  v-model="profileForm.name"
                  :disabled="!isEditing"
                  required
                />
              </div>
              <div class="space-y-2">
                <Label for="email">Email Address *</Label>
                <Input
                  id="email"
                  v-model="profileForm.email"
                  type="email"
                  :disabled="!isEditing"
                  required
                />
              </div>
              <div class="space-y-2">
                <Label>Department</Label>
                <Input :value="user?.department || 'N/A'" disabled />
              </div>
              <div class="space-y-2">
                <Label for="position">Position</Label>
                <Input
                  id="position"
                  v-model="profileForm.position"
                  :disabled="!isEditing"
                  placeholder="No position specified"
                />
              </div>
              <div class="space-y-2">
                <Label>Employed On</Label>
                <Input :value="formatDate(user?.employed_on)" disabled />
              </div>
              <div class="space-y-2">
                <Label for="address">Address</Label>
                <Input
                  id="address"
                  v-model="profileForm.address"
                  :disabled="!isEditing"
                  placeholder="No address provided"
                />
              </div>
            </div>
            <div class="space-y-2">
              <Label for="emergency_contact">Emergency Contact</Label>
              <Input
                id="emergency_contact"
                v-model="profileForm.emergency_contact"
                :disabled="!isEditing"
                placeholder="No emergency contact provided"
              />
            </div>
            <div v-if="profileError" class="mt-4 text-sm text-destructive">
              {{ profileError }}
            </div>
            <div v-if="profileSuccess" class="mt-4 text-sm text-green-600">
              {{ profileSuccess }}
            </div>
          </form>
        </CardContent>
      </Card>
    </div>

    <!-- Change Password Card -->
    <Card>
      <CardHeader>
        <CardTitle>Change Password</CardTitle>
        <CardDescription>Update your account password</CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="handleChangePassword" class="space-y-4">
          <div class="space-y-2">
            <Label for="current-password">Current Password</Label>
            <PasswordInput
              id="current-password"
              v-model="passwordForm.currentPassword"
              required
            />
          </div>
          <div class="space-y-2">
            <Label for="new-password">New Password</Label>
            <PasswordInput
              id="new-password"
              v-model="passwordForm.newPassword"
              required
              minlength="8"
            />
          </div>
          <div class="space-y-2">
            <Label for="confirm-password">Confirm New Password</Label>
            <PasswordInput
              id="confirm-password"
              v-model="passwordForm.confirmPassword"
              required
            />
          </div>
          <div v-if="passwordError" class="text-sm text-destructive">
            {{ passwordError }}
          </div>
          <div v-if="passwordSuccess" class="text-sm text-green-600">
            {{ passwordSuccess }}
          </div>
          <Button type="submit" :disabled="passwordLoading" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            <span v-if="passwordLoading">Updating...</span>
            <span v-else>Update Password</span>
          </Button>
        </form>
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { Edit } from 'lucide-vue-next';
import Card from '@/components/ui/Card.vue';
import CardHeader from '@/components/ui/CardHeader.vue';
import CardTitle from '@/components/ui/CardTitle.vue';
import CardDescription from '@/components/ui/CardDescription.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Input from '@/components/ui/Input.vue';
import PasswordInput from '@/components/ui/PasswordInput.vue';
import Label from '@/components/ui/Label.vue';
import Button from '@/components/ui/Button.vue';
import Badge from '@/components/ui/Badge.vue';
import apiClient from '@/api/client';

const authStore = useAuthStore();
const user = computed(() => authStore.user);

const isEditing = ref(false);
const profileForm = ref({
  name: '',
  email: '',
  address: '',
  position: '',
  emergency_contact: ''
});

// Initialize profile form with user data
const initializeProfileForm = () => {
  if (user.value) {
    profileForm.value.name = user.value.name || '';
    profileForm.value.email = user.value.email || '';
    profileForm.value.address = user.value.address || '';
    profileForm.value.position = user.value.position || '';
    profileForm.value.emergency_contact = user.value.emergency_contact || '';
  }
};

const userInitials = computed(() => {
  if (!user.value?.name) return 'U';
  const names = user.value.name.split(' ');
  return names.map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

// Refresh user data when component mounts to ensure we have the latest info
onMounted(async () => {
  if (authStore.token && authStore.user) {
    try {
      await authStore.checkAuth();
    } catch (error) {
      console.error('Failed to refresh user data:', error);
    }
  }
  // Wait for next tick to ensure user data is available
  await nextTick();
  // Initialize form after user data is available
  if (user.value) {
    initializeProfileForm();
  }
});

// Watch for user changes to update form (only when not editing)
watch(() => user.value, (newUser) => {
  if (newUser) {
    if (!isEditing.value) {
      initializeProfileForm();
    }
  }
}, { deep: true, immediate: true });

const profileError = ref('');
const profileSuccess = ref('');
const profileLoading = ref(false);

const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
});

const passwordError = ref('');
const passwordSuccess = ref('');
const passwordLoading = ref(false);

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
};

const startEditing = () => {
  isEditing.value = true;
  profileError.value = '';
  profileSuccess.value = '';
  // Re-initialize form with current user data
  initializeProfileForm();
};

const cancelEditing = () => {
  isEditing.value = false;
  profileError.value = '';
  profileSuccess.value = '';
  // Reset form to original user data
  initializeProfileForm();
};

const handleUpdateProfile = async () => {
  profileError.value = '';
  profileSuccess.value = '';
  
  // Client-side validation
  if (!profileForm.value.name || !profileForm.value.name.trim()) {
    profileError.value = 'Full name is required';
    return;
  }
  
  if (!profileForm.value.email || !profileForm.value.email.trim()) {
    profileError.value = 'Email address is required';
    return;
  }
  
  // Basic email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(profileForm.value.email)) {
    profileError.value = 'Please enter a valid email address';
    return;
  }
  
  profileLoading.value = true;
  
  try {
    const response = await apiClient.put('/user/profile', {
      name: profileForm.value.name.trim(),
      email: profileForm.value.email.trim(),
      address: profileForm.value.address?.trim() || null,
      position: profileForm.value.position?.trim() || null,
      emergency_contact: profileForm.value.emergency_contact?.trim() || null,
    });
    
    // Update user in auth store
    if (response.data.user) {
      authStore.user = response.data.user;
      // Update form with new values
      initializeProfileForm();
    }
    
    profileSuccess.value = 'Profile updated successfully';
    isEditing.value = false;
    
    // Clear success message after 5 seconds
    setTimeout(() => {
      profileSuccess.value = '';
    }, 5000);
  } catch (error) {
    // Handle validation errors from backend
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      const firstError = Object.values(errors).flat()[0];
      profileError.value = firstError || 'Validation failed. Please check your input.';
    } else {
      profileError.value = error.response?.data?.message || 'Failed to update profile. Please try again.';
    }
  } finally {
    profileLoading.value = false;
  }
};

const handleChangePassword = async () => {
  passwordError.value = '';
  passwordSuccess.value = '';
  
  // Client-side validation
  if (!passwordForm.value.currentPassword) {
    passwordError.value = 'Current password is required';
    return;
  }
  
  if (!passwordForm.value.newPassword) {
    passwordError.value = 'New password is required';
    return;
  }
  
  if (passwordForm.value.newPassword.length < 8) {
    passwordError.value = 'Password must be at least 8 characters long';
    return;
  }
  
  if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
    passwordError.value = 'New passwords do not match';
    return;
  }
  
  passwordLoading.value = true;
  
  try {
    await apiClient.put('/user/change-password', {
      current_password: passwordForm.value.currentPassword,
      new_password: passwordForm.value.newPassword,
      new_password_confirmation: passwordForm.value.confirmPassword
    });
    
    passwordSuccess.value = 'Password updated successfully';
    passwordForm.value = {
      currentPassword: '',
      newPassword: '',
      confirmPassword: ''
    };
    
    // Clear success message after 5 seconds
    setTimeout(() => {
      passwordSuccess.value = '';
    }, 5000);
  } catch (error) {
    // Handle validation errors from backend
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      if (errors.new_password) {
        passwordError.value = Array.isArray(errors.new_password) 
          ? errors.new_password[0] 
          : errors.new_password;
      } else if (errors.current_password) {
        passwordError.value = Array.isArray(errors.current_password) 
          ? errors.current_password[0] 
          : errors.current_password;
      } else {
        passwordError.value = 'Validation failed. Please check your input.';
      }
    } else {
      passwordError.value = error.response?.data?.message || 'Failed to update password. Please try again.';
    }
  } finally {
    passwordLoading.value = false;
  }
};
</script>

