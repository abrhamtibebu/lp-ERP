<template>
  <div class="flex items-center gap-2">
    <Select v-model="selectedCurrency" @update:model-value="handleChange">
      <SelectItem value="USD">USD ($)</SelectItem>
      <SelectItem value="ETB">ETB (Br)</SelectItem>
    </Select>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import Select from './Select.vue';
import SelectItem from './SelectItem.vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: 'USD'
  }
});

const emit = defineEmits(['update:modelValue']);

const selectedCurrency = ref(props.modelValue || 'USD');

const handleChange = (value) => {
  selectedCurrency.value = value;
  emit('update:modelValue', value);
};

watch(() => props.modelValue, (newValue) => {
  if (newValue !== selectedCurrency.value) {
    selectedCurrency.value = newValue || 'USD';
  }
});

onMounted(() => {
  selectedCurrency.value = props.modelValue || 'USD';
});
</script>

