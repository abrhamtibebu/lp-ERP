<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#8B4513]">Expenses</h1>
        <p class="text-gray-600 mt-1">Track and manage company expenses</p>
      </div>
      <Button @click="dialogOpen = true" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
        <Plus class="h-4 w-4 mr-2" />
        Add Expense
      </Button>
    </div>

    <Card>
      <CardContent class="p-0">
        <DataTable :data="expenses" :columns="columns">
          <template #cell-description="{ row }">
            <span class="font-medium">{{ row.description }}</span>
          </template>
          <template #cell-amount="{ row }">
            <span class="font-semibold text-green-600">${{ parseFloat(row.amount).toLocaleString() }}</span>
          </template>
          <template #cell-cost_center="{ row }">
            <Badge variant="secondary">{{ row.cost_center }}</Badge>
          </template>
          <template #cell-category="{ row }">
            <Badge variant="outline">{{ row.category }}</Badge>
          </template>
          <template #cell-expense_date="{ row }">
            <span>{{ new Date(row.expense_date).toLocaleDateString() }}</span>
          </template>
          <template #rowActions="{ row }">
            <div class="flex gap-2">
              <Button variant="ghost" size="sm" @click="editExpense(row)">
                <Edit class="h-4 w-4" />
              </Button>
              <Button 
                variant="ghost" 
                size="sm" 
                @click="deleteExpense(row.id)" 
                :disabled="deleting[row.id]"
                class="text-red-600 hover:text-red-700"
              >
                <Loader2 v-if="deleting[row.id]" class="h-4 w-4 animate-spin" />
                <Trash2 v-else class="h-4 w-4" />
              </Button>
            </div>
          </template>
        </DataTable>
      </CardContent>
    </Card>

    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Expense' : 'Register New Expense'" class="max-w-3xl">
      <form @submit.prevent="saveExpense" class="space-y-6">
        <!-- Expense Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <DollarSign class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Expense Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2 md:col-span-2">
              <Label for="description" class="flex items-center gap-2">
                <FileText class="h-4 w-4 text-gray-500" />
                Description *
              </Label>
              <Input id="description" v-model="form.description" placeholder="Enter expense description" required />
            </div>
            <div class="space-y-2">
              <Label for="amount" class="flex items-center gap-2">
                <DollarSign class="h-4 w-4 text-gray-500" />
                Amount ($) *
              </Label>
              <Input id="amount" v-model.number="form.amount" type="number" step="0.01" required placeholder="0.00" min="0" />
            </div>
            <div class="space-y-2">
              <Label for="expense_date" class="flex items-center gap-2">
                <Calendar class="h-4 w-4 text-gray-500" />
                Expense Date *
              </Label>
              <Input id="expense_date" v-model="form.expense_date" type="date" required />
            </div>
          </div>
        </div>

        <!-- Classification Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Tag class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Classification</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="cost_center" class="flex items-center gap-2">
                <Building2 class="h-4 w-4 text-gray-500" />
                Cost Center *
              </Label>
              <Select v-model="form.cost_center" placeholder="Select cost center">
                <SelectItem value="">Select cost center</SelectItem>
                <SelectItem value="Production">Production</SelectItem>
                <SelectItem value="Administration">Administration</SelectItem>
                <SelectItem value="Sales">Sales</SelectItem>
                <SelectItem value="Logistics">Logistics</SelectItem>
                <SelectItem value="Maintenance">Maintenance</SelectItem>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="category" class="flex items-center gap-2">
                <Tag class="h-4 w-4 text-gray-500" />
                Category *
              </Label>
              <Select v-model="form.category" placeholder="Select category">
                <SelectItem value="">Select category</SelectItem>
                <SelectItem value="Raw Materials">Raw Materials</SelectItem>
                <SelectItem value="Utilities">Utilities</SelectItem>
                <SelectItem value="Salaries">Salaries</SelectItem>
                <SelectItem value="Transportation">Transportation</SelectItem>
                <SelectItem value="Office Supplies">Office Supplies</SelectItem>
                <SelectItem value="Maintenance">Maintenance</SelectItem>
                <SelectItem value="Marketing">Marketing</SelectItem>
              </Select>
            </div>
          </div>
        </div>
      </form>
      <template #footer>
        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="dialogOpen = false">
            Cancel
          </Button>
          <Button type="button" @click="saveExpense" :disabled="saving" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            <Loader2 v-if="saving" class="h-4 w-4 mr-2 animate-spin" />
            {{ saving ? (isEditing ? 'Updating...' : 'Registering...') : (isEditing ? 'Update Expense' : 'Register Expense') }}
          </Button>
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '@/api/client';
import { Edit, Trash2, Plus, DollarSign, FileText, Tag, Building2, Calendar, Loader2 } from 'lucide-vue-next';
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

const { toast } = useToast();
const { confirm } = useConfirm();

const saving = ref(false);
const deleting = ref({});

const expenses = ref([]);
const dialogOpen = ref(false);
const isEditing = ref(false);
const form = ref({
  id: null,
  description: '',
  amount: 0,
  cost_center: '',
  category: '',
  expense_date: new Date().toISOString().split('T')[0],
});

const columns = [
  { key: 'description', label: 'Description', sortable: true },
  { key: 'amount', label: 'Amount', sortable: true },
  { key: 'cost_center', label: 'Cost Center', sortable: true },
  { key: 'category', label: 'Category', sortable: true },
  { key: 'expense_date', label: 'Date', sortable: true },
];

async function fetchExpenses() {
  try {
    const response = await apiClient.get('/expenses');
    expenses.value = response.data;
  } catch (error) {
    console.error('Error fetching expenses:', error);
  }
}

function editExpense(expense) {
  isEditing.value = true;
  form.value = {
    id: expense.id,
    description: expense.description,
    amount: expense.amount,
    cost_center: expense.cost_center,
    category: expense.category,
    expense_date: new Date(expense.expense_date).toISOString().split('T')[0],
  };
  dialogOpen.value = true;
}

async function saveExpense() {
  // Validation
  if (!form.value.description || form.value.description.trim() === '') {
    toast.warning('Please enter expense description');
    return;
  }
  
  if (!form.value.amount || form.value.amount <= 0) {
    toast.warning('Please enter a valid amount (greater than 0)');
    return;
  }
  
  if (!form.value.cost_center || form.value.cost_center.trim() === '') {
    toast.warning('Please select cost center');
    return;
  }
  
  if (!form.value.category || form.value.category.trim() === '') {
    toast.warning('Please select category');
    return;
  }
  
  if (!form.value.expense_date) {
    toast.warning('Please enter expense date');
    return;
  }

  try {
    saving.value = true;
    const payload = {
      ...form.value,
      amount: parseFloat(form.value.amount),
    };
    
    if (isEditing.value) {
      await apiClient.put(`/expenses/${form.value.id}`, payload);
    } else {
      await apiClient.post('/expenses', payload);
    }
    await fetchExpenses();
    dialogOpen.value = false;
    resetForm();
    toast.success(isEditing.value ? 'Expense updated successfully' : 'Expense created successfully');
  } catch (error) {
    console.error('Error saving expense:', error);
    const errorMessage = error.response?.data?.message || error.response?.data?.errors
      ? (error.response.data.errors ? JSON.stringify(error.response.data.errors) : error.response.data.message)
      : 'Error saving expense';
    toast.error('Error saving expense', errorMessage);
  } finally {
    saving.value = false;
  }
}

async function deleteExpense(id) {
  const confirmed = await confirm(
    'Are you sure you want to delete this expense?',
    'Delete Expense',
    'danger'
  );
  
  if (!confirmed) return;

  try {
    deleting.value[id] = true;
    await apiClient.delete(`/expenses/${id}`);
    await fetchExpenses();
    toast.success('Expense deleted successfully');
  } catch (error) {
    console.error('Error deleting expense:', error);
    toast.error('Error deleting expense', error.response?.data?.message || 'Error deleting expense');
  } finally {
    deleting.value[id] = false;
  }
}

function resetForm() {
  form.value = {
    id: null,
    description: '',
    amount: 0,
    cost_center: '',
    category: '',
    expense_date: new Date().toISOString().split('T')[0],
  };
  isEditing.value = false;
}

onMounted(fetchExpenses);
</script>
