<template>
  <div>
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Race Results</h2>
    
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-f1-red mx-auto"></div>
      <p class="mt-2 text-gray-600 dark:text-gray-400">Loading race results...</p>
    </div>
    
    <div v-else-if="!results.length" class="text-center py-8">
      <p class="text-gray-600 dark:text-gray-400">No results available yet.</p>
    </div>
    
    <div v-else class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Pos</th>
              <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Grid</th>
              <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Driver</th>
              <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Constructor</th>
              <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Laps</th>
              <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Time/Retired</th>
              <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Fastest Lap</th>
              <th scope="col" class="px-3 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Points</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
            <tr v-for="result in results" :key="result.result_id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
              <td class="whitespace-nowrap px-3 py-4 text-sm">
                <span :class="getPositionClass(result.position)">{{ result.position_text || result.position }}</span>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                {{ result.grid_position }}
              </td>
              <td class="px-3 py-4">
                <div class="flex items-center">
                  <CountryFlag v-if="result.driver?.nationality" :code="result.driver.nationality.code" class="h-4 w-6 mr-2" />
                  <span class="font-medium text-gray-900 dark:text-white">{{ result.driver?.first_name }} {{ result.driver?.last_name }}</span>
                </div>
              </td>
              <td class="px-3 py-4">
                <div class="flex items-center">
                  <div class="h-3 w-1 mr-2" :style="{ backgroundColor: constructorColor(result.constructor) }"></div>
                  <span class="text-sm text-gray-900 dark:text-white">{{ result.constructor?.name }}</span>
                </div>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                {{ result.laps }}
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                <span v-if="result.status === 'Finished'">{{ result.race_time || '-' }}</span>
                <span v-else class="text-red-600 dark:text-red-400">{{ result.status }}</span>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm">
                <div v-if="result.fastest_lap_time">
                  <span class="text-gray-500 dark:text-gray-400">{{ result.fastest_lap_time }}</span>
                  <span v-if="isFastestLap(result)" class="ml-2 inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900 dark:text-purple-300">
                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M13 7L11 7 11 15 15 15 15 13 13 13z"></path>
                      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"></path>
                    </svg>
                    FL
                  </span>
                </div>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-right text-sm font-medium">
                {{ result.points }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import CountryFlag from '@/components/CountryFlag.vue';

const props = defineProps({
  race: Object,
  results: Array,
  loading: Boolean
});

// Encontrar el resultado con la vuelta más rápida
const fastestLapResult = computed(() => {
  if (!props.results || props.results.length === 0) return null;
  
  let fastest = null;
  let fastestTime = Infinity;
  
  props.results.forEach(result => {
    if (result.fastest_lap_time && !isNaN(parseFloat(result.fastest_lap_speed))) {
      const speed = parseFloat(result.fastest_lap_speed);
      if (speed > 0 && speed < fastestTime) {
        fastestTime = speed;
        fastest = result;
      }
    }
  });
  
  return fastest;
});

// Comprobar si un resultado tiene la vuelta más rápida
function isFastestLap(result) {
  if (!fastestLapResult.value) return false;
  return result.result_id === fastestLapResult.value.result_id;
}

// Determinar el color para cada constructor
function constructorColor(constructor) {
  if (!constructor) return '#cccccc';
  
  const name = constructor.name || '';
  
  // Mapeo flexible de colores para constructores
  if (name.includes('Mercedes')) return '#00D2BE';
  if (name.includes('Ferrari')) return '#DC0000';
  if (name.includes('Red Bull')) return '#0600EF';
  if (name.includes('McLaren')) return '#FF8700';
  if (name.includes('Alpine')) return '#0090FF';
  if (name.includes('Racing Bulls')) return '#2B4562';
  if (name.includes('Aston Martin')) return '#006F62';
  if (name.includes('Williams')) return '#005AFF';
  if (name.includes('Kick')) return '#900000';
  if (name.includes('Haas')) return '#FFFFFF';
  
  // Color por defecto si no hay coincidencias
  return '#cccccc';
}

// Obtener clase para posiciones especiales
function getPositionClass(position) {
  if (position === 1) {
    return 'font-bold text-yellow-600 dark:text-yellow-400';
  } else if (position === 2) {
    return 'font-bold text-gray-600 dark:text-gray-400';
  } else if (position === 3) {
    return 'font-bold text-amber-700 dark:text-amber-500';
  }
  return 'text-gray-900 dark:text-white';
}
</script>