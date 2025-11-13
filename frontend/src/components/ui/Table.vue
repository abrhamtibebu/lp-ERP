<template>
  <div class="relative w-full overflow-hidden">
    <table :class="cn('w-full caption-bottom text-sm', className)">
      <slot />
    </table>
  </div>
</template>

<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
});

const className = props.class;
</script>

