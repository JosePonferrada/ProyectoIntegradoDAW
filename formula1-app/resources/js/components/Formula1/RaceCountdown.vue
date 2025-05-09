<template>
  <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg shadow-lg overflow-hidden">
    <div class="p-4 flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
        Next Race Weekend
      </h3>
      <span class="px-2 py-1 bg-blue-600 dark:bg-blue-800 text-white rounded-full text-xs font-medium">
        {{ race?.status || 'Scheduled' }}
      </span>
    </div>
    
    <div v-if="race" class="px-4 pb-4">
      <div>
        <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ race.name }}</h4>
        <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
          <CountryFlag v-if="race.circuit?.country" :countryCode="race.circuit.country.code" class="h-4 w-5 mr-2" />
          {{ race.circuit?.name }}, {{ race.circuit?.country?.name }}
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          {{ formatEventDate(race) }}
        </div>
      </div>
      
      <div v-if="timeRemaining" class="mt-4">
        <div class="grid grid-cols-4 gap-2 text-center">
          <div class="bg-gray-100 dark:bg-gray-800 rounded-md p-2">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ timeRemaining.days }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">DAYS</div>
          </div>
          <div class="bg-gray-100 dark:bg-gray-800 rounded-md p-2">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ timeRemaining.hours }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">HOURS</div>
          </div>
          <div class="bg-gray-100 dark:bg-gray-800 rounded-md p-2">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ timeRemaining.minutes }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">MINS</div>
          </div>
          <div class="bg-gray-100 dark:bg-gray-800 rounded-md p-2">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ timeRemaining.seconds }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">SECS</div>
          </div>
        </div>
        <div class="mt-3 text-center text-sm text-gray-500 dark:text-gray-400">
          until practice 1 start
        </div>
      </div>
    </div>
    
    <div v-else class="text-center p-4">
      <div class="text-gray-500 dark:text-gray-400 py-8">
        <i class="fas fa-calendar-times text-2xl mb-2"></i>
        <p>No upcoming races scheduled</p>
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
    default: null
  }
});

// Estado
const timeRemaining = ref(null);
const timerId = ref(null);

// Fechas calculadas para el fin de semana de carreras
const weekendEvents = computed(() => {
  if (!props.race?.race_date) return null;
  
  const raceDate = new Date(props.race.race_date);
  
  // Típicamente, P1 es el viernes (2 días antes)
  const practice1Date = new Date(raceDate);
  practice1Date.setDate(raceDate.getDate() - 2);
  practice1Date.setHours(10, 30, 0); // 10:30 AM típicamente
  
  // P2 suele ser el mismo viernes
  const practice2Date = new Date(raceDate);
  practice2Date.setDate(raceDate.getDate() - 2);
  practice2Date.setHours(14, 0, 0); // 2:00 PM típicamente
  
  // P3 suele ser el sábado
  const practice3Date = new Date(raceDate);
  practice3Date.setDate(raceDate.getDate() - 1);
  practice3Date.setHours(11, 0, 0); // 11:00 AM típicamente
  
  // Clasificación suele ser el sábado
  const qualifyingDate = new Date(raceDate);
  qualifyingDate.setDate(raceDate.getDate() - 1);
  qualifyingDate.setHours(14, 0, 0); // 2:00 PM típicamente
  
  return {
    practice1: practice1Date,
    practice2: practice2Date,
    practice3: practice3Date,
    qualifying: qualifyingDate,
    race: raceDate
  };
});

// Determinar el siguiente evento y su fecha
const nextEvent = computed(() => {
  if (!weekendEvents.value) return { type: null, date: null };
  
  const now = new Date();
  const events = [
    { type: 'practice 1', date: weekendEvents.value.practice1 },
    { type: 'practice 2', date: weekendEvents.value.practice2 },
    { type: 'practice 3', date: weekendEvents.value.practice3 },
    { type: 'qualifying', date: weekendEvents.value.qualifying },
    { type: 'race', date: weekendEvents.value.race }
  ];
  
  // Encontrar el próximo evento
  for (const event of events) {
    if (event.date > now) {
      return event;
    }
  }
  
  // Si todos los eventos ya pasaron, devolver el último evento (la carrera)
  return events[events.length - 1];
});

// Obtener el tipo del próximo evento
const nextEventType = computed(() => {
  return nextEvent.value?.type || 'race';
});

// Obtener el tipo del evento actual en progreso
const currentEventType = computed(() => {
  if (!weekendEvents.value) return 'Event';
  
  const now = new Date();
  
  // Verificar qué evento está en progreso actualmente
  if (isDateInProgress(weekendEvents.value.practice1, 1.5)) return 'Practice 1';
  if (isDateInProgress(weekendEvents.value.practice2, 1.5)) return 'Practice 2';
  if (isDateInProgress(weekendEvents.value.practice3, 1)) return 'Practice 3';
  if (isDateInProgress(weekendEvents.value.qualifying, 1.5)) return 'Qualifying';
  if (isDateInProgress(weekendEvents.value.race, 2)) return 'Race';
  
  return 'Event';
});

// Verificar si hay algún evento en progreso
const eventInProgress = computed(() => {
  if (!weekendEvents.value) return false;
  
  return isDateInProgress(weekendEvents.value.practice1, 1.5) ||
         isDateInProgress(weekendEvents.value.practice2, 1.5) ||
         isDateInProgress(weekendEvents.value.practice3, 1) ||
         isDateInProgress(weekendEvents.value.qualifying, 1.5) ||
         isDateInProgress(weekendEvents.value.race, 2);
});

// Función helper para verificar si una fecha está en progreso
function isDateInProgress(date, durationHours) {
  const now = new Date();
  const endTime = new Date(date);
  endTime.setHours(endTime.getHours() + durationHours);
  
  return now >= date && now <= endTime;
}

// Formatear fecha del evento
function formatEventDate(race) {
  if (!race.race_date) return 'TBA';
  
  // Mostrar el rango del fin de semana de carreras
  const raceDate = new Date(race.race_date);
  const practice1Date = new Date(raceDate);
  practice1Date.setDate(raceDate.getDate() - 2);
  
  const formatter = new Intl.DateTimeFormat('es-ES', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
  
  return `${formatter.format(practice1Date)} - ${formatter.format(raceDate)}`;
}

// Calcular el tiempo restante
function calculateTimeRemaining() {
  const targetDate = nextEvent.value?.date;
  
  if (!targetDate) {
    timeRemaining.value = null;
    return;
  }
  
  const now = new Date();
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

// Configurar el temporizador al montar el componente
onMounted(() => {
  calculateTimeRemaining();
  timerId.value = setInterval(calculateTimeRemaining, 1000);
});

// Limpiar el temporizador al desmontar el componente
onBeforeUnmount(() => {
  if (timerId.value) {
    clearInterval(timerId.value);
  }
});
</script>