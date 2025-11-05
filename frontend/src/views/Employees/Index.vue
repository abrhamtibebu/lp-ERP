<template>
  <div class="space-y-6">
    <ActionBar title="Employees" badge="Manage workforce">
      <template #actions>
        <Button @click="openCreateDialog">
          <Plus class="mr-2 h-4 w-4" />
          Add Employee
        </Button>
      </template>
    </ActionBar>

    <Card>
      <CardContent class="p-0">
        <DataTable
          :data="employees"
          :columns="columns"
          :selectable="true"
          @bulk-action="handleBulkAction"
        >
          <template #actions>
            <Button variant="ghost" size="sm">Export</Button>
          </template>
          <template #cell-name="{ row }">
            <div class="flex items-center space-x-2">
              <span class="font-medium">{{ row.name }}</span>
            </div>
          </template>
          <template #cell-department="{ row }">
            <Badge variant="secondary">{{ row.department }}</Badge>
          </template>
          <template #rowActions="{ row }">
            <div class="flex items-center space-x-2">
              <Button variant="ghost" size="sm" @click="editEmployee(row)">
                <Edit class="h-4 w-4" />
              </Button>
              <Button variant="ghost" size="sm" @click="deleteEmployee(row)">
                <Trash2 class="h-4 w-4 text-destructive" />
              </Button>
            </div>
          </template>
        </DataTable>
      </CardContent>
    </Card>

    <!-- Create/Edit Dialog -->
    <Dialog v-model="dialogOpen" :title="dialogTitle" :description="dialogDescription">
      <form @submit.prevent="saveEmployee" class="space-y-4">
        <div class="space-y-2">
          <Label for="name">Name</Label>
          <Input id="name" v-model="form.name" required />
        </div>
        <div class="space-y-2">
          <Label for="email">Email</Label>
          <Input id="email" type="email" v-model="form.email" required />
        </div>
        <div class="space-y-2">
          <Label for="department">Department</Label>
          <Select v-model="form.department">
            <SelectItem value="HR">HR</SelectItem>
            <SelectItem value="Inventory">Inventory</SelectItem>
            <SelectItem value="Production">Production</SelectItem>
            <SelectItem value="Logistics">Logistics</SelectItem>
            <SelectItem value="Finance">Finance</SelectItem>
            <SelectItem value="Management">Management</SelectItem>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="position">Position</Label>
          <Input id="position" v-model="form.position" />
        </div>
      </form>
      <template #footer>
        <Button type="button" variant="outline" @click="dialogOpen = false">Cancel</Button>
        <Button type="button" @click="saveEmployee">Save</Button>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Plus, Edit, Trash2 } from 'lucide-vue-next';
import apiClient from '@/api/client';
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

const employees = ref([]);
const dialogOpen = ref(false);
const editingEmployee = ref(null);

const form = ref({
  name: '',
  email: '',
  department: '',
  position: '',
});

const dialogTitle = computed(() => editingEmployee.value ? 'Edit Employee' : 'Add Employee');
const dialogDescription = computed(() => editingEmployee.value ? 'Update employee information' : 'Create a new employee record');

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'department', label: 'Department', sortable: true },
  { key: 'position', label: 'Position', sortable: true },
];

const openCreateDialog = () => {
  editingEmployee.value = null;
  form.value = { name: '', email: '', department: '', position: '' };
  dialogOpen.value = true;
};

const editEmployee = (employee) => {
  editingEmployee.value = employee;
  form.value = { ...employee };
  dialogOpen.value = true;
};

const deleteEmployee = async (employee) => {
  if (confirm(`Delete ${employee.name}?`)) {
    try {
      await apiClient.delete(`/employees/${employee.id}`);
      await loadEmployees();
    } catch (error) {
      console.error('Error deleting employee:', error);
    }
  }
};

const saveEmployee = async () => {
  try {
    if (editingEmployee.value) {
      await apiClient.put(`/employees/${editingEmployee.value.id}`, form.value);
    } else {
      await apiClient.post('/employees', form.value);
    }
    dialogOpen.value = false;
    await loadEmployees();
  } catch (error) {
    console.error('Error saving employee:', error);
  }
};

const handleBulkAction = (selectedIds) => {
  console.log('Bulk action on:', selectedIds);
};

const loadEmployees = async () => {
  try {
    const response = await apiClient.get('/employees');
    employees.value = response.data?.data || response.data || [];
  } catch (error) {
    console.error('Error loading employees:', error);
  }
};

onMounted(() => {
  loadEmployees();
});
</script>
