<template>
  <AppLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Contenedor principal con estilo coherente con Standings.vue -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
          <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Race Calendar</h1>
            
            <!-- Solo mostrar a administradores -->
            <div v-if="isAdmin">
              <a href="/formula1/races/create" 
                 class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md font-medium text-sm">
                <i class="fas fa-plus mr-1"></i> Add Race
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
              @click="goToRaceDetails(race.id)" 
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
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

// Calcular la próxima carrera programada
const nextRace = computed(() => {
  const now = new Date();
  
  // Filtrar carreras programadas o próximas
  const upcomingRaces = races.value.filter(race => {
    if (!race.race_date) return false;
    
    const raceDate = new Date(race.race_date);
    const status = race.status?.toLowerCase() || '';
    
    return raceDate >= now || ['scheduled', 'upcoming', 'confirmed'].includes(status);
  });
  
  // Ordenar por fecha (la más cercana primero)
  upcomingRaces.sort((a, b) => new Date(a.race_date) - new Date(b.race_date));
  
  // Devolver la primera carrera (la más cercana)
  return upcomingRaces.length > 0 ? upcomingRaces[0] : null;
});

// Cargar datos iniciales
onMounted(async () => {
  console.log('Component mounted');
  await loadSeasons();
});

// Cargar temporadas
async function loadSeasons() {
  try {
    console.log('Loading seasons...');
    const response = await axios.get('/api/seasons');
    seasons.value = response.data.data || [];
    console.log('Seasons loaded:', seasons.value);
    
    // Seleccionar la temporada actual (2025 en este caso)
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
    // Valores por defecto en caso de error
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
    
    // Filtrar carreras por temporada (filtro adicional por si la API no filtra correctamente)
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

// Filtrar carreras según el estado seleccionado
const filteredRaces = computed(() => {
  console.log('Filtering races with status:', statusFilter.value);
  
  // Primero aseguramos que solo se muestren carreras de la temporada seleccionada
  const racesBySelectedSeason = races.value.filter(race => {
    return race.season?.id === selectedSeason.value;
  });
  
  // Aplicar filtro por estado
  let result;
  if (statusFilter.value === 'all') {
    result = racesBySelectedSeason;
  } else {
    result = racesBySelectedSeason.filter(race => {
      const status = race.status?.toLowerCase() || '';
      
      if (statusFilter.value === 'upcoming') {
        return ['scheduled', 'upcoming', 'confirmed'].includes(status);
      } else if (statusFilter.value === 'completed') {
        return ['completed', 'finished'].includes(status);
      } else if (statusFilter.value === 'cancelled') {
        return ['cancelled', 'postponed'].includes(status);
      }
      
      return true;
    });
  }
  
  // Ordenar por fecha: carreras futuras primero (más cercanas primero), luego pasadas (más recientes primero)
  const now = new Date();
  const futureRaces = result.filter(race => new Date(race.race_date) >= now);
  const pastRaces = result.filter(race => new Date(race.race_date) < now);
  
  // Ordenar futuras cronológicamente (más cercanas primero)
  futureRaces.sort((a, b) => new Date(a.race_date) - new Date(b.race_date));
  
  // Ordenar pasadas cronológicamente inverso (más recientes primero)
  pastRaces.sort((a, b) => new Date(b.race_date) - new Date(a.race_date));
  
  // Retornar primero las futuras y luego las pasadas
  return [...futureRaces, ...pastRaces];
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