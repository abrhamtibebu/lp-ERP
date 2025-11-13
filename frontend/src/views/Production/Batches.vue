<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#8B4513]">Production</h1>
        <p class="text-gray-600 mt-1">Track active batch progress through production stages</p>
      </div>
      <Button @click="createNewBatch" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
        <Plus class="h-4 w-4 mr-2" />
        Create New Batch
      </Button>
    </div>
    
    <!-- Batches List -->
    <div class="space-y-6">
      <div v-for="batch in batches" :key="batch.id" class="bg-white rounded-lg border border-gray-200 p-6">
        <!-- Batch Header -->
        <div class="flex items-start justify-between mb-4 pb-4 border-b">
          <div class="flex-1">
            <h3 class="text-xl font-bold text-[#8B4513] mb-1">{{ batch.batch_id }}</h3>
            <p class="text-sm text-gray-600">
              {{ batch.order?.product?.product_name || 'N/A' }}
              <span v-if="batch.order?.product?.sku" class="text-gray-500">
                ({{ batch.order.product.sku }})
              </span>
            </p>
          </div>
          <div class="text-right">
            <div class="bg-amber-100 text-amber-800 px-3 py-1 rounded-md text-xs font-semibold mb-1">
              {{ getCurrentStageName(batch) }}
            </div>
            <div class="text-xs text-gray-600">{{ batch.total_quantity }} units</div>
          </div>
        </div>

        <!-- Overall Progress -->
        <div class="mb-6">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-[#8B4513]">Overall Progress</span>
            <span class="text-sm font-semibold text-gray-900">{{ batch.overall_progress || 0 }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div 
              class="bg-[#8B4513] h-2.5 rounded-full transition-all duration-300"
              :style="{ width: `${batch.overall_progress || 0}%` }"
            ></div>
          </div>
        </div>

        <!-- Production Stage Flow -->
        <div class="mb-6">
          <div class="flex items-center gap-2 overflow-x-auto pb-2">
            <div 
              v-for="stage in getDisplayStages(batch)" 
              :key="stage.id"
              class="flex items-center gap-2 flex-shrink-0"
            >
              <div 
                class="px-4 py-3 rounded-lg min-w-[120px] text-center transition-all"
                :class="getStageClass(stage, batch)"
              >
                <div class="font-semibold text-sm mb-1">{{ getStageDisplayName(stage.name) }}</div>
                <div class="text-xs font-medium">{{ stage.units_completed }}/{{ batch.total_quantity }}</div>
              </div>
              <ArrowRight v-if="stage !== getDisplayStages(batch)[getDisplayStages(batch).length - 1]" 
                class="h-5 w-5 text-gray-400 flex-shrink-0" />
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-3">
          <Button 
            variant="outline" 
            @click="viewDetails(batch.id)"
            class="border-gray-300 text-gray-700 hover:bg-gray-50"
          >
            View Details
          </Button>
          <Button 
            @click="updateStage(batch.id)"
            class="bg-[#8B4513] hover:bg-[#6B3410] text-white"
          >
            Update Stage
          </Button>
          <Button 
            variant="ghost" 
            @click="deleteBatch(batch.id)"
            :disabled="deleting[batch.id]"
            class="text-red-600 hover:text-red-700 hover:bg-red-50"
          >
            <Loader2 v-if="deleting[batch.id]" class="h-4 w-4 mr-2 animate-spin" />
            <Trash2 v-else class="h-4 w-4 mr-2" />
            {{ deleting[batch.id] ? 'Deleting...' : 'Delete' }}
          </Button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="batches.length === 0" class="bg-white rounded-lg border border-gray-200 p-12 text-center">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <p class="text-gray-500 text-lg">No active batches found</p>
        <p class="text-gray-400 text-sm mt-2">Create an order and generate a batch to start production</p>
      </div>
    </div>

    <!-- Update Stage Dialog -->
    <Dialog v-model="updateStageDialogOpen" title="Update Stage" description="Move units to the next production stage" class="max-w-3xl">
      <form @submit.prevent="submitStageUpdate" class="space-y-6">
        <!-- Stage Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <ArrowRightCircle class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Stage Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="space-y-2">
              <Label for="to_stage" class="flex items-center gap-2">
                <ArrowRightCircle class="h-4 w-4 text-gray-500" />
                To Stage *
              </Label>
              <Select v-model="stageUpdateForm.to_stage_id" placeholder="Select a stage">
            <SelectItem value="">Select a stage</SelectItem>
            <SelectItem 
              v-for="stage in availableStages" 
              :key="stage.id" 
              :value="String(stage.id)"
            >
              {{ stage.name }}
            </SelectItem>
          </Select>
        </div>
        <div class="space-y-2">
              <Label for="quantity" class="flex items-center gap-2">
                <Hash class="h-4 w-4 text-gray-500" />
                Quantity *
              </Label>
          <Input 
            id="quantity" 
            type="number" 
            v-model.number="stageUpdateForm.quantity" 
            :max="selectedBatch?.current_quantity"
            min="1"
            required 
                placeholder="Enter quantity"
          />
          <p class="text-xs text-gray-500">Available: {{ selectedBatch?.current_quantity || 0 }} units</p>
            </div>
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
                v-model="stageUpdateForm.notes"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-[#8B4513]"
                placeholder="Enter notes or comments..."
              ></textarea>
            </div>
          </div>
        </div>
      </form>
      <template #footer>
        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="updateStageDialogOpen = false">
            Cancel
          </Button>
        <Button type="button" @click="submitStageUpdate" :disabled="updating" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
          <Loader2 v-if="updating" class="h-4 w-4 mr-2 animate-spin" />
          {{ updating ? 'Updating...' : 'Update Stage' }}
        </Button>
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Plus, ArrowRight, Package, ArrowRightCircle, FileText, Hash, Trash2, Loader2 } from 'lucide-vue-next';
import apiClient from '@/api/client';
import Button from '@/components/ui/Button.vue';
import Dialog from '@/components/ui/Dialog.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Select from '@/components/ui/Select.vue';
import SelectItem from '@/components/ui/SelectItem.vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const router = useRouter();
const { toast } = useToast();
const { confirm } = useConfirm();

const batches = ref([]);
const updateStageDialogOpen = ref(false);
const selectedBatch = ref(null);
const availableStages = ref([]);
const deleting = ref({});
const updating = ref(false);
const stageUpdateForm = ref({
  to_stage_id: '',
  quantity: 1,
  notes: '',
});

// Display stages in order (main production flow)
const displayStageOrder = ['Cutting', 'Schiving', 'Initial Stitching', 'Final Assembly', 'Quality Inspection'];

const getDisplayStages = (batch) => {
  if (!batch.stage_progress) return [];
  
  const currentStageId = batch.current_stage_id;
  
  // Filter and sort stages by display order
  const stages = batch.stage_progress
    .filter(stage => displayStageOrder.includes(stage.name))
    .sort((a, b) => {
      const indexA = displayStageOrder.indexOf(a.name);
      const indexB = displayStageOrder.indexOf(b.name);
      return indexA - indexB;
    })
    .map(stage => {
      // For the current stage, include WIP units in the count
      // For other stages, show only completed units
      let units_completed = stage.completed_units || 0;
      if (stage.id === currentStageId) {
        // Include WIP units for current stage
        units_completed = Math.min(
          (stage.completed_units || 0) + (stage.wip_units || 0),
          batch.total_quantity
        );
      }
      
      return {
        ...stage,
        units_completed: units_completed
      };
    });
  
  return stages;
};

const getStageDisplayName = (name) => {
  const displayNames = {
    'Cutting': 'Cutting',
    'Schiving': 'Schiving',
    'Initial Stitching': 'Stitching',
    'Final Assembly': 'Assembly',
    'Quality Inspection': 'QA',
  };
  return displayNames[name] || name;
};

const getStageClass = (stage, batch) => {
  const total = batch.total_quantity;
  const completed = stage.units_completed || 0;
  
  if (completed >= total) {
    // Completed - Green
    return 'bg-green-500 text-white';
  } else if (completed > 0) {
    // In Progress - Orange
    return 'bg-orange-500 text-white';
  } else {
    // Pending - Grey
    return 'bg-gray-200 text-gray-700';
  }
};

const getCurrentStageName = (batch) => {
  if (!batch.currentStage) return 'N/A';
  
  const displayNames = {
    'Cutting': 'Cutting',
    'Schiving': 'Schiving',
    'Initial Stitching': 'Stitching',
    'Final Assembly': 'Assembly',
    'Quality Inspection': 'Quality Inspection',
  };
  
  return displayNames[batch.currentStage.name] || batch.currentStage.name;
};

const createNewBatch = () => {
  // Redirect to orders page where users can create batches from pending orders
  router.push('/production/orders');
  toast.info('Select a pending order and click "Create Batch" to start production');
};

const viewDetails = (batchId) => {
  router.push(`/production/batches/${batchId}`);
};

const updateStage = async (batchId) => {
  selectedBatch.value = batches.value.find(b => b.id === batchId);
  
  // Load available stages
  try {
    const response = await apiClient.get('/production-stages');
    availableStages.value = response.data || [];
  } catch (error) {
    console.error('Error loading stages:', error);
    // Fallback: use default stages
    availableStages.value = [
      { id: 1, name: 'Cutting' },
      { id: 2, name: 'Schiving' },
      { id: 3, name: 'Initial Stitching' },
      { id: 4, name: 'Final Assembly' },
      { id: 5, name: 'Binding' },
      { id: 6, name: 'Polishing & Painting' },
      { id: 7, name: 'Quality Inspection' },
    ];
  }
  
  stageUpdateForm.value = {
    to_stage_id: '',
    quantity: 1,
    notes: '',
  };
  
  updateStageDialogOpen.value = true;
};

const submitStageUpdate = async () => {
  if (!selectedBatch.value || !stageUpdateForm.value.to_stage_id) {
    toast.warning('Please select a stage');
    return;
  }
  
  try {
    updating.value = true;
    await apiClient.post(`/batches/${selectedBatch.value.id}/move-stage`, {
      to_stage_id: parseInt(stageUpdateForm.value.to_stage_id),
      quantity: stageUpdateForm.value.quantity,
      notes: stageUpdateForm.value.notes,
    });
    
    updateStageDialogOpen.value = false;
    await loadBatches();
    toast.success('Batch stage updated successfully');
  } catch (error) {
    console.error('Error updating stage:', error);
    toast.error('Error updating stage', error.response?.data?.message || 'Error updating stage');
  } finally {
    updating.value = false;
  }
};

const loadBatches = async () => {
  try {
    // Load only active/opened batches (excludes completed)
    const response = await apiClient.get('/batches');
    batches.value = response.data || [];
  } catch (error) {
    console.error('Error fetching batches:', error);
    toast.error('Error loading batches', error.response?.data?.message || 'Failed to load batches');
  }
};

async function deleteBatch(id) {
  const confirmed = await confirm({
    title: 'Delete Batch',
    message: 'Are you sure you want to delete this batch? This action cannot be undone and will affect production tracking.',
    type: 'danger'
  });

  if (!confirmed) return;

  try {
    deleting.value[id] = true;
    await apiClient.delete(`/batches/${id}`);
    await loadBatches();
    toast.success('Batch deleted successfully');
  } catch (error) {
    console.error('Error deleting batch:', error);
    const errorMessage = error.response?.data?.message || 'Error deleting batch';
    toast.error('Error deleting batch', errorMessage);
  } finally {
    deleting.value[id] = false;
  }
}

onMounted(() => {
  loadBatches();
});
</script>
