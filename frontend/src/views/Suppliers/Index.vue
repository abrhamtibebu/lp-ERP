<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#8B4513]">Suppliers</h1>
        <p class="text-gray-600 mt-1">Manage your supplier network</p>
      </div>
      <Button @click="openAddDialog" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
        <Plus class="h-4 w-4 mr-2" />
        Add Supplier
      </Button>
    </div>

    <Card>
      <CardContent class="p-0">
        <DataTable :data="suppliers" :columns="columns">
          <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-[#8B4513] to-[#6B3410] rounded-lg flex items-center justify-center text-white font-semibold">
                {{ row.name.charAt(0).toUpperCase() }}
              </div>
              <span class="font-medium">{{ row.name }}</span>
            </div>
          </template>
          <template #cell-products_supplied="{ row }">
            <Badge variant="secondary">{{ row.products_supplied || 'N/A' }}</Badge>
          </template>
          <template #rowActions="{ row }">
            <div class="flex gap-2">
              <Button variant="ghost" size="sm" @click="editSupplier(row)">
                <Edit class="h-4 w-4" />
              </Button>
              <Button 
                variant="ghost" 
                size="sm" 
                @click="deleteSupplier(row.id)" 
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

    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Supplier' : 'Add New Supplier'" class="max-w-3xl">
      <form @submit.prevent="saveSupplier" class="space-y-6">
        <!-- Business Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Building2 class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Business Information</h3>
          </div>
          <div class="space-y-2">
            <Label for="tin_number" class="flex items-center gap-2">
              <FileText class="h-4 w-4 text-gray-500" />
              TIN Number
            </Label>
            <div class="flex gap-2">
              <div class="relative flex-1">
                <Input 
                  id="tin_number" 
                  v-model="form.tin_number" 
                  placeholder="Enter TIN number"
                  :disabled="isEditing"
                  class="pr-2"
                />
              </div>
              <Button
                v-if="!isEditing"
                type="button"
                @click="handleFetchBusinessInfo"
                :disabled="!form.tin_number || form.tin_number.trim() === '' || isFetchingBusinessInfo"
                class="bg-[#8B4513] hover:bg-[#6B3410] text-white whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <template v-if="isFetchingBusinessInfo">
                  <svg 
                    class="animate-spin h-4 w-4 mr-2 inline" 
                    xmlns="http://www.w3.org/2000/svg" 
                    fill="none" 
                    viewBox="0 0 24 24"
                  >
                    <circle 
                      class="opacity-25" 
                      cx="12" 
                      cy="12" 
                      r="10" 
                      stroke="currentColor" 
                      stroke-width="4"
                    ></circle>
                    <path 
                      class="opacity-75" 
                      fill="currentColor" 
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                  </svg>
                  Fetching...
                </template>
                <template v-else-if="fetchError">
                  Retry
                </template>
                <template v-else>
                  <Search class="h-4 w-4 mr-2 inline" />
                  Fetch Business Info
                </template>
              </Button>
            </div>
            <p v-if="isFetchingBusinessInfo" class="text-xs text-gray-500">
              Fetching business information from Ethiopian Trade Bureau...
            </p>
            <p v-else-if="fetchError" class="text-xs text-red-500">
              {{ fetchError }}
            </p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="name" class="flex items-center gap-2">
                <Building2 class="h-4 w-4 text-gray-500" />
                Business Name *
              </Label>
              <Input id="name" v-model="form.name" placeholder="Enter business name" required />
            </div>
            <div class="space-y-2">
              <Label for="business_number" class="flex items-center gap-2">
                <FileText class="h-4 w-4 text-gray-500" />
                Business Number
              </Label>
              <Input id="business_number" v-model="form.business_number" placeholder="Enter business registration number" />
            </div>
          </div>
        </div>

        <!-- Contact Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Phone class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Contact Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="phone_number" class="flex items-center gap-2">
                <Phone class="h-4 w-4 text-gray-500" />
                Phone Number
              </Label>
              <Input id="phone_number" v-model="form.phone_number" placeholder="Enter phone number" />
            </div>
            <div class="space-y-2">
              <Label for="contact_info" class="flex items-center gap-2">
                <User class="h-4 w-4 text-gray-500" />
                Contact Person
              </Label>
              <Input id="contact_info" v-model="form.contact_info" placeholder="Enter contact person name" />
            </div>
          </div>
        </div>

        <!-- Location Details Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <MapPin class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Location Details</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2 md:col-span-2">
              <Label for="address" class="flex items-center gap-2">
                <MapPin class="h-4 w-4 text-gray-500" />
                Address
              </Label>
              <Input id="address" v-model="form.address" placeholder="Enter full address" />
            </div>
            <div class="space-y-2">
              <Label for="woreda" class="flex items-center gap-2">
                <MapPin class="h-4 w-4 text-gray-500" />
                Woreda
              </Label>
              <Input id="woreda" v-model="form.woreda" placeholder="Enter woreda" />
            </div>
            <div class="space-y-2">
              <Label for="house_number" class="flex items-center gap-2">
                <MapPin class="h-4 w-4 text-gray-500" />
                House Number
              </Label>
              <Input id="house_number" v-model="form.house_number" placeholder="Enter house number" />
            </div>
          </div>
        </div>

        <!-- Products Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Package class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Products</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2 md:col-span-2">
              <Label for="products_supplied" class="flex items-center gap-2">
                <Package class="h-4 w-4 text-gray-500" />
                Products Supplied
              </Label>
              <Input id="products_supplied" v-model="form.products_supplied" placeholder="Enter products or services supplied" />
            </div>
          </div>
        </div>
      </form>
      <template #footer>
        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="dialogOpen = false">
            Cancel
          </Button>
          <Button type="button" @click="saveSupplier" :disabled="saving" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
            <Loader2 v-if="saving" class="h-4 w-4 mr-2 animate-spin" />
            {{ saving ? (isEditing ? 'Updating...' : 'Creating...') : (isEditing ? 'Update Supplier' : 'Create Supplier') }}
          </Button>
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '@/api/client';
import { Edit, Trash2, Plus, Building2, FileText, MapPin, Phone, Package, User, Search, Loader2 } from 'lucide-vue-next';
import Card from '@/components/ui/Card.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Button from '@/components/ui/Button.vue';
import DataTable from '@/components/ui/DataTable.vue';
import Dialog from '@/components/ui/Dialog.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Badge from '@/components/ui/Badge.vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const { toast } = useToast();
const { confirm } = useConfirm();

const suppliers = ref([]);
const dialogOpen = ref(false);
const isEditing = ref(false);
const isFetchingBusinessInfo = ref(false);
const fetchError = ref(null); // Track fetch errors to show "Retry" button
const saving = ref(false);
const deleting = ref({});
const form = ref({
  id: null,
  name: '',
  tin_number: '',
  business_number: '',
  address: '',
  woreda: '',
  house_number: '',
  phone_number: '',
  products_supplied: '',
  contact_info: '',
});

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'tin_number', label: 'TIN Number', sortable: true },
  { key: 'products_supplied', label: 'Products Supplied', sortable: true },
  { key: 'contact_info', label: 'Contact Info', sortable: false },
];

async function fetchSuppliers() {
  try {
    const response = await apiClient.get('/suppliers');
    // Ensure we always have an array, even if the response structure is different
    suppliers.value = Array.isArray(response.data) ? response.data : [];
    
    if (suppliers.value.length === 0) {
      console.log('No suppliers found for this tenant');
    }
  } catch (error) {
    console.error('Error fetching suppliers:', error);
    // Set to empty array on error to prevent UI issues
    suppliers.value = [];
    toast.error('Error loading suppliers', error.response?.data?.message || error.message);
  }
}

function openAddDialog() {
  resetForm();
  dialogOpen.value = true;
}

function editSupplier(supplier) {
  isEditing.value = true;
  form.value = { ...supplier };
  fetchError.value = null; // Clear fetch errors when editing
  dialogOpen.value = true;
}

/**
 * Handle fetch business info button click
 * Manually triggers the fetch when user clicks the button
 */
async function handleFetchBusinessInfo() {
  // Validate TIN number
  if (!form.value.tin_number || !form.value.tin_number.trim()) {
    toast.warning('Please enter a TIN number');
    return;
  }

  // Clear any previous errors
  fetchError.value = null;

  // Fetch business info
  await fetchBusinessInfoByTin(form.value.tin_number.trim());
}

/**
 * Fetch business information from Ethiopian Trade Bureau API by TIN number
 * 
 * This function calls the backend API endpoint which in turn calls the
 * Ethiopian Trade Bureau API to retrieve business registration information.
 * The backend handles CORS and redirects, then returns the mapped data.
 * 
 * Backend endpoint: /api/suppliers/fetch-business-info/{tinNumber}
 * External API: https://etrade.gov.et/api/Registration/GetRegistrationInfoByTin/{tinNumber}/am
 * 
 * @param {string} tinNumber - The TIN (Tax Identification Number) to lookup
 */
async function fetchBusinessInfoByTin(tinNumber) {
  // Validate TIN number
  if (!tinNumber || !tinNumber.trim()) {
    fetchError.value = 'Please enter a valid TIN number';
    return;
  }

  // Skip if editing (to prevent overwriting existing data)
  if (isEditing.value) {
    return;
  }

  // Clear previous errors
  fetchError.value = null;

  // Set loading state
  isFetchingBusinessInfo.value = true;

  try {
    // Call backend API endpoint to fetch business info
    // The backend handles the external API call to Ethiopian Trade Bureau using cURL
    // This avoids CORS issues since the backend can make server-to-server requests
    const response = await apiClient.get(`/suppliers/fetch-business-info/${tinNumber.trim()}`);
    
    console.log('Response from backend API:', response.data);

    // Check if we received valid data
    // The backend returns: { success: true, data: {...}, raw_data: {...} }
    console.log('Full response:', response.data);
    
    if (response.data && response.data.success && response.data.data) {
      const businessData = response.data.data;
      
      console.log('Business data received:', businessData);
      console.log('Current form values before population:', { ...form.value });

      // Pre-fill form fields with the fetched data
      // When creating a new supplier (not editing), populate all available fields
      // For new records, we populate even if fields have values (from previous fetch)
      // This ensures users always see the latest data from the API
      let fieldsPopulated = 0;
      
      // Helper function to safely set form field value
      const populateField = (fieldKey, value) => {
        if (value !== null && value !== undefined && value !== '') {
          // Convert to string and trim
          const stringValue = String(value).trim();
          if (stringValue !== '') {
            // For new records, always populate (we're not editing, so it's safe)
            // This allows the API data to refresh form fields even if user typed something
            form.value[fieldKey] = stringValue;
            fieldsPopulated++;
            console.log(`✓ Populated ${fieldKey}:`, stringValue);
            return true;
          }
        }
        console.log(`✗ Skipped ${fieldKey} - no valid value`);
        return false;
      };

      // Populate all fields from the API response
      // Always attempt to populate each field - the populateField function will validate the value
      populateField('name', businessData.name);
      populateField('business_number', businessData.business_number);
      populateField('address', businessData.address);
      populateField('woreda', businessData.woreda);
      populateField('house_number', businessData.house_number);
      populateField('phone_number', businessData.phone_number);
      populateField('contact_info', businessData.contact_info);
      
      // Log all available fields from the API response for debugging
      console.log('All available fields in businessData:', Object.keys(businessData));
      console.log('All businessData values:', businessData);
      
      // Also populate tin_number if it's not already set (should already be set by user)
      if (!form.value.tin_number || form.value.tin_number.trim() === '') {
        form.value.tin_number = tinNumber.trim();
      }

      console.log('Form values after population:', { ...form.value });
      console.log(`Total fields populated: ${fieldsPopulated}`);

      // Show success message
      if (fieldsPopulated > 0) {
        toast.success(`Business information loaded successfully. ${fieldsPopulated} field(s) populated.`);
        // Clear error on success
        fetchError.value = null;
      } else if (Object.keys(businessData).length > 0) {
        toast.warning('Business data received but no fields were populated. Check console for details.');
        fetchError.value = 'Business data received but no fields were populated';
      } else {
        toast.warning('No business information found for this TIN number');
        fetchError.value = 'No business information found for this TIN number';
      }
    } else {
      // No data found or invalid response
      console.warn('Invalid response structure:', response.data);
      toast.warning('No business information found for this TIN number');
      fetchError.value = 'No business information found for this TIN number';
    }

  } catch (error) {
    console.error('Error fetching business info:', error);

    // Handle different error scenarios
    if (error.response) {
      // The request was made and the server responded with a status code
      const status = error.response.status;
      const errorData = error.response.data;

      console.error('Error response status:', status);
      console.error('Error response data:', errorData);
      console.error('Error response HTTP code from API:', errorData?.http_code);

      // Get error message from backend response
      // The backend returns the error message in the 'message' field
      const errorMessage = errorData?.message || errorData?.error || 'Unable to fetch business information';
      
      // Check if the backend included the original API HTTP code
      const apiHttpCode = errorData?.http_code;

      // Set error message for retry button
      // Handle 429 status code (rate limiting) - check both response status and API HTTP code
      if (status === 429 || apiHttpCode === 429) {
        // Rate limiting - Too Many Requests
        const retryAfter = errorData?.retry_after;
        let rateLimitMessage = errorMessage || 'Too many requests. Please wait a few moments before trying again.';
        
        if (retryAfter) {
          rateLimitMessage = `Rate limit exceeded. Please wait ${retryAfter} seconds before trying again.`;
          fetchError.value = `Rate limit exceeded. Wait ${retryAfter} seconds.`;
        } else {
          fetchError.value = 'Rate limit exceeded. Please try again later.';
        }
        
        toast.error('Rate limit exceeded', rateLimitMessage);
        console.warn('Rate limit exceeded. Retry after:', retryAfter, 'seconds');
        console.warn('API returned HTTP 429 - Too Many Requests');
      } else if (status === 404 || apiHttpCode === 404) {
        // TIN not found
        fetchError.value = errorMessage || 'No business information found for this TIN number';
        toast.warning(errorMessage || 'No business information found for this TIN number');
      } else if (status === 400 || apiHttpCode === 400) {
        // Invalid TIN format
        fetchError.value = errorMessage || 'Invalid TIN number format';
        toast.warning(errorMessage || 'Invalid TIN number format');
      } else if (status === 403 || apiHttpCode === 403) {
        // Forbidden - might be authentication issue
        fetchError.value = errorMessage || 'Access denied';
        toast.error('Access denied', errorMessage || 'Unable to access the business registration service. Please contact the administrator.');
      } else if (status === 503 || apiHttpCode === 503) {
        // Connection error (service unavailable)
        fetchError.value = 'Connection error. Please try again.';
        toast.error('Connection error', errorMessage || 'Unable to connect to the business registration service. Please try again later.');
      } else if (status >= 500 || (apiHttpCode && apiHttpCode >= 500)) {
        // Server error
        fetchError.value = 'Server error. Please try again later.';
        toast.error('Server error', errorMessage || 'The business registration service is temporarily unavailable. Please try again later.');
      } else {
        // Other errors - check if we have an API HTTP code to provide better error message
        fetchError.value = errorMessage || 'Error fetching business information. Please try again.';
        if (apiHttpCode) {
          console.warn('API returned HTTP', apiHttpCode, 'but frontend received', status);
          toast.error('Error fetching business info', errorMessage);
        } else {
          toast.error('Error fetching business info', errorMessage);
        }
      }
    } else if (error.request) {
      // The request was made but no response was received
      // This usually means a network error
      console.error('No response received:', error.request);
      fetchError.value = 'Network error. Please check your internet connection and try again.';
      toast.error('Network error', 'Unable to connect to the server. Please check your internet connection and try again.');
    } else if (error.code === 'ECONNABORTED' || error.message?.includes('timeout')) {
      // Request timeout
      fetchError.value = 'Request timeout. Please try again.';
      toast.error('Timeout error', 'The request took too long. Please try again.');
    } else {
      // Something happened in setting up the request that triggered an Error
      console.error('Error setting up request:', error.message);
      fetchError.value = error.message || 'An unexpected error occurred. Please try again.';
      toast.error('Error', error.message || 'An unexpected error occurred while fetching business information');
    }
  } finally {
    // Clear loading state
    isFetchingBusinessInfo.value = false;
  }
}

async function saveSupplier() {
  // Validation
  if (!form.value.name || form.value.name.trim() === '') {
    toast.warning('Please enter supplier name');
    return;
  }

  try {
    saving.value = true;
    const payload = {
      ...form.value,
      name: form.value.name.trim(),
    };
    
    if (isEditing.value) {
      await apiClient.put(`/suppliers/${form.value.id}`, payload);
    } else {
      await apiClient.post('/suppliers', payload);
    }
    dialogOpen.value = false;
    resetForm();
    // Refresh the suppliers list after successful save
    await fetchSuppliers();
    toast.success(
      isEditing.value ? 'Supplier updated successfully' : 'Supplier created successfully'
    );
  } catch (error) {
    console.error('Error saving supplier:', error);
    const errorMessage = error.response?.data?.message || error.response?.data?.errors 
      ? (error.response.data.errors ? JSON.stringify(error.response.data.errors) : error.response.data.message)
      : 'Error saving supplier';
    toast.error('Error saving supplier', errorMessage);
  } finally {
    saving.value = false;
  }
}

async function deleteSupplier(id) {
  const confirmed = await confirm(
    'Are you sure you want to delete this supplier?',
    'Delete Supplier',
    'danger'
  );
  
  if (!confirmed) return;

  try {
    deleting.value[id] = true;
    await apiClient.delete(`/suppliers/${id}`);
    await fetchSuppliers();
    toast.success('Supplier deleted successfully');
  } catch (error) {
    console.error('Error deleting supplier:', error);
    toast.error('Error deleting supplier', error.response?.data?.message || 'Error deleting supplier');
  } finally {
    deleting.value[id] = false;
  }
}

function resetForm() {
  form.value = {
    id: null,
    name: '',
    tin_number: '',
    business_number: '',
    address: '',
    woreda: '',
    house_number: '',
    phone_number: '',
    products_supplied: '',
    contact_info: '',
  };
  isEditing.value = false;
  isFetchingBusinessInfo.value = false;
  fetchError.value = null; // Clear fetch errors when resetting form
}

onMounted(fetchSuppliers);
</script>
