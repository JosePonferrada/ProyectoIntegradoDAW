<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';

const latestRaces = ref([]);
const upcomingRaces = ref([]);
const driverStandings = ref([]);
const constructorStandings = ref([]);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
  try {
    // Add current season filter to get more relevant data
    const currentYear = new Date().getFullYear();
    const seasonResponse = await axios.get('/api/seasons', {
      params: { year: currentYear }
    });
    
    const currentSeason = seasonResponse.data.data[0]?.id || null;
    
    const [racesRes, driverRes, constructorRes] = await Promise.all([
      axios.get('/api/races'),
      axios.get('/api/driver-standings', { 
        params: { season_id: currentSeason, accumulated: true } 
      }),
      axios.get('/api/constructor-standings', {
        params: { season_id: currentSeason, accumulated: true } 
      })
    ]);
    
    // Process data
    const races = racesRes.data.data || [];
    const now = new Date();
    
    upcomingRaces.value = races
      .filter(race => new Date(race.race_date) > now)
      .sort((a, b) => new Date(a.race_date) - new Date(b.race_date))
      .slice(0, 5);
      
    latestRaces.value = races
      .filter(race => new Date(race.race_date) <= now)
      .sort((a, b) => new Date(b.race_date) - new Date(a.race_date))
      .slice(0, 5);
      
    driverStandings.value = (driverRes.data.data || []).slice(0, 10);
    constructorStandings.value = (constructorRes.data.data || []).slice(0, 10);
  } catch (e) {
    console.error('Error fetching dashboard data:', e);
    error.value = e.message;
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <AppLayout>
    <Head title="Dashboard" />
    
    <div class="py-6">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Formula 1 Dashboard</h1>
      
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
      
      <!-- Content -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Upcoming Races -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white flex items-center">
            <i class="fas fa-flag-checkered mr-2 text-f1-red"></i>
            Upcoming Races
          </h2>
          <div v-if="upcomingRaces.length === 0" class="text-gray-500 dark:text-gray-400 py-4 text-center">
            <i class="fas fa-calendar-times mb-2 text-2xl opacity-70"></i>
            <p>No upcoming races scheduled</p>
          </div>
          <ul v-else class="space-y-3">
            <li v-for="race in upcomingRaces" :key="race.race_id" class="border-b dark:border-gray-700 pb-2">
              <div class="font-medium dark:text-white">{{ race.name }}</div>
              <div class="text-sm text-gray-600 dark:text-gray-300">
                {{ race.circuit?.name }}, {{ race.circuit?.country?.name }}
              </div>
              <div class="text-sm text-gray-500 dark:text-gray-400">{{ new Date(race.race_date).toLocaleDateString() }}</div>
            </li>
          </ul>
        </div>
        
        <!-- Driver Standings -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white flex items-center">
            <i class="fas fa-user-helmet-safety mr-2 text-f1-red"></i>
            Driver Standings
          </h2>
          <div v-if="driverStandings.length === 0" class="text-gray-500 dark:text-gray-400 py-4 text-center">
            <i class="fas fa-table mb-2 text-2xl opacity-70"></i>
            <p>No standings available</p>
          </div>
          <ul v-else class="space-y-2">
            <li v-for="standing in driverStandings" :key="standing.id" 
                class="flex justify-between items-center py-1 border-b dark:border-gray-700 last:border-0">
              <span class="flex items-center">
                <span class="inline-block w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white flex items-center justify-center mr-3 text-sm">
                  {{ standing.position }}
                </span>
                {{ standing.driver?.first_name }} {{ standing.driver?.last_name }}
              </span>
              <span class="font-bold text-gray-900 dark:text-white">{{ standing.points }} pts</span>
            </li>
          </ul>
          <div class="mt-3 text-right">
            <a href="/standings?tab=drivers" class="text-sm text-f1-red hover:underline">View full standings</a>
          </div>
        </div>
        
        <!-- Constructor Standings -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white flex items-center">
            <i class="fas fa-trophy mr-2 text-f1-red"></i>
            Constructor Standings
          </h2>
          <div v-if="constructorStandings.length === 0" class="text-gray-500 dark:text-gray-400 py-4 text-center">
            <i class="fas fa-table mb-2 text-2xl opacity-70"></i>
            <p>No standings available</p>
          </div>
          <ul v-else class="space-y-2">
            <li v-for="standing in constructorStandings" :key="standing.id"
                class="flex justify-between items-center py-1 border-b dark:border-gray-700 last:border-0">
              <span class="flex items-center">
                <span class="inline-block w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white flex items-center justify-center mr-3 text-sm">
                  {{ standing.position }}
                </span>
                {{ standing.constructor?.name }}
              </span>
              <span class="font-bold text-gray-900 dark:text-white">{{ standing.points }} pts</span>
            </li>
          </ul>
          <div class="mt-3 text-right">
            <a href="/standings?tab=constructors" class="text-sm text-f1-red hover:underline">View full standings</a>
          </div>
        </div>
        
        <!-- Latest Results -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white flex items-center">
            <i class="fas fa-history mr-2 text-f1-red"></i>
            Latest Results
          </h2>
          <div v-if="latestRaces.length === 0" class="text-gray-500 dark:text-gray-400 py-4 text-center">
            <i class="fas fa-calendar-minus mb-2 text-2xl opacity-70"></i>
            <p>No race results available</p>
          </div>
          <ul v-else class="space-y-3">
            <li v-for="race in latestRaces" :key="race.race_id" class="border-b dark:border-gray-700 pb-2">
              <div class="font-medium dark:text-white">{{ race.name }}</div>
              <div class="text-sm text-gray-600 dark:text-gray-300">
                {{ race.circuit?.name }}
              </div>
              <div class="text-sm text-gray-500 dark:text-gray-400">{{ new Date(race.race_date).toLocaleDateString() }}</div>
              <div class="mt-1">
                <a :href="`/races/${race.race_id}/results`" class="text-xs text-f1-red hover:underline">View results</a>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AppLayout>
</template>