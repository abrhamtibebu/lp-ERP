<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 bg-black/80"
        @click="closeDialog"
      />
    </Transition>
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="modelValue"
        :class="cn(
          'fixed left-[50%] top-[50%] z-50 flex w-full max-w-lg translate-x-[-50%] translate-y-[-50%] flex-col gap-4 border bg-background p-6 shadow-lg sm:rounded-lg max-h-[90vh] overflow-hidden',
          className
        )"
        @click.stop
      >
        <div v-if="title || description" class="flex flex-col space-y-1.5 text-center sm:text-left flex-shrink-0">
          <h2 v-if="title" class="text-lg font-semibold leading-none tracking-tight">{{ title }}</h2>
          <p v-if="description" class="text-sm text-muted-foreground">{{ description }}</p>
        </div>
        <div class="flex-1 overflow-y-auto min-h-0">
        <slot />
        </div>
        <div v-if="$slots.footer" class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 flex-shrink-0">
          <slot name="footer" />
        </div>
        <button
          @click="closeDialog"
          class="absolute right-4 top-4 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none"
        >
          <X class="h-4 w-4" />
          <span class="sr-only">Close</span>
        </button>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue';
import { X } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: '',
  },
  description: {
    type: String,
    default: '',
  },
  class: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue']);

const className = props.class;

const closeDialog = () => {
  emit('update:modelValue', false);
};
</script>
