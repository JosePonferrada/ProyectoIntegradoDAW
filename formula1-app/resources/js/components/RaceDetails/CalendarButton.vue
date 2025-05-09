<template>
  <div class="relative" ref="dropdown">
    <button 
      @click="toggleDropdown"
      class="flex items-center px-3 py-1.5 bg-f1-red hover:bg-red-700 text-white rounded-md text-sm font-medium transition-colors"
    >
      <i class="fas fa-calendar-plus mr-2"></i>
      Add to Calendar
      <i class="fas fa-chevron-down ml-2 text-xs"></i>
    </button>
    
    <!-- Menú desplegable - posicionado absolutamente debajo del botón -->
    <div 
      v-if="showOptions" 
      class="absolute right-0 top-full mt-1 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 z-20 border border-gray-200 dark:border-gray-700"
    >
      <div class="py-1">
        <a 
          :href="icsUrl" 
          download
          class="flex items-center px-4 py-2 text-sm text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <i class="fas fa-download mr-2"></i>
          Download .ics file
        </a>
        <a 
          :href="googleUrl" 
          target="_blank" 
          class="flex items-center px-4 py-2 text-sm text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <i class="fab fa-google mr-2"></i>
          Google Calendar
        </a>
        <a 
          :href="outlookUrl" 
          target="_blank" 
          class="flex items-center px-4 py-2 text-sm text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <i class="fab fa-microsoft mr-2"></i>
          Outlook Calendar
        </a>
        <a 
          :href="appleUrl" 
          target="_blank" 
          class="flex items-center px-4 py-2 text-sm text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <i class="fab fa-apple mr-2"></i>
          Apple Calendar
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  race: {
    type: Object,
    required: true
  }
});

const showOptions = ref(false);
const dropdown = ref(null);

const toggleDropdown = () => {
  showOptions.value = !showOptions.value;
};

// URLs para servicios de calendarios
const icsUrl = computed(() => {
  if (!props.race || !props.race.id) return '#';
  return `/races/${props.race.id}/calendar.ics`;
});

// URL for Google Calendar
const googleUrl = computed(() => {
  if (!props.race?.race_date || !props.race?.start_time) return '#';
  
  // Extraer partes de fecha y hora
  const datePart = props.race.race_date.substring(0, 10); // YYYY-MM-DD
  const timePart = props.race.start_time.substring(11, 16); // HH:MM
  
  // Combinar fecha y hora
  const startDate = new Date(`${datePart}T${timePart}:00`);
  const endDate = new Date(startDate.getTime() + 2 * 60 * 60 * 1000); // +2 horas
  
  const formatDate = (date) => {
    return date.toISOString().replace(/-|:|\.\d+/g, '');
  };
  
  const start = formatDate(startDate);
  const end = formatDate(endDate);
  const title = encodeURIComponent(`Formula 1: ${props.race.name}`);
  const location = encodeURIComponent(getLocation());
  const details = encodeURIComponent(`Formula 1 Grand Prix race`);
  
  return `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${title}&dates=${start}/${end}&location=${location}&details=${details}`;
});

// URL para Outlook Calendar (versión mejorada)
const outlookUrl = computed(() => {
  if (!props.race?.race_date) return '#';
  
  // Extraer partes de fecha y hora
  const datePart = props.race.race_date.substring(0, 10); // YYYY-MM-DD
  let timePart = '15:00';
  
  if (props.race.start_time) {
    timePart = props.race.start_time.substring(11, 16); // HH:MM
  } else if (props.race.race_time) {
    timePart = props.race.race_time;
  }
  
  // Crear objetos Date para inicio y fin
  const raceDate = new Date(`${datePart}T${timePart}:00`);
  const endDate = new Date(raceDate.getTime() + 2 * 60 * 60 * 1000); // +2 horas
  
  // Formatear fechas como lo espera Outlook
  const startStr = raceDate.toISOString();
  const endStr = endDate.toISOString();
  
  // Limpiar y codificar parámetros
  const subject = encodeURIComponent(`Formula 1: ${props.race.name}`);
  
  // Simplificar la descripción para evitar caracteres especiales
  const body = encodeURIComponent(`Formula 1 Grand Prix race`);
  
  // Simplificar la ubicación (solo nombre del circuito)
  let location = '';
  if (props.race.circuit?.name) {
    location = encodeURIComponent(props.race.circuit.name);
  }
  
  // Construir URL en formato más compatible con Outlook
  return `https://outlook.live.com/calendar/0/deeplink/compose?subject=${subject}&startdt=${startStr}&enddt=${endStr}&body=${body}&location=${location}`;
});

const appleUrl = computed(() => icsUrl.value);

// Función auxiliar para obtener la ubicación
function getLocation() {
  if (!props.race || !props.race.circuit) return '';
  
  let location = props.race.circuit.name || '';
  
  if (props.race.circuit.location) {
    location += ', ' + props.race.circuit.location;
  }
  
  if (props.race.circuit.country && props.race.circuit.country.name) {
    location += ', ' + props.race.circuit.country.name;
  }
  
  return location;
}

// Cerrar el dropdown cuando se hace clic fuera
function handleClickOutside(event) {
  if (dropdown.value && !dropdown.value.contains(event.target)) {
    showOptions.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>