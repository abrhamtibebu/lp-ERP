<template>
  <div class="flex items-center justify-between border-b bg-background px-4 py-3">
    <div class="flex items-center space-x-2">
      <h2 class="text-2xl font-semibold">{{ title }}</h2>
      <Badge v-if="badge" :variant="badgeVariant">{{ badge }}</Badge>
    </div>
    <div class="flex items-center space-x-2">
      <slot name="actions" />
    </div>
  </div>
</template>

<script setup>
import Badge from '@/components/ui/Badge.vue';

defineProps({
  title: {
    type: String,
    required: true,
  },
  badge: {
    type: String,
    default: '',
  },
  badgeVariant: {
    type: String,
    default: 'default',
  },
});
</script>

