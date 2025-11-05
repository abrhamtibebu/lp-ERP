<template>
  <thead :class="cn('[&_tr]:border-b', className)">
    <slot />
  </thead>
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

