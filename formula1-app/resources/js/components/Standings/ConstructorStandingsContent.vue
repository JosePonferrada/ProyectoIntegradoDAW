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
      <div v-if="constructorStandings.length === 0" class="bg-white dark:bg-gray-900 rounded-md p-8 text-center shadow">
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
            <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Team</th>
            <th scope="col" class="w-32 px-6 py-3 text-center font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Nationality</th>
            <th scope="col" class="w-24 px-6 py-3 text-right font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Points</th>
            <th scope="col" class="w-16 px-6 py-3 text-center font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Wins</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(standing, index) in constructorStandings" :key="standing.id" 
              :class="[
                index % 2 ? 'bg-gray-50 dark:bg-gray-800/50' : 'bg-white dark:bg-gray-900',
                'hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors duration-150'
              ]">
            
            <!-- Posición con círculo mejorado -->
            <td class="px-4 py-4 text-center">
              <div class="flex justify-center">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white font-bold">
                  {{ standing.position }}
                </span>
              </div>
            </td>
            
            <!-- Constructor con barra de color -->
            <td class="px-4 py-4">
              <div class="flex items-center gap-3">
                <div class="w-1 h-10" :style="`background-color: ${getConstructorColor(standing.constructor?.name)}`"></div>
                <img v-if="standing.constructor?.logo" :src="`/storage/${standing.constructor.logo}`" 
                     class="h-8 w-auto object-contain" :alt="standing.constructor?.name">
                <div class="font-medium text-gray-900 dark:text-white">{{ standing.constructor?.name }}</div>
              </div>
            </td>
            
            <!-- Nacionalidad con bandera -->
            <td class="px-4 py-4 text-center">
              <div class="flex items-center justify-center gap-2">
                <CountryFlag v-if="standing.constructor?.nationality?.code" 
                            :countryCode="standing.constructor.nationality.code" 
                            class="h-4 w-6" />
                <span class="text-gray-700 dark:text-gray-300">{{ standing.constructor?.nationality?.name }}</span>
              </div>
            </td>
            
            <!-- Puntos -->
            <td class="px-4 py-4 text-right">
              <span class="font-bold text-lg text-gray-900 dark:text-white">{{ standing.points }}</span>
            </td>
            
            <!-- Victorias con estilo de badge -->
            <td class="px-4 py-4 text-center">
              <span v-if="standing.wins > 0" 
                    class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 rounded-full bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200 font-medium">
                {{ standing.wins }}
              </span>
              <span v-else class="text-gray-400 dark:text-gray-600">0</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Vista de gráfico -->
    <div v-else-if="viewMode === 'chart'" class="mt-4">
      <!-- Mensaje cuando no hay datos -->
      <div v-if="constructorStandings.length === 0" class="bg-white dark:bg-gray-900 rounded-md p-8 text-center shadow">
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
    <div v-if="constructorStandings.length > 0" class="mt-12">
      <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Season Progression</h2>
      
      <!-- Mensaje cuando no hay suficientes carreras -->
      <div v-if="!seasonProgression?.races?.length || seasonProgression.races.length < 2" 
           class="bg-white dark:bg-gray-900 rounded-md p-8 text-center shadow">
        <div class="text-gray-500 dark:text-gray-400">
          <i class="fas fa-chart-line text-4xl mb-4"></i>
          <p class="text-lg">At least two races are needed to show progression.</p>
        </div>
      </div>
      
      <!-- Gráfico cuando hay datos suficientes -->
      <div v-else class="h-[400px]">
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
import { ref, computed, onMounted } from 'vue';
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
const constructorStandings = ref([]);
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
    let url;
    if (selectedRace.value === 'current') {
      // Cargar clasificación acumulada
      url = `/api/constructor-standings?season_id=${selectedSeason.value}&accumulated=true`;
      console.log("Loading accumulated constructor standings");
    } else {
      // Cargar clasificación para una carrera específica
      url = `/api/constructor-standings?race_id=${selectedRace.value}`;
      console.log("Loading constructor standings for specific race:", selectedRace.value);
    }
    
    const response = await axios.get(url);
    constructorStandings.value = response.data.data || [];
    
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
    const response = await axios.get(`/api/seasons/${selectedSeason.value}/constructor-progression`);
    seasonProgression.value = response.data || null;
  } catch (error) {
    console.error('Error loading progression data:', error);
  }
};

// Datos para el gráfico de barras
const chartData = computed(() => {
  if (!constructorStandings.value.length) return null;
  
  // Ordenar constructores por posición
  const sortedConstructors = [...constructorStandings.value].sort((a, b) => a.position - b.position);
  
  return {
    labels: sortedConstructors.map(s => s.constructor?.name),
    datasets: [
      {
        label: 'Points',
        backgroundColor: sortedConstructors.map(s => getConstructorColor(s.constructor?.name)),
        data: sortedConstructors.map(s => s.points),
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
          const constructor = constructorStandings.value[index]?.constructor;
          return constructor ? constructor.name : '';
        },
        afterLabel: (item) => {
          const constructor = constructorStandings.value[item.dataIndex]?.constructor;
          return constructor ? `Nationality: ${constructor.nationality?.name || 'N/A'}` : '';
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
  if (!seasonProgression.value || !seasonProgression.value.races || seasonProgression.value.races.length === 0) return null;
  
  // Usar los constructores de la clasificación general para determinar cuáles mostrar en la progresión
  const topConstructors = constructorStandings.value
    .filter(standing => standing.constructor) // Asegurarse que el constructor existe
    .sort((a, b) => a.position - b.position);
  
  const datasets = topConstructors.map(standing => {
    const constructorId = standing.constructor.constructor_id; // ID del constructor
    const constructorName = standing.constructor.name || 'Unknown Constructor';
    const color = getConstructorColor(constructorName);
    
    const accumulatedPointsData = [];
    let lastPointsForConstructor = 0; // Para el caso de que un constructor no aparezca en una carrera intermedia

    seasonProgression.value.races.forEach(race => {
      const raceStandingForConstructor = race.standings.find(s => s.constructor_id === constructorId);
      
      if (raceStandingForConstructor) {
        lastPointsForConstructor = parseFloat(raceStandingForConstructor.points);
        accumulatedPointsData.push(lastPointsForConstructor);
      } else {
        // Si el constructor no está en los standings de esta carrera (ej. no participó o no hay datos),
        // mantenemos sus puntos anteriores.
        accumulatedPointsData.push(lastPointsForConstructor);
      }
    });
    
    return {
      label: constructorName,
      data: accumulatedPointsData, // Usar directamente los puntos acumulados de la API
      borderColor: color,
      backgroundColor: color + '40', // Para el área bajo la línea si fill es true
      fill: false,
      tension: 0.1
    };
  });
  
  // Asegurarse de que todos los datasets tengan la misma longitud que el número de carreras
  // (Esto podría ser redundante si la API siempre devuelve datos para todos los constructores en todas las carreras,
  // pero es una buena salvaguarda)
  const numRaces = seasonProgression.value.races.length;
  datasets.forEach(ds => {
    if (ds.data.length < numRaces) {
      const diff = numRaces - ds.data.length;
      const lastVal = ds.data.length > 0 ? ds.data[ds.data.length - 1] : 0;
      for (let i = 0; i < diff; i++) {
        ds.data.push(lastVal); // Rellenar con el último valor conocido
      }
    }
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
    'Oracle Red Bull Racing': '#0600EF',
    'Mercedes-AMG PETRONAS Formula One Team': '#00D2BE',
    'Scuderia Ferrari HP': '#DC0000',
    'McLaren F1 Team': '#FF8700',
    'Aston Martin Aramco Cognizant F1 Team': '#006F62',
    'BWT Alpine Formula One Team': '#0090FF',
    'Williams Racing': '#005AFF',
    'Visa Cash App Racing Bulls Formula One Team': '#2B4562',
    'Stake F1 Team Kick Sauber': '#00E700',
    'MoneyGram Haas F1 Team': '#9C9FA2'
  };
  
  // Partial search if exact match not found
  if (!colors[constructorName]) {
    for (const [key, value] of Object.entries(colors)) {
      if (constructorName && key.includes(constructorName) || 
          (constructorName && constructorName.includes(key))) {
        return value;
      }
    }
  }
  
  return colors[constructorName] || '#' + Math.floor(Math.random()*16777215).toString(16);
}
</script>