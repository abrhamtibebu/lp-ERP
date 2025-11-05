<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-800">Batch Details</h2>
        <p class="text-sm text-gray-600 mt-1">{{ batch?.batch_id || 'Loading...' }}</p>
      </div>
      <Button variant="outline" @click="$router.back()">Back</Button>
    </div>

    <div v-if="loading" class="text-center py-12">
      <p class="text-gray-500">Loading batch details...</p>
    </div>

    <div v-else-if="batch" class="space-y-6">
      <!-- Batch Info Card -->
      <Card>
        <CardHeader>
          <CardTitle>Batch Information</CardTitle>
        </CardHeader>
        <CardContent class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div>
            <p class="text-sm text-gray-600">Product</p>
            <p class="font-semibold">{{ batch.order?.product?.product_name || 'N/A' }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Current Stage</p>
            <p class="font-semibold">{{ batch.currentStage?.name || 'N/A' }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Quantity</p>
            <p class="font-semibold">{{ batch.current_quantity }} / {{ batch.total_quantity }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Status</p>
            <Badge :variant="getStatusVariant(batch.status)">{{ batch.status }}</Badge>
          </div>
        </CardContent>
      </Card>

      <!-- Stage Movement -->
      <Card>
        <CardHeader>
          <CardTitle>Move to Stage</CardTitle>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="moveToStage" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="space-y-2">
                <Label for="to_stage">To Stage</Label>
                <Select v-model="movementForm.to_stage_id" required>
                  <SelectItem value="">Select Stage</SelectItem>
                  <SelectItem v-for="stage in productionStages" :key="stage.id" :value="stage.id.toString()">
                    {{ stage.name }}
                  </SelectItem>
                </Select>
              </div>
              <div class="space-y-2">
                <Label for="quantity">Quantity</Label>
                <Input id="quantity" v-model.number="movementForm.quantity" type="number" min="1" :max="batch.current_quantity" required />
              </div>
              <div class="space-y-2">
                <Label for="notes">Notes (Optional)</Label>
                <Input id="notes" v-model="movementForm.notes" />
              </div>
            </div>
            <Button type="submit" :disabled="moving">Move Batch</Button>
          </form>
        </CardContent>
      </Card>

      <!-- Stage Movements History -->
      <Card>
        <CardHeader>
          <CardTitle>Stage Movement History</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>From Stage</TableHead>
                <TableHead>To Stage</TableHead>
                <TableHead>Quantity</TableHead>
                <TableHead>Supervisor</TableHead>
                <TableHead>Date</TableHead>
                <TableHead>Notes</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="movement in batch.stageMovements || []" :key="movement.id">
                <TableCell>{{ movement.fromStage?.name || 'N/A' }}</TableCell>
                <TableCell>{{ movement.toStage?.name || 'N/A' }}</TableCell>
                <TableCell>{{ movement.quantity }}</TableCell>
                <TableCell>{{ movement.supervisor?.name || 'N/A' }}</TableCell>
                <TableCell>{{ formatDate(movement.created_at) }}</TableCell>
                <TableCell>{{ movement.notes || '-' }}</TableCell>
              </TableRow>
              <TableRow v-if="!batch.stageMovements || batch.stageMovements.length === 0">
                <TableCell colspan="6" class="text-center text-gray-500 py-8">
                  No stage movements recorded
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <!-- WIP Inventory -->
      <Card v-if="batch.wipInventories && batch.wipInventories.length > 0">
        <CardHeader>
          <CardTitle>Work in Progress Inventory</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Stage</TableHead>
                <TableHead>Quantity</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="wip in batch.wipInventories" :key="wip.id">
                <TableCell>{{ wip.stage?.name || 'N/A' }}</TableCell>
                <TableCell>{{ wip.quantity }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import apiClient from '@/api/client';
import Card from '@/components/ui/Card.vue';
import CardHeader from '@/components/ui/CardHeader.vue';
import CardTitle from '@/components/ui/CardTitle.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Button from '@/components/ui/Button.vue';
import Badge from '@/components/ui/Badge.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Select from '@/components/ui/Select.vue';
import SelectItem from '@/components/ui/SelectItem.vue';
import Table from '@/components/ui/Table.vue';
import TableHeader from '@/components/ui/TableHeader.vue';
import TableBody from '@/components/ui/TableBody.vue';
import TableRow from '@/components/ui/TableRow.vue';
import TableHead from '@/components/ui/TableHead.vue';
import TableCell from '@/components/ui/TableCell.vue';

const route = useRoute();
const batch = ref(null);
const productionStages = ref([]);
const loading = ref(true);
const moving = ref(false);

const movementForm = ref({
  to_stage_id: '',
  quantity: 1,
  notes: '',
});

const getStatusVariant = (status) => {
  const variants = {
    pending: 'secondary',
    in_progress: 'default',
    completed: 'default',
    on_hold: 'destructive',
  };
  return variants[status] || 'secondary';
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString();
};

async function fetchBatch() {
  try {
    loading.value = true;
    const response = await apiClient.get(`/batches/${route.params.id}`);
    batch.value = response.data;
  } catch (error) {
    console.error('Error fetching batch:', error);
    alert('Failed to load batch details');
  } finally {
    loading.value = false;
  }
}

async function fetchProductionStages() {
  try {
    const response = await apiClient.get('/production-stages');
    productionStages.value = response.data || [];
  } catch (error) {
    console.error('Error fetching production stages:', error);
  }
}

async function moveToStage() {
  if (!movementForm.value.to_stage_id || !movementForm.value.quantity) {
    alert('Please fill in all required fields');
    return;
  }

  try {
    moving.value = true;
    await apiClient.post(`/batches/${route.params.id}/move-stage`, {
      to_stage_id: parseInt(movementForm.value.to_stage_id),
      quantity: movementForm.value.quantity,
      notes: movementForm.value.notes,
    });
    alert('Batch moved successfully!');
    movementForm.value = { to_stage_id: '', quantity: 1, notes: '' };
    await fetchBatch(); // Refresh batch data
  } catch (error) {
    console.error('Error moving batch:', error);
    alert(error.response?.data?.message || 'Failed to move batch');
  } finally {
    moving.value = false;
  }
}

onMounted(() => {
  fetchBatch();
  fetchProductionStages();
});
</script>
