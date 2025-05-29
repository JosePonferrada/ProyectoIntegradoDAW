<script setup>
// Importaciones
import { ref, onMounted, computed, markRaw } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import PredictionsBanner from '@/components/PredictionsBanner.vue';

// Importaciones de widgets
import UpcomingRacesWidget from '@/components/Widgets/UpcomingRacesWidget.vue';
import DriverStandingsWidget from '@/components/Widgets/DriverStandingsWidget.vue';
import ConstructorStandingsWidget from '@/components/Widgets/ConstructorStandingsWidget.vue';
import RaceCountdownWidget from '@/components/Widgets/RaceCountdownWidget.vue';
import SeasonSummaryWidget from '@/components/Widgets/SeasonSummaryWidget.vue';

// Referencias reactivas para los datos
const latestRaces = ref([]);
const upcomingRaces = ref([]);
const driverStandings = ref([]);
const constructorStandings = ref([]);
const seasonInfo = ref({});
const loading = ref(true);
const error = ref(null);
const showWidgetSelector = ref(false);

// Widget system - Usa markRaw para los componentes
const availableWidgets = ref([
  { id: 'upcoming-races', title: 'Upcoming Races', icon: 'fas fa-flag-checkered', component: markRaw(UpcomingRacesWidget), enabled: true, data: {} },
  { id: 'next-race', title: 'Next Race Countdown', icon: 'fas fa-stopwatch', component: markRaw(RaceCountdownWidget), enabled: true, data: {} },
  { id: 'driver-standings', title: 'Driver Standings', icon: 'fas fa-user-helmet', component: markRaw(DriverStandingsWidget), enabled: true, data: {} },
  { id: 'constructor-standings', title: 'Constructor Standings', icon: 'fas fa-trophy', component: markRaw(ConstructorStandingsWidget), enabled: true, data: {} },
  { id: 'season-summary', title: 'Season Summary', icon: 'fas fa-calendar-check', component: markRaw(SeasonSummaryWidget), enabled: true, data: {} }
]);

// Active widgets in current order
const activeWidgets = computed(() => {
  return availableWidgets.value.filter(widget => widget.enabled);
});

// User preferences saved in localStorage
onMounted(() => {
  try {
    const savedPrefs = localStorage.getItem('f1_dashboard_prefs');
    if (savedPrefs) {
      const prefs = JSON.parse(savedPrefs);
      // Apply saved preferences to our widgets
      availableWidgets.value = availableWidgets.value.map(widget => {
        const savedWidget = prefs.find(w => w.id === widget.id);
        return savedWidget ? { ...widget, enabled: savedWidget.enabled } : widget;
      });
    }
  } catch (e) {
    console.error('Error loading dashboard preferences:', e);
  }
});

// Widget management functions
function saveWidgetPreferences() {
  localStorage.setItem('f1_dashboard_prefs', JSON.stringify(
    availableWidgets.value.map(w => ({ id: w.id, enabled: w.enabled }))
  ));
}

function toggleWidget(widgetId) {
  const widget = availableWidgets.value.find(w => w.id === widgetId);
  if (widget) {
    widget.enabled = !widget.enabled;
    saveWidgetPreferences();
  }
}

function moveWidgetUp(widgetId) {
  const index = availableWidgets.value.findIndex(w => w.id === widgetId);
  if (index > 0) {
    // Solo si no es el primer elemento
    const temp = availableWidgets.value[index];
    availableWidgets.value[index] = availableWidgets.value[index - 1];
    availableWidgets.value[index - 1] = temp;
    saveWidgetPreferences();
  }
}

function moveWidgetDown(widgetId) {
  const index = availableWidgets.value.findIndex(w => w.id === widgetId);
  if (index < availableWidgets.value.length - 1) {
    // Solo si no es el último elemento
    const temp = availableWidgets.value[index];
    availableWidgets.value[index] = availableWidgets.value[index + 1];
    availableWidgets.value[index + 1] = temp;
    saveWidgetPreferences();
  }
}

function removeWidget(widgetId) {
  toggleWidget(widgetId);
}

onMounted(async () => {
  try {
    // Add current season filter to get more relevant data
    const currentYear = new Date().getFullYear();
    const seasonResponse = await axios.get('/api/seasons', {
      params: { year: currentYear }
    });
    
    const currentSeason = seasonResponse.data.data[0]?.id || null;
    
    // Fetch all the data we need for the widgets
    const [racesRes, driverRes, constructorRes, qualifyingRes, predictionsRes] = await Promise.all([
      axios.get('/api/races', { // MODIFICACIÓN AQUÍ
        params: { season_id: currentSeason } 
      }),
      axios.get('/api/driver-standings', { 
        params: { season_id: currentSeason, accumulated: true } 
      }),
      axios.get('/api/constructor-standings', {
        params: { season_id: currentSeason, accumulated: true } 
      }),
      axios.get('/api/qualifying-results', {
        params: { season_id: currentSeason, limit: 20 }
      }),
    ]);
    
    // Process data
    const races = racesRes.data.data || [];
    const now = new Date();
    
    // Upcoming races
    upcomingRaces.value = races
      .filter(race => new Date(race.race_date) > now)
      .sort((a, b) => new Date(a.race_date) - new Date(b.race_date))
      .slice(0, 5);
      
    // Latest races
    latestRaces.value = races
      .filter(race => new Date(race.race_date) <= now)
      .sort((a, b) => new Date(b.race_date) - new Date(a.race_date))
      .slice(0, 5);
    
    // Next race circuit info  
    if (upcomingRaces.value.length > 0) {
      const nextRaceId = upcomingRaces.value[0].id;
      const circuitRes = await axios.get(`/api/circuits/${nextRaceId}`);
    }
    
    // Driver and constructor standings
    driverStandings.value = (driverRes.data.data || []).slice(0, 10);
    constructorStandings.value = (constructorRes.data.data || []).slice(0, 10);

    // Season summary
    seasonInfo.value = {
      year: currentYear,
      totalRaces: races.length, // Esto ahora será el total de carreras para la temporada actual
      completedRaces: latestRaces.value.length,
      upcomingRaces: upcomingRaces.value.length,
      leaderDriver: driverStandings.value[0] || null,
      leaderTeam: constructorStandings.value[0] || null
    };

    // Llama a esta función después de cargar los datos
    updateWidgetData();
  } catch (e) {
    console.error('Error fetching dashboard data:', e);
    error.value = e.message;
  } finally {
    loading.value = false;
  }
});

// Después de cargar los datos en onMounted:
function updateWidgetData() {
  availableWidgets.value.forEach(widget => {
    switch(widget.id) {
      case 'upcoming-races':
        widget.data = { upcomingRaces: upcomingRaces.value };
        break;
      case 'driver-standings':
        widget.data = { driverStandings: driverStandings.value };
        break;
      case 'constructor-standings':
        widget.data = { constructorStandings: constructorStandings.value };
        break;
      case 'latest-results':
        widget.data = { latestRaces: latestRaces.value };
        break;
      case 'next-race':
        widget.data = { nextRace: upcomingRaces.value[0] || null };
        break;
      case 'season-summary':
        widget.data = { seasonInfo: seasonInfo.value };
        break;
    }
  });
}
</script>

<template>
  <AppLayout title="Dashboard">
    <div class="container mx-auto px-4 py-6">
      <!-- Banner de predicciones -->
      <PredictionsBanner :nextRace="upcomingRaces[0]" />
      
      <!-- Dashboard Header with Customize Button -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Formula 1 Dashboard</h1>
        <button 
          @click="showWidgetSelector = true" 
          class="bg-f1-red hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center transition-colors duration-150"
        >
          <i class="fas fa-cog mr-2"></i>
          Customize Dashboard
        </button>
      </div>
      
      <!-- Loading state -->
      <div v-if="loading" class="text-center py-12">
        <div class="flex justify-center items-center">
          <svg class="animate-spin h-10 w-10 text-f1-red" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <p class="text-gray-500 text-lg mt-4">Loading dashboard data...</p>
      </div>
      
      <!-- Error state -->
      <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 p-6 rounded-lg text-center">
        <p class="text-red-800 dark:text-red-300">
          <i class="fas fa-exclamation-triangle mr-2"></i>
          Error loading data: {{ error }}
        </p>
        <button @click="window.location.reload()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
          Retry
        </button>
      </div>
      
      <!-- Widget Grid with reordering capability -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-for="widget in activeWidgets" :key="widget.id" 
             class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
          <!-- Widget header with options -->
          <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center">
              <i :class="widget.icon + ' mr-2 text-f1-red'"></i>
              {{ widget.title }}
            </h2>
            <div class="flex space-x-2">
              <button @click="moveWidgetUp(widget.id)" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="fas fa-arrow-up"></i>
              </button>
              <button @click="moveWidgetDown(widget.id)" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="fas fa-arrow-down"></i>
              </button>
              <button @click="removeWidget(widget.id)" class="text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          
          <!-- Widget content depends on type -->
          <component :is="widget.component" :data="widget.data" class="p-6" />
        </div>
      </div>

      <!-- Modal for selecting widgets -->
      <div v-if="showWidgetSelector" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-75 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              Customize Dashboard
            </h3>
            <button @click="showWidgetSelector = false" class="text-gray-400 hover:text-gray-500">
              <i class="fas fa-times"></i>
            </button>
          </div>
          
          <div class="mb-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Select the widgets you want to display on your dashboard.
            </p>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div 
              v-for="widget in availableWidgets" 
              :key="widget.id" 
              class="border rounded-lg p-3 flex items-center cursor-pointer"
              :class="{ 
                'border-red-500 bg-red-50 dark:bg-red-900/20': widget.enabled,
                'border-gray-300 dark:border-gray-700': !widget.enabled 
              }"
              @click="toggleWidget(widget.id)"
            >
              <div class="flex-shrink-0 mr-3">
                <i :class="widget.icon + ' text-xl ' + (widget.enabled ? 'text-red-600' : 'text-gray-400')"></i>
              </div>
              <div class="flex-1">
                <h4 class="font-medium" :class="widget.enabled ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'">
                  {{ widget.title }}
                </h4>
              </div>
              <div class="flex-shrink-0 ml-2">
                <div class="w-6 h-6 rounded-full flex items-center justify-center" 
                     :class="widget.enabled ? 'bg-red-600' : 'bg-gray-300 dark:bg-gray-600'">
                  <i class="fas fa-check text-xs text-white" v-if="widget.enabled"></i>
                </div>
              </div>
            </div>
          </div>
          
          <div class="mt-6 flex justify-end">
            <button 
              @click="showWidgetSelector = false" 
              class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md"
            >
              Done
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>