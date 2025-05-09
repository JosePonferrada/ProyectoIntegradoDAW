<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    @click.self="onCancel"
  >
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg max-w-md w-full">
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
        {{ title }}
      </h3>
      
      <p class="text-gray-700 dark:text-gray-300 mb-6">
        {{ message }}
      </p>
      
      <div class="flex justify-end space-x-4">
        <button
          @click="onCancel"
          class="px-4 py-2 rounded-md text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600"
        >
          {{ cancelText }}
        </button>
        <button
          @click="onConfirm"
          :class="[
            'px-4 py-2 rounded-md text-white',
            type === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-f1-red hover:bg-red-700'
          ]"
        >
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Confirmation'
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed?'
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  type: {
    type: String,
    default: 'primary', // 'primary', 'danger'
    validator: (value) => ['primary', 'danger'].includes(value)
  }
});

const emit = defineEmits(['confirm', 'cancel', 'update:show']);

const onConfirm = () => {
  emit('confirm');
  emit('update:show', false);
};

const onCancel = () => {
  emit('cancel');
  emit('update:show', false);
};

// Sync the show prop with parent
watch(() => props.show, (value) => {
  emit('update:show', value);
});
</script>