<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, onMounted, computed, onUnmounted } from 'vue';

// Add user timezone preference
const userTimezone = ref(localStorage.getItem('preferredTimezone') || Intl.DateTimeFormat().resolvedOptions().timeZone);

// Common timezones for selection
const commonTimezones = [
  { value: 'Europe/Madrid', label: 'Madrid (CET/CEST)' },
  { value: 'UTC', label: 'UTC' },
  { value: 'America/New_York', label: 'New York (ET)' },
  { value: 'America/Los_Angeles', label: 'Los Angeles (PT)' },
  { value: 'Asia/Tokyo', label: 'Tokyo (JST)' },
  { value: 'Australia/Sydney', label: 'Sydney (AEST)' },
];

// Show timezone selector
const showTimezoneSelector = ref(false);

// Get current timezone display name
const currentTimezoneName = computed(() => {
  const found = commonTimezones.find(tz => tz.value === userTimezone.value);
  return found ? found.label : userTimezone.value;
});

// Save timezone preference
function setTimezone(timezone) {
  userTimezone.value = timezone;
  localStorage.setItem('preferredTimezone', timezone);
  showTimezoneSelector.value = false;
}

// Add click-outside handler
const closeDropdown = (e) => {
  // Don't close if clicking the button itself (it has its own toggle handler)
  if (e.target.closest('.timezone-selector-button')) return;
  showTimezoneSelector.value = false;
};

function formatDate(dateString, timeString) {
  if (!dateString) return 'Date TBD';

  try {
    // 1. Parsear componentes de fecha de dateString (esperado YYYY-MM-DD o similar)
    //    y componentes de hora de timeString (esperado HH:MM o HH:MM:SS)
    //    Estos representan la fecha y hora en "Europe/Madrid".
    const dateMatch = String(dateString).match(/(\d{4})[-/](\d{1,2})[-/](\d{1,2})/);
    if (!dateMatch) {
      console.error("Invalid dateString format:", dateString);
      return `${dateString} ${timeString || ''} (Invalid DateFmt)`;
    }
    const year = parseInt(dateMatch[1]);
    const month_1_indexed = parseInt(dateMatch[2]); // Mes como 1-12
    const day = parseInt(dateMatch[3]);

    let sourceHour = 0;
    let sourceMinute = 0;
    if (timeString) {
      const timeMatch = String(timeString).match(/(\d{1,2}):(\d{1,2})/); // Captura HH:MM
      if (timeMatch) {
        sourceHour = parseInt(timeMatch[1]);
        sourceMinute = parseInt(timeMatch[2]);
      } else {
        console.warn("Could not parse timeString, using midnight:", timeString);
      }
    }

    // 2. Determinar el offset de "Europe/Madrid" desde UTC para la fecha dada.
    //    Usamos el mediodía UTC de esa fecha como punto de referencia estable para evitar
    //    problemas con cambios de hora justo a medianoche.
    const refDateUTC = new Date(Date.UTC(year, month_1_indexed - 1, day, 12, 0, 0)); // Mes 0-indexado para Date.UTC

    // Función auxiliar para obtener partes de fecha/hora en una zona horaria específica
    const getPartsInTimezone = (dateObj, tz) => {
      const formatter = new Intl.DateTimeFormat('en-US', { // Usar 'en-US' para parseo numérico consistente
        year: 'numeric', month: 'numeric', day: 'numeric',
        hour: 'numeric', // No usar hour12 aquí para obtener 0-23 si es posible
        hourCycle: 'h23', // Solicitar explícitamente formato 0-23
        timeZone: tz
      });
      const parts = formatter.formatToParts(dateObj);
      return {
        year: parseInt(parts.find(p => p.type === 'year').value),
        month: parseInt(parts.find(p => p.type === 'month').value), // 1-12
        day: parseInt(parts.find(p => p.type === 'day').value),
        hour: parseInt(parts.find(p => p.type === 'hour').value) // 0-23
      };
    };

    const partsAtUTCRef = getPartsInTimezone(refDateUTC, 'UTC'); // Hora en UTC (debería ser 12)
    const partsAtMadridRef = getPartsInTimezone(refDateUTC, 'Europe/Madrid'); // Misma hora UTC, pero vista en Madrid

    // 3. Calcular el offset en horas. (Hora de Madrid - Hora UTC) para el mismo instante.
    let hourOffset = partsAtMadridRef.hour - partsAtUTCRef.hour;

    // Ajustar el offset si hay un cambio de día entre Madrid y UTC para el mismo instante.
    // (ej. Madrid es 01:00 del día D+1, mientras UTC es 23:00 del día D)
    const madridRefDateObj = new Date(partsAtMadridRef.year, partsAtMadridRef.month - 1, partsAtMadridRef.day);
    const utcRefDateObj = new Date(partsAtUTCRef.year, partsAtUTCRef.month - 1, partsAtUTCRef.day);

    if (madridRefDateObj.getTime() > utcRefDateObj.getTime()) { // Madrid está en un día posterior
      hourOffset += 24;
    } else if (madridRefDateObj.getTime() < utcRefDateObj.getTime()) { // Madrid está en un día anterior
      hourOffset -= 24;
    }
    // hourOffset ahora es el offset de "Europe/Madrid" desde UTC (ej: +1 para CET, +2 para CEST)

    // 4. Calcular la hora UTC objetivo restando el offset de la hora original de Madrid.
    //    Date.UTC maneja correctamente el desbordamiento/subdesbordamiento de horas ajustando el día.
    const targetDateInUTC = new Date(Date.UTC(year, month_1_indexed - 1, day, sourceHour - hourOffset, sourceMinute, 0));

    // 5. Formatear esta fecha UTC final a la zona horaria deseada por el usuario.
    //    Usamos 'en-GB' como base para un formato común y luego extraemos las partes.
    const displayFormatter = new Intl.DateTimeFormat('en-GB', {
      year: 'numeric',
      month: 'short', // Para "MMM" (ej. "Jul")
      day: '2-digit', // Para "dd" (ej. "05")
      hour: '2-digit',
      minute: '2-digit',
      hourCycle: 'h23', // Formato 24h
      timeZone: userTimezone.value // La zona horaria seleccionada por el usuario
    });

    const formattedParts = displayFormatter.formatToParts(targetDateInUTC);
    const fDay = formattedParts.find(p => p.type === 'day').value;
    const fMonth = formattedParts.find(p => p.type === 'month').value;
    const fYear = formattedParts.find(p => p.type === 'year').value;
    const fHour = formattedParts.find(p => p.type === 'hour').value;
    const fMinute = formattedParts.find(p => p.type === 'minute').value;

    return `${fDay} ${fMonth} ${fYear}, ${fHour}:${fMinute}`;

  } catch (error) {
    console.error("Error formatting date (without library):", error, { dateString, timeString, userTimezone: userTimezone.value });
    // Fallback simple
    let fallbackDisplay = dateString ? String(dateString).split('T')[0] : 'Date TBD';
    if (timeString) {
        const timeOnlyMatch = String(timeString).match(/(\d{2}:\d{2})/);
        fallbackDisplay += timeOnlyMatch ? ` ${timeOnlyMatch[0]}` : ` ${timeString}`;
    }
    return `${fallbackDisplay} (TZ Conv. Error)`;
  }
}

function isValidDriverName(firstName, lastName) {
  return firstName && lastName;
}

function hasPredictionForRace(raceId) {
  return props.userPredictions.some(prediction => prediction.race_id === raceId);
}

// Función mejorada para verificar si la carrera está bloqueada
function isRaceLocked(race) {
  if (!race || !race.race_date) return false;
  
  try {
    // Extraer solo la parte de fecha
    const raceDate = new Date(race.race_date).toISOString().split('T')[0];
    
    let raceTime = "00:00:00";
    // Extraer solo la parte de tiempo
    if (race.start_time) {
      const timeMatch = race.start_time.toString().match(/(\d{1,2}):(\d{1,2})/);
      if (timeMatch) {
        raceTime = `${timeMatch[1].padStart(2, '0')}:${timeMatch[2].padStart(2, '0')}:00`;
      }
    }
    
    // Crear la fecha combinada correctamente
    const raceDateTime = new Date(`${raceDate}T${raceTime}`);
    const now = new Date();
    
    console.log('Race date/time:', raceDateTime);
    console.log('Current time:', now);
    console.log('Is race locked?', now >= raceDateTime);
    
    return now >= raceDateTime;
  } catch (error) {
    console.error('Error checking if race is locked:', error);
    return false;
  }
}

const props = defineProps({
  upcomingRaces: Object, // <--- CAMBIAR A Object
  userPredictions: Array
});

const showDebug = ref(false);
const showScoringInfo = ref(false);

onMounted(() => {
  // Add event listener for closing dropdown
  document.addEventListener('click', closeDropdown);
  
  // Vemos qué datos tenemos en las props
  console.log('Upcoming', props.upcomingRaces);
  console.log('User Predictions', props.userPredictions);
  console.log('Everthing', props);
  console.log('Carreras terminadas', props.userPredictions.filter(p => p.is_locked));
  console.log('Upcoming Races Data for Template:', props.upcomingRaces?.data);
  if (props.upcomingRaces?.data) {
    props.upcomingRaces.data.forEach((race, index) => {
      console.log(`Race ${index} ID:`, race?.race_id, 'Race Object:', race);
    });
  }
});

onUnmounted(() => {
  // Clean up event listener
  document.removeEventListener('click', closeDropdown);
});

// Estado para pestañas y acordeones
const activeTab = ref('upcoming');
const expandedPredictions = ref([]);
const expandedSeasons = ref([new Date().getFullYear()]);  // Expandir el año actual por defecto
const upcomingPage = ref(1);
const predictionsPage = ref(1);
const itemsPerPage = ref(6);

// Calcular elementos paginados
const paginatedUpcomingRaces = computed(() => {
  // Acceder a los datos a través de props.upcomingRaces.data
  if (props.upcomingRaces && props.upcomingRaces.data) { // <--- AÑADIR VERIFICACIÓN
    const start = (upcomingPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return props.upcomingRaces.data.slice(start, end); // <--- USAR .data
  }
  return []; // Devolver array vacío si no hay datos
});

const totalUpcomingPages = computed(() => {
  return Math.ceil((props.upcomingRaces?.data?.length || 0) / itemsPerPage.value) || 1;
});

// Cambiar de página
function nextPage(pageName) {
  if (pageName === 'upcoming' && upcomingPage.value < totalUpcomingPages.value) {
    upcomingPage.value++;
  } else if (pageName === 'predictions' && predictionsPage.value < totalPredictionPages.value) {
    predictionsPage.value++;
  }
}

function prevPage(pageName) {
  if (pageName === 'upcoming' && upcomingPage.value > 1) {
    upcomingPage.value--;
  } else if (pageName === 'predictions' && predictionsPage.value > 1) {
    predictionsPage.value--;
  }
}

// Agrupar predicciones por año
const groupedPredictions = computed(() => {
  const groups = {};
  props.userPredictions.forEach(prediction => {
    // Extraer el año de la carrera
    const year = prediction.race?.race_date 
      ? new Date(prediction.race.race_date).getFullYear() 
      : 'Unknown';
    
    if (!groups[year]) groups[year] = [];
    groups[year].push(prediction);
  });
  
  // Ordenar años en orden descendente
  return Object.fromEntries(
    Object.entries(groups).sort((a, b) => b[0] - a[0])
  );
});

// Toggle para acordeones
function togglePrediction(id) {
  if (expandedPredictions.value.includes(id)) {
    expandedPredictions.value = expandedPredictions.value.filter(i => i !== id);
  } else {
    expandedPredictions.value.push(id);
  }
}

function toggleSeason(year) {
  if (expandedSeasons.value.includes(year)) {
    expandedSeasons.value = expandedSeasons.value.filter(y => y !== year);
  } else {
    expandedSeasons.value.push(year);
  }
}
</script>

<template>
  <AppLayout title="Predictions">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <!-- Sistema de pestañas -->
          <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
            <div class="-mb-px flex space-x-8">
              <button 
                @click="activeTab = 'upcoming'" 
                :class="[
                  activeTab === 'upcoming' ? 'border-red-500 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 dark:text-gray-400',
                  'pb-2 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Upcoming Races
              </button>
              <button 
                @click="activeTab = 'predictions'" 
                :class="[
                  activeTab === 'predictions' ? 'border-red-500 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 dark:text-gray-400',
                  'pb-2 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                My Predictions
              </button>
              <Link 
                :href="route('predictions.leaderboard')"
                class="pb-2 px-1 border-b-2 border-transparent text-gray-500 dark:text-gray-400 font-medium text-sm"
              >
                <i class="fas fa-trophy mr-1"></i>
                Leaderboard
              </Link>
            </div>
          </div>

          <!-- Contenido de la pestaña "Upcoming Races" -->
          <div v-if="activeTab === 'upcoming'" class="mb-8">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-semibold dark:text-white">Upcoming races</h2>
              
              <!-- Selector de Zona Horaria -->
              <div class="relative timezone-selector-button">
                <button 
                  @click="showTimezoneSelector = !showTimezoneSelector"
                  class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                >
                  <i class="fas fa-globe-europe mr-1"></i>
                  {{ currentTimezoneName }}
                  <i :class="showTimezoneSelector ? 'fas fa-chevron-up ml-1' : 'fas fa-chevron-down ml-1'" class="text-xs"></i>
                </button>
                <div 
                  v-if="showTimezoneSelector" 
                  class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 z-50"
                >
                  <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                    <a 
                      v-for="tz in commonTimezones" 
                      :key="tz.value"
                      @click="setTimezone(tz.value)"
                      href="#" 
                      :class="[
                        'block px-4 py-2 text-sm',
                        userTimezone === tz.value ? 'bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 hover:text-gray-900 dark:hover:text-white'
                      ]" 
                      role="menuitem"
                    >
                      {{ tz.label }}
                    </a>
                  </div>
                </div>
              </div>
              <!-- Fin Selector de Zona Horaria -->

            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
              Times shown in {{ currentTimezoneName }}
            </p>
            
            <!-- Grid de carreras paginado -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div v-for="race in paginatedUpcomingRaces" :key="race.race_id" class="border rounded-lg p-4 dark:border-gray-600 mb-4">
                <h3 class="font-bold dark:text-white">{{ race?.name || 'Upcoming Race' }}</h3>
                <p class="text-gray-600 dark:text-gray-300 flex items-center">
                  {{ formatDate(race?.race_date, race?.start_time) || 'Date TBD' }}
                </p>
                <p class="text-gray-600 dark:text-gray-300 mb-4">{{ race?.circuit?.name || 'Circuit TBD' }}</p>
                
                <template v-if="isRaceLocked(race)">
                  <button 
                    disabled
                    class="mt-3 inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed"
                  >
                    <i class="fas fa-lock mr-1"></i>
                    Predictions Closed
                  </button>
                </template>
                <template v-else>
                  <Link 
                    :href="route('predictions.create', { race: race })"
                    class="mt-3 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-300 disabled:opacity-25 transition"
                  >
                    <span v-if="hasPredictionForRace(race.id)">Edit Prediction</span>
                    <span v-else>Make Prediction</span>
                  </Link>
                </template>
              </div>
            </div>
            
            <div v-if="!props.upcomingRaces?.data?.length" 
                 class="text-gray-600 dark:text-gray-300">
              No upcoming races scheduled.
            </div>

            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
              Times shown in {{ currentTimezoneName }}
            </p>
          </div>
          
          <!-- Contenido de la pestaña "My Predictions" -->
          <div v-if="activeTab === 'predictions'">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-semibold dark:text-white">My predictions</h2>
              <button @click="showScoringInfo = !showScoringInfo" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                <i class="fas fa-info-circle mr-1"></i>How are points calculated?
              </button>
            </div>
            <!-- Modal o div condicional para showScoringInfo -->
            <div v-if="showScoringInfo" class="mb-4 p-3 bg-gray-100 dark:bg-gray-700 rounded-md text-sm text-gray-700 dark:text-gray-300">
              <div class="flex justify-between items-center mb-2">
                <h4 class="font-semibold">Scoring System:</h4>
                <button @click="showScoringInfo = false" class="text-red-500 text-xl">&times;</button>
              </div>
              <ul class="list-disc list-inside ml-4 text-xs space-y-1">
                <li>Correct P1: <strong>3 points</strong></li>
                <li>Correct P2-P10 (each): <strong>1 point</strong></li>
                <li>Correct Full Podium (P1, P2, P3 in order): <strong>10 points</strong></li>
                <li>Correct Fastest Lap Driver: <strong>1 point</strong></li>
                <li>DNFs: Up to <strong>10 points</strong>. Points are awarded based on how close your prediction is to the actual number of DNFs. If you predict fewer DNFs than actual, you get 10 minus the difference. If you predict more, the penalty is doubled (10 minus twice the difference). Minimum 0 points.</li>
              </ul>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
              Times shown in {{ currentTimezoneName }}
            </p>
            
            <!-- Predicciones agrupadas por temporada -->
            <div v-for="(predictions, year) in groupedPredictions" :key="year" class="mb-4">
              <button 
                @click="toggleSeason(year)"
                class="w-full flex justify-between items-center p-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg"
              >
                <span class="font-semibold dark:text-white">{{ year }} Season</span>
                <i :class="expandedSeasons.includes(year) ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="text-gray-500"></i>
              </button>
              
              <!-- Predicciones de esta temporada (acordeón) -->
              <div v-if="expandedSeasons.includes(year)" class="mt-3 space-y-3">
                <div v-for="prediction in predictions" :key="prediction.id" 
                     class="border rounded-lg p-4 dark:border-gray-600">
                  <div class="flex justify-between items-start">
                    <!-- Información básica de la predicción -->
                    <div>
                      <h3 class="font-bold dark:text-white">{{ prediction?.race?.name || 'Race' }}</h3>
                      <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ formatDate(prediction?.race?.race_date, prediction?.race?.start_time) || 'Date not set' }}
                      </p>
                    </div>
                    <!-- Puntuación -->
                    <div class="text-right bg-gray-100 dark:bg-gray-700 p-2 rounded">
                      <span class="text-xl font-bold dark:text-white">{{ prediction?.points || 0 }} pts</span>
                      <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ prediction?.is_locked ? 'Completed' : 'Pending' }}
                      </p>
                    </div>
                  </div>
                  
                  <!-- Botón para expandir/colapsar detalles -->
                  <button 
                    @click="togglePrediction(prediction.id)"
                    class="mt-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                  >
                    <i :class="expandedPredictions.includes(prediction.id) ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="mr-1"></i>
                    {{ expandedPredictions.includes(prediction.id) ? 'Hide details' : 'Show details' }}
                  </button>
                  
                  <!-- Detalles expandibles de la predicción -->
                  <div v-if="expandedPredictions.includes(prediction.id)" class="mt-3">
                    <!-- Detalles completos de la predicción -->
                    <div v-if="prediction?.positions?.length">
                      <h4 class="font-semibold dark:text-white">Predicted Top 10:</h4>
                      <ol class="list-decimal list-inside mt-2 grid grid-cols-1 md:grid-cols-2">
                        <li v-for="pos in prediction.positions" :key="pos.id" class="dark:text-gray-300">
                          <template v-if="isValidDriverName(pos?.driver?.first_name, pos?.driver?.last_name)">
                            {{ pos?.driver?.first_name }} {{ pos?.driver?.last_name }}
                          </template>
                          <template v-else>
                            Driver {{ pos.position }}
                          </template>
                        </li>
                      </ol>
                      
                      <div class="mt-2 dark:text-gray-300 grid grid-cols-1 md:grid-cols-2 gap-2">
                        <p><span class="font-semibold">Predicted DNFs:</span> {{ prediction?.dnf_count || 0 }}</p>
                        <p><span class="font-semibold">Fastest lap: </span> 
                          <template v-if="isValidDriverName(prediction?.fastest_lap_driver?.first_name, prediction?.fastest_lap_driver?.last_name)">
                            {{ prediction?.fastest_lap_driver?.first_name }} {{ prediction?.fastest_lap_driver?.last_name }}
                          </template>
                          <template v-else>
                            No selection
                          </template>
                        </p>
                      </div>
                    </div>

                    <!-- Resultados reales -->
                    <div v-if="prediction.is_locked && prediction.race?.results?.length">
                      <h4 class="font-semibold dark:text-white mt-4">Actual Results:</h4>
                      <ol class="list-decimal list-inside mt-1 space-y-1 text-sm">
                        <li v-for="actual_result in prediction.race.results.filter(r => r.status === 'Finished' || !isNaN(parseInt(r.position))).sort((a, b) => a.position - b.position).slice(0, 10)" 
                            :key="`actual-${actual_result.driver_id}`" 
                            :class="{
                              'dark:text-green-400 font-bold': prediction.positions.find(p => p.position === actual_result.position && p.driver_id === actual_result.driver_id),
                              'dark:text-gray-300': !prediction.positions.find(p => p.position === actual_result.position && p.driver_id === actual_result.driver_id)
                            }">
                          {{ actual_result.position }}. {{ actual_result.driver?.first_name }} {{ actual_result.driver?.last_name }}
                          <span v-if="prediction.positions.find(p => p.position === actual_result.position && p.driver_id === actual_result.driver_id)" class="text-green-500 ml-1">(Correct!)</span>
                        </li>
                      </ol>
                      
                      <div class="mt-3 space-y-1 text-sm dark:text-gray-300">
                        <p><span class="font-medium">Actual DNFs:</span> 
                          <span :class="{'dark:text-green-400 font-bold': prediction.dnf_count === prediction.race?.actual_dnf_count}">
                            {{ prediction.race?.actual_dnf_count !== undefined ? prediction.race?.actual_dnf_count : 'N/A' }}
                            <span v-if="prediction.dnf_count === prediction.race?.actual_dnf_count && prediction.race?.actual_dnf_count !== undefined" class="text-green-500 ml-1">(Correct!)</span>
                          </span>
                        </p>
                        <p><span class="font-medium">Fastest Lap: </span> 
                          <template v-if="prediction.race.actual_fastest_lap_driver && isValidDriverName(prediction.race.actual_fastest_lap_driver?.first_name, prediction.race.actual_fastest_lap_driver?.last_name)">
                            <span :class="{'dark:text-green-400 font-bold': prediction.fastest_lap_driver_id === prediction.race.actual_fastest_lap_driver?.driver_id}">
                              {{ prediction.race.actual_fastest_lap_driver.first_name }} {{ prediction.race.actual_fastest_lap_driver.last_name }}
                              <span v-if="prediction.fastest_lap_driver_id === prediction.race.actual_fastest_lap_driver?.driver_id" class="text-green-500 ml-1">(Correct!)</span>
                            </span>
                          </template>
                          <template v-else>
                            Not recorded or N/A
                          </template>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Add this after your existing template content -->
          <div v-if="showDebug" class="mt-8 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
            <div class="flex justify-between mb-4">
              <h3 class="font-bold dark:text-white">Debug Information</h3>
              <button @click="showDebug = false" class="text-red-500 hover:text-red-700">Close</button>
            </div>
            
            <div class="mb-4">
              <h4 class="font-semibold dark:text-white">First Prediction Sample:</h4>
              <pre class="text-xs overflow-auto bg-white dark:bg-gray-800 p-2 rounded">{{ JSON.stringify(userPredictions[0], null, 2) }}</pre>
            </div>
            
            <div class="mb-4" v-if="userPredictions[0]?.positions?.length">
              <h4 class="font-semibold dark:text-white">First Position Sample:</h4>
              <pre class="text-xs overflow-auto bg-white dark:bg-gray-800 p-2 rounded">{{ JSON.stringify(userPredictions[0].positions[0], null, 2) }}</pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>