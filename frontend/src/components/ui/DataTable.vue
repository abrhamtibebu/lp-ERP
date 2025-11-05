<template>
  <div class="space-y-4">
    <!-- Search and Actions Bar -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <Input
          v-model="searchQuery"
          placeholder="Search..."
          class="h-9 w-[250px]"
        />
        <Button
          v-if="selectedRows.length > 0"
          variant="outline"
          size="sm"
          @click="handleBulkAction"
        >
          Bulk Actions ({{ selectedRows.length }})
        </Button>
      </div>
      <div class="flex items-center space-x-2">
        <slot name="actions" />
      </div>
    </div>

    <!-- Table -->
    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead v-if="selectable" class="w-12">
              <input
                type="checkbox"
                :checked="allSelected"
                @change="toggleSelectAll"
                class="rounded border-gray-300"
              />
            </TableHead>
            <TableHead
              v-for="column in columns"
              :key="column.key"
              :class="[
                column.class,
                { 'cursor-pointer hover:bg-muted/50': column.sortable }
              ]"
              @click="column.sortable ? handleSort(column.key) : null"
            >
              <div class="flex items-center space-x-2">
                <span>{{ column.label }}</span>
                <span v-if="column.sortable && sortKey === column.key" class="text-xs">
                  {{ sortOrder === 'asc' ? '↑' : '↓' }}
                </span>
              </div>
            </TableHead>
            <TableHead v-if="actions" class="text-right">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow
            v-for="row in paginatedData"
            :key="row.id"
            :data-state="selectedRows.includes(row.id) ? 'selected' : undefined"
          >
            <TableCell v-if="selectable">
              <input
                type="checkbox"
                :checked="selectedRows.includes(row.id)"
                @change="toggleRow(row.id)"
                class="rounded border-gray-300"
              />
            </TableCell>
            <TableCell
              v-for="column in columns"
              :key="column.key"
              :class="column.class"
            >
              <slot
                :name="`cell-${column.key}`"
                :row="row"
                :value="row[column.key]"
              >
                {{ formatValue(row[column.key], column) }}
              </slot>
            </TableCell>
            <TableCell v-if="actions" class="text-right">
              <slot name="rowActions" :row="row" />
            </TableCell>
          </TableRow>
          <TableRow v-if="paginatedData.length === 0">
            <TableCell :colspan="columns.length + (selectable ? 1 : 0) + (actions ? 1 : 0)" class="h-24 text-center">
              No results found.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="flex items-center justify-between">
      <div class="text-sm text-muted-foreground">
        Showing {{ startIndex + 1 }} to {{ endIndex }} of {{ filteredData.length }} results
      </div>
      <div class="flex items-center space-x-2">
        <Button
          variant="outline"
          size="sm"
          @click="currentPage = Math.max(1, currentPage - 1)"
          :disabled="currentPage === 1"
        >
          Previous
        </Button>
        <span class="text-sm">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <Button
          variant="outline"
          size="sm"
          @click="currentPage = Math.min(totalPages, currentPage + 1)"
          :disabled="currentPage === totalPages"
        >
          Next
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import Input from './Input.vue';
import Button from './Button.vue';
import Table from './Table.vue';
import TableBody from './TableBody.vue';
import TableCell from './TableCell.vue';
import TableHead from './TableHead.vue';
import TableHeader from './TableHeader.vue';
import TableRow from './TableRow.vue';

const props = defineProps({
  data: {
    type: Array,
    required: true,
  },
  columns: {
    type: Array,
    required: true,
  },
  selectable: {
    type: Boolean,
    default: false,
  },
  actions: {
    type: Boolean,
    default: true,
  },
  pagination: {
    type: Boolean,
    default: true,
  },
  pageSize: {
    type: Number,
    default: 10,
  },
});

const emit = defineEmits(['bulk-action', 'row-click']);

const searchQuery = ref('');
const selectedRows = ref([]);
const sortKey = ref(null);
const sortOrder = ref('asc');
const currentPage = ref(1);

const filteredData = computed(() => {
  let result = [...props.data];

  // Search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter((row) =>
      props.columns.some((col) => {
        const value = row[col.key];
        return value?.toString().toLowerCase().includes(query);
      })
    );
  }

  // Sort
  if (sortKey.value) {
    result.sort((a, b) => {
      const aVal = a[sortKey.value];
      const bVal = b[sortKey.value];
      const comparison = aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
      return sortOrder.value === 'asc' ? comparison : -comparison;
    });
  }

  return result;
});

const paginatedData = computed(() => {
  if (!props.pagination) return filteredData.value;
  const start = (currentPage.value - 1) * props.pageSize;
  const end = start + props.pageSize;
  return filteredData.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(filteredData.value.length / props.pageSize));
const startIndex = computed(() => (currentPage.value - 1) * props.pageSize);
const endIndex = computed(() => Math.min(startIndex.value + props.pageSize, filteredData.value.length));

const allSelected = computed(() => {
  return paginatedData.value.length > 0 && paginatedData.value.every((row) => selectedRows.value.includes(row.id));
});

const handleSort = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortOrder.value = 'asc';
  }
  currentPage.value = 1;
};

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedRows.value = selectedRows.value.filter(
      (id) => !paginatedData.value.some((row) => row.id === id)
    );
  } else {
    paginatedData.value.forEach((row) => {
      if (!selectedRows.value.includes(row.id)) {
        selectedRows.value.push(row.id);
      }
    });
  }
};

const toggleRow = (id) => {
  const index = selectedRows.value.indexOf(id);
  if (index > -1) {
    selectedRows.value.splice(index, 1);
  } else {
    selectedRows.value.push(id);
  }
};

const handleBulkAction = () => {
  emit('bulk-action', selectedRows.value);
};

const formatValue = (value, column) => {
  if (column.format) {
    return column.format(value);
  }
  return value ?? '-';
};

watch(() => props.data, () => {
  currentPage.value = 1;
  selectedRows.value = [];
}, { immediate: false });

watch(searchQuery, () => {
  currentPage.value = 1;
}, { immediate: false });
</script>

