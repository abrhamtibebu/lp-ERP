<template>
  <div class="relative">
    <input
      :type="showPassword ? 'text' : 'password'"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :class="cn(
        'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 pr-10 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
        className
      )"
      v-bind="$attrs"
    />
    <button
      type="button"
      @click="togglePassword"
      class="absolute inset-y-0 right-0 flex items-center pr-3 text-muted-foreground hover:text-foreground transition-colors"
      tabindex="-1"
    >
      <Eye v-if="!showPassword" class="h-4 w-4" />
      <EyeOff v-else class="h-4 w-4" />
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  class: {
    type: String,
    default: '',
  },
});

defineEmits(['update:modelValue']);

const className = props.class;
const showPassword = ref(false);

const togglePassword = () => {
  showPassword.value = !showPassword.value;
};
</script>

