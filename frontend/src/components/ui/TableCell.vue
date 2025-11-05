<template>
  <td :class="cn('p-4 align-middle [&:has([role=checkbox])]:pr-0', className)" v-bind="$attrs">
    <slot />
  </td>
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

