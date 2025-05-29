<template>
  <div class="space-y-6">
    <!-- Información principal del circuito -->
    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg overflow-hidden">
      <div class="w-full h-64 bg-gray-200 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
        <img v-if="circuit?.map_image && !mapImageError"
             :src="`/storage/${circuit.map_image}`" 
             :alt="circuit.name" 
             class="w-full h-full object-cover"
             @error="onMapImageError">
        <i v-else class="fas fa-image fa-4x text-gray-400 dark:text-gray-500"></i>
      </div>
      
      <div class="p-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
          <CountryFlag v-if="circuit?.country" 
                       :countryCode="circuit.country.code" 
                       class="h-5 w-7 mr-3" />
          {{ circuit?.name || 'Circuit information not available' }}
        </h2>
        
        <p class="mt-2 text-gray-600 dark:text-gray-300">
          {{ circuit?.location }}, {{ circuit?.country?.name }}
        </p>
      </div>
    </div>

    <!-- Detalles técnicos del circuito -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          <i class="fas fa-info-circle mr-2 text-f1-red"></i>
          Circuit Details
        </h3>
        
        <ul class="space-y-3">
          <li class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Length:</span>
            <span class="font-medium text-gray-900 dark:text-white">{{ circuit?.length ? `${circuit.length} km` : 'N/A' }}</span>
          </li>
          
          <li class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Lap Record:</span>
            <span class="font-medium text-gray-900 dark:text-white">{{ circuit?.lap_record || 'N/A' }}</span>
          </li>
          
          <li class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Record Holder:</span>
            <span class="font-medium text-gray-900 dark:text-white">{{ circuit?.lap_record_driver || 'N/A' }}</span>
          </li>
          
          <li class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Record Year:</span>
            <span class="font-medium text-gray-900 dark:text-white">{{ circuit?.lap_record_year || 'N/A' }}</span>
          </li>
        </ul>
      </div>
      
      <div class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          <i class="fas fa-map-marker-alt mr-2 text-f1-red"></i>
          Circuit Layout
        </h3>
        
        <div v-if="circuit?.layout_image && !layoutImageError" 
             class="bg-white dark:bg-gray-800 rounded-md overflow-hidden mb-4">
          <div class="relative h-[300px] overflow-hidden flex items-center justify-center bg-gray-200 dark:bg-gray-700">
            <img :src="`/storage/${circuit.layout_image}`" 
                 :alt="`${circuit.name} layout`" 
                 class="object-contain max-h-full max-w-full"
                 @error="onLayoutImageError">
            
            <button 
              @click="showFullLayout = true" 
              class="absolute bottom-2 right-2 bg-gray-800/70 text-white p-2 rounded-full hover:bg-gray-700/90"
              title="View full layout"
            >
              <i class="fas fa-expand-alt text-sm"></i>
            </button>
          </div>
        </div>
        <div v-else 
             class="bg-gray-100 dark:bg-gray-800 text-center py-10 rounded-md flex flex-col items-center justify-center min-h-[150px]">
          <i class="fas fa-drafting-compass fa-3x text-gray-400 dark:text-gray-500 mb-2"></i>
          <p class="text-gray-500 dark:text-gray-400">Circuit layout not available</p>
        </div>
      </div>
    </div>
    
    <!-- Descripción del circuito -->
    <div v-if="circuit?.description" class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-lg">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
        <i class="fas fa-align-left mr-2 text-f1-red"></i>
        About the Circuit
      </h3>
      <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ circuit.description }}</p>
    </div>

    <!-- Modal para mostrar la imagen completa -->
    <div v-if="showFullLayout && circuit?.layout_image" 
         class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4"
         @click="showFullLayout = false">
      <div class="relative max-w-4xl max-h-[90vh] w-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
        <img v-if="!modalLayoutImageError"
             :src="`/storage/${circuit.layout_image}`" 
             :alt="`${circuit.name} layout`" 
             class="w-full h-full object-contain"
             @error="onModalLayoutImageError">
        <i v-else class="fas fa-exclamation-triangle fa-5x text-red-500"></i>
        <button 
          @click.stop="showFullLayout = false" 
          class="absolute top-2 right-2 bg-gray-800/70 text-white p-2 rounded-full hover:bg-red-500/90"
          title="Close"
        >
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, ref, watch } from 'vue';
import CountryFlag from '@/components/CountryFlag.vue';

const showFullLayout = ref(false);
const mapImageError = ref(false);
const layoutImageError = ref(false);
const modalLayoutImageError = ref(false);

const props = defineProps({
  circuit: {
    type: Object,
    required: false,
    default: null
  }
});

// Resetear errores de imagen cuando el circuito cambie
watch(() => props.circuit, (newCircuit) => {
  mapImageError.value = false;
  layoutImageError.value = false;
  modalLayoutImageError.value = false;
}, { deep: true });

function onMapImageError() {
  mapImageError.value = true;
}

function onLayoutImageError() {
  layoutImageError.value = true;
}

function onModalLayoutImageError() {
  modalLayoutImageError.value = true;
}
</script>