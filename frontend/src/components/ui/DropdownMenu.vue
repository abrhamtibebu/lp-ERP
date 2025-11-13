<template>
  <div class="relative inline-block text-left" ref="containerRef">
    <div @click="toggleOpen" ref="triggerRef">
      <slot name="trigger" />
    </div>
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
        :class="cn(
          'dropdown-menu-content absolute z-50 min-w-[8rem] max-h-[300px] overflow-y-auto overflow-x-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md',
          align === 'end' ? 'right-0' : 'left-0',
          side === 'top' ? 'bottom-full mb-1' : 'top-full mt-1',
          className
        )"
        @click.stop
      >
        <slot />
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { cn } from '@/lib/utils';
import { onClickOutside } from '@vueuse/core';

const props = defineProps({
  align: {
    type: String,
    default: 'start',
  },
  side: {
    type: String,
    default: 'bottom',
  },
  class: {
    type: String,
    default: '',
  },
});

const isOpen = ref(false);
const triggerRef = ref(null);
const contentRef = ref(null);
const containerRef = ref(null);
const className = props.class;

const toggleOpen = () => {
  isOpen.value = !isOpen.value;
};

onClickOutside(containerRef, () => {
  if (isOpen.value) {
    isOpen.value = false;
  }
});
</script>

<style scoped>
/* Custom scrollbar styling for dropdown menu */
.dropdown-menu-content {
  scrollbar-width: thin;
  scrollbar-color: rgb(209, 213, 219) rgb(243, 244, 246);
}

.dropdown-menu-content::-webkit-scrollbar {
  width: 8px;
}

.dropdown-menu-content::-webkit-scrollbar-track {
  background: rgb(243, 244, 246);
  border-radius: 4px;
}

.dropdown-menu-content::-webkit-scrollbar-thumb {
  background: rgb(209, 213, 219);
  border-radius: 4px;
}

.dropdown-menu-content::-webkit-scrollbar-thumb:hover {
  background: rgb(156, 163, 175);
}

/* Firefox scrollbar styling */
@supports (scrollbar-width: thin) {
  .dropdown-menu-content {
    scrollbar-width: thin;
    scrollbar-color: rgb(209, 213, 219) rgb(243, 244, 246);
  }
}
</style>
