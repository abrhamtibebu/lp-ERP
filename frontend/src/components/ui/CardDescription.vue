<template>
  <p :class="cn('text-sm text-muted-foreground', className)">
    <slot />
  </p>
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

