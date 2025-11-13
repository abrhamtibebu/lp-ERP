<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#8B4513]">Products</h1>
        <p class="text-gray-600 mt-1">Product catalog and SKU management</p>
      </div>
      <div class="flex gap-2">
        <Button 
          v-if="canManageProducts" 
          @click="importParkerClay" 
          :disabled="isImporting"
          variant="outline"
          class="border-[#8B4513] text-[#8B4513] hover:bg-[#8B4513] hover:text-white"
        >
          <Download v-if="!isImporting" class="h-4 w-4 mr-2" />
          <Loader2 v-else class="h-4 w-4 mr-2 animate-spin" />
          {{ isImporting ? 'Importing...' : 'Import Parker Clay' }}
        </Button>
        <Button 
          v-if="canManageProducts" 
          @click="dialogOpen = true" 
          class="bg-[#8B4513] hover:bg-[#6B3410] text-white"
        >
          <Plus class="h-4 w-4 mr-2" />
          Add Product
        </Button>
        <div v-else class="text-sm text-gray-500">Read-only access</div>
      </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card v-for="product in products" :key="product.id" class="hover:shadow-lg transition-shadow">
        <CardContent class="p-6">
          <div class="flex items-start justify-between mb-4">
            <div class="relative">
            <div v-if="product.image_url" class="w-16 h-16 rounded-xl overflow-hidden shadow-lg">
              <img :src="product.image_url" :alt="product.product_name" class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-16 h-16 bg-gradient-to-br from-[#8B4513] to-[#6B3410] rounded-xl flex items-center justify-center text-white text-2xl font-bold shadow-lg">
              {{ product.product_name?.charAt(0).toUpperCase() || 'P' }}
            </div>
            <div v-if="product.brand_logo_url" class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-white p-0.5 shadow-md">
              <img :src="product.brand_logo_url" :alt="product.brand_name || 'Brand'" class="w-full h-full object-contain rounded-full" />
            </div>
          </div>
            <Badge variant="secondary" class="bg-gray-100 text-gray-700">
              {{ product.sku }}
            </Badge>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">{{ product.product_name }}</h3>
          <div class="space-y-2 mb-4">
            <div class="flex items-center gap-2 text-sm text-gray-600">
              <span>Color:</span>
              <span class="font-medium">{{ product.color || 'N/A' }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600">
              <span>Weight:</span>
              <span class="font-medium">{{ product.weight_kg ? `${product.weight_kg} kg` : 'N/A' }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="text-gray-600">Unit Price:</span>
              <span class="font-semibold text-gray-900">{{ formatCurrency(product.unit_price, product.currency || 'USD') }}</span>
            </div>
            <div v-if="product.brand_name" class="flex items-center gap-2 text-xs text-gray-500">
              <span>Brand:</span>
              <span class="font-medium">{{ product.brand_name }}</span>
            </div>
          </div>
          <p v-if="product.description" class="text-sm text-gray-600 mb-4 line-clamp-2">{{ product.description }}</p>
          <div class="flex gap-2" v-if="canManageProducts">
            <Button 
              variant="outline" 
              size="sm"
              @click="editProduct(product)"
              class="flex-1"
            >
              Edit
            </Button>
            <Button 
              variant="outline" 
              size="sm"
              @click="deleteProduct(product.id)"
              class="text-red-600 hover:text-red-700 hover:bg-red-50"
            >
              Delete
            </Button>
          </div>
          <div v-else class="text-xs text-gray-500 italic">Read-only</div>
        </CardContent>
      </Card>

      <Card v-if="products.length === 0" class="col-span-full">
        <CardContent class="p-12 text-center">
          <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
          </svg>
          <p class="text-gray-500 text-lg">No products found</p>
          <p class="text-gray-400 text-sm mt-2">Add your first product to get started</p>
        </CardContent>
      </Card>
    </div>

    <!-- Add/Edit Product Dialog -->
    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Product' : 'Add New Product'" class="max-w-3xl">
      <form @submit.prevent="saveProduct" class="space-y-6">
        <!-- Product Information Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Package class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Product Information</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2 md:col-span-2">
              <Label for="product_name" class="flex items-center gap-2">
                <Package class="h-4 w-4 text-gray-500" />
                Product Name *
              </Label>
              <Input 
                id="product_name" 
                v-model="form.product_name" 
                placeholder="e.g., Classic Leather Wallet"
                required 
              />
            </div>
            <div class="space-y-2">
              <Label for="sku" class="flex items-center gap-2">
                <Hash class="h-4 w-4 text-gray-500" />
                SKU / Product Code *
              </Label>
              <Input 
                id="sku" 
                v-model="form.sku" 
                placeholder="e.g., CLW-BRN-001"
                required 
              />
              <p v-if="skuError" class="text-xs text-red-600">{{ skuError }}</p>
            </div>
            <div class="space-y-2">
              <Label for="color" class="flex items-center gap-2">
                <Palette class="h-4 w-4 text-gray-500" />
                Color
              </Label>
              <Input 
                id="color" 
                v-model="form.color" 
                placeholder="e.g., Brown, Black"
              />
            </div>
          </div>
        </div>

        <!-- Pricing & Specifications Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <DollarSign class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Pricing & Specifications</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="unit_price" class="flex items-center gap-2">
                <DollarSign class="h-4 w-4 text-gray-500" />
                Unit Price *
              </Label>
              <div class="flex gap-2">
                <Input 
                  id="unit_price" 
                  v-model.number="form.unit_price" 
                  type="number" 
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                  required 
                  class="flex-1"
                />
                <CurrencySelector v-model="form.currency" class="w-32" />
              </div>
            </div>
            <div class="space-y-2">
              <Label for="weight_kg" class="flex items-center gap-2">
                <Scale class="h-4 w-4 text-gray-500" />
                Weight (kg)
              </Label>
              <Input 
                id="weight_kg" 
                v-model.number="form.weight_kg" 
                type="number" 
                step="0.01"
                min="0"
                placeholder="0.00"
              />
            </div>
          </div>
        </div>

        <!-- Production Details Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <Code class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Production Details</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2 md:col-span-2">
              <Label for="consumption_formula" class="flex items-center gap-2">
                <Code class="h-4 w-4 text-gray-500" />
                Consumption Formula
              </Label>
              <Input 
                id="consumption_formula" 
                v-model="form.consumption_formula" 
                placeholder="e.g., length * width * 0.5"
              />
              <p class="text-xs text-gray-500">Formula for auto-calculating leather consumption</p>
            </div>
          </div>
        </div>

        <!-- Description Section -->
        <div class="space-y-4">
          <div class="flex items-center gap-2 pb-2 border-b border-gray-200">
            <FileText class="h-5 w-5 text-[#8B4513]" />
            <h3 class="text-lg font-semibold text-gray-900">Description</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2 md:col-span-2">
              <Label for="description" class="flex items-center gap-2">
                <FileText class="h-4 w-4 text-gray-500" />
                Product Description
              </Label>
              <textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-[#8B4513]"
                placeholder="Enter product description..."
              ></textarea>
            </div>
          </div>
        </div>
      </form>
      <template #footer>
        <div class="flex justify-end gap-3">
          <Button type="button" variant="outline" @click="closeDialog">
            Cancel
          </Button>
          <Button 
            type="button" 
            @click="saveProduct" 
            class="bg-[#8B4513] hover:bg-[#6B3410] text-white"
            :disabled="isSaving"
          >
            {{ isSaving ? 'Saving...' : (isEditing ? 'Update Product' : 'Create Product') }}
          </Button>
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Plus, Download, Loader2, Package, Tag, Palette, Hash, Scale, DollarSign, FileText, Code } from 'lucide-vue-next';
import apiClient from '@/api/client';
import { useAuthStore } from '@/stores/auth';
import Card from '@/components/ui/Card.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Button from '@/components/ui/Button.vue';
import Badge from '@/components/ui/Badge.vue';
import Dialog from '@/components/ui/Dialog.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import CurrencySelector from '@/components/ui/CurrencySelector.vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { useCurrency } from '@/composables/useCurrency';

const authStore = useAuthStore();
const { toast } = useToast();
const { confirm } = useConfirm();

const products = ref([]);
const dialogOpen = ref(false);
const isEditing = ref(false);
const isSaving = ref(false);
const isImporting = ref(false);
const skuError = ref('');

// Permission checks
const canManageProducts = computed(() => {
  return authStore.hasRole('Admin') || authStore.hasPermission('products.manage');
});

const canViewProducts = computed(() => {
  return authStore.hasRole('Admin') || authStore.hasPermission('products.view') || authStore.hasPermission('products.manage');
});

const form = ref({
  id: null,
  product_name: '',
  color: '',
  sku: '',
  weight_kg: null,
  unit_price: null,
  currency: 'USD',
  consumption_formula: '',
  description: '',
});

const { formatCurrency: formatCurrencyAmount } = useCurrency();

const formatCurrency = (amount, currency = 'USD') => {
  return formatCurrencyAmount(amount, currency);
};

async function fetchProducts() {
  try {
    const response = await apiClient.get('/products');
    products.value = response.data || [];
  } catch (error) {
    console.error('Error fetching products:', error);
    toast.error('Error loading products', error.response?.data?.message || error.message);
  }
}

async function importParkerClay() {
  const confirmed = await confirm({
    title: 'Import Parker Clay Products',
    message: 'This will import products and brand information from Parker Clay website. Continue?',
    type: 'info'
  });

  if (!confirmed) return;

  isImporting.value = true;
  try {
    const response = await apiClient.post('/products/import/parker-clay');
    await fetchProducts();
    
    const imported = response.data?.imported || 0;
    const errors = response.data?.errors || [];
    
    if (imported > 0) {
      toast.success(`Successfully imported ${imported} products from Parker Clay`);
    }
    
    if (errors.length > 0) {
      toast.warning(`Import completed with ${errors.length} errors. Check console for details.`);
      console.warn('Import errors:', errors);
    }
  } catch (error) {
    console.error('Error importing Parker Clay products:', error);
    toast.error('Error importing products', error.response?.data?.message || error.message);
  } finally {
    isImporting.value = false;
  }
}

function editProduct(product) {
  isEditing.value = true;
  form.value = {
    id: product.id,
    product_name: product.product_name || '',
    color: product.color || '',
    sku: product.sku || '',
    weight_kg: product.weight_kg || null,
    unit_price: product.unit_price || null,
    currency: product.currency || 'USD',
    consumption_formula: product.consumption_formula || '',
    description: product.description || '',
  };
  skuError.value = '';
  dialogOpen.value = true;
}

function closeDialog() {
  dialogOpen.value = false;
  resetForm();
}

function resetForm() {
  form.value = {
    id: null,
    product_name: '',
    color: '',
    sku: '',
    weight_kg: null,
    unit_price: null,
    currency: 'USD',
    consumption_formula: '',
    description: '',
  };
  isEditing.value = false;
  skuError.value = '';
  isSaving.value = false;
}

async function saveProduct() {
  // Validation
  if (!form.value.product_name || !form.value.sku || !form.value.unit_price) {
    toast.warning('Please fill in all required fields');
    return;
  }

  if (form.value.unit_price < 0) {
    toast.warning('Unit price cannot be negative');
    return;
  }

  if (form.value.weight_kg !== null && form.value.weight_kg < 0) {
    toast.warning('Weight cannot be negative');
    return;
  }

  isSaving.value = true;
  skuError.value = '';

  try {
    const payload = {
      product_name: form.value.product_name,
      color: form.value.color || null,
      sku: form.value.sku,
      weight_kg: form.value.weight_kg || null,
      unit_price: form.value.unit_price,
      consumption_formula: form.value.consumption_formula || null,
      description: form.value.description || null,
    };

    if (isEditing.value) {
      await apiClient.put(`/products/${form.value.id}`, payload);
    } else {
      await apiClient.post('/products', payload);
    }

    await fetchProducts();
    closeDialog();
    toast.success(
      isEditing.value ? 'Product updated successfully' : 'Product created successfully'
    );
  } catch (error) {
    console.error('Error saving product:', error);
    
    // Handle SKU uniqueness error
    if (error.response?.status === 422) {
      const errors = error.response.data.errors || {};
      if (errors.sku) {
        skuError.value = errors.sku[0] || 'This SKU is already in use';
      } else {
        const errorMsg = error.response.data.message || 'Validation error';
        toast.error('Validation error', errorMsg);
      }
    } else {
      toast.error('Error saving product', error.response?.data?.message || 'Error saving product');
    }
  } finally {
    isSaving.value = false;
  }
}

async function deleteProduct(id) {
  const confirmed = await confirm(
    'Are you sure you want to delete this product? This action cannot be undone.',
    'Delete Product',
    'danger'
  );
  
  if (!confirmed) return;

  try {
    await apiClient.delete(`/products/${id}`);
    await fetchProducts();
    toast.success('Product deleted successfully');
  } catch (error) {
    console.error('Error deleting product:', error);
    toast.error('Error deleting product', error.response?.data?.message || 'Error deleting product');
  }
}

onMounted(() => {
  fetchProducts();
});
</script>
