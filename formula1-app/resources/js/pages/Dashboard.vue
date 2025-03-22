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

onMounted(async () => {
  try {
    const [racesRes, driverRes, constructorRes] = await Promise.all([
      axios.get('/api/races'),
      axios.get('/api/driver-standings'),
      axios.get('/api/constructor-standings')
    ]);
    
    // Procesamiento de datos
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
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <AppLayout>
    <Head title="Dashboard" />
    
    <div class="py-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Formula 1 Dashboard</h1>
      
      <div v-if="loading" class="text-center py-12">
        <p class="text-gray-500 text-lg">Loading dashboard data...</p>
      </div>
      
      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Upcoming Races -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-800">Upcoming Races</h2>
          <div v-if="upcomingRaces.length === 0" class="text-gray-500">No upcoming races</div>
          <ul v-else class="space-y-3">
            <li v-for="race in upcomingRaces" :key="race.race_id" class="border-b pb-2">
              <div class="font-medium">{{ race.name }}</div>
              <div class="text-sm text-gray-600">
                {{ race.circuit?.name }}, {{ race.circuit?.country?.name }}
              </div>
              <div class="text-sm text-gray-500">{{ new Date(race.race_date).toLocaleDateString() }}</div>
            </li>
          </ul>
        </div>
        
        <!-- Driver Standings -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-800">Driver Standings</h2>
          <div v-if="driverStandings.length === 0" class="text-gray-500">No standings available</div>
          <ul v-else class="space-y-2">
            <li v-for="standing in driverStandings" :key="standing.driver_standing_id" class="flex justify-between">
              <span>
                <span class="font-medium">{{ standing.position }}.</span>
                {{ standing.driver?.first_name }} {{ standing.driver?.last_name }}
              </span>
              <span class="font-bold">{{ standing.points }} pts</span>
            </li>
          </ul>
        </div>
        
        <!-- Constructor Standings -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-800">Constructor Standings</h2>
          <div v-if="constructorStandings.length === 0" class="text-gray-500">No standings available</div>
          <ul v-else class="space-y-2">
            <li v-for="standing in constructorStandings" :key="standing.constructor_standing_id" class="flex justify-between">
              <span>
                <span class="font-medium">{{ standing.position }}.</span>
                {{ standing.constructor?.name }}
              </span>
              <span class="font-bold">{{ standing.points }} pts</span>
            </li>
          </ul>
        </div>
        
        <!-- Latest Results -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-800">Latest Results</h2>
          <div v-if="latestRaces.length === 0" class="text-gray-500">No race results available</div>
          <ul v-else class="space-y-3">
            <li v-for="race in latestRaces" :key="race.race_id" class="border-b pb-2">
              <div class="font-medium">{{ race.name }}</div>
              <div class="text-sm text-gray-600">
                {{ race.circuit?.name }}
              </div>
              <div class="text-sm text-gray-500">{{ new Date(race.race_date).toLocaleDateString() }}</div>
            </li>
          </ul>
        </div>
        <div class="bg-white border border-gray-200 p-6 rounded-lg shadow-sm">
          <h2 class="text-xl font-semibold text-gray-800 mb-4">Próximas Carreras</h2>
          <p class="text-gray-700">No hay carreras programadas próximamente.</p>
        </div>
        
        <!-- Otra tarjeta de ejemplo -->
        <div class="bg-white border border-gray-200 p-6 rounded-lg shadow-sm">
          <h2 class="text-xl font-semibold text-gray-800 mb-4">Clasificación de Pilotos</h2>
          <p class="text-gray-700">Cargando clasificación...</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>