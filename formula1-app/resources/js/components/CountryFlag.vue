<script setup>
import { computed } from 'vue';

const props = defineProps({
  countryCode: {
    type: String,
    required: true
  },
  size: {
    type: String,
    default: 'w-4 h-3' // Tamaño por defecto
  },
  rounded: {
    type: Boolean,
    default: false
  }
});

// Normalizar el código del país
const normalizedCode = computed(() => {
  return props.countryCode.toLowerCase();
});
</script>

<template>
  <span
    v-if="countryCode"  
    :class="[
      'fi', 
      `fi-${normalizedCode}`,
      size,
      { 'rounded-sm': rounded }
    ]"
    :title="countryCode"
  ></span>
  <span v-else class="text-gray-400">N/A</span>
</template>