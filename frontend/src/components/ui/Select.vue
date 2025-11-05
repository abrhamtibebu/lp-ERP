<template>
  <div class="relative" ref="containerRef">
    <button
      type="button"
      :class="cn(
        'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
        className
      )"
      @click="toggleOpen"
    >
      <span>{{ selectedLabel || placeholder }}</span>
      <ChevronDown class="h-4 w-4 opacity-50" />
    </button>
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        ref="contentRef"
        class="absolute z-50 mt-1 w-full min-w-[8rem] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md"
        @click.stop
      >
        <div class="p-1">
          <slot />
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, provide } from 'vue';
import { ChevronDown } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import { onClickOutside } from '@vueuse/core';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: 'Select...',
  },
  class: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const contentRef = ref(null);
const containerRef = ref(null);
const selectedLabel = ref('');
const className = props.class;

const toggleOpen = () => {
  isOpen.value = !isOpen.value;
};

onClickOutside(containerRef, () => {
  if (isOpen.value) {
    isOpen.value = false;
  }
});

// Provide select context to children
provide('selectValue', computed(() => props.modelValue));
provide('selectUpdate', (value, label) => {
  emit('update:modelValue', value);
  selectedLabel.value = label;
  isOpen.value = false;
});
</script>
