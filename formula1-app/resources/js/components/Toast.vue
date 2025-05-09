<template>
  <div 
    v-if="show"
    :class="[
      'fixed z-50 p-4 rounded-md shadow-lg transition-all duration-300',
      positionClasses,
      typeClasses
    ]"
  >
    <div class="flex items-center">
      <!-- Icono para el tipo de notificación -->
      <div v-if="type === 'success'" class="mr-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <div v-else-if="type === 'error'" class="mr-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </div>
      <div v-else-if="type === 'warning'" class="mr-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      <div v-else class="mr-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      
      <!-- Mensaje -->
      <div class="flex-1">{{ message }}</div>
      
      <!-- Botón de cerrar -->
      <button @click="close" class="ml-4 text-white hover:text-gray-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  message: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'info', // info, success, error, warning
    validator: (value) => ['info', 'success', 'error', 'warning'].includes(value)
  },
  position: {
    type: String,
    default: 'top-right', // top-right, top-left, bottom-right, bottom-left, top, bottom
    validator: (value) => ['top-right', 'top-left', 'bottom-right', 'bottom-left', 'top', 'bottom'].includes(value)
  },
  duration: {
    type: Number,
    default: 3000 // milisegundos
  },
  autoClose: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['close']);
const show = ref(true);
let timer = null;

const typeClasses = computed(() => {
  switch(props.type) {
    case 'success': return 'bg-green-600 text-white';
    case 'error': return 'bg-red-600 text-white';
    case 'warning': return 'bg-yellow-500 text-white';
    default: return 'bg-blue-600 text-white';
  }
});

const positionClasses = computed(() => {
  switch(props.position) {
    case 'top-right': return 'top-4 right-4';
    case 'top-left': return 'top-4 left-4';
    case 'bottom-right': return 'bottom-4 right-4';
    case 'bottom-left': return 'bottom-4 left-4';
    case 'top': return 'top-4 left-1/2 transform -translate-x-1/2';
    case 'bottom': return 'bottom-4 left-1/2 transform -translate-x-1/2';
    default: return 'top-4 right-4';
  }
});

const close = () => {
  show.value = false;
  emit('close');
};

onMounted(() => {
  if (props.autoClose && props.duration > 0) {
    timer = setTimeout(() => {
      close();
    }, props.duration);
  }
});

onUnmounted(() => {
  if (timer) {
    clearTimeout(timer);
  }
});
</script>