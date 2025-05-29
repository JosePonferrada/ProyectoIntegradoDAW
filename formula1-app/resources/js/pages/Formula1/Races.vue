<template>
  <AppLayout title="Race Calendar">
    <Head title="Race Calendar" />
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Contenedor principal con estilo coherente con Standings.vue -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
          <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Race Calendar</h1>
            
            <!-- Solo mostrar a administradores -->
            <div v-if="isAdmin" class="flex space-x-3">
              <a href="/formula1/races/create" 
                 class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md font-medium text-sm inline-flex items-center">
                <i class="fas fa-plus mr-1"></i> Add Race
              </a>
              <a :href="route('formula1.circuits.manage')" 
                 class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md font-medium text-sm inline-flex items-center">
                <i class="fas fa-road mr-1"></i> Manage Circuits
              </a>
            </div>
          </div>
          
          <!-- Sección de cuenta atrás para la próxima carrera -->
          <div class="mb-8" v-if="nextRace">
            <RaceCountdown :race="nextRace" />
          </div>
          
          <!-- Filtros con estilo actualizado y consistente -->
          <div class="flex flex-wrap gap-4 mb-6">
            <!-- Selector de temporada con estilo consistente -->
            <div class="relative w-40">
              <select v-model="selectedSeason" @change="loadRaces" 
                      class="bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white rounded-md py-2 px-4 appearance-none focus:ring-red-500 focus:border-red-500 block w-full">
                <option v-for="season in seasons" :key="season.id" :value="season.id">
                  {{ season.year }}
                </option>
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            
            <!-- Selector de filtro de estado con estilo consistente -->
            <div class="relative w-40">
              <select v-model="statusFilter" 
                      class="bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white rounded-md py-2 px-4 appearance-none focus:ring-red-500 focus:border-red-500 block w-full">
                <option value="all">All Races</option>
                <option value="in_progress">In Progress</option>
                <option value="upcoming">Upcoming</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>

            <!-- INICIO: Selector de Zona Horaria -->
            <div class="relative timezone-selector-button w-auto sm:w-48 ml-auto">
              <button 
                @click="showTimezoneSelector = !showTimezoneSelector"
                class="w-full text-left px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md text-sm text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 flex items-center justify-between"
              >
                <span class="truncate">
                  <i class="fas fa-globe-europe mr-1"></i>
                  {{ currentTimezoneName }}
                </span>
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
            <!-- FIN: Selector de Zona Horaria -->
          </div>
          
          <!-- Estado de carga -->
          <div v-if="loading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-500 mx-auto"></div>
            <p class="mt-3 text-gray-600 dark:text-gray-400">Loading race calendar...</p>
          </div>
          
          <!-- No hay carreras -->
          <div v-else-if="!filteredRaces.length" class="text-center py-12 bg-gray-50 dark:bg-gray-900 rounded-md">
            <div class="text-gray-500 dark:text-gray-400">
              <i class="fas fa-info-circle text-4xl mb-4"></i>
              <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No races found</h3>
              <p class="mt-2">Try changing your filters or check back later.</p>
            </div>
          </div>
          
          <!-- Lista de carreras con estilo actualizado -->
          <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <RaceCard 
              v-for="race in filteredRaces" 
              :key="race.id" 
              :race="race"
              :user-timezone="userTimezone"
              :format-date-fn="formatDate"
              @click="goToRaceDetails(race.id)" 
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from 'vue';
import { router, usePage, Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import RaceCard from '@/components/Formula1/RaceCard.vue';
import RaceCountdown from '@/components/Formula1/RaceCountdown.vue';
import axios from 'axios';

// Estado
const races = ref([]);
const seasons = ref([]);
const selectedSeason = ref(null);
const loading = ref(true);
const statusFilter = ref('all');

// Comprobación de admin usando el mismo método que en Constructors.vue
const isAdmin = computed(() => {
  const user = usePage().props.auth.user;
  return user && user.role === 'admin';
});

// --- INICIO: Lógica de Zona Horaria ---
const userTimezone = ref(localStorage.getItem('preferredTimezone') || Intl.DateTimeFormat().resolvedOptions().timeZone);

const commonTimezones = [
  { value: 'Europe/Madrid', label: 'Madrid (CET/CEST)' },
  { value: 'UTC', label: 'UTC' },
  { value: 'America/New_York', label: 'New York (ET)' },
  { value: 'America/Los_Angeles', label: 'Los Angeles (PT)' },
  { value: 'Asia/Tokyo', label: 'Tokyo (JST)' },
  { value: 'Australia/Sydney', label: 'Sydney (AEST)' },
];

const showTimezoneSelector = ref(false);

const currentTimezoneName = computed(() => {
  const found = commonTimezones.find(tz => tz.value === userTimezone.value);
  return found ? found.label : userTimezone.value;
});

function setTimezone(timezone) {
  userTimezone.value = timezone;
  localStorage.setItem('preferredTimezone', timezone);
  showTimezoneSelector.value = false;
}

const closeDropdown = (e) => {
  if (e.target.closest('.timezone-selector-button')) return;
  showTimezoneSelector.value = false;
};

function formatDate(dateString, timeString, targetTimezone) {
  if (!dateString) return 'Date TBD';

  try {
    const dateMatch = String(dateString).match(/(\d{4})[-/](\d{1,2})[-/](\d{1,2})/);
    if (!dateMatch) {
      console.warn("Invalid dateString format for formatDate:", dateString);
      return `${dateString} ${timeString || ''} (Invalid DateFmt)`;
    }
    const year = parseInt(dateMatch[1]);
    const month_1_indexed = parseInt(dateMatch[2]);
    const day = parseInt(dateMatch[3]);

    let sourceHour = 0;
    let sourceMinute = 0;
    if (timeString) {
      const timeMatch = String(timeString).match(/(\d{1,2}):(\d{1,2})/);
      if (timeMatch) {
        sourceHour = parseInt(timeMatch[1]);
        sourceMinute = parseInt(timeMatch[2]);
      } else {
        console.warn("Could not parse timeString for formatDate, using midnight:", timeString);
      }
    }

    const refDateUTC = new Date(Date.UTC(year, month_1_indexed - 1, day, 12, 0, 0));

    const getPartsInTimezone = (dateObj, tz) => {
      const formatter = new Intl.DateTimeFormat('en-US', {
        year: 'numeric', month: 'numeric', day: 'numeric',
        hour: 'numeric', hourCycle: 'h23', timeZone: tz
      });
      const parts = formatter.formatToParts(dateObj);
      return {
        year: parseInt(parts.find(p => p.type === 'year').value),
        month: parseInt(parts.find(p => p.type === 'month').value),
        day: parseInt(parts.find(p => p.type === 'day').value),
        hour: parseInt(parts.find(p => p.type === 'hour').value)
      };
    };

    const partsAtUTCRef = getPartsInTimezone(refDateUTC, 'UTC');
    const partsAtMadridRef = getPartsInTimezone(refDateUTC, 'Europe/Madrid');

    let hourOffset = partsAtMadridRef.hour - partsAtUTCRef.hour;
    const madridRefDateObj = new Date(partsAtMadridRef.year, partsAtMadridRef.month - 1, partsAtMadridRef.day);
    const utcRefDateObj = new Date(partsAtUTCRef.year, partsAtUTCRef.month - 1, partsAtUTCRef.day);

    if (madridRefDateObj.getTime() > utcRefDateObj.getTime()) {
      hourOffset += 24;
    } else if (madridRefDateObj.getTime() < utcRefDateObj.getTime()) {
      hourOffset -= 24;
    }

    const targetDateInUTC = new Date(Date.UTC(year, month_1_indexed - 1, day, sourceHour - hourOffset, sourceMinute, 0));

    const displayFormatter = new Intl.DateTimeFormat('en-GB', {
      year: 'numeric', month: 'short', day: '2-digit',
      hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
      timeZone: targetTimezone
    });

    const formattedParts = displayFormatter.formatToParts(targetDateInUTC);
    const fDay = formattedParts.find(p => p.type === 'day').value;
    const fMonth = formattedParts.find(p => p.type === 'month').value;
    const fYear = formattedParts.find(p => p.type === 'year').value;
    const fHour = formattedParts.find(p => p.type === 'hour').value;
    const fMinute = formattedParts.find(p => p.type === 'minute').value;

    return `${fDay} ${fMonth}, ${fHour}:${fMinute}`;

  } catch (error) {
    console.error("Error formatting date in Races.vue:", error, { dateString, timeString, targetTimezone });
    let fallbackDisplay = dateString ? String(dateString).split('T')[0] : 'Date TBD';
    if (timeString) {
        const timeOnlyMatch = String(timeString).match(/(\d{2}:\d{2})/);
        fallbackDisplay += timeOnlyMatch ? ` ${timeOnlyMatch[0]}` : ` ${timeString}`;
    }
    return `${fallbackDisplay} (TZ Conv. Error)`;
  }
}
// --- FIN: Lógica de Zona Horaria ---

// Helper function to get race start date and time
const getRaceStartDateTime = (race) => {
  if (!race.race_date) {
    return new Date(0);
  }

  if (race.start_time && typeof race.start_time === 'string') {
    const raceDatePart = race.race_date.split('T')[0];
    
    let timePart = null;
    if (race.start_time.includes('T')) {
      timePart = race.start_time.split('T')[1];
      if (timePart.endsWith('Z')) {
          timePart = timePart.substring(0, timePart.length -1);
      }
    } else if (race.start_time.match(/^\d{2}:\d{2}:\d{2}$/)) {
      timePart = race.start_time;
    }

    if (timePart) {
      const dateTimeString = `${raceDatePart}T${timePart}Z`;
      const parsedDate = new Date(dateTimeString);
      if (!isNaN(parsedDate.getTime())) {
        return parsedDate;
      }
    }
  }

  const raceDateDirect = new Date(race.race_date);
  if (!isNaN(raceDateDirect.getTime())) {
    return raceDateDirect;
  }

  return new Date(NaN);
};

// Calcular la próxima carrera programada
const nextRace = computed(() => {
  if (!races.value || races.value.length === 0) {
    return null;
  }

  const now = new Date();
  let currentLiveRace = null;
  const upcomingRaces = [];

  console.log('Races.vue - nextRace computed:');
  console.log('Current time (now):', now.toISOString());
  console.log('All races available to nextRace:', JSON.parse(JSON.stringify(races.value.map(r => ({
      name: r.name, 
      race_date: r.race_date, 
      start_time: r.start_time, 
      status: r.status,
  })))));
  
  for (const race of races.value) {
    if (!race.race_date) {
        console.log(`Carrera ${race.name} ignorada en nextRace: sin race_date`);
        continue;
    }

    const raceStatus = race.status?.toLowerCase();
    const raceStartDateTime = getRaceStartDateTime(race);

    if (isNaN(raceStartDateTime.getTime())) {
        console.log(`Carrera ${race.name} ignorada en nextRace: fecha de inicio inválida según getRaceStartDateTime.`);
        continue;
    }
    
    if (raceStatus === 'in progress' || raceStatus === 'live') {
      console.log(`Carrera ${race.name} es LIVE por backend status.`);
      currentLiveRace = race;
      break; 
    }

    const eventWindowStart = race.practice1_date ? getRaceStartDateTime({ race_date: race.practice1_date, start_time: race.practice1_time }) : raceStartDateTime;
    const eventWindowEnd = new Date(raceStartDateTime.getTime() + 3 * 60 * 60 * 1000);

    if (now >= eventWindowStart && now <= eventWindowEnd && raceStatus !== 'completed' && raceStatus !== 'finished' && raceStatus !== 'cancelled') {
        console.log(`Carrera ${race.name} es LIVE por TIEMPO (Ventana: ${eventWindowStart.toISOString()} - ${eventWindowEnd.toISOString()}).`);
        if (!currentLiveRace) { 
            currentLiveRace = race;
        }
        continue; 
    }
    
    if (raceStartDateTime > now && raceStatus !== 'completed' && raceStatus !== 'finished' && raceStatus !== 'cancelled') {
      console.log(`Carrera ${race.name} es UPCOMING (Fecha inicio: ${raceStartDateTime.toISOString()}).`);
      upcomingRaces.push(race);
    }
  }

  if (currentLiveRace) {
    console.log('Retornando currentLiveRace:', currentLiveRace.name);
    return currentLiveRace;
  }

  upcomingRaces.sort((a, b) => {
    const aDate = getRaceStartDateTime(a);
    const bDate = getRaceStartDateTime(b);
    if (isNaN(aDate.getTime()) && !isNaN(bDate.getTime())) return 1;
    if (!isNaN(aDate.getTime()) && isNaN(bDate.getTime())) return -1;
    if (isNaN(aDate.getTime()) && isNaN(bDate.getTime())) return 0;
    return aDate.getTime() - bDate.getTime();
  });
  
  const resultadoFinal = upcomingRaces.length > 0 ? upcomingRaces[0] : null;
  console.log('Selected nextRace (upcoming):', resultadoFinal ? resultadoFinal.name : null);
  return resultadoFinal;
});

// Cargar datos iniciales
onMounted(async () => {
  console.log('Component mounted');
  await loadSeasons();
  document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown);
});

// Cargar temporadas
async function loadSeasons() {
  try {
    console.log('Loading seasons...');
    const response = await axios.get('/api/seasons');
    seasons.value = response.data.data || [];
    console.log('Seasons loaded:', seasons.value);
    
    const currentYear = new Date().getFullYear();
    const defaultSeason = seasons.value.find(s => s.year === currentYear) || 
                          seasons.value.find(s => s.year === 2025) || 
                          seasons.value[seasons.value.length - 1];
                          
    if (defaultSeason) {
      console.log('Setting default season:', defaultSeason);
      selectedSeason.value = defaultSeason.id;
      await loadRaces();
    } else {
      console.error('No se pudo encontrar una temporada por defecto');
    }
  } catch (error) {
    console.error('Error loading seasons:', error);
    seasons.value = [{ id: 5, year: 2025 }];
    selectedSeason.value = 5;
    await loadRaces();
  }
}

// Cargar carreras
async function loadRaces() {
  if (!selectedSeason.value) {
    console.error('No hay temporada seleccionada');
    return;
  }
  
  loading.value = true;
  console.log('Loading races for season:', selectedSeason.value);
  
  try {
    const response = await axios.get(`/api/races?season_id=${selectedSeason.value}`);
    console.log('API response:', response.data);
    
    const seasonId = selectedSeason.value;
    const filteredData = response.data.data.filter(race => {
      return race.season?.id === seasonId;
    });
    
    console.log(`Filtered to ${filteredData.length} races for season ${seasonId}`);
    races.value = filteredData;
  } catch (error) {
    console.error('Error loading races:', error);
    races.value = [];
  } finally {
    loading.value = false;
  }
}

// Filtrar y ORDENAR carreras para la LISTA GENERAL
const filteredRaces = computed(() => {
  console.log('Filtering races for display with status:', statusFilter.value);
  
  const racesBySelectedSeason = races.value.filter(race => {
    return race.season?.id === selectedSeason.value;
  });
  
  let resultAfterStatusFilter;
  if (statusFilter.value === 'all') {
    resultAfterStatusFilter = racesBySelectedSeason;
  } else {
    resultAfterStatusFilter = racesBySelectedSeason.filter(race => {
      const status = race.status?.toLowerCase() || 'unknown';
      
      if (statusFilter.value === 'in_progress') {
        return ['in progress', 'live'].includes(status);
      } else if (statusFilter.value === 'upcoming') {
        return ['scheduled', 'upcoming', 'confirmed'].includes(status); 
      } else if (statusFilter.value === 'completed') {
        return ['completed', 'finished'].includes(status);
      } else if (statusFilter.value === 'cancelled') {
        return ['cancelled', 'postponed'].includes(status);
      }
      return false; 
    });
  }

  const now = new Date();
  console.log('--- Iniciando ordenación de filteredRaces ---');
  console.log('Current time (now):', now.toISOString());

  return [...resultAfterStatusFilter].sort((raceA, raceB) => {
    const dateTimeA = getRaceStartDateTime(raceA);
    const dateTimeB = getRaceStartDateTime(raceB);

    if (isNaN(dateTimeA.getTime()) && !isNaN(dateTimeB.getTime())) return 1;
    if (!isNaN(dateTimeA.getTime()) && isNaN(dateTimeB.getTime())) return -1;
    if (isNaN(dateTimeA.getTime()) && isNaN(dateTimeB.getTime())) return 0;

    const statusA = raceA.status?.toLowerCase() || 'unknown';
    const statusB = raceB.status?.toLowerCase() || 'unknown';
    
    const isEffectivelyFutureA = (dateTimeA >= now && statusA !== 'completed' && statusA !== 'finished');
    const isEffectivelyFutureB = (dateTimeB >= now && statusB !== 'completed' && statusB !== 'finished');

    let sortResult;

    if (statusFilter.value === 'all' || statusFilter.value === 'upcoming') {
        if (statusA === 'ongoing' && statusB !== 'ongoing') { sortResult = -1; }
        else if (statusB === 'ongoing' && statusA !== 'ongoing') { sortResult = 1; }
        else if (statusA === 'ongoing' && statusB === 'ongoing') {
             sortResult = dateTimeA.getTime() - dateTimeB.getTime();
        }
        if (sortResult !== undefined) {
            return sortResult;
        }
    }
    
    if (isEffectivelyFutureA && !isEffectivelyFutureB) { sortResult = -1; }
    else if (!isEffectivelyFutureA && isEffectivelyFutureB) { sortResult = 1; }
    if (sortResult !== undefined) {
        return sortResult;
    }

    if (isEffectivelyFutureA && isEffectivelyFutureB) {
      sortResult = dateTimeA.getTime() - dateTimeB.getTime();
      return sortResult;
    }

    if (!isEffectivelyFutureA && !isEffectivelyFutureB) {
      sortResult = dateTimeB.getTime() - dateTimeA.getTime();
      return sortResult;
    }
    
    return dateTimeA.getTime() - dateTimeB.getTime();
  });
});

// Ver detalles de carrera
function goToRaceDetails(raceId) {
  if (!raceId) {
    console.error('Intentando navegar a una carrera sin ID');
    return;
  }
  console.log('Navigating to race details:', raceId);
  router.visit(`/races/${raceId}`);
}

// Recargar carreras cuando cambia el filtro de temporada
watch(selectedSeason, async (newValue) => {
  if (newValue) {
    console.log('Season changed to:', newValue);
    await loadRaces();
  }
});
</script>