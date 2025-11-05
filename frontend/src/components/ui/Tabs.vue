<template>
  <div>
    <slot />
  </div>
</template>

<script setup>
import { computed, provide } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  orientation: {
    type: String,
    default: 'horizontal',
  },
});

const emit = defineEmits(['update:modelValue']);

provide('tabsValue', computed(() => props.modelValue));
provide('tabsUpdate', (value) => emit('update:modelValue', value));
</script>

