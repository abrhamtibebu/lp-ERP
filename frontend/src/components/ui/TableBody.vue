<template>
  <tbody :class="cn('[&_tr:last-child]:border-0', className)">
    <slot />
  </tbody>
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

