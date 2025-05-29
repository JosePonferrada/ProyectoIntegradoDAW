<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  data: {
    type: Object,
    default: () => ({ nextRace: null })
  }
});

const countdown = ref({
  days: '00',
  hours: '00',
  minutes: '00',
  seconds: '00'
});

const hasRace = computed(() => props.data.nextRace && props.data.nextRace.race_date);

const raceDate = computed(() => {
  if (!hasRace.value) return null;
  return new Date(props.data.nextRace.race_date);
});

const updateCountdown = () => {
  if (!hasRace.value) return;
  
  const now = new Date();
  const raceTime = new Date(props.data.nextRace.race_date);
  
  // Si la carrera ya pasó, muestra ceros
  if (now >= raceTime) {
    countdown.value = { days: '00', hours: '00', minutes: '00', seconds: '00' };
    return;
  }
  
  const diff = raceTime - now;
  
  // Calcular días, horas, minutos y segundos
  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((diff % (1000 * 60)) / 1000);
  
  // Formatear los números para que siempre tengan dos dígitos
  countdown.value = {
    days: days.toString().padStart(2, '0'),
    hours: hours.toString().padStart(2, '0'),
    minutes: minutes.toString().padStart(2, '0'),
    seconds: seconds.toString().padStart(2, '0')
  };
};

// Formatea la fecha en inglés
const formattedDate = computed(() => {
  if (!hasRace.value) return '';
  
  // Usar locale 'en-US' o 'en-GB' para forzar el inglés
  return new Date(props.data.nextRace.race_date).toLocaleDateString('en-US', { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
});

// Intervalo para actualizar el contador cada segundo
let countdownInterval = null;

onMounted(() => {
  updateCountdown(); // Actualizar inmediatamente al montar
  countdownInterval = setInterval(updateCountdown, 1000);
});

onBeforeUnmount(() => {
  if (countdownInterval) {
    clearInterval(countdownInterval);
  }
});
</script>

<template>
  <div class="p-4 text-center">
    <div v-if="hasRace">
      <h3 class="text-lg font-semibold mb-2">{{ props.data.nextRace.name }}</h3>
      <div class="flex justify-center space-x-2 my-3">
        <div class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded-md">
          <span class="text-2xl font-bold">{{ countdown.days }}</span>
          <p class="text-xs text-gray-500 dark:text-gray-400">days</p>
        </div>
        <div class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded-md">
          <span class="text-2xl font-bold">{{ countdown.hours }}</span>
          <p class="text-xs text-gray-500 dark:text-gray-400">hours</p>
        </div>
        <div class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded-md">
          <span class="text-2xl font-bold">{{ countdown.minutes }}</span>
          <p class="text-xs text-gray-500 dark:text-gray-400">min</p>
        </div>
        <div class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded-md">
          <span class="text-2xl font-bold">{{ countdown.seconds }}</span>
          <p class="text-xs text-gray-500 dark:text-gray-400">sec</p>
        </div>
      </div>
      <!-- Usar la fecha formateada en inglés -->
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        {{ formattedDate }}
      </p>
      <p class="text-sm" v-if="props.data.nextRace.circuit_name">{{ props.data.nextRace.circuit_name }}</p>
    </div>
    <p v-else class="text-gray-500 dark:text-gray-400 py-8">No upcoming race data for countdown.</p>
  </div>
</template>