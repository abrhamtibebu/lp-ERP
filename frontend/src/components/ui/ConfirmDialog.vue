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
        v-if="isOpen"
        class="fixed inset-0 z-[200] bg-black/60 backdrop-blur-sm"
        @click.self="handleCancel"
      />
    </Transition>
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 scale-95 translate-y-4"
      enter-to-class="opacity-100 scale-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 scale-100 translate-y-0"
      leave-to-class="opacity-0 scale-95 translate-y-4"
    >
      <div
        v-if="isOpen"
        class="fixed left-[50%] top-[50%] z-[201] w-full max-w-md translate-x-[-50%] translate-y-[-50%] rounded-xl border bg-background shadow-2xl"
        @click.stop
      >
        <div class="p-6">
          <!-- Icon -->
          <div 
            v-if="type"
            :class="cn(
              'mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full',
              type === 'danger' ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' :
              type === 'warning' ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400' :
              'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400'
            )"
          >
            <component 
              :is="getIcon(type)" 
              class="h-6 w-6" 
            />
          </div>

          <!-- Title -->
          <h2 
            v-if="title"
            class="mb-2 text-center text-lg font-semibold text-foreground"
          >
            {{ title }}
          </h2>

          <!-- Description -->
          <p 
            v-if="message"
            class="mb-6 text-center text-sm text-muted-foreground"
          >
            {{ message }}
          </p>

          <!-- Actions -->
          <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button
              @click="handleCancel"
              :class="cn(
                'inline-flex h-10 items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition-colors',
                'border border-input bg-background text-foreground hover:bg-accent hover:text-accent-foreground',
                'focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
                'disabled:pointer-events-none disabled:opacity-50'
              )"
            >
              {{ cancelText }}
            </button>
            <button
              @click="handleConfirm"
              :class="cn(
                'inline-flex h-10 items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition-colors',
                'focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
                'disabled:pointer-events-none disabled:opacity-50',
                type === 'danger' 
                  ? 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 dark:bg-red-600 dark:hover:bg-red-700' 
                  : type === 'warning'
                  ? 'bg-amber-600 text-white hover:bg-amber-700 focus:ring-amber-500 dark:bg-amber-600 dark:hover:bg-amber-700'
                  : 'bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary'
              )"
            >
              {{ confirmText }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue';
import { AlertTriangle, AlertCircle, HelpCircle } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Confirm Action',
  },
  message: {
    type: String,
    default: '',
  },
  confirmText: {
    type: String,
    default: 'Confirm',
  },
  cancelText: {
    type: String,
    default: 'Cancel',
  },
  type: {
    type: String,
    default: 'danger', // 'danger', 'warning', 'info'
    validator: (value) => ['danger', 'warning', 'info'].includes(value),
  },
});

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel']);

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
});

const getIcon = (type) => {
  switch (type) {
    case 'danger':
      return AlertCircle;
    case 'warning':
      return AlertTriangle;
    case 'info':
    default:
      return HelpCircle;
  }
};

const handleConfirm = () => {
  emit('confirm');
  emit('update:modelValue', false);
};

const handleCancel = () => {
  emit('cancel');
  emit('update:modelValue', false);
};
</script>

