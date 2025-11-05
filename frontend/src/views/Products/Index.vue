<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#8B4513]">Products</h1>
        <p class="text-gray-600 mt-1">Product catalog and SKU management</p>
      </div>
      <Button @click="dialogOpen = true" class="bg-[#8B4513] hover:bg-[#6B3410] text-white">
        <Plus class="h-4 w-4 mr-2" />
        Add Product
      </Button>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card v-for="product in products" :key="product.id" class="hover:shadow-lg transition-shadow">
        <CardContent class="p-6">
          <div class="flex items-start justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-[#8B4513] to-[#6B3410] rounded-xl flex items-center justify-center text-white text-2xl font-bold shadow-lg">
              {{ product.product_name?.charAt(0).toUpperCase() || 'P' }}
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
              <span class="font-semibold text-gray-900">${{ formatCurrency(product.unit_price) }}</span>
            </div>
          </div>
          <div class="flex gap-2">
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
    <Dialog v-model="dialogOpen" :title="isEditing ? 'Edit Product' : 'Add New Product'">
      <form @submit.prevent="saveProduct" class="space-y-4">
        <div class="space-y-2">
          <Label for="product_name">Product Name *</Label>
          <Input 
            id="product_name" 
            v-model="form.product_name" 
            placeholder="e.g., Classic Leather Wallet"
            required 
          />
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="color">Color</Label>
            <Input 
              id="color" 
              v-model="form.color" 
              placeholder="e.g., Brown, Black"
            />
          </div>
          <div class="space-y-2">
            <Label for="sku">SKU / Product Code *</Label>
            <Input 
              id="sku" 
              v-model="form.sku" 
              placeholder="e.g., CLW-BRN-001"
              required 
            />
            <p v-if="skuError" class="text-xs text-red-600">{{ skuError }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="weight_kg">Weight (kg)</Label>
            <Input 
              id="weight_kg" 
              v-model.number="form.weight_kg" 
              type="number" 
              step="0.01"
              min="0"
              placeholder="0.00"
            />
          </div>
          <div class="space-y-2">
            <Label for="unit_price">Unit Price ($) *</Label>
            <Input 
              id="unit_price" 
              v-model.number="form.unit_price" 
              type="number" 
              step="0.01"
              min="0"
              placeholder="0.00"
              required 
            />
          </div>
        </div>

        <div class="space-y-2">
          <Label for="consumption_formula">Consumption Formula</Label>
          <Input 
            id="consumption_formula" 
            v-model="form.consumption_formula" 
            placeholder="e.g., length * width * 0.5"
          />
          <p class="text-xs text-gray-500">Formula for auto-calculating leather consumption</p>
        </div>

        <div class="space-y-2">
          <Label for="description">Description</Label>
          <textarea
            id="description"
            v-model="form.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-[#8B4513]"
            placeholder="Product description..."
          ></textarea>
        </div>
      </form>
      <template #footer>
        <Button type="button" variant="outline" @click="closeDialog">Cancel</Button>
        <Button 
          type="button" 
          @click="saveProduct" 
          class="bg-[#8B4513] hover:bg-[#6B3410] text-white"
          :disabled="isSaving"
        >
          {{ isSaving ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
        </Button>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Plus } from 'lucide-vue-next';
import apiClient from '@/api/client';
import Card from '@/components/ui/Card.vue';
import CardContent from '@/components/ui/CardContent.vue';
import Button from '@/components/ui/Button.vue';
import Badge from '@/components/ui/Badge.vue';
import Dialog from '@/components/ui/Dialog.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';

const products = ref([]);
const dialogOpen = ref(false);
const isEditing = ref(false);
const isSaving = ref(false);
const skuError = ref('');

const form = ref({
  id: null,
  product_name: '',
  color: '',
  sku: '',
  weight_kg: null,
  unit_price: null,
  consumption_formula: '',
  description: '',
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount);
};

async function fetchProducts() {
  try {
    const response = await apiClient.get('/products');
    products.value = response.data || [];
  } catch (error) {
    console.error('Error fetching products:', error);
    alert('Error loading products');
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
    alert('Please fill in all required fields');
    return;
  }

  if (form.value.unit_price < 0) {
    alert('Unit price cannot be negative');
    return;
  }

  if (form.value.weight_kg !== null && form.value.weight_kg < 0) {
    alert('Weight cannot be negative');
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
  } catch (error) {
    console.error('Error saving product:', error);
    
    // Handle SKU uniqueness error
    if (error.response?.status === 422) {
      const errors = error.response.data.errors || {};
      if (errors.sku) {
        skuError.value = errors.sku[0] || 'This SKU is already in use';
      } else {
        const errorMsg = error.response.data.message || 'Validation error';
        alert(errorMsg);
      }
    } else {
      alert(error.response?.data?.message || 'Error saving product');
    }
  } finally {
    isSaving.value = false;
  }
}

async function deleteProduct(id) {
  if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
    return;
  }

  try {
    await apiClient.delete(`/products/${id}`);
    await fetchProducts();
  } catch (error) {
    console.error('Error deleting product:', error);
    alert(error.response?.data?.message || 'Error deleting product');
  }
}

onMounted(() => {
  fetchProducts();
});
</script>
