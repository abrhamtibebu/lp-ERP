<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-3xl font-bold text-[#8B4513]">Role Assignment</h1>
      <p class="text-gray-600 mt-1">Admin: Assign roles to users by email and department</p>
    </div>

    <Card>
      <CardContent class="p-0">
        <DataTable :data="users" :columns="columns">
          <template #cell-email="{ row }">
            <div class="font-medium">{{ row.email }}</div>
          </template>
          <template #cell-department="{ row }">
            <Badge variant="secondary">{{ row.department || 'N/A' }}</Badge>
          </template>
          <template #cell-roles="{ row }">
            <div class="flex gap-2 flex-wrap">
              <Badge v-for="role in row.roles" :key="role.id" variant="default">
                {{ role.display_name || role.name }}
              </Badge>
            </div>
          </template>
          <template #rowActions="{ row }">
            <div class="flex gap-2">
              <Button variant="ghost" size="sm" @click="openAssignDialog(row)">
                <Edit class="h-4 w-4" />
                Assign Role
              </Button>
              <Button variant="ghost" size="sm" @click="openChangePasswordDialog(row)">
                <Key class="h-4 w-4" />
                Change Password
              </Button>
              <Button 
                variant="ghost" 
                size="sm" 
                @click="toggleApproverAccess(row)"
                :class="isApprover(row) ? 'text-green-600' : 'text-gray-600'"
              >
                <CheckCircle v-if="isApprover(row)" class="h-4 w-4" />
                <XCircle v-else class="h-4 w-4" />
                {{ isApprover(row) ? 'Approver' : 'Not Approver' }}
              </Button>
            </div>
          </template>
        </DataTable>
      </CardContent>
    </Card>

    <!-- Assign Role Dialog -->
    <Dialog v-model="assignDialogOpen" title="Assign Role" description="Assign role and department to user" class="max-w-3xl">
      <form @submit.prevent="assignRole" class="space-y-6">
        <!-- User Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <User class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">User Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2 md:col-span-2">
              <Label class="flex items-center gap-2">
                <User class="h-4 w-4 text-gray-500" />
                User Email
              </Label>
              <Input :value="selectedUser?.email" disabled placeholder="User email address" />
            </div>
          </div>
        </div>

        <!-- Role Assignment Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Shield class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Role Assignment</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="role_id" class="flex items-center gap-2">
                <Shield class="h-4 w-4 text-gray-500" />
                Role *
              </Label>
              <Select v-model="assignForm.role_id" placeholder="Select role">
                <SelectItem v-for="role in roles" :key="role.id" :value="String(role.id)">
                  {{ role.display_name || role.name }}
                </SelectItem>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="department" class="flex items-center gap-2">
                <Building2 class="h-4 w-4 text-gray-500" />
                Department
              </Label>
              <Select v-model="assignForm.department" placeholder="Select department">
                <SelectItem value="">Select department</SelectItem>
                <SelectItem value="HR">HR</SelectItem>
                <SelectItem value="Inventory">Inventory</SelectItem>
                <SelectItem value="Production">Production</SelectItem>
                <SelectItem value="Logistics">Logistics</SelectItem>
                <SelectItem value="Finance">Finance</SelectItem>
                <SelectItem value="Operations">Operations</SelectItem>
              </Select>
            </div>
          </div>
        </div>
      </form>
      <template #footer>
        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="assignDialogOpen = false">
            Cancel
          </Button>
          <Button type="button" @click="assignRole" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            Assign Role
          </Button>
        </div>
      </template>
    </Dialog>

    <!-- Change Password Dialog -->
    <Dialog v-model="passwordDialogOpen" title="Change Password" description="Change password for user" class="max-w-3xl">
      <form @submit.prevent="changePassword" class="space-y-6">
        <!-- User Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <User class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">User Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2 md:col-span-2">
              <Label class="flex items-center gap-2">
                <User class="h-4 w-4 text-gray-500" />
                User Email
              </Label>
              <Input :value="selectedUser?.email" disabled placeholder="User email address" />
            </div>
          </div>
        </div>

        <!-- Password Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Lock class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Password Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="new_password" class="flex items-center gap-2">
                <Lock class="h-4 w-4 text-gray-500" />
                New Password *
              </Label>
              <Input id="new_password" v-model="passwordForm.new_password" type="password" required minlength="8" placeholder="Enter new password" />
              <p class="text-xs text-gray-500">Minimum 8 characters</p>
            </div>
            <div class="space-y-2">
              <Label for="confirm_password" class="flex items-center gap-2">
                <Lock class="h-4 w-4 text-gray-500" />
                Confirm Password *
              </Label>
              <Input id="confirm_password" v-model="passwordForm.confirm_password" type="password" required minlength="8" placeholder="Confirm new password" />
            </div>
            <div class="space-y-2 md:col-span-2">
              <p v-if="passwordError" class="text-sm text-red-600">{{ passwordError }}</p>
              <p v-if="passwordSuccess" class="text-sm text-green-600">{{ passwordSuccess }}</p>
            </div>
          </div>
        </div>
      </form>
      <template #footer>
        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="passwordDialogOpen = false">
            Cancel
          </Button>
          <Button type="button" @click="changePassword" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            Change Password
          </Button>
        </div>
      </template>
    </Dialog>

    <!-- Approver Access Section -->
    <Card class="mt-6">
      <CardContent class="p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Approver Access</h2>
        <p class="text-sm text-gray-600 mb-4">Users with approver access can approve procurement requests and other operational requests.</p>
        <div class="space-y-2">
          <div v-for="user in users" :key="user.id" class="flex items-center justify-between p-3 border rounded">
            <div>
              <p class="font-medium">{{ user.name }} ({{ user.email }})</p>
              <p class="text-sm text-gray-600">{{ user.department || 'N/A' }}</p>
            </div>
            <div class="flex items-center gap-2">
              <Badge v-if="isApprover(user)" variant="default" class="bg-green-100 text-green-800">
                Approver
              </Badge>
              <Badge v-else variant="secondary">
                Not Approver
              </Badge>
              <Button 
                variant="ghost" 
                size="sm" 
                @click="toggleApproverAccess(user)"
              >
                {{ isApprover(user) ? 'Revoke' : 'Grant' }}
              </Button>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Edit, Key, CheckCircle, XCircle, User, Shield, Building2, Lock } from 'lucide-vue-next';
import apiClient from '@/api/client';
import { useAuthStore } from '@/stores/auth';
import Card from '@/components/ui/Card.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Button from '@/components/ui/Button.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Dialog from '@/components/ui/Dialog.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Select from '@/components/ui/Select.vue';
import SelectItem from '@/components/ui/SelectItem.vue';
import Badge from '@/components/ui/Badge.vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const authStore = useAuthStore();
const { toast } = useToast();
const { confirm } = useConfirm();

const users = ref([]);
const roles = ref([]);
const assignDialogOpen = ref(false);
const passwordDialogOpen = ref(false);
const selectedUser = ref(null);

const assignForm = ref({
  role_id: '',
  department: '',
});

const passwordForm = ref({
  new_password: '',
  confirm_password: '',
});

const passwordError = ref('');
const passwordSuccess = ref('');

const columns = [
  { key: 'email', label: 'Email', sortable: true },
  { key: 'name', label: 'Name', sortable: true },
  { key: 'department', label: 'Department', sortable: true },
  { key: 'roles', label: 'Roles', sortable: false },
];

const openAssignDialog = (user) => {
  selectedUser.value = user;
  assignForm.value = {
    role_id: user.roles?.[0]?.id ? String(user.roles[0].id) : '',
    department: user.department || '',
  };
  assignDialogOpen.value = true;
};

const assignRole = async () => {
  try {
    const payload = {
      role_id: parseInt(assignForm.value.role_id),
      department: assignForm.value.department,
    };
    await apiClient.post(`/users/${selectedUser.value.id}/assign-role`, payload);
    assignDialogOpen.value = false;
    await loadUsers();
    toast.success('Role assigned successfully');
  } catch (error) {
    console.error('Error assigning role:', error);
    toast.error('Error assigning role', error.response?.data?.message || 'Error assigning role');
  }
};

const openChangePasswordDialog = (user) => {
  selectedUser.value = user;
  passwordForm.value = {
    new_password: '',
    confirm_password: '',
  };
  passwordError.value = '';
  passwordSuccess.value = '';
  passwordDialogOpen.value = true;
};

const changePassword = async () => {
  passwordError.value = '';
  passwordSuccess.value = '';

  if (!passwordForm.value.new_password) {
    passwordError.value = 'New password is required';
    return;
  }

  if (passwordForm.value.new_password.length < 8) {
    passwordError.value = 'Password must be at least 8 characters long';
    return;
  }

  if (passwordForm.value.new_password !== passwordForm.value.confirm_password) {
    passwordError.value = 'Passwords do not match';
    return;
  }

  try {
    await apiClient.put(`/users/${selectedUser.value.id}/change-password`, {
      new_password: passwordForm.value.new_password,
      new_password_confirmation: passwordForm.value.confirm_password,
    });
    passwordSuccess.value = 'Password changed successfully';
    setTimeout(() => {
      passwordDialogOpen.value = false;
      passwordForm.value = {
        new_password: '',
        confirm_password: '',
      };
    }, 1500);
    await loadUsers();
  } catch (error) {
    console.error('Error changing password:', error);
    passwordError.value = error.response?.data?.message || 'Error changing password';
  }
};

const isApprover = (user) => {
  if (!user.permissions) return false;
  return user.permissions.some(permission => permission.name === 'operations.approve');
};

const toggleApproverAccess = async (user) => {
  const confirmed = await confirm(
    `Are you sure you want to ${isApprover(user) ? 'revoke' : 'grant'} approver access for ${user.name}?`,
    isApprover(user) ? 'Revoke Approver Access' : 'Grant Approver Access',
    'warning'
  );
  
  if (!confirmed) return;

  try {
    await apiClient.put(`/users/${user.id}/approver-access`, {
      can_approve: !isApprover(user),
    });
    await loadUsers();
    toast.success(`Approver access ${isApprover(user) ? 'revoked' : 'granted'} successfully`);
  } catch (error) {
    console.error('Error updating approver access:', error);
    toast.error('Error updating approver access', error.response?.data?.message || 'Error updating approver access');
  }
};

const loadUsers = async () => {
  try {
    const response = await apiClient.get('/users');
    users.value = response.data || [];
  } catch (error) {
    console.error('Error loading users:', error);
  }
};

const loadRoles = async () => {
  try {
    const response = await apiClient.get('/roles');
    // Handle new response structure with roles array
    roles.value = response.data.roles || response.data || [];
  } catch (error) {
    console.error('Error loading roles:', error);
    toast.error('Error loading roles', error.response?.data?.message || 'Failed to load roles');
  }
};

onMounted(() => {
  if (authStore.hasRole('Admin')) {
    loadUsers();
    loadRoles();
  }
});
</script>
