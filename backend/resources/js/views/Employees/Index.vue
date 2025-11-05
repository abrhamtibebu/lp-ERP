<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold text-gray-800">Employees</h2>
      <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
        Add Employee
      </button>
    </div>
    
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="employee in employees" :key="employee.id">
            <td class="px-6 py-4 whitespace-nowrap">{{ employee.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ employee.email }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ employee.department }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ employee.position }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button class="text-indigo-600 hover:text-indigo-900">Edit</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '../../api/client';

const employees = ref([]);

onMounted(async () => {
  try {
    const response = await apiClient.get('/employees');
    employees.value = response.data;
  } catch (error) {
    console.error('Error fetching employees:', error);
  }
});
</script>

