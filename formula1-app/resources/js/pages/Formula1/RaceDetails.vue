<template>
  <AppLayout :title="race && race.name ? `${race.name} - Details` : 'Race Details'">
    <Head :title="race && race.name ? `${race.name} - Details` : 'Race Details'" />
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Botón Volver -->
        <div class="mb-4">
          <Link 
            :href="route('races')"
            class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-f1-red"
          >
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Races
          </Link>
        </div>

        <!-- Header con información básica de la carrera -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex-grow">
              <div class="flex items-center">
                <div>
                  <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ race?.name }}</h1>
                  <div class="flex items-center mt-2">
                    <CountryFlag v-if="race?.circuit?.country" :countryCode="race.circuit.country.code" class="h-5 w-7 mr-2" />
                    <span class="text-gray-600 dark:text-gray-300">{{ race?.circuit?.name }}, {{ race?.circuit?.country?.name }}</span>
                  </div>
                  <div class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                    {{ formatDateTimeWithTimezone(race?.race_date, race?.start_time, userTimezone) }}
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Estado y Cuenta atrás -->
            <div class="mt-4 md:mt-0 flex flex-col items-start md:items-end space-y-2">
              <div class="px-3 py-1 rounded-full text-xs font-medium" 
                   :class="getRaceStatusClass(race?.status)">
                {{ race?.status || 'Scheduled' }}
              </div>
              <div v-if="shouldShowCountdown(race)" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ countdown }}
              </div>
            </div>
          </div>
        </div>

        <!-- Selector de Zona Horaria -->
        <div class="mb-4 flex justify-end">
            <div class="relative timezone-selector-button-details w-auto sm:w-56">
              <button 
                @click="showTimezoneSelector = !showTimezoneSelector"
                class="w-full text-left px-3 py-1.5 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md text-xs text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 flex items-center justify-between"
              >
                <span class="truncate">
                  <i class="fas fa-globe-europe mr-1"></i>
                  {{ currentTimezoneName }}
                </span>
                <i :class="showTimezoneSelector ? 'fas fa-chevron-up ml-1' : 'fas fa-chevron-down ml-1'" class="text-xs"></i>
              </button>
              <div 
                v-if="showTimezoneSelector" 
                class="absolute right-0 mt-1 w-full rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 z-50"
              >
                <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu-details">
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
        </div>
        
        <!-- Navegación por pestañas -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px space-x-8 px-4 sm:px-6" aria-label="Tabs">
              <a v-for="tab in tabs" 
                 :key="tab.id"
                 @click.prevent="currentTab = tab.id"
                 :class="[
                   currentTab === tab.id 
                     ? 'border-f1-red text-f1-red' 
                     : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                   'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm cursor-pointer'
                 ]">
                {{ tab.name }}
              </a>
            </nav>
          </div>
          
          <!-- Contenido de cada pestaña -->
          <div class="p-6">
            <!-- Info -->
            <RaceInfo v-if="currentTab === 'info'" 
                      :race="race" 
                      :isAdmin="isAdmin"
                      :countdown="countdown"
                      :user-timezone="userTimezone"
                      :format-date-time-fn="formatDateTimeWithTimezone" />

            <!-- Results -->
            <div v-if="currentTab === 'results'">
              <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Race Results</h2>
                
                <!-- Botones para cambiar el modo (solo para administradores) -->
                <div v-if="isAdmin" class="flex items-center space-x-2">
                  <button 
                    @click="resultsMode = 'view'" 
                    :class="[
                      'px-3 py-1.5 rounded-md text-sm',
                      resultsMode === 'view' 
                        ? 'bg-red-600 text-white' 
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                    ]"
                  >
                    <i class="fas fa-table mr-1"></i> View
                  </button>
                  <button 
                    @click="resultsMode = 'bulk'" 
                    :class="[
                      'px-3 py-1.5 rounded-md text-sm',
                      resultsMode === 'bulk' 
                        ? 'bg-red-600 text-white' 
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                    ]"
                  >
                    <i class="fas fa-edit mr-1"></i> Manage All
                  </button>
                </div>
              </div>
              
              <!-- Componente normal para vista y edición individual -->
              <RaceResults 
                v-if="resultsMode === 'view'" 
                :race="race" 
                :results="raceResults" 
                :loading="loading.results"
                :results-mode="resultsMode"
                @results-updated="loadRaceResults"
              />
              
              <!-- Componente para edición en bloque -->
              <BulkRaceResults 
                v-else
                :race="race" 
                :loading="loading.results"
                :results-mode="resultsMode"
                @results-updated="loadRaceResults"
              />
            </div>
            
            <!-- Qualifying -->
            <div v-if="currentTab === 'qualifying'">
              <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Qualifying Results</h2>
                
                <!-- Botones para cambiar el modo (solo para administradores) -->
                <div v-if="isAdmin" class="flex items-center space-x-2">
                  <button 
                    @click="editingQualifying = false; resultsMode = 'view'" 
                    :class="[
                      'px-3 py-1.5 rounded-md text-sm',
                      !editingQualifying 
                        ? 'bg-red-600 text-white' 
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                    ]"
                    :disabled="loading.qualifying && editingQualifying"
                  >
                    <i class="fas fa-table mr-1"></i> View
                  </button>
                  <button 
                    @click="enableQualifyingEditMode()" 
                    :class="[
                      'px-3 py-1.5 rounded-md text-sm',
                      editingQualifying 
                        ? 'bg-red-600 text-white' 
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                    ]"
                    :disabled="loading.qualifying && !editingQualifying"
                  >
                    <i class="fas fa-edit mr-1"></i> Manage All
                  </button>
                </div>
              </div>

              <!-- Componentes de Qualifying -->
              <div v-if="editingQualifying">
                <BulkQualifyingResults 
                    :race="race"
                    :existing-qualifying-results="qualifyingResults" 
                    :available-drivers="allDriversForSeason"
                    :available-constructors="allConstructorsForSeason"
                    :loading="loading.qualifying"
                    @qualifying-results-updated="handleQualyUpdateAndExitEditMode"
                    @cancel-edit="cancelQualifyingEdit"
                />
              </div>
              <div v-else>
                <QualifyingResults 
                    :results="qualifyingResults" 
                    :loading="loading.qualifying" 
                />
              </div>
            </div>
            
            <!-- Circuit -->
            <CircuitView v-else-if="currentTab === 'circuit'" 
                           :circuit="race?.circuit" />
            
            <!-- Steward Decisions -->
            <StewardDecisions v-else-if="currentTab === 'decisions'"
                              :race="race" :isAdmin="isAdmin"/>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { usePage, Link, Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CountryFlag from '@/components/CountryFlag.vue';
import RaceInfo from '@/components/RaceDetails/RaceInfo.vue';
import RaceResults from '@/components/RaceDetails/RaceResults.vue';
import BulkRaceResults from '@/components/RaceDetails/BulkRaceResults.vue';
import QualifyingResults from '@/components/RaceDetails/QualifyingResults.vue';
import BulkQualifyingResults from '@/components/RaceDetails/BulkQualifyingResults.vue';
import CircuitView from '@/components/RaceDetails/CircuitView.vue';
import StewardDecisions from '@/components/RaceDetails/StewardDecisions.vue';
import axios from 'axios';

const props = defineProps({
  id: Number // Recibe el ID de la carrera desde el controlador de Inertia
});

const race = ref(null);
const raceResults = ref([]);
const qualifyingResults = ref([]);
const countdown = ref(null);
const countdownInterval = ref(null);
const editingQualifying = ref(false);
const allDriversForSeason = ref([]);
const allConstructorsForSeason = ref([]);

const tabs = [
  { id: 'info', name: 'Information' },
  { id: 'results', name: 'Race Results' },
  { id: 'qualifying', name: 'Qualifying' },
  { id: 'circuit', name: 'Circuit' },
  { id: 'decisions', name: 'Steward Decisions' }
];

const currentTab = ref('info');
const loading = ref({
  race: true,
  results: false,
  qualifying: false
});

const resultsMode = ref('view'); // 'view' o 'bulk'

const isAdmin = computed(() => {
  const user = usePage().props.auth.user;
  return user && user.role === 'admin';
});

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
  if (e.target.closest('.timezone-selector-button-details')) return;
  showTimezoneSelector.value = false;
};

async function loadRaceResults() {
  const raceId = props.id;
  if (!raceId) {
    console.error("Race ID is missing, cannot load results.");
    raceResults.value = [];
    return;
  }
  loading.value.results = true;
  try {
    const response = await axios.get(`/api/race-results?race_id=${raceId}`);
    raceResults.value = response.data.data || [];
  } catch (error) {
    console.error('Error cargando resultados de carrera (desde loadRaceResults):', error);
    raceResults.value = [];
  } finally {
    loading.value.results = false;
  }
}

async function loadQualifyingFormData() {
  if (!race.value?.season?.id) {
    console.error("Season ID is missing from race, cannot load qualifying form data.");
    allDriversForSeason.value = [];
    allConstructorsForSeason.value = [];
    return;
  }
  if (allDriversForSeason.value.length > 0 && allConstructorsForSeason.value.length > 0) {
    return;
  }

  loading.value.qualifying = true; 
  try {
    const seasonId = race.value.season.id;
    const driversResponse = await axios.get(`/api/seasons/${seasonId}/main-drivers`);
    allDriversForSeason.value = driversResponse.data.data || [];
    console.log("Drivers for season loaded:", driversResponse);
    const constructorsResponse = await axios.get(`/api/constructors`);
    allConstructorsForSeason.value = constructorsResponse.data.data || [];
    console.log("Constructors for season loaded:", constructorsResponse);
  } catch (error) {
    console.error("Error loading drivers/constructors for qualifying forms:", error);
    allDriversForSeason.value = [];
    allConstructorsForSeason.value = [];
  } finally {
    loading.value.qualifying = false;
  }
}

async function enableQualifyingEditMode() {
  let formReady = false;
  let resultsReady = false;

  if (!loading.value.qualifying) {
    loading.value.qualifying = true;
  }

  const loadFormPromise = async () => {
    if (allDriversForSeason.value.length === 0 || allConstructorsForSeason.value.length === 0) {
      await loadQualifyingFormData();
    }
    formReady = true;
  };

  const loadResultsPromise = async () => {
    if (qualifyingResults.value.length === 0) {
      try {
          const raceId = props.id;
          const response = await axios.get(`/api/qualifying-results?race_id=${raceId}`);
          qualifyingResults.value = response.data.data || [];
      } catch (error) {
          console.error('Error cargando resultados de clasificación (en enableQualifyingEditMode):', error);
          qualifyingResults.value = [];
      }
    }
    resultsReady = true;
  };

  await Promise.all([loadFormPromise(), loadResultsPromise()]);
  
  if (formReady && resultsReady) {
    editingQualifying.value = true;
  }
  loading.value.qualifying = false;
}

function cancelQualifyingEdit() {
  editingQualifying.value = false;
}

function handleQualyUpdateAndExitEditMode(updatedResults) {
  editingQualifying.value = false;
  loading.value.qualifying = true;
  const raceId = props.id;
  axios.get(`/api/qualifying-results?race_id=${raceId}`)
    .then(response => {
      qualifyingResults.value = response.data.data || [];
    })
    .catch(error => {
      console.error('Error recargando resultados de clasificación después de actualizar:', error);
      qualifyingResults.value = [];
    })
    .finally(() => {
      loading.value.qualifying = false;
    });
}

onMounted(async () => {
  const raceId = props.id;
  loading.value.race = true;
  
  try {
    const response = await axios.get(`/api/races/${raceId}`);
    race.value = response.data.data;
    
    if (shouldShowCountdown(race.value)) {
      startCountdown();
    }
  } catch (error) {
    console.error('Error cargando detalles de carrera:', error);
  } finally {
    loading.value.race = false;
  }
  console.log('Season id loaded:', race.value.season.id);

  document.addEventListener('click', closeDropdown);
});

watch(currentTab, async (newTab) => {
  if (newTab === 'results' && raceResults.value.length === 0) {
    await loadRaceResults();
  }
  
  if (newTab === 'qualifying' && qualifyingResults.value.length === 0 && !editingQualifying.value) {
    loading.value.qualifying = true;
    try {
      const raceId = props.id;
      const response = await axios.get(`/api/qualifying-results?race_id=${raceId}`);
      qualifyingResults.value = response.data.data;
    } catch (error) {
      console.error('Error cargando resultados de clasificación:', error);
    } finally {
      loading.value.qualifying = false;
    }
  }
});

onUnmounted(() => {
  if (countdownInterval.value) {
    clearInterval(countdownInterval.value);
  }
  document.removeEventListener('click', closeDropdown);
});

function shouldShowCountdown(race) {
  if (!race || !race.race_date) return false;
  
  const now = new Date();
  const raceDate = new Date(race.race_date);
  const status = race.status?.toLowerCase() || '';
  
  return raceDate > now && 
         (status === 'scheduled' || status === 'upcoming' || status === 'confirmed');
}

function startCountdown() {
  countdownInterval.value = setInterval(() => {
    const now = new Date();
    const raceDate = new Date(race.value.race_date);
    const diff = raceDate - now;
    
    if (diff <= 0) {
      clearInterval(countdownInterval.value);
      countdown.value = "Race starting now";
      return;
    }
    
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
    
    countdown.value = `${days}d ${hours}h ${minutes}m ${seconds}s`;
  }, 1000);
}

function formatDate(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    weekday: 'long', 
    day: 'numeric', 
    month: 'long', 
    year: 'numeric' 
  });
}

function getRaceStatusClass(status) {
  if (!status) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
  
  const statusLower = status.toLowerCase();
  
  if (statusLower === 'completed' || statusLower === 'finished') {
    return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
  }
  
  if (statusLower === 'in progress' || statusLower === 'ongoing' || statusLower === 'live') {
    return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
  }
  
  if (statusLower === 'cancelled' || statusLower === 'postponed') {
    return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
  }
  
  return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
}

function formatDateTimeWithTimezone(dateString, timeString, targetTimezone) {
  if (!dateString) return 'Date TBD';
  try {
    const dateMatch = String(dateString).match(/(\d{4})[-/](\d{1,2})[-/](\d{1,2})/);
    if (!dateMatch) {
      console.warn("Invalid dateString format for formatDateTimeWithTimezone:", dateString);
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
        console.warn("Could not parse timeString for formatDateTimeWithTimezone, using midnight:", timeString);
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

    return `${fDay} ${fMonth} ${fYear}, ${fHour}:${fMinute}`;

  } catch (error) {
    console.error("Error formatting date in RaceDetails.vue (formatDateTimeWithTimezone):", error, { dateString, timeString, targetTimezone });
    let fallbackDisplay = dateString ? String(dateString).split('T')[0] : 'Date TBD';
    if (timeString) {
        const timeOnlyMatch = String(timeString).match(/(\d{2}:\d{2})/);
        fallbackDisplay += timeOnlyMatch ? ` ${timeOnlyMatch[0]}` : ` ${timeString}`;
    }
    return `${fallbackDisplay} (TZ Conv. Error)`;
  }
}
</script>