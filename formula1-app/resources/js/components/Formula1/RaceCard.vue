<template>
  <div @click="$emit('click')" 
       class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-hidden hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors cursor-pointer flex flex-col h-[200px]">
    <!-- Contenido principal con flex-1 para que crezca y ocupe el espacio disponible -->
    <div class="p-4 flex flex-col flex-1">
      <!-- Header con fecha y estado -->
      <div class="flex justify-between items-center mb-2">
        <span class="text-sm text-gray-500 dark:text-gray-400">
          {{ formatDate(race.race_date) }}
        </span>
        <span class="px-2 py-1 rounded-full text-xs font-medium" :class="getStatusClass(race.status)">
          {{ race.status || 'Scheduled' }}
        </span>
      </div>
      
      <!-- Nombre de la carrera -->
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
        {{ race.name }}
      </h3>
      
      <!-- Circuito e información -->
      <div class="mt-2 flex items-center">
        <CountryFlag v-if="race.circuit?.country" :countryCode="race.circuit.country.code" class="h-4 w-5 mr-2" />
        <span class="text-sm text-gray-700 dark:text-gray-300">
          {{ race.circuit?.name }}, {{ race.circuit?.country?.name }}
        </span>
      </div>
      
      <!-- Espacio flexible para mantener consistencia vertical -->
      <div class="flex-grow"></div>
      
      <!-- Badge para formato sprint -->
      <div v-if="race.weekend_format === 'sprint'" class="mt-auto mb-2 inline-flex items-center">
        <span class="bg-red-100 dark:bg-red-900 dark:bg-opacity-20 text-red-600 dark:text-red-400 text-xs px-2 py-0.5 rounded-full">
          <i class="fas fa-bolt mr-1"></i> Sprint
        </span>
      </div>
      
      <!-- Cuenta atrás para la carrera principal -->
      <div v-if="isUpcoming" class="text-xs text-gray-500 dark:text-gray-400">
        <div class="flex items-center">
          <i class="fas fa-flag-checkered mr-1 text-red-600 dark:text-red-500"></i>
          <span>{{ countdownText }}</span>
        </div>
      </div>
    </div>
    
    <!-- Footer con opciones rápidas - Siempre al final gracias al flex-col -->
    <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 py-2 mt-auto">
      <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400">
        <span>Round {{ race.round }}</span>
        <span class="flex items-center">
          <i class="fas fa-chevron-right"></i>
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import CountryFlag from '@/components/CountryFlag.vue';

const props = defineProps({
  race: {
    type: Object,
    required: true
  }
});

const timeRemaining = ref(null);
const timerId = ref(null);

function formatDate(dateString) {
  if (!dateString) return 'TBA';
  const date = new Date(dateString);
  return date.toLocaleDateString('es-ES', {
    day: 'numeric',
    month: 'short'
  });
}

function getStatusClass(status) {
  if (!status) return 'bg-blue-600 text-white';
  
  const statusLower = status.toLowerCase();
  
  if (statusLower === 'completed' || statusLower === 'finished') {
    return 'bg-green-600 dark:bg-green-800 text-white';
  }
  
  if (statusLower === 'in progress' || statusLower === 'ongoing') {
    return 'bg-yellow-500 dark:bg-yellow-700 text-white';
  }
  
  if (statusLower === 'cancelled' || statusLower === 'postponed') {
    return 'bg-red-600 dark:bg-red-800 text-white';
  }
  
  return 'bg-blue-600 dark:bg-blue-800 text-white';
}

// Verificar si la carrera está próxima (en el futuro)
const isUpcoming = computed(() => {
  if (!props.race.race_date) return false;
  
  const now = new Date();
  const raceDate = new Date(props.race.race_date);
  
  return raceDate > now;
});

// Calcular el texto para la cuenta atrás
const countdownText = computed(() => {
  if (!timeRemaining.value) return '';
  
  if (timeRemaining.value.days > 0) {
    return `${timeRemaining.value.days}d until race`;
  } else if (timeRemaining.value.hours > 0) {
    return `${timeRemaining.value.hours}h ${timeRemaining.value.minutes}m until race`;
  } else {
    return `${timeRemaining.value.minutes}m until race`;
  }
});

// Calcular el tiempo restante
function calculateTimeRemaining() {
  if (!props.race.race_date) {
    timeRemaining.value = null;
    return;
  }
  
  const now = new Date();
  const targetDate = new Date(props.race.race_date);
  const diff = targetDate - now;
  
  // Si la fecha ya pasó
  if (diff <= 0) {
    timeRemaining.value = null;
    return;
  }
  
  // Calcular días, horas, minutos y segundos
  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((diff % (1000 * 60)) / 1000);
  
  timeRemaining.value = { days, hours, minutes, seconds };
}

// Configurar el temporizador al montar el componente, solo si la carrera está próxima
onMounted(() => {
  if (isUpcoming.value) {
    calculateTimeRemaining();
    timerId.value = setInterval(calculateTimeRemaining, 60000); // Actualizar cada minuto
  }
});

// Limpiar el temporizador al desmontar el componente
onBeforeUnmount(() => {
  if (timerId.value) {
    clearInterval(timerId.value);
  }
});
</script>