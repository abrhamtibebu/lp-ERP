<template>
  <Teleport to="body">
    <TransitionGroup
      name="toast"
      tag="div"
      class="fixed top-0 z-[100] flex max-h-screen w-full flex-col-reverse p-4 sm:bottom-0 sm:right-0 sm:top-auto sm:flex-col md:max-w-[420px]"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="cn(
          'group pointer-events-auto relative flex w-full items-center justify-between space-x-4 overflow-hidden rounded-md border p-6 pr-8 shadow-lg transition-all',
          toast.type === 'success' ? 'border-green-500 bg-green-50 text-green-900' :
          toast.type === 'error' ? 'border-red-500 bg-red-50 text-red-900' :
          toast.type === 'warning' ? 'border-yellow-500 bg-yellow-50 text-yellow-900' :
          'border-blue-500 bg-blue-50 text-blue-900'
        )"
      >
        <div class="flex-1">
          <p class="font-semibold">{{ toast.title }}</p>
          <p v-if="toast.description" class="text-sm opacity-90">{{ toast.description }}</p>
        </div>
        <button
          @click="removeToast(toast.id)"
          class="absolute right-2 top-2 rounded-md p-1 text-foreground/50 opacity-0 transition-opacity hover:text-foreground focus:opacity-100 focus:outline-none focus:ring-2 group-hover:opacity-100"
        >
          <X class="h-4 w-4" />
        </button>
      </div>
    </TransitionGroup>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue';
import { X } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const toasts = ref([]);
let toastId = 0;

const addToast = (toast) => {
  const id = ++toastId;
  toasts.value.push({ ...toast, id });
  if (toast.duration) {
    setTimeout(() => removeToast(id), toast.duration);
  }
};

const removeToast = (id) => {
  const index = toasts.value.findIndex((t) => t.id === id);
  if (index > -1) {
    toasts.value.splice(index, 1);
  }
};

defineExpose({
  addToast,
  removeToast,
});
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>

