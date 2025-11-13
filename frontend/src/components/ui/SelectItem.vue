<template>
  <div
    ref="itemRef"
    :class="cn(
      'relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50 hover:bg-accent hover:text-accent-foreground',
      isSelected ? 'bg-accent text-accent-foreground' : '',
      className
    )"
    @click="selectItem"
  >
    <slot />
  </div>
</template>

<script setup>
import { inject, computed, ref } from 'vue';
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
const itemRef = ref(null);

const selectValue = inject('selectValue', computed(() => ''));
const selectUpdate = inject('selectUpdate', () => {});

const isSelected = computed(() => {
  if (!selectValue || !selectValue.value) return false;
  return selectValue.value === props.value;
});

const selectItem = () => {
  // Get the label from the slot content (text content of the element)
  let label = props.value; // Fallback to value
  if (itemRef.value) {
    const textContent = itemRef.value.textContent?.trim();
    if (textContent) {
      label = textContent;
    }
  }
  selectUpdate(props.value, label);
};
</script>
