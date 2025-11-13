<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#8B4513]">Operations</h1>
        <p class="text-gray-600 mt-1">Procurement requests and approvals</p>
      </div>
      <Button @click="openCreateDialog" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
        <Plus class="h-4 w-4 mr-2" />
        Create Request
      </Button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-gray-900">{{ stats.total || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">Total Requests</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-orange-600">{{ stats.pending || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">Pending Approval</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-green-600">{{ stats.approved || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">Approved</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="text-3xl font-bold text-blue-600">{{ stats.completed || 0 }}</div>
          <div class="text-sm text-gray-600 mt-1">Completed</div>
        </CardContent>
      </Card>
    </div>

    <!-- Procurement Requests -->
    <div class="space-y-4">
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-900">Procurement Requests</h2>
        <div class="relative w-64">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
          <Input
            v-model="searchQuery"
            placeholder="Search requests..."
            class="pl-10"
          />
        </div>
      </div>

      <Card>
        <CardContent class="p-0">
          <div class="overflow-y-auto max-h-[calc(100vh-300px)]">
            <table class="w-full table-auto">
              <thead class="sticky top-0 bg-gray-50 z-10">
                <tr class="border-b">
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request #</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Date</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="request in filteredRequests" :key="request.id" class="hover:bg-gray-50">
                  <td class="px-4 py-4 text-sm font-medium text-gray-900">
                    {{ request.request_number }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ request.supplier?.name || 'N/A' }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ formatDate(request.request_date) }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ request.requested_by?.name || 'N/A' }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ request.items?.length || 0 }} item(s)
                  </td>
                  <td class="px-4 py-4">
                    <Badge :class="getStatusClass(request.status)">
                      {{ getStatusLabel(request.status) }}
                    </Badge>
                  </td>
                  <td class="px-4 py-4 text-sm">
                    <div class="flex gap-2 flex-wrap">
                      <Button 
                        variant="ghost" 
                        size="sm"
                        @click="viewRequest(request)"
                      >
                        <Eye class="h-4 w-4" />
                      </Button>
                      <Button 
                        v-if="request.status === 'pending'"
                        variant="ghost" 
                        size="sm"
                        @click="editRequest(request)"
                      >
                        <Edit class="h-4 w-4" />
                      </Button>
                      <Button 
                        v-if="request.status === 'pending' && canApprove"
                        variant="ghost" 
                        size="sm"
                        @click="approveRequest(request)"
                        class="text-green-600 hover:text-green-700"
                      >
                        <Check class="h-4 w-4" />
                      </Button>
                      <Button 
                        v-if="request.status === 'pending' && canApprove"
                        variant="ghost" 
                        size="sm"
                        @click="rejectRequest(request)"
                        class="text-red-600 hover:text-red-700"
                      >
                        <X class="h-4 w-4" />
                      </Button>
                      <Button 
                        v-if="request.status === 'pending'"
                        variant="ghost" 
                        size="sm"
                        @click="deleteRequest(request)"
                        class="text-red-600 hover:text-red-700"
                      >
                        <Trash2 class="h-4 w-4" />
                      </Button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredRequests.length === 0">
                  <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                    No procurement requests found
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Create/Edit Request Dialog -->
    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Procurement Request' : 'Create Procurement Request'" class="max-w-3xl">
      <form @submit.prevent="saveRequest" class="space-y-6">
        <!-- Request Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <ShoppingCart class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Request Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="request_date" class="flex items-center gap-2">
                <Calendar class="h-4 w-4 text-gray-500" />
                Request Date *
              </Label>
              <Input id="request_date" v-model="form.request_date" type="date" required />
            </div>
            <div class="space-y-2">
              <Label for="supplier_id" class="flex items-center gap-2">
                <Building2 class="h-4 w-4 text-gray-500" />
                Supplier (Optional)
              </Label>
              <Select id="supplier_id" v-model="form.supplier_id" placeholder="Select a supplier">
                <SelectItem value="">Select a supplier</SelectItem>
                <SelectItem v-for="supplier in suppliers" :key="supplier.id" :value="String(supplier.id)">
                  {{ supplier.name }}
                </SelectItem>
              </Select>
            </div>
          </div>
        </div>

        <!-- People Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <User class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">People</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="submitted_by" class="flex items-center gap-2">
                <User class="h-4 w-4 text-gray-500" />
                Submitted By
              </Label>
              <Select id="submitted_by" v-model="form.submitted_by" placeholder="Select user">
                <SelectItem value="">Select user</SelectItem>
                <SelectItem v-for="user in users" :key="user.id" :value="String(user.id)">
                  {{ user.name }}
                </SelectItem>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="received_by" class="flex items-center gap-2">
                <User class="h-4 w-4 text-gray-500" />
                Received By
              </Label>
              <Select id="received_by" v-model="form.received_by" placeholder="Select user">
                <SelectItem value="">Select user</SelectItem>
                <SelectItem v-for="user in users" :key="user.id" :value="String(user.id)">
                  {{ user.name }}
                </SelectItem>
              </Select>
            </div>
          </div>
        </div>

        <!-- Items Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Package class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Items *</h3>
          </div>
          <div class="space-y-3">
            <div v-for="(item, index) in form.items" :key="index" class="flex gap-3 items-start p-4 bg-gray-50 rounded-lg border border-gray-200">
              <div class="flex-1 space-y-3">
                <div class="space-y-2">
                  <Label class="flex items-center gap-2 text-sm font-medium">
                    <Package class="h-3 w-3 text-gray-500" />
                    Item Name *
                  </Label>
                  <Input v-model="item.item_name" placeholder="Enter item name" required />
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div class="space-y-2">
                    <Label class="flex items-center gap-2 text-sm font-medium">
                      <Hash class="h-3 w-3 text-gray-500" />
                      Quantity *
                    </Label>
                    <Input v-model.number="item.quantity" type="number" step="0.01" min="0.01" placeholder="0.00" required class="flex-1" />
                  </div>
                  <div class="space-y-2">
                    <Label class="flex items-center gap-2 text-sm font-medium">
                      <Tag class="h-3 w-3 text-gray-500" />
                      Unit *
                    </Label>
                    <Input v-model="item.unit" placeholder="e.g., kg, pcs" required class="flex-1" />
                  </div>
                </div>
                <div class="space-y-2">
                  <Label class="flex items-center gap-2 text-sm font-medium">
                    <FileText class="h-3 w-3 text-gray-500" />
                    Specifications (Optional)
                  </Label>
                  <Input v-model="item.specifications" placeholder="Enter specifications" />
                </div>
              </div>
              <Button type="button" variant="ghost" size="sm" @click="removeItem(index)" class="text-red-600 hover:text-red-700 hover:bg-red-50 mt-1">
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>
            <Button type="button" variant="outline" @click="addItem" class="w-full">
              <Plus class="h-4 w-4 mr-2" />
              Add Item
            </Button>
          </div>
        </div>

        <!-- Additional Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <FileText class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Additional Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2 md:col-span-2">
              <Label for="notes" class="flex items-center gap-2">
                <FileText class="h-4 w-4 text-gray-500" />
                Notes
              </Label>
              <textarea
                id="notes"
                v-model="form.notes"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-[#8B4513]"
                placeholder="Enter additional notes or comments..."
              ></textarea>
            </div>
          </div>
        </div>
      </form>
      <template #footer>
        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="dialogOpen = false">
            Cancel
          </Button>
          <Button type="button" @click="saveRequest" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            {{ isEditing ? 'Update Request' : 'Create Request' }}
          </Button>
        </div>
      </template>
    </Dialog>

    <!-- View Request Dialog -->
    <Dialog v-model="viewDialogOpen" title="Procurement Request Details" class="max-w-3xl">
      <div class="space-y-6" v-if="selectedRequest">
        <!-- Request Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <ShoppingCart class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Request Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label class="flex items-center gap-2">
                <Hash class="h-4 w-4 text-gray-500" />
                Request Number
              </Label>
              <p class="text-sm font-medium">{{ selectedRequest.request_number }}</p>
            </div>
            <div class="space-y-2">
              <Label class="flex items-center gap-2">
                <Calendar class="h-4 w-4 text-gray-500" />
                Request Date
              </Label>
              <p class="text-sm">{{ formatDate(selectedRequest.request_date) }}</p>
            </div>
            <div class="space-y-2">
              <Label class="flex items-center gap-2">
                <Building2 class="h-4 w-4 text-gray-500" />
                Supplier
              </Label>
              <p class="text-sm">{{ selectedRequest.supplier?.name || 'N/A' }}</p>
            </div>
            <div class="space-y-2">
              <Label class="flex items-center gap-2">
                <User class="h-4 w-4 text-gray-500" />
                Requested By
              </Label>
              <p class="text-sm">{{ selectedRequest.requested_by?.name || 'N/A' }}</p>
            </div>
            <div class="space-y-2" v-if="selectedRequest.submitted_by || selectedRequest.submittedBy">
              <Label class="flex items-center gap-2">
                <User class="h-4 w-4 text-gray-500" />
                Submitted By
              </Label>
              <p class="text-sm">{{ selectedRequest.submittedBy?.name || selectedRequest.submitted_by?.name || 'N/A' }}</p>
            </div>
            <div class="space-y-2" v-if="selectedRequest.received_by || selectedRequest.receivedBy">
              <Label class="flex items-center gap-2">
                <User class="h-4 w-4 text-gray-500" />
                Received By
              </Label>
              <p class="text-sm">{{ selectedRequest.receivedBy?.name || selectedRequest.received_by?.name || 'N/A' }}</p>
            </div>
          </div>
        </div>

        <!-- Approval Information Section -->
        <div class="space-y-4" v-if="selectedRequest.approved_by || selectedRequest.approved_date">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Check class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Approval Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2" v-if="selectedRequest.approved_by">
              <Label class="flex items-center gap-2">
                <User class="h-4 w-4 text-gray-500" />
                Approved By
              </Label>
              <p class="text-sm">{{ selectedRequest.approved_by?.name || 'N/A' }}</p>
            </div>
            <div class="space-y-2" v-if="selectedRequest.approved_date">
              <Label class="flex items-center gap-2">
                <Calendar class="h-4 w-4 text-gray-500" />
                Approved Date
              </Label>
              <p class="text-sm">{{ formatDate(selectedRequest.approved_date) }}</p>
            </div>
            <div class="space-y-2">
              <Label class="flex items-center gap-2">
                <Tag class="h-4 w-4 text-gray-500" />
                Status
              </Label>
              <Badge :class="getStatusClass(selectedRequest.status)">
                {{ getStatusLabel(selectedRequest.status) }}
              </Badge>
            </div>
          </div>
        </div>
        <div class="space-y-4" v-else>
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Tag class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Status</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label class="flex items-center gap-2">
                <Tag class="h-4 w-4 text-gray-500" />
                Status
              </Label>
              <Badge :class="getStatusClass(selectedRequest.status)">
                {{ getStatusLabel(selectedRequest.status) }}
              </Badge>
            </div>
          </div>
        </div>

        <!-- Items Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Package class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Items</h3>
          </div>
          <div class="space-y-3">
            <div v-for="(item, index) in selectedRequest.items" :key="index" class="p-4 border border-gray-200 rounded-lg bg-gray-50">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <p class="font-medium text-gray-900 mb-2">{{ item.item_name }}</p>
                  <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                      <Hash class="h-3 w-3" />
                      Quantity: {{ item.quantity }} {{ item.unit }}
                    </div>
                    <div v-if="item.specifications" class="flex items-center gap-2">
                      <FileText class="h-3 w-3" />
                      {{ item.specifications }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes Section -->
        <div class="space-y-4" v-if="selectedRequest.notes">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <FileText class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Notes</h3>
          </div>
          <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
            <p class="text-sm text-gray-700">{{ selectedRequest.notes }}</p>
          </div>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end">
          <Button type="button" @click="viewDialogOpen = false" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            Close
          </Button>
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Plus, Search, Edit, Trash2, Eye, Check, X, ShoppingCart, Building2, Calendar, Package, FileText, Hash, Tag, User } from 'lucide-vue-next';
import apiClient from '@/api/client';
import { useAuthStore } from '@/stores/auth';
import Card from '@/components/ui/Card.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Button from '@/components/ui/Button.vue';
import Badge from '@/components/ui/Badge.vue';
import Dialog from '@/components/ui/Dialog.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Select from '@/components/ui/Select.vue';
import SelectItem from '@/components/ui/SelectItem.vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const authStore = useAuthStore();
const { toast } = useToast();
const { confirm } = useConfirm();
const requests = ref([]);
const suppliers = ref([]);
const users = ref([]);
const stats = ref({
  total: 0,
  pending: 0,
  approved: 0,
  completed: 0,
});
const dialogOpen = ref(false);
const viewDialogOpen = ref(false);
const isEditing = ref(false);
const searchQuery = ref('');
const selectedRequest = ref(null);

const form = ref({
  id: null,
  supplier_id: '',
  request_date: new Date().toISOString().split('T')[0],
  submitted_by: '',
  received_by: '',
  items: [
    { item_name: '', quantity: 0, unit: '', specifications: '' }
  ],
  notes: '',
});

const filteredRequests = computed(() => {
  if (!searchQuery.value) return requests.value;
  
  const query = searchQuery.value.toLowerCase();
  return requests.value.filter(request => {
    const requestNumber = (request.request_number || '').toLowerCase();
    const supplierName = (request.supplier?.name || '').toLowerCase();
    const requestedBy = (request.requested_by?.name || '').toLowerCase();
    
    return requestNumber.includes(query) || supplierName.includes(query) || requestedBy.includes(query);
  });
});

const canApprove = computed(() => {
  // Check if user has permission to approve (Admin or Operations role)
  return authStore.hasRole('Admin') || authStore.hasPermission('operations.approve');
});

const getStatusLabel = (status) => {
  const statusMap = {
    'pending': 'Pending',
    'approved': 'Approved',
    'rejected': 'Rejected',
    'completed': 'Completed',
  };
  return statusMap[status] || status;
};

const getStatusClass = (status) => {
  const classes = {
    'pending': 'bg-orange-100 text-orange-800',
    'approved': 'bg-green-100 text-green-800',
    'rejected': 'bg-red-100 text-red-800',
    'completed': 'bg-blue-100 text-blue-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toISOString().split('T')[0];
};

async function fetchRequests() {
  try {
    const response = await apiClient.get('/procurement-requests');
    requests.value = response.data || [];
    
    // Calculate statistics
    stats.value = {
      total: requests.value.length,
      pending: requests.value.filter(r => r.status === 'pending').length,
      approved: requests.value.filter(r => r.status === 'approved').length,
      completed: requests.value.filter(r => r.status === 'completed').length,
    };
  } catch (error) {
    console.error('Error fetching procurement requests:', error);
  }
}

async function fetchSuppliers() {
  try {
    const response = await apiClient.get('/suppliers');
    suppliers.value = response.data || [];
  } catch (error) {
    console.error('Error fetching suppliers:', error);
  }
}

async function fetchUsers() {
  try {
    const response = await apiClient.get('/users');
    users.value = response.data || [];
  } catch (error) {
    console.error('Error fetching users:', error);
  }
}

function openCreateDialog() {
  isEditing.value = false;
  resetForm();
  dialogOpen.value = true;
}

function editRequest(request) {
  isEditing.value = true;
  form.value = {
    id: request.id,
    supplier_id: request.supplier_id ? String(request.supplier_id) : '',
    request_date: request.request_date ? new Date(request.request_date).toISOString().split('T')[0] : new Date().toISOString().split('T')[0],
    submitted_by: request.submitted_by ? String(request.submitted_by) : (request.submittedBy?.id ? String(request.submittedBy.id) : ''),
    received_by: request.received_by ? String(request.received_by) : (request.receivedBy?.id ? String(request.receivedBy.id) : ''),
    items: request.items && request.items.length > 0 
      ? request.items.map(item => ({
          item_name: item.item_name || '',
          quantity: item.quantity || 0,
          unit: item.unit || '',
          specifications: item.specifications || '',
        }))
      : [{ item_name: '', quantity: 0, unit: '', specifications: '' }],
    notes: request.notes || '',
  };
  dialogOpen.value = true;
}

function viewRequest(request) {
  selectedRequest.value = request;
  viewDialogOpen.value = true;
}

function addItem() {
  form.value.items.push({ item_name: '', quantity: 0, unit: '', specifications: '' });
}

function removeItem(index) {
  if (form.value.items.length > 1) {
    form.value.items.splice(index, 1);
  }
}

function resetForm() {
  form.value = {
    id: null,
    supplier_id: '',
    request_date: new Date().toISOString().split('T')[0],
    submitted_by: '',
    received_by: '',
    items: [
      { item_name: '', quantity: 0, unit: '', specifications: '' }
    ],
    notes: '',
  };
  isEditing.value = false;
}

async function saveRequest() {
  try {
    // Validate items
    if (form.value.items.length === 0 || form.value.items.some(item => !item.item_name || !item.quantity || !item.unit)) {
      toast.warning('Please fill in all required item fields');
      return;
    }

    const payload = {
      supplier_id: form.value.supplier_id ? parseInt(form.value.supplier_id) : null,
      request_date: form.value.request_date,
      submitted_by: form.value.submitted_by ? parseInt(form.value.submitted_by) : null,
      received_by: form.value.received_by ? parseInt(form.value.received_by) : null,
      items: form.value.items.map(item => ({
        item_name: item.item_name,
        quantity: parseFloat(item.quantity),
        unit: item.unit,
        specifications: item.specifications || null,
      })),
      notes: form.value.notes || null,
    };

    if (isEditing.value) {
      await apiClient.put(`/procurement-requests/${form.value.id}`, payload);
    } else {
      await apiClient.post('/procurement-requests', payload);
    }

    await fetchRequests();
    dialogOpen.value = false;
    resetForm();
    toast.success(
      isEditing.value ? 'Procurement request updated successfully' : 'Procurement request created successfully'
    );
  } catch (error) {
    console.error('Error saving procurement request:', error);
    const errorMessage = error.response?.data?.message || error.response?.data?.errors
      ? JSON.stringify(error.response.data.errors || error.response.data.message)
      : 'Error saving procurement request';
    toast.error('Error saving procurement request', errorMessage);
  }
}

async function approveRequest(request) {
  const confirmed = await confirm(
    'Are you sure you want to approve this procurement request?',
    'Approve Request',
    'info'
  );
  
  if (!confirmed) return;

  try {
    await apiClient.post(`/procurement-requests/${request.id}/approve`);
    await fetchRequests();
    toast.success('Procurement request approved successfully');
  } catch (error) {
    console.error('Error approving request:', error);
    toast.error('Error approving request', error.response?.data?.message || 'Error approving request');
  }
}

async function rejectRequest(request) {
  const confirmed = await confirm(
    'Are you sure you want to reject this procurement request?',
    'Reject Request',
    'warning'
  );
  
  if (!confirmed) return;

  try {
    await apiClient.post(`/procurement-requests/${request.id}/reject`);
    await fetchRequests();
    toast.success('Procurement request rejected');
  } catch (error) {
    console.error('Error rejecting request:', error);
    toast.error('Error rejecting request', error.response?.data?.message || 'Error rejecting request');
  }
}

async function deleteRequest(request) {
  const confirmed = await confirm(
    'Are you sure you want to delete this procurement request?',
    'Delete Request',
    'danger'
  );
  
  if (!confirmed) return;

  try {
    await apiClient.delete(`/procurement-requests/${request.id}`);
    await fetchRequests();
    toast.success('Procurement request deleted successfully');
  } catch (error) {
    console.error('Error deleting request:', error);
    toast.error('Error deleting request', error.response?.data?.message || 'Error deleting request');
  }
}

onMounted(() => {
  fetchRequests();
  fetchSuppliers();
  fetchUsers();
});
</script>

