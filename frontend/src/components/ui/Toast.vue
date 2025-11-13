<template>
  <Teleport to="body">
    <TransitionGroup
      name="toast"
      tag="div"
      class="fixed top-0 z-[100] flex max-h-screen w-full flex-col-reverse p-4 sm:bottom-0 sm:right-0 sm:top-auto sm:flex-col md:max-w-[420px] gap-3"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="cn(
          'group pointer-events-auto relative flex w-full items-start gap-3 overflow-hidden rounded-xl border bg-background p-4 shadow-2xl transition-all backdrop-blur-sm',
          toast.type === 'success' ? 'border-emerald-500/50 bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/30 dark:to-background' :
          toast.type === 'error' ? 'border-red-500/50 bg-gradient-to-br from-red-50 to-white dark:from-red-950/30 dark:to-background' :
          toast.type === 'warning' ? 'border-amber-500/50 bg-gradient-to-br from-amber-50 to-white dark:from-amber-950/30 dark:to-background' :
          'border-blue-500/50 bg-gradient-to-br from-blue-50 to-white dark:from-blue-950/30 dark:to-background'
        )"
      >
        <!-- Icon -->
        <div :class="cn(
          'flex-shrink-0 mt-0.5 rounded-full p-1.5',
          toast.type === 'success' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400' :
          toast.type === 'error' ? 'bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-400' :
          toast.type === 'warning' ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400' :
          'bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400'
        )">
          <component :is="getIcon(toast.type)" class="h-5 w-5" />
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0 space-y-1">
          <p :class="cn(
            'font-semibold text-sm leading-tight',
            toast.type === 'success' ? 'text-emerald-900 dark:text-emerald-100' :
            toast.type === 'error' ? 'text-red-900 dark:text-red-100' :
            toast.type === 'warning' ? 'text-amber-900 dark:text-amber-100' :
            'text-blue-900 dark:text-blue-100'
          )">
            {{ toast.title }}
          </p>
          <p v-if="toast.description" :class="cn(
            'text-sm leading-relaxed',
            toast.type === 'success' ? 'text-emerald-700/90 dark:text-emerald-300/90' :
            toast.type === 'error' ? 'text-red-700/90 dark:text-red-300/90' :
            toast.type === 'warning' ? 'text-amber-700/90 dark:text-amber-300/90' :
            'text-blue-700/90 dark:text-blue-300/90'
          )">
            {{ toast.description }}
          </p>
        </div>

        <!-- Close Button -->
        <button
          @click="removeToast(toast.id)"
          :class="cn(
            'flex-shrink-0 rounded-lg p-1 transition-all hover:bg-black/5 dark:hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-offset-2',
            toast.type === 'success' ? 'text-emerald-600 focus:ring-emerald-500 dark:text-emerald-400' :
            toast.type === 'error' ? 'text-red-600 focus:ring-red-500 dark:text-red-400' :
            toast.type === 'warning' ? 'text-amber-600 focus:ring-amber-500 dark:text-amber-400' :
            'text-blue-600 focus:ring-blue-500 dark:text-blue-400'
          )"
          aria-label="Close notification"
        >
          <X class="h-4 w-4" />
        </button>

        <!-- Progress Bar -->
        <div
          v-if="toast.duration && toast.duration > 0"
          class="absolute bottom-0 left-0 right-0 h-0.5 overflow-hidden"
        >
          <div
            :class="cn(
              'h-full transition-all ease-linear',
              toast.type === 'success' ? 'bg-emerald-500' :
              toast.type === 'error' ? 'bg-red-500' :
              toast.type === 'warning' ? 'bg-amber-500' :
              'bg-blue-500'
            )"
            :style="{ 
              width: `${toast.progress}%`
            }"
          />
        </div>
      </div>
    </TransitionGroup>
  </Teleport>
</template>

<script setup>
import { ref, onUnmounted } from 'vue';
import { X, CheckCircle2, XCircle, AlertTriangle, Info } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const toasts = ref([]);
let toastId = 0;
const progressIntervals = new Map();

const getIcon = (type) => {
  switch (type) {
    case 'success':
      return CheckCircle2;
    case 'error':
      return XCircle;
    case 'warning':
      return AlertTriangle;
    case 'info':
    default:
      return Info;
  }
};

const addToast = (toast) => {
  const id = ++toastId;
  const duration = toast.duration || 5000;
  const updateInterval = 50; // Update progress every 50ms for smooth animation
  
  const toastData = {
    ...toast,
    id,
    duration,
    progress: 100,
    updateInterval,
    startTime: Date.now(),
  };

  toasts.value.push(toastData);

  if (duration > 0) {
    // Start progress animation
    const startProgress = () => {
      const interval = setInterval(() => {
        const index = toasts.value.findIndex((t) => t.id === id);
        if (index === -1) {
          clearInterval(interval);
          progressIntervals.delete(id);
          return;
        }

        const toast = toasts.value[index];
        const elapsed = Date.now() - toast.startTime;
        const remaining = Math.max(0, duration - elapsed);
        toast.progress = (remaining / duration) * 100;

        if (remaining <= 0) {
          clearInterval(interval);
          progressIntervals.delete(id);
          removeToast(id);
        }
      }, updateInterval);

      progressIntervals.set(id, interval);
    };

    startProgress();
  }
};

const removeToast = (id) => {
  // Clear progress interval if exists
  const interval = progressIntervals.get(id);
  if (interval) {
    clearInterval(interval);
    progressIntervals.delete(id);
  }

  const index = toasts.value.findIndex((t) => t.id === id);
  if (index > -1) {
    toasts.value.splice(index, 1);
  }
};

// Cleanup on unmount
onUnmounted(() => {
  progressIntervals.forEach((interval) => clearInterval(interval));
  progressIntervals.clear();
});

defineExpose({
  addToast,
  removeToast,
});
</script>

<style scoped>
.toast-enter-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 1, 1);
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.toast-enter-to {
  opacity: 1;
  transform: translateX(0) scale(1);
}

.toast-leave-from {
  opacity: 1;
  transform: translateX(0) scale(1);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.toast-move {
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>

