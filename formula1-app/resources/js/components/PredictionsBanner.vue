<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
  nextRace: {
    type: Object,
    default: null
  }
});

const hasUpcomingRace = computed(() => props.nextRace && new Date(props.nextRace.race_date) > new Date());

// Comprobación adicional para asegurarse de que tenemos un ID de carrera válido
const canPredict = computed(() => hasUpcomingRace.value && props.nextRace.id);

// Para ocultar el banner si el usuario lo cierra
const showBanner = ref(true);
</script>

<template>
  <div v-if="showBanner && hasUpcomingRace" class="bg-gradient-to-r from-f1-red to-red-700 text-white rounded-md shadow-lg mb-6 overflow-hidden">
    <div class="flex items-center justify-between p-4">
      <div class="flex-1">
        <div class="flex items-center">
          <div class="hidden sm:block mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-bold">Make Your Predictions!</h3>
            <p class="text-white text-opacity-90">
              {{ nextRace ? `The ${nextRace.name} starts soon! Who will win?` : 'Next race is coming up soon!' }}
            </p>
          </div>
        </div>
      </div>
      
      <div class="flex items-center">
        <Link 
          v-if="canPredict"
          :href="route('predictions.create', { race: nextRace.id })"
          class="bg-white text-f1-red hover:bg-gray-100 py-2 px-4 rounded-md font-semibold transition-colors mr-3"
        >
          Predict Now
        </Link>
        <span v-else class="bg-white bg-opacity-20 py-2 px-4 rounded-md font-semibold mr-3">
          Coming Soon
        </span>
        
        <button @click="showBanner = false" class="text-white hover:text-gray-200 focus:outline-none">
          <span class="sr-only">Close</span>
          <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>