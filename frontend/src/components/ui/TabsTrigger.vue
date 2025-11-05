<template>
  <button
    :class="cn(
      'inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
      isActive ? 'bg-background text-foreground shadow-sm' : '',
      className
    )"
    @click="selectTab"
  >
    <slot />
  </button>
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
const tabsUpdate = inject('tabsUpdate', () => {});

const isActive = computed(() => tabsValue.value === props.value);

const selectTab = () => {
  tabsUpdate(props.value);
};
</script>
