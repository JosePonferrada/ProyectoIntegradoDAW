<template>
  <div>
    <!-- Header con filtros -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
      <div class="flex flex-wrap items-center gap-4 ml-auto">
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
            <option value="current">Championship Standings</option>
            <option v-for="race in races" :key="race.race_id" :value="race.race_id">
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
      <!-- Mensaje cuando no hay datos -->
      <div v-if="driverStandings.length === 0" class="bg-white dark:bg-gray-900 rounded-md p-8 text-center shadow">
        <div class="text-gray-500 dark:text-gray-400">
          <i class="fas fa-info-circle text-4xl mb-4"></i>
          <p class="text-lg">No standings data available for this selection.</p>
          <p class="mt-2">Try selecting another race or season.</p>
        </div>
      </div>

      <table v-else class="min-w-full bg-white dark:bg-gray-900 text-gray-800 dark:text-white rounded-lg overflow-hidden">
        <thead class="bg-gray-100 dark:bg-gray-800">
          <tr>
            <th scope="col" class="w-16 px-6 py-3 text-center font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Pos</th>
            <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Driver</th>
            <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Team</th>
            <th scope="col" class="w-24 px-6 py-3 text-right font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Points</th>
            <th scope="col" class="w-16 px-6 py-3 text-center font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Wins</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(standing, index) in driverStandings" :key="standing.id" 
              :class="index % 2 ? 'bg-gray-50 dark:bg-gray-800/50' : 'bg-white dark:bg-gray-900'"
              class="border-b border-gray-200 dark:border-gray-700">
            <!-- Posición -->
            <td class="px-3 py-4 text-center font-bold">
              <div class="flex items-center justify-center">
                <span class="inline-block w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white flex items-center justify-center">
                  {{ standing.position }}
                </span>
              </div>
            </td>
            
            <!-- Piloto -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <img v-if="standing.driver?.image" :src="`/storage/${standing.driver?.image}`" 
                    alt="" class="h-10 w-10 mr-4 rounded-full object-cover border-2" 
                    :style="`border-color: ${getTeamColor(standing.constructor_name || standing.constructor_id || standing.driver?.code)}`">
                <div>
                  <div class="font-medium text-gray-900 dark:text-white">
                    {{ standing.driver?.first_name }} {{ standing.driver?.last_name }}
                  </div>
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ standing.driver?.code }}
                  </div>
                </div>
              </div>
            </td>
            
            <!-- Equipo -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="w-1 h-10 mr-3" 
                    :style="`background-color: ${getTeamColor(standing.constructor_name || standing.constructor_id || standing.driver?.code)}`"></div>
                <img v-if="standing.constructor_logo" :src="`/storage/${standing.constructor_logo}`" 
                    alt="" class="h-6 w-auto mr-2">
                <span class="text-gray-800 dark:text-white">{{ standing.constructor_name || 'N/A' }}</span>
              </div>
            </td>
            
            <!-- Puntos -->
            <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-gray-900 dark:text-white">
              {{ standing.points }}
            </td>
            
            <!-- Victorias -->
            <td class="px-6 py-4 whitespace-nowrap text-center text-gray-900 dark:text-white">
              {{ standing.wins }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Vista de gráfico -->
    <div v-else-if="viewMode === 'chart'" class="mt-4">
      <!-- Mensaje cuando no hay datos -->
      <div v-if="driverStandings.length === 0" class="bg-white dark:bg-gray-900 rounded-md p-8 text-center shadow">
        <div class="text-gray-500 dark:text-gray-400">
          <i class="fas fa-chart-bar text-4xl mb-4"></i>
          <p class="text-lg">No data available to generate chart.</p>
          <p class="mt-2">Select another race or season with available data.</p>
        </div>
      </div>
      
      <!-- Gráfico cuando hay datos -->
      <div v-else class="h-[600px]">
        <BarChart 
          v-if="chartData"
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
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
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
const debugInfo = ref([]); // Al principio de tu script

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

// Modificar el watcher de selectedSeason para forzar recarga
watch(selectedSeason, async (newValue) => {
  if (newValue) {
    // Reiniciar la selección de carrera a "current"
    selectedRace.value = 'current';
    // Forzar recarga de las carreras de la temporada
    await loadRaces();
    // Cargar clasificaciones
    await loadStandings();
  }
});

// Cargar carreras de la temporada seleccionada
const loadRaces = async () => {
  try {
    const racesResponse = await axios.get(`/api/seasons/${selectedSeason.value}/races`);
    races.value = racesResponse.data.data || [];
    console.log("Carreras:", races.value);
  } catch (error) {
    console.error('Error loading races:', error);
  }
};

// Modificar el método loadStandings
const loadStandings = async () => {
  loading.value = true;
  try {
    await loadRaces();
    
    let url;
    if (selectedRace.value === 'current') {
      // Cargar clasificación acumulada
      url = `/api/driver-standings?season_id=${selectedSeason.value}&accumulated=true`;
      console.log("Cargando clasificación acumulada");
    } else {
      // Cargar clasificación para una carrera específica
      url = `/api/driver-standings?race_id=${selectedRace.value}`;
      console.log("Cargando clasificación para carrera específica:", selectedRace.value);
    }
    
    console.log("URL de solicitud:", url);
    const response = await axios.get(url);
    console.log("Respuesta de API:", response.data);
    
    driverStandings.value = response.data.data || [];
    console.log("Datos de standings cargados:", driverStandings.value.length);
    
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
    // Añadir un timestamp para evitar caché
    const timestamp = new Date().getTime();
    const response = await axios.get(`/api/seasons/${selectedSeason.value}/driver-progression?t=${timestamp}`);
    seasonProgression.value = response.data || null;
    
    // Verificar si los datos contienen la nueva carrera
    console.log("Carreras cargadas en la progresión:", 
      seasonProgression.value?.races?.map(r => r.name) || []);
  } catch (error) {
    console.error('Error loading progression data:', error);
  }
};

// Añadir esta función a tu script
const updateStandingsAfterRace = async () => {
  try {
    loading.value = true;
    
    // Si no hay carrera seleccionada, mostrar mensaje
    if (selectedRace.value === 'current') {
      alert('Please select a specific race to update standings');
      loading.value = false;
      return;
    }
    
    // Llamar al endpoint para actualizar las clasificaciones
    const response = await axios.post(`/api/races/${selectedRace.value}/update-standings`);
    console.log('Update response:', response.data);
    
    // Recargar los datos
    await loadStandings();
    
    // Mostrar mensaje de éxito
    alert('Standings updated successfully!');
  } catch (error) {
    console.error('Error updating standings:', error);
    alert(`Error updating standings: ${error.response?.data?.message || error.message}`);
  } finally {
    loading.value = false;
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
        backgroundColor: sortedDrivers.map(s => getTeamColor(s.constructor_name || s.constructor_id || s.driver?.code)),
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
          return driver ? `${driver.driver.first_name} ${driver.driver.last_name}` : '';
        },
        afterLabel: (item) => {
          const driver = driverStandings.value.find(d => d.driver.code === item.label);
          return driver ? `Team: ${driver.constructor?.name || 'N/A'}` : '';
        }
      }
    }
  },
  scales: {
    x: {
      title: {
        display: true,
        text: 'Points',
        color: document.documentElement.classList.contains('dark') ? 'white' : '#333'
      },
      grid: {
        color: document.documentElement.classList.contains('dark') ? 'rgba(200, 200, 200, 0.2)' : 'rgba(0, 0, 0, 0.1)'
      },
      ticks: {
        color: document.documentElement.classList.contains('dark') ? '#ddd' : '#666'
      }
    },
    y: {
      grid: {
        display: false
      },
      ticks: {
        color: document.documentElement.classList.contains('dark') ? '#ddd' : '#666'
      }
    }
  }
};

// Datos para el gráfico de progresión
const progressionData = computed(() => {
  if (!seasonProgression.value || !seasonProgression.value.races) return null;
  
  // Obtener datos de la primera carrera para establecer colores consistentes
  const firstRaceStandings = seasonProgression.value.races[0]?.standings || [];
  
  // Agrupar pilotos por constructor para asignar colores coherentes
  const constructorDrivers = {};
  
  // Primero agrupamos los pilotos por constructor
  firstRaceStandings.forEach(standing => {
    const constructorId = standing.constructor_id;
    if (!constructorDrivers[constructorId]) {
      constructorDrivers[constructorId] = [];
    }
    constructorDrivers[constructorId].push(standing);
  });
  
  // Creamos los datasets ordenados por constructor
  const datasets = [];
  
  // Para cada constructor, procesamos sus pilotos
  Object.entries(constructorDrivers).forEach(([constructorId, drivers]) => {
    // Ordenamos los pilotos por posición dentro del equipo
    drivers.sort((a, b) => (a.position_number || 999) - (b.position_number || 999));
    
    // Obtenemos el color del constructor una sola vez
    const baseColor = getTeamColor(drivers[0].constructor_name || drivers[0].constructor_id || drivers[0].driver_code);
    
    // Para cada piloto del constructor, creamos su dataset con el estilo correspondiente
    drivers.forEach((standing, index) => {
      const driverCode = standing.driver_code || 'Unknown';
      
      // Todos los pilotos del mismo equipo comparten el mismo color base
      const color = baseColor || getTeamColor(driverCode);
      
      // El estilo de línea se basa en la posición en el equipo
      let borderDash = [];
      let borderWidth = 3;
      
      // Primer piloto: línea sólida, segundo: discontinua, resto: punteada
      if (index === 1) {
        borderDash = [5, 5];  // línea discontinua para segundo piloto
      } else if (index > 1) {
        borderDash = [2, 2];  // línea de puntos para reserva/prueba
        borderWidth = 2;
      }
      
      datasets.push({
        label: driverCode,
        data: seasonProgression.value.races.map(race => {
          const raceStanding = race.standings.find(s => s.driver_id === standing.driver_id);
          return raceStanding ? raceStanding.points : 0;
        }),
        borderColor: color,
        backgroundColor: color + '40',
        fill: false,
        tension: 0.1,
        borderWidth: borderWidth,
        borderDash: borderDash
      });
    });
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
        text: 'Races',
        color: document.documentElement.classList.contains('dark') ? 'white' : '#333'
      },
      grid: {
        color: document.documentElement.classList.contains('dark') ? 'rgba(200, 200, 200, 0.2)' : 'rgba(0, 0, 0, 0.1)'
      },
      ticks: {
        color: document.documentElement.classList.contains('dark') ? '#ddd' : '#666'
      }
    },
    y: {
      title: {
        display: true,
        text: 'Points'
      },
      grid: {
        color: 'rgba(200, 200, 200, 0.2)'
      },
      ticks: {
        color: document.documentElement.classList.contains('dark') ? '#ddd' : '#666'
      }
    }
  }
};

// Función unificada para obtener un color según el equipo o piloto
function getTeamColor(data) {
  // Manejar diferentes tipos de datos de entrada
  if (!data) return '#777777'; // Color por defecto si no hay datos
  
  // Colores por equipo
  const teamColors = {
    // Por nombre (case insensitive)
    'red bull': '#0600EF',
    'ferrari': '#DC0000',
    'mercedes': '#00D2BE',
    'mclaren': '#FF8700',
    'aston martin': '#006F62',
    'alpine': '#0090FF',
    'rb': '#2B4562',
    'alphatauri': '#2B4562', 
    'haas': '#FFFFFF',
    'williams': '#005AFF',
    'alfa romeo': '#900000',
    
    // Por ID numérico
    1: '#0600EF',  // Red Bull
    2: '#DC0000',  // Ferrari
    3: '#00D2BE',  // Mercedes
    4: '#FF8700',  // McLaren
    5: '#006F62',  // Aston Martin
    6: '#0090FF',  // Alpine
    7: '#2B4562',  // RB/AlphaTauri
    8: '#FFFFFF',  // Haas
    9: '#005AFF',  // Williams
    10: '#900000', // Alfa Romeo
    
    // Por código de pilotos
    'VER': '#0600EF', 'PER': '#0600EF',  // Red Bull
    'HAM': '#00D2BE', 'RUS': '#00D2BE',  // Mercedes
    'LEC': '#DC0000', 'SAI': '#DC0000',  // Ferrari  
    'NOR': '#FF8700', 'PIA': '#FF8700',  // McLaren
    'ALO': '#006F62', 'STR': '#006F62',  // Aston Martin
    'GAS': '#0090FF', 'OCO': '#0090FF',  // Alpine
    'TSU': '#2B4562', 'SAR': '#2B4562',  // RB/AlphaTauri
    'HUL': '#FFFFFF', 'MAG': '#FFFFFF',  // Haas
    'ALB': '#005AFF', 'LAW': '#005AFF',  // Williams
    'BOT': '#900000', 'ZHO': '#900000'   // Alfa Romeo
  };
  
  // Intentar varias posibilidades
  if (typeof data === 'string') {
    // Intentar match exacto
    if (teamColors[data]) return teamColors[data];
    
    // Intentar match no sensible a mayúsculas/minúsculas
    const lowerCaseData = data.toLowerCase();
    if (teamColors[lowerCaseData]) return teamColors[lowerCaseData];
    
    // Buscar por substring (para nombres parciales)
    for (const [key, value] of Object.entries(teamColors)) {
      if (typeof key === 'string' && key.length > 2 && 
          lowerCaseData.includes(key.toLowerCase())) {
        return value;
      }
    }
    
    console.log('No color match for:', data); // Para depuración
  } else if (typeof data === 'number') {
    // Para IDs numéricos
    if (teamColors[data]) return teamColors[data];
  }
  
  // Color por defecto si no hay match
  return '#777777';
}
</script>