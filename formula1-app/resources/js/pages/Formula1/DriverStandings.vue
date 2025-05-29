<template>
  <AppLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
          <!-- Header con título y filtros -->
          <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 md:mb-0">Driver Standings</h1>
            
            <div class="flex flex-wrap items-center gap-4">
              <!-- Selector de temporada -->
              <div class="w-full sm:w-auto">
                <select 
                  v-model="selectedSeason" 
                  @change="loadStandings"
                  class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                >
                  <option v-for="season in seasons" :key="season.id" :value="season.id">
                    {{ season.year }}
                  </option>
                </select>
              </div>
              
              <!-- Selector de carrera -->
              <div class="w-full sm:w-auto">
                <select 
                  v-model="selectedRace" 
                  @change="loadStandings"
                  class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                >
                  <option value="current">Current Standings</option>
                  <option v-for="race in races" :key="race.id" :value="race.id">
                    {{ race.name }}
                  </option>
                </select>
              </div>
              
              <!-- Selector de visualización -->
              <div class="flex rounded-md shadow-sm">
                <button 
                  @click="viewMode = 'table'" 
                  :class="[
                    'px-4 py-2 text-sm font-medium rounded-l-md',
                    viewMode === 'table' 
                      ? 'bg-f1-red text-white' 
                      : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-600'
                  ]"
                >
                  <span class="sr-only">Table View</span>
                  <i class="fas fa-table"></i>
                </button>
                <button 
                  @click="viewMode = 'chart'" 
                  :class="[
                    'px-4 py-2 text-sm font-medium rounded-r-md',
                    viewMode === 'chart' 
                      ? 'bg-f1-red text-white' 
                      : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-600'
                  ]"
                >
                  <span class="sr-only">Chart View</span>
                  <i class="fas fa-chart-bar"></i>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Spinner de carga -->
          <div v-if="loading" class="flex justify-center items-center py-12">
            <svg class="animate-spin h-10 w-10 text-f1-red" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
          
          <!-- Vista de tabla -->
          <div v-else-if="viewMode === 'table'" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pos</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Driver</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Team</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Points</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Wins</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="(standing, index) in driverStandings" :key="standing.id" :class="index % 2 ? 'bg-gray-50 dark:bg-gray-700' : ''">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ standing.position }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <img v-if="standing.driver.image" :src="`/storage/${standing.driver.image}`" alt="" class="h-8 w-8 rounded-full mr-3 object-cover">
                      <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                          {{ standing.driver.first_name }} {{ standing.driver.last_name }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                          <CountryFlag 
                            v-if="standing.driver.nationality?.code" 
                            :countryCode="standing.driver.nationality.code" 
                            size="w-4 h-3" 
                            :rounded="true" 
                            class="mr-1" 
                          />
                          {{ standing.driver.code }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <img v-if="standing.constructor?.logo" :src="`/storage/${standing.constructor?.logo}`" alt="" class="h-6 mr-2 object-contain">
                      <span class="text-sm text-gray-900 dark:text-white">{{ standing.constructor?.name || 'N/A' }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">{{ standing.points }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ standing.wins }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Vista de gráfico -->
          <div v-else-if="viewMode === 'chart'" class="mt-4">
            <div class="h-[600px]">
              <!-- Gráfico de barras horizontales -->
              <BarChart 
                :chart-data="chartData"
                :options="chartOptions"
              />
            </div>
          </div>

          <!-- Sección de evolución en la temporada -->
          <div v-if="driverStandings.length > 0" class="mt-12">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Season Progression</h2>
            <div class="h-[400px]">
              <!-- Gráfico de líneas mostrando evolución -->
              <LineChart 
                v-if="progressionData"
                :chart-data="progressionData"
                :options="progressionOptions"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CountryFlag from '@/components/CountryFlag.vue';
import BarChart from '@/components/Charts/BarChart.vue';
import LineChart from '@/components/Charts/LineChart.vue';
import axios from 'axios';

// Estados
const loading = ref(true);
const seasons = ref([]);
const races = ref([]);
const selectedSeason = ref(null);
const selectedRace = ref('current');
const driverStandings = ref([]);
const viewMode = ref('table');
const seasonProgression = ref(null); // Para el gráfico de evolución

// Cargar datos iniciales
onMounted(async () => {
  try {
    // Cargar temporadas
    const seasonsResponse = await axios.get('/api/seasons');
    seasons.value = seasonsResponse.data.data || [];
    
    // Establecer temporada actual por defecto
    if (seasons.value.length > 0) {
      const currentYear = new Date().getFullYear();
      const currentSeason = seasons.value.find(s => s.year === currentYear) || seasons.value[0];
      selectedSeason.value = currentSeason.id;
      
      // Cargar carreras de la temporada seleccionada
      await loadRaces();
      
      // Cargar clasificación
      await loadStandings();
    }
  } catch (error) {
    console.error('Error loading initial data:', error);
  } finally {
    loading.value = false;
  }
});

// Cargar carreras de la temporada seleccionada
const loadRaces = async () => {
  try {
    const racesResponse = await axios.get(`/api/seasons/${selectedSeason.value}/races`);
    races.value = racesResponse.data.data || [];
  } catch (error) {
    console.error('Error loading races:', error);
  }
};

// Cargar clasificación
const loadStandings = async () => {
  loading.value = true;
  try {
    // Cargar carreras si cambia la temporada
    if (selectedRace.value === 'current') {
      await loadRaces();
    }
    
    // Endpoint para obtener clasificación
    let url = `/api/seasons/${selectedSeason.value}/driver-standings`;
    if (selectedRace.value !== 'current') {
      url = `/api/races/${selectedRace.value}/driver-standings`;
    }
    
    const response = await axios.get(url);
    driverStandings.value = response.data.data || [];
    
    // También cargar datos de progresión para el gráfico de evolución
    await loadSeasonProgression();
  } catch (error) {
    console.error('Error loading standings:', error);
  } finally {
    loading.value = false;
  }
};

// Cargar datos de progresión para el gráfico de evolución
const loadSeasonProgression = async () => {
  try {
    const response = await axios.get(`/api/seasons/${selectedSeason.value}/driver-progression`);
    console.log('API Response for seasonProgression:', response.data); // <--- AÑADE ESTO
    seasonProgression.value = response.data || null;
    console.log('Assigned seasonProgression.value:', seasonProgression.value); // <--- Y ESTO
  } catch (error) {
    console.error('Error loading progression data:', error);
    seasonProgression.value = null; // Asegúrate de resetear en caso de error
  }
};

// Datos para el gráfico de barras
const chartData = computed(() => {
  if (!driverStandings.value.length) return null;
  
  // Ordenar pilotos por posición
  const sortedDrivers = [...driverStandings.value].sort((a, b) => a.position - b.position);
  
  return {
    labels: sortedDrivers.map(s => `${s.driver.code || s.driver.last_name}`),
    datasets: [
      {
        label: 'Points',
        backgroundColor: sortedDrivers.map(s => getConstructorColor(s.constructor?.name)),
        data: sortedDrivers.map(s => s.points),
        barThickness: 20,
      }
    ]
  };
});

// Opciones para el gráfico de barras
const chartOptions = {
  indexAxis: 'y',
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      callbacks: {
        title: (items) => {
          const index = items[0].dataIndex;
          const driver = driverStandings.value.find(d => d.driver.code === items[0].label);
          return `${driver.driver.first_name} ${driver.driver.last_name}`;
        },
        afterLabel: (item) => {
          const index = item.dataIndex;
          const driver = driverStandings.value.find(d => d.driver.code === item.label);
          return `Team: ${driver.constructor?.name || 'N/A'}`;
        }
      }
    }
  },
  scales: {
    x: {
      title: {
        display: true,
        text: 'Points'
      },
      grid: {
        color: 'rgba(200, 200, 200, 0.2)'
      }
    },
    y: {
      grid: {
        display: false
      }
    }
  }
};

// Datos para el gráfico de progresión
const progressionData = computed(() => {
  if (!seasonProgression.value) return null;
  
  // Crear datasets para cada piloto (limitado a los 5-8 mejores para claridad)
  const topDrivers = driverStandings.value
    .sort((a, b) => a.position - b.position)
    .slice(0, 8);
  
  const datasets = topDrivers.map(driver => {
    const color = getConstructorColor(driver.constructor?.name);
    
    return {
      label: driver.driver.code || driver.driver.last_name,
      data: seasonProgression.value.races.map(race => {
        const standing = race.standings.find(s => s.driver_id === driver.driver.id);
        return standing ? standing.points : 0;
      }),
      borderColor: color,
      backgroundColor: color + '40',
      fill: false,
      tension: 0.1
    };
  });
  
  return {
    labels: seasonProgression.value.races.map(r => r.name),
    datasets
  };
});

// Opciones para el gráfico de progresión
const progressionOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    tooltip: {
      callbacks: {
        label: (context) => {
          return `${context.dataset.label}: ${context.raw} points`;
        }
      }
    }
  },
  scales: {
    x: {
      title: {
        display: true,
        text: 'Races'
      },
      grid: {
        color: 'rgba(200, 200, 200, 0.2)'
      }
    },
    y: {
      title: {
        display: true,
        text: 'Points'
      },
      grid: {
        color: 'rgba(200, 200, 200, 0.2)'
      }
    }
  }
};

// Función para obtener un color según el constructor
function getConstructorColor(constructorName) {
  const colors = {
    'Mercedes': '#00D2BE',
    'Red Bull': '#0600EF',
    'Ferrari': '#DC0000',
    'McLaren': '#FF8700',
    'Aston Martin': '#006F62',
    'Alpine': '#0090FF',
    'Williams': '#005AFF',
    'AlphaTauri': '#2B4562',
    'Alfa Romeo': '#900000',
    'Haas': '#FFFFFF'
  };
  
  return colors[constructorName] || '#' + Math.floor(Math.random()*16777215).toString(16);
}
</script>