<template>
  <img :class="cn('aspect-square h-full w-full', className)" v-bind="$attrs" />
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

