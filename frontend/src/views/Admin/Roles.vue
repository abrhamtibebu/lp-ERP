<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#8B4513]">Role Management</h1>
        <p class="text-gray-600 mt-1">Create and manage user roles with custom permissions</p>
      </div>
      <Button @click="openCreateDialog" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
        <Plus class="h-4 w-4 mr-2" />
        Create Role
      </Button>
    </div>

    <!-- Roles List -->
    <Card>
      <CardContent class="p-0">
        <div class="overflow-y-auto max-h-[calc(100vh-300px)]">
          <table class="w-full table-auto">
            <thead class="sticky top-0 bg-gray-50 z-10">
              <tr class="border-b">
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Display Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="role in roles" :key="role.id" class="hover:bg-gray-50">
                <td class="px-4 py-4 text-sm font-medium text-gray-900">
                  {{ role.name }}
                </td>
                <td class="px-4 py-4 text-sm text-gray-900">
                  {{ role.display_name }}
                </td>
                <td class="px-4 py-4 text-sm text-gray-600">
                  {{ role.description || 'N/A' }}
                </td>
                <td class="px-4 py-4 text-sm text-gray-600">
                  <div class="flex flex-wrap gap-1">
                    <Badge
                      v-for="permission in role.permissions"
                      :key="permission.id"
                      class="text-xs"
                    >
                      {{ permission.display_name }}
                    </Badge>
                    <span v-if="!role.permissions || role.permissions.length === 0" class="text-gray-400">
                      No permissions
                    </span>
                  </div>
                </td>
                <td class="px-4 py-4 text-sm text-gray-600">
                  {{ getAssignedUsersCount(role) }} user(s)
                </td>
                <td class="px-4 py-4 text-sm">
                  <div class="flex gap-2">
                    <Button variant="ghost" size="sm" @click="editRole(role)">
                      <Edit class="h-4 w-4 mr-1" />
                      Edit
                    </Button>
                    <Button
                      variant="ghost"
                      size="sm"
                      @click="deleteRole(role)"
                      :disabled="role.name === 'Admin'"
                      class="text-red-600 hover:text-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <Trash2 class="h-4 w-4 mr-1" />
                      Delete
                    </Button>
                  </div>
                </td>
              </tr>
              <tr v-if="roles.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                  No roles found. Create your first role to get started.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardContent>
    </Card>

    <!-- Create/Edit Role Dialog -->
    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Role' : 'Create Role'" class="max-w-4xl">
      <form @submit.prevent="saveRole" class="space-y-6">
        <!-- Role Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Shield class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Role Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="role_name" class="flex items-center gap-2">
                <Hash class="h-4 w-4 text-gray-500" />
                Role Name *
              </Label>
              <Input
                id="role_name"
                v-model="form.name"
                placeholder="e.g., inventory_manager"
                required
                :disabled="isEditing && form.name === 'Admin'"
              />
              <p class="text-xs text-gray-500">Unique identifier for the role (lowercase, no spaces)</p>
            </div>
            <div class="space-y-2">
              <Label for="display_name" class="flex items-center gap-2">
                <Tag class="h-4 w-4 text-gray-500" />
                Display Name *
              </Label>
              <Input
                id="display_name"
                v-model="form.display_name"
                placeholder="e.g., Inventory Manager"
                required
              />
              <p class="text-xs text-gray-500">Human-readable name for the role</p>
            </div>
            <div class="space-y-2 md:col-span-2">
              <Label for="description" class="flex items-center gap-2">
                <FileText class="h-4 w-4 text-gray-500" />
                Description
              </Label>
              <textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-[#8B4513]"
                placeholder="Enter role description..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Permissions Selection Section -->
        <div class="space-y-4">
          <div class="flex items-center justify-between pb-2 border-b border-gray-200">
            <div class="flex items-center gap-2">
              <Lock class="h-5 w-5 text-[#8B4513]" />
              <h3 class="text-lg font-semibold text-gray-900">Permissions</h3>
            </div>
            <div class="flex items-center gap-2">
              <Button
                type="button"
                variant="ghost"
                size="sm"
                @click="selectAllPermissions"
                class="text-xs"
              >
                Select All
              </Button>
              <Button
                type="button"
                variant="ghost"
                size="sm"
                @click="deselectAllPermissions"
                class="text-xs"
              >
                Deselect All
              </Button>
            </div>
          </div>
          <div class="space-y-4 max-h-[400px] overflow-y-auto">
            <!-- Permissions grouped by module -->
            <div
              v-for="(modulePermissions, module) in permissionsByModule"
              :key="module"
              class="border border-gray-200 rounded-lg p-4"
            >
              <div class="flex items-center justify-between mb-3">
                <h4 class="font-semibold text-gray-900 capitalize">{{ module }}</h4>
                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  @click="toggleModulePermissions(module)"
                  class="text-xs"
                >
                  {{ areAllModulePermissionsSelected(module) ? 'Deselect All' : 'Select All' }}
                </Button>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <label
                  v-for="permission in modulePermissions"
                  :key="permission.id"
                  class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 cursor-pointer"
                >
                  <input
                    type="checkbox"
                    :value="permission.id"
                    v-model="form.permissions"
                    class="h-4 w-4 text-[#8B4513] focus:ring-[#8B4513] border-gray-300 rounded"
                  />
                  <span class="text-sm text-gray-700">{{ permission.display_name }}</span>
                </label>
              </div>
            </div>
          </div>
          <p v-if="form.permissions.length === 0" class="text-sm text-orange-600">
            Please select at least one permission for this role.
          </p>
        </div>
      </form>
      <template #footer>
        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="dialogOpen = false">
            Cancel
          </Button>
          <Button
            type="button"
            @click="saveRole"
            :disabled="form.permissions.length === 0"
            class="bg-[#8B4513] hover:bg-[#6B3410] text-white disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ isEditing ? 'Update Role' : 'Create Role' }}
          </Button>
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Plus, Edit, Trash2, Shield, Hash, Tag, FileText, Lock } from 'lucide-vue-next';
import apiClient from '@/api/client';
import Card from '@/components/ui/Card.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Button from '@/components/ui/Button.vue';
import Badge from '@/components/ui/Badge.vue';
import Dialog from '@/components/ui/Dialog.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const { toast } = useToast();
const { confirm } = useConfirm();

const roles = ref([]);
const permissions = ref([]);
const permissionsByModule = ref({});
const dialogOpen = ref(false);
const isEditing = ref(false);
const selectedRole = ref(null);

const form = ref({
  name: '',
  display_name: '',
  description: '',
  permissions: [],
});

// Fetch roles and permissions
const fetchRoles = async () => {
  try {
    const response = await apiClient.get('/roles');
    roles.value = response.data.roles || [];
    permissions.value = response.data.permissions || [];
    permissionsByModule.value = response.data.permissions_by_module || {};
  } catch (error) {
    console.error('Error fetching roles:', error);
    toast.error('Error fetching roles', error.response?.data?.message || 'Failed to load roles');
  }
};

// Get assigned users count for a role
const getAssignedUsersCount = (role) => {
  if (!role.users) return 0;
  return role.users.length;
};

// Check if all permissions in a module are selected
const areAllModulePermissionsSelected = (module) => {
  const modulePermissions = permissionsByModule.value[module] || [];
  if (modulePermissions.length === 0) return false;
  return modulePermissions.every(permission =>
    form.value.permissions.includes(permission.id)
  );
};

// Toggle all permissions in a module
const toggleModulePermissions = (module) => {
  const modulePermissions = permissionsByModule.value[module] || [];
  const allSelected = areAllModulePermissionsSelected(module);
  
  if (allSelected) {
    // Deselect all permissions in this module
    modulePermissions.forEach(permission => {
      const index = form.value.permissions.indexOf(permission.id);
      if (index > -1) {
        form.value.permissions.splice(index, 1);
      }
    });
  } else {
    // Select all permissions in this module
    modulePermissions.forEach(permission => {
      if (!form.value.permissions.includes(permission.id)) {
        form.value.permissions.push(permission.id);
      }
    });
  }
};

// Select all permissions
const selectAllPermissions = () => {
  form.value.permissions = permissions.value.map(p => p.id);
};

// Deselect all permissions
const deselectAllPermissions = () => {
  form.value.permissions = [];
};

// Open create dialog
const openCreateDialog = () => {
  isEditing.value = false;
  selectedRole.value = null;
  form.value = {
    name: '',
    display_name: '',
    description: '',
    permissions: [],
  };
  dialogOpen.value = true;
};

// Edit role
const editRole = (role) => {
  isEditing.value = true;
  selectedRole.value = role;
  form.value = {
    name: role.name,
    display_name: role.display_name,
    description: role.description || '',
    permissions: role.permissions ? role.permissions.map(p => p.id) : [],
  };
  dialogOpen.value = true;
};

// Save role (create or update)
const saveRole = async () => {
  // Validation
  if (!form.value.name || form.value.name.trim() === '') {
    toast.warning('Please enter role name');
    return;
  }
  
  if (!form.value.display_name || form.value.display_name.trim() === '') {
    toast.warning('Please enter display name');
    return;
  }
  
  if (form.value.permissions.length === 0) {
    toast.warning('Please select at least one permission');
    return;
  }

  try {
    const payload = {
      name: form.value.name.trim(),
      display_name: form.value.display_name.trim(),
      description: form.value.description?.trim() || null,
      permissions: form.value.permissions.map(id => parseInt(id)),
    };
    
    if (isEditing.value) {
      await apiClient.put(`/roles/${selectedRole.value.id}`, payload);
      toast.success('Role updated successfully');
    } else {
      await apiClient.post('/roles', payload);
      toast.success('Role created successfully');
    }
    
    await fetchRoles();
    dialogOpen.value = false;
    resetForm();
  } catch (error) {
    console.error('Error saving role:', error);
    const errorMessage = error.response?.data?.message || error.response?.data?.errors
      ? (error.response.data.errors ? JSON.stringify(error.response.data.errors) : error.response.data.message)
      : 'Error saving role';
    toast.error('Error saving role', errorMessage);
  }
};

// Delete role
const deleteRole = async (role) => {
  // Prevent deleting Admin role
  if (role.name === 'Admin') {
    toast.warning('Cannot delete the Admin role for security reasons');
    return;
  }

  const confirmed = await confirm(
    `Are you sure you want to delete the role "${role.display_name}"? This action cannot be undone.`,
    'Delete Role',
    'warning'
  );
  
  if (!confirmed) return;

  try {
    await apiClient.delete(`/roles/${role.id}`);
    toast.success('Role deleted successfully');
    await fetchRoles();
  } catch (error) {
    console.error('Error deleting role:', error);
    const errorMessage = error.response?.data?.message || 'Error deleting role';
    toast.error('Error deleting role', errorMessage);
    
    // Show specific error if users are assigned
    if (error.response?.status === 422) {
      toast.warning(error.response.data.message || 'Cannot delete role with assigned users');
    }
  }
};

// Reset form
const resetForm = () => {
  form.value = {
    name: '',
    display_name: '',
    description: '',
    permissions: [],
  };
  isEditing.value = false;
  selectedRole.value = null;
};

onMounted(() => {
  fetchRoles();
});
</script>

