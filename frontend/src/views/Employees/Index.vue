<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#8B4513]">Employees</h1>
        <p class="text-gray-600 mt-1">Manage workforce</p>
      </div>
      <Button @click="openCreateDialog" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
        <Plus class="mr-2 h-4 w-4" />
        Add Employee
      </Button>
    </div>

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
          <template #cell-country="{ row }">
            {{ getCountryName(row.country) }}
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
    <Dialog v-model="dialogOpen" :title="dialogTitle" :description="dialogDescription" class="max-w-3xl">
      <form @submit.prevent="saveEmployee" class="space-y-6">
        <!-- Content Area -->
        <div class="space-y-6">
          
          <!-- Personal Information Section -->
          <div class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
              <User class="h-5 w-5 text-[#8B4513]" />
              <h3 class="text-lg font-semibold text-gray-900">Personal Information</h3>
        </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="space-y-2">
                <Label for="name" class="flex items-center gap-2">
                  <User class="h-4 w-4 text-gray-500" />
                  Full Name *
                </Label>
                <Input id="name" v-model="form.name" placeholder="Enter full name" required />
        </div>
        <div class="space-y-2">
                <Label for="email" class="flex items-center gap-2">
                  <Mail class="h-4 w-4 text-gray-500" />
                  Email Address *
                </Label>
                <Input id="email" type="email" v-model="form.email" placeholder="employee@example.com" required />
        </div>
        <div class="space-y-2" v-if="!editingEmployee">
                <Label for="password" class="flex items-center gap-2">
                  <FileText class="h-4 w-4 text-gray-500" />
                  Password *
                </Label>
                <PasswordInput id="password" v-model="form.password" placeholder="Enter password" required />
              </div>
              <div class="space-y-2">
                <Label for="address" class="flex items-center gap-2">
                  <MapPin class="h-4 w-4 text-gray-500" />
                  Address
                </Label>
                <Input id="address" v-model="form.address" placeholder="Enter address" />
        </div>
        <div class="space-y-2">
                <Label for="country" class="flex items-center gap-2">
                  <MapPin class="h-4 w-4 text-gray-500" />
                  Country
                </Label>
                <Select v-model="form.country" placeholder="Select country">
                  <SelectItem value="">Select country</SelectItem>
                  <SelectItem v-for="country in countries" :key="country.code" :value="country.code">
                    {{ country.name }}
                  </SelectItem>
                </Select>
              </div>
            </div>
          </div>

          <!-- Employment Details Section -->
          <div class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
              <Briefcase class="h-5 w-5 text-[#8B4513]" />
              <h3 class="text-lg font-semibold text-gray-900">Employment Details</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label for="department" class="flex items-center gap-2">
                  <Building2 class="h-4 w-4 text-gray-500" />
                  Department *
                </Label>
                <Select v-model="form.department" placeholder="Select department">
            <SelectItem value="HR">HR</SelectItem>
            <SelectItem value="Inventory">Inventory</SelectItem>
            <SelectItem value="Production">Production</SelectItem>
            <SelectItem value="Logistics">Logistics</SelectItem>
            <SelectItem value="Finance">Finance</SelectItem>
            <SelectItem value="Operations">Operations</SelectItem>
          </Select>
        </div>
        <div class="space-y-2">
                <Label for="position" class="flex items-center gap-2">
                  <Briefcase class="h-4 w-4 text-gray-500" />
                  Position Held *
                </Label>
                <Input id="position" v-model="form.position" placeholder="Enter position" required />
        </div>
        <div class="space-y-2">
                <Label for="employed_on" class="flex items-center gap-2">
                  <Calendar class="h-4 w-4 text-gray-500" />
                  Employment Date *
                </Label>
          <Input id="employed_on" type="date" v-model="form.employed_on" required />
        </div>
            </div>
          </div>

          <!-- Emergency Contact Section -->
          <div class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
              <Phone class="h-5 w-5 text-[#8B4513]" />
              <h3 class="text-lg font-semibold text-gray-900">Emergency Contact</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label for="emergency_contact" class="flex items-center gap-2">
                  <Phone class="h-4 w-4 text-gray-500" />
                  Emergency Contact Person *
                </Label>
                <Input id="emergency_contact" v-model="form.emergency_contact" placeholder="Enter emergency contact name" required />
              </div>
            </div>
          </div>

          <!-- Documents Section -->
          <div class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
              <Upload class="h-5 w-5 text-[#8B4513]" />
              <h3 class="text-lg font-semibold text-gray-900">Documents</h3>
            </div>
            <div class="space-y-4">
        <div class="space-y-2">
                <Label for="documents" class="flex items-center gap-2">
                  <FileText class="h-4 w-4 text-gray-500" />
                  Upload Documents
                </Label>
                <div class="flex items-center justify-center w-full">
                  <label
                    for="documents"
                    class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors"
                  >
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                      <Upload class="w-8 h-8 mb-2 text-gray-500" />
                      <p class="mb-2 text-sm text-gray-500">
                        <span class="font-semibold">Click to upload</span> or drag and drop
                      </p>
                      <p class="text-xs text-gray-500">PDF, DOC, DOCX, PNG, JPG (MAX. 10MB each)</p>
                    </div>
                    <input
                      id="documents"
                      type="file"
                      class="hidden"
                      multiple
                      @change="handleFileChange"
                      accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
                    />
                  </label>
        </div>
                <!-- File List Preview -->
                <div v-if="selectedFiles.length > 0" class="mt-3 space-y-2">
                  <p class="text-sm font-medium text-gray-700">Selected Files:</p>
        <div class="space-y-2">
                    <div
                      v-for="(file, index) in selectedFiles"
                      :key="index"
                      class="flex items-center justify-between p-2 bg-gray-50 rounded-md border border-gray-200"
                    >
                      <div class="flex items-center gap-2 flex-1 min-w-0">
                        <FileText class="h-4 w-4 text-gray-500 flex-shrink-0" />
                        <span class="text-sm text-gray-700 truncate">{{ file.name }}</span>
                        <span class="text-xs text-gray-500 flex-shrink-0">({{ formatFileSize(file.size) }})</span>
                      </div>
                      <button
                        type="button"
                        @click="removeFile(index)"
                        class="ml-2 p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition-colors"
                      >
                        <X class="h-4 w-4" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </form>
      <template #footer>
        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="dialogOpen = false">
            Cancel
          </Button>
          <Button type="button" @click="saveEmployee" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            {{ editingEmployee ? 'Update Employee' : 'Create Employee' }}
          </Button>
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Plus, Edit, Trash2, User, Mail, MapPin, Building2, Briefcase, Calendar, Phone, FileText, Upload, X } from 'lucide-vue-next';
import apiClient from '@/api/client';
import Card from '@/components/ui/Card.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Button from '@/components/ui/Button.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Dialog from '@/components/ui/Dialog.vue';
import Input from '@/components/ui/Input.vue';
import PasswordInput from '@/components/ui/PasswordInput.vue';
import Label from '@/components/ui/Label.vue';
import Select from '@/components/ui/Select.vue';
import SelectItem from '@/components/ui/SelectItem.vue';
import Badge from '@/components/ui/Badge.vue';
import { countries } from '@/data/countries';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const { toast } = useToast();
const { confirm } = useConfirm();

const employees = ref([]);
const dialogOpen = ref(false);
const editingEmployee = ref(null);
const selectedFiles = ref([]);

const form = ref({
  name: '',
  address: '',
  country: '',
  email: '',
  password: '',
  department: '',
  position: '',
  employed_on: '',
  emergency_contact: '',
  documents: null,
});

const dialogTitle = computed(() => editingEmployee.value ? 'Edit Employee' : 'Add Employee');
const dialogDescription = computed(() => editingEmployee.value ? 'Update employee information' : 'Create a new employee record');

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'department', label: 'Department', sortable: true },
  { key: 'position', label: 'Position', sortable: true },
  { key: 'country', label: 'Country', sortable: true },
];

const getCountryName = (code) => {
  if (!code) return 'N/A';
  const country = countries.find(c => c.code === code);
  return country ? country.name : code;
};

const handleFileChange = (event) => {
  const files = Array.from(event.target.files || []);
  form.value.documents = event.target.files;
  selectedFiles.value = files;
};

const removeFile = (index) => {
  selectedFiles.value.splice(index, 1);
  // Update the file input
  const dataTransfer = new DataTransfer();
  selectedFiles.value.forEach(file => {
    dataTransfer.items.add(file);
  });
  const fileInput = document.getElementById('documents');
  if (fileInput) {
    fileInput.files = dataTransfer.files;
    form.value.documents = dataTransfer.files;
  }
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const openCreateDialog = () => {
  editingEmployee.value = null;
  form.value = { 
    name: '', 
    address: '',
    country: '',
    email: '', 
    password: '',
    department: '', 
    position: '', 
    employed_on: '',
    emergency_contact: '',
    documents: null
  };
  selectedFiles.value = [];
  dialogOpen.value = true;
};

const editEmployee = (employee) => {
  editingEmployee.value = employee;
  form.value = { ...employee };
  selectedFiles.value = [];
  dialogOpen.value = true;
};

const deleteEmployee = async (employee) => {
  const confirmed = await confirm(
    `Are you sure you want to delete ${employee.name}?`,
    'Delete Employee',
    'danger'
  );
  
  if (!confirmed) return;

  try {
    await apiClient.delete(`/employees/${employee.id}`);
    await loadEmployees();
    toast.success('Employee deleted successfully');
  } catch (error) {
    console.error('Error deleting employee:', error);
    toast.error('Error deleting employee', error.response?.data?.message || 'Error deleting employee');
  }
};

const saveEmployee = async () => {
  // Validation
  if (!form.value.name || form.value.name.trim() === '') {
    toast.warning('Please enter employee name');
    return;
  }
  
  if (!form.value.email || form.value.email.trim() === '') {
    toast.warning('Please enter email address');
    return;
  }
  
  if (!editingEmployee.value && (!form.value.password || form.value.password.trim() === '')) {
    toast.warning('Please enter password for new employee');
    return;
  }
  
  if (!form.value.department || form.value.department.trim() === '') {
    toast.warning('Please select department');
    return;
  }
  
  if (!form.value.position || form.value.position.trim() === '') {
    toast.warning('Please enter position');
    return;
  }
  
  if (!form.value.employed_on) {
    toast.warning('Please enter employment date');
    return;
  }
  
  if (!form.value.emergency_contact || form.value.emergency_contact.trim() === '') {
    toast.warning('Please enter emergency contact');
    return;
  }

  try {
    const formData = new FormData();
    Object.keys(form.value).forEach(key => {
      if (key === 'documents') {
        if (form.value.documents) {
          Array.from(form.value.documents).forEach(file => {
            formData.append('documents[]', file);
          });
        }
      } else if (form.value[key] !== null && form.value[key] !== '') {
        formData.append(key, form.value[key]);
      }
    });

    if (editingEmployee.value) {
      await apiClient.put(`/employees/${editingEmployee.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    } else {
      await apiClient.post('/employees', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    }
    dialogOpen.value = false;
    await loadEmployees();
    toast.success(
      editingEmployee.value ? 'Employee updated successfully' : 'Employee created successfully'
    );
  } catch (error) {
    console.error('Error saving employee:', error);
    const errorMessage = error.response?.data?.message || error.response?.data?.errors
      ? (error.response.data.errors ? JSON.stringify(error.response.data.errors) : error.response.data.message)
      : 'Error saving employee';
    toast.error('Error saving employee', errorMessage);
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
