<template>
  <div class="space-y-6">
    <ActionBar title="Role Assignment" description="GM: Assign roles to users by email and department" />

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
              <Badge v-for="role in row.roles" :key="role.id" variant="primary">
                {{ role.display_name || role.name }}
              </Badge>
            </div>
          </template>
          <template #rowActions="{ row }">
            <Button variant="ghost" size="sm" @click="openAssignDialog(row)">
              <Edit class="h-4 w-4" />
              Assign Role
            </Button>
          </template>
        </DataTable>
      </CardContent>
    </Card>

    <!-- Assign Role Dialog -->
    <Dialog v-model="assignDialogOpen" title="Assign Role" description="Assign role and department to user">
      <form @submit.prevent="assignRole" class="space-y-4">
        <div class="space-y-2">
          <Label>User Email</Label>
          <Input :value="selectedUser?.email" disabled />
        </div>
        <div class="space-y-2">
          <Label for="role_id">Role *</Label>
          <Select v-model="assignForm.role_id">
            <SelectItem v-for="role in roles" :key="role.id" :value="role.id">
              {{ role.display_name || role.name }}
            </SelectItem>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="department">Department</Label>
          <Select v-model="assignForm.department">
            <SelectItem value="HR">HR</SelectItem>
            <SelectItem value="Inventory">Inventory</SelectItem>
            <SelectItem value="Production">Production</SelectItem>
            <SelectItem value="Logistics">Logistics</SelectItem>
            <SelectItem value="Finance">Finance</SelectItem>
          </Select>
        </div>
      </form>
      <template #footer>
        <Button type="button" variant="outline" @click="assignDialogOpen = false">Cancel</Button>
        <Button type="button" @click="assignRole">Assign</Button>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Edit } from 'lucide-vue-next';
import apiClient from '@/api/client';
import { useAuthStore } from '@/stores/auth';
import ActionBar from '@/components/layout/ActionBar.vue';
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

const authStore = useAuthStore();
const users = ref([]);
const roles = ref([]);
const assignDialogOpen = ref(false);
const selectedUser = ref(null);

const assignForm = ref({
  role_id: '',
  department: '',
});

const columns = [
  { key: 'email', label: 'Email', sortable: true },
  { key: 'name', label: 'Name', sortable: true },
  { key: 'department', label: 'Department', sortable: true },
  { key: 'roles', label: 'Roles', sortable: false },
];

const openAssignDialog = (user) => {
  selectedUser.value = user;
  assignForm.value = {
    role_id: user.roles?.[0]?.id || '',
    department: user.department || '',
  };
  assignDialogOpen.value = true;
};

const assignRole = async () => {
  try {
    await apiClient.post(`/users/${selectedUser.value.id}/assign-role`, assignForm.value);
    assignDialogOpen.value = false;
    await loadUsers();
  } catch (error) {
    console.error('Error assigning role:', error);
    alert(error.response?.data?.message || 'Error assigning role');
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
    roles.value = response.data || [];
  } catch (error) {
    console.error('Error loading roles:', error);
  }
};

onMounted(() => {
  if (authStore.hasRole('GM')) {
    loadUsers();
    loadRoles();
  }
});
</script>
