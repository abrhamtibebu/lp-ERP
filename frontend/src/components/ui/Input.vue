<template>
  <input
    :type="type"
    :value="inputValue"
    @input="handleInput"
    :class="cn(
      'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
      className
    )"
    v-bind="$attrs"
  />
</template>

<script setup>
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: undefined,
  },
  value: {
    type: [String, Number],
    default: undefined,
  },
  type: {
    type: String,
    default: 'text',
  },
  class: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue']);

const className = props.class;

// Support both v-model (modelValue) and :value
const inputValue = computed(() => {
  if (props.modelValue !== undefined) {
    return props.modelValue;
  }
  if (props.value !== undefined) {
    return props.value;
  }
  return '';
});

const handleInput = (event) => {
  // Only emit if using v-model
  if (props.modelValue !== undefined) {
    emit('update:modelValue', event.target.value);
  }
};
</script>

