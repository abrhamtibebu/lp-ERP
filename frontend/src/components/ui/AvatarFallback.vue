<template>
  <div :class="cn('flex h-full w-full items-center justify-center rounded-full bg-muted', className)">
    <slot />
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

