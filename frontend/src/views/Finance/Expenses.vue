<template>
  <div class="space-y-6">
    <ActionBar title="Expenses" description="Track and manage company expenses" @add-new="dialogOpen = true" />

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
            <DropdownMenu>
              <template #trigger>
                <Button variant="ghost" class="h-8 w-8 p-0">
                  <span class="sr-only">Open menu</span>
                  <MoreHorizontal class="h-4 w-4" />
                </Button>
              </template>
              <DropdownMenuItem @click="editExpense(row)">Edit</DropdownMenuItem>
              <DropdownMenuItem @click="deleteExpense(row.id)" class="text-destructive">Delete</DropdownMenuItem>
            </DropdownMenu>
          </template>
        </DataTable>
      </CardContent>
    </Card>

    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Expense' : 'Register New Expense'">
      <form @submit.prevent="saveExpense" class="space-y-4">
        <div class="space-y-2">
          <Label for="description">Description</Label>
          <Input id="description" v-model="form.description" required />
        </div>
        <div class="space-y-2">
          <Label for="amount">Amount</Label>
          <Input id="amount" v-model.number="form.amount" type="number" step="0.01" required />
        </div>
        <div class="space-y-2">
          <Label for="cost_center">Cost Center</Label>
          <Select v-model="form.cost_center">
            <SelectItem value="Production">Production</SelectItem>
            <SelectItem value="Administration">Administration</SelectItem>
            <SelectItem value="Sales">Sales</SelectItem>
            <SelectItem value="Logistics">Logistics</SelectItem>
            <SelectItem value="Maintenance">Maintenance</SelectItem>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="category">Category</Label>
          <Select v-model="form.category">
            <SelectItem value="Raw Materials">Raw Materials</SelectItem>
            <SelectItem value="Utilities">Utilities</SelectItem>
            <SelectItem value="Salaries">Salaries</SelectItem>
            <SelectItem value="Transportation">Transportation</SelectItem>
            <SelectItem value="Office Supplies">Office Supplies</SelectItem>
            <SelectItem value="Maintenance">Maintenance</SelectItem>
            <SelectItem value="Marketing">Marketing</SelectItem>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="expense_date">Expense Date</Label>
          <Input id="expense_date" v-model="form.expense_date" type="date" required />
        </div>
      </form>
      <template #footer>
        <Button type="button" variant="outline" @click="dialogOpen = false">Cancel</Button>
        <Button type="button" @click="saveExpense">Save</Button>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '@/api/client';
import { MoreHorizontal } from 'lucide-vue-next';
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
import DropdownMenu from '@/components/ui/DropdownMenu.vue';
import DropdownMenuItem from '@/components/ui/DropdownMenuItem.vue';

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
  try {
    if (isEditing.value) {
      await apiClient.put(`/expenses/${form.value.id}`, form.value);
    } else {
      await apiClient.post('/expenses', form.value);
    }
    await fetchExpenses();
    dialogOpen.value = false;
    resetForm();
  } catch (error) {
    console.error('Error saving expense:', error);
  }
}

async function deleteExpense(id) {
  if (confirm('Are you sure you want to delete this expense?')) {
    try {
      await apiClient.delete(`/expenses/${id}`);
      await fetchExpenses();
    } catch (error) {
      console.error('Error deleting expense:', error);
    }
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
