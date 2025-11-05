<template>
  <div
    v-if="isActive"
    :class="cn(
      'mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
      className
    )"
  >
    <slot />
  </div>
</template>

<script setup>
import { inject, computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
  value: {
    type: String,
    required: true,
  },
  class: {
    type: String,
    default: '',
  },
});

const className = props.class;

const tabsValue = inject('tabsValue', computed(() => ''));

const isActive = computed(() => tabsValue.value === props.value);
</script>
