<template>
  <div
    :class="cn(
      'shrink-0 bg-border',
      props.orientation === 'horizontal' ? 'h-[1px] w-full' : 'h-full w-[1px]',
      className
    )"
  />
</template>

<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
  orientation: {
    type: String,
    default: 'horizontal',
  },
  class: {
    type: String,
    default: '',
  },
});

const className = props.class;
</script>
