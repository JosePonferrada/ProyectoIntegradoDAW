<template>
  <div>
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold text-gray-900 dark:text-white">Bulk Results Management</h2>
      
      <div class="flex space-x-2">
        <button
          @click="addAllDefaultPilots"
          title="Add all available drivers with default values"
          class="px-3 py-1.5 bg-gray-700 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-500 text-white rounded-md text-sm flex items-center"
        >
          <i class="fas fa-users-cog mr-1"></i>
          Add All
        </button>
        <button
          @click="addMissingPilots"
          title="Add any missing drivers from the available list"
          class="px-3 py-1.5 bg-gray-700 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-500 text-white rounded-md text-sm flex items-center"
        >
          <i class="fas fa-user-plus mr-1"></i>
          Add Missing
        </button>
        <button 
          @click="saveAllResults" 
          :disabled="isSaving"
          class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm flex items-center"
        >
          <i v-if="isSaving" class="fas fa-spinner fa-spin mr-1"></i>
          <i v-else class="fas fa-save mr-1"></i> 
          Save Results
        </button>
      </div>
    </div>

    <div v-if="internalLoading" class="text-center py-8">
      <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-f1-red mx-auto"></div>
      <p class="mt-2 text-gray-600 dark:text-gray-400">Loading data...</p>
    </div>
    
    <div v-else>
      <!-- Instrucciones para el usuario -->
      <div class="bg-blue-50 dark:bg-blue-900/30 border-l-4 border-blue-500 p-4 mb-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="fas fa-info-circle text-blue-500"></i>
          </div>
          <div class="ml-3">
            <p class="text-sm text-blue-700 dark:text-blue-300">
              Drag and drop drivers to set the final race order. Position will be assigned automatically.
            </p>
          </div>
        </div>
      </div>
      
      <!-- Encabezados de columnas -->
      <div class="grid grid-cols-12 gap-4 px-4 py-2 bg-gray-100 dark:bg-gray-800 text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 rounded-t-md border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10">
        <div class="col-span-1 text-center">Pos</div>
        <div class="col-span-3">Driver/Team</div>
        <div class="col-span-1 text-center">Grid</div>
        <div class="col-span-4">Status/Time</div>
        <div class="col-span-1 text-center">Laps</div>
        <div class="col-span-2 text-center">Points</div>
      </div>
      
      <!-- Tabla con draggable -->
      <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
        <draggable 
          :list="raceResults"  
          item-key="tempId" 
          handle=".handle"  
          class="divide-y divide-gray-200 dark:divide-gray-700"
          @change="updatePositionsAfterChange" 
        >
          <div 
            v-for="(element, index) in raceResults" 
            :key="element.tempId"
            class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors duration-150"
          >
            <div class="p-4 grid grid-cols-12 gap-4 items-center">
              <!-- Columna Pos con icono de FL -->
              <div class="col-span-1 flex items-center">
                <div class="handle cursor-move text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 mr-2">
                  <i class="fas fa-grip-vertical"></i>
                </div>
                <span class="font-bold text-lg" :class="getPositionClass(index + 1)">{{ index + 1 }}</span>
                <i 
                  v-if="fastestLapIndex === index" 
                  class="fas fa-stopwatch text-xs text-purple-500 ml-1.5" 
                  title="Has Fastest Lap"
                ></i>
              </div>
              <!-- Driver + Team Selection -->
              <div class="col-span-3">
                <select v-model="element.driver_id" disabled @change="() => handleDriverChange(element)" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 dark:text-white text-sm p-2">
                  <option value="">Select Driver</option>
                  <option v-for="driver in availableDrivers" :key="driver.id" :value="driver.id">
                    {{ driver.full_name }} ({{ driver.code }})
                  </option>
                </select>
                <select v-model="element.constructor_id" disabled class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 dark:text-white text-sm p-2">
                  <option value="">Select Team</option>
                  <option v-for="constructor in availableConstructors" :key="constructor.id" :value="constructor.id">
                    {{ constructor.name }}
                  </option>
                </select>
              </div>
              <!-- Columna Grid -->
              <div class="col-span-1">
                <input type="number" v-model.number="element.grid_position" min="1" max="20" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 dark:text-white text-sm p-2 text-center" />
              </div>
              <!-- Columna Status/Time -->
              <div class="col-span-4">
                <div class="flex space-x-2">
                    <select v-model="element.status" class="mt-1 block w-1/2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 dark:text-white text-sm p-2">
                        <option value="Finished">Finished</option>
                        <option value="DNF">DNF</option>
                        <option value="DSQ">DSQ</option>
                        <option value="DNS">DNS</option>
                        <option value="+1 Lap">+1 Lap</option>
                        <option v-for="i in 3" :key="`+${i+1}Laps`" :value="`+${i+1} Laps`">
                          +{{ i + 1 }} Laps
                        </option>
                    </select>
                    <input 
                        type="text" 
                        v-model="element.race_time" 
                        placeholder="HH:MM:SS.mmm" 
                        :disabled="element.status !== 'Finished'"
                        class="mt-1 block w-1/2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 dark:text-white text-sm p-2" />
                </div>
              </div>
              <!-- Columna Laps -->
              <div class="col-span-1">
                <input type="number" v-model.number="element.laps" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 dark:text-white text-sm p-2 text-center" />
              </div>
              <!-- Columna Points + Botón Desplegar FL -->
              <div class="col-span-2 flex items-center justify-end space-x-2">
                <input type="number" disabled v-model.number="element.points" class="mt-1 block w-16 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 dark:text-white text-sm p-2 text-center" />
                <button 
                  @click="toggleRowExpansion(element.tempId)" 
                  class="p-1 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                  :title="expandedRowId === element.tempId ? 'Hide Fastest Lap Details' : 'Show Fastest Lap Details'"
                >
                  <i :class="['fas', expandedRowId === element.tempId ? 'fa-chevron-up text-red-500' : 'fa-chevron-down text-gray-500 dark:text-gray-400']"></i>
                </button>
              </div>
            </div>

            <!-- Sección Desplegable para Detalles de Vuelta Rápida -->
            <div v-if="expandedRowId === element.tempId" class="px-4 pb-4 pt-3 mt-1 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 rounded-b-md">
              <div class="grid grid-cols-1 md:grid-cols-4 gap-x-4 gap-y-2 items-end">
                <div>
                  <label :for="'fl_lap_num_' + element.tempId" class="block text-xs font-medium text-gray-700 dark:text-gray-400">FL Lap #</label>
                  <input 
                    type="number" 
                    min="1"
                    :max="element.laps > 0 ? element.laps : null"
                    :id="'fl_lap_num_' + element.tempId"
                    v-model.number="element.fastest_lap" 
                    @change="handleFastestLapChange(element, index)"
                    placeholder="Lap" 
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 dark:text-white text-sm p-2" 
                  />
                </div>
                <div>
                  <label :for="'fl_time_' + element.tempId" class="block text-xs font-medium text-gray-700 dark:text-gray-400">FL Time (MM:SS.mmm)</label>
                  <input 
                    type="text" 
                    :id="'fl_time_' + element.tempId"
                    v-model="element.fastest_lap_time" 
                    placeholder="01:23.456" 
                    :disabled="!element.fastest_lap || element.fastest_lap <= 0"
                    @change="updateFastestLapDetails(element, index)"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 dark:text-white text-sm p-2" 
                  />
                </div>
                <div>
                  <label :for="'fl_speed_' + element.tempId" class="block text-xs font-medium text-gray-700 dark:text-gray-400">FL Avg. Speed (km/h)</label>
                  <input 
                    type="number" 
                    step="0.01"
                    :id="'fl_speed_' + element.tempId"
                    v-model.number="element.fastest_lap_speed" 
                    placeholder="220.50" 
                    :disabled="!element.fastest_lap || element.fastest_lap <= 0"
                    @change="updateFastestLapDetails(element, index)"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 dark:text-white text-sm p-2" 
                  />
                </div>
                <div class="flex items-end">
                    <button 
                        v-if="element.fastest_lap && element.fastest_lap > 0"
                        @click="clearFastestLapForRow(element, index)"
                        class="mt-1 w-full px-3 py-2 text-xs bg-gray-600 hover:bg-gray-500 text-white rounded-md"
                        title="Clear Fastest Lap for this driver"
                    >
                        Clear FL
                    </button>
                </div>
              </div>
            </div>
          </div>
        </draggable>
      </div>
    </div>

    <!-- Notificación de éxito/error -->
    <div 
      v-if="notification.show" 
      :class="[
        'fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50 transition-all duration-500',
        notification.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
      ]"
    >
      <div class="flex items-center">
        <i :class="notification.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'" class="mr-2"></i>
        <span>{{ notification.message }}</span>
        <button @click="notification.show = false" class="ml-4 text-white hover:text-gray-200">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </div>
  <ConfirmationDialog
    v-model:show="showAddAllConfirmModal"
    title="Replace Existing Results?"
    message="This action will replace all current race results with the default list of drivers. Are you sure you want to continue?"
    confirmText="Replace All"
    cancelText="Cancel"
    type="danger" 
    @confirm="proceedWithAddAll"
    @cancel="showAddAllConfirmModal = false" 
  />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { VueDraggableNext as draggable } from 'vue-draggable-next';
import ConfirmationDialog from '../ConfirmationDialog.vue';

const props = defineProps({
  race: Object,
  loading: Boolean,
  resultsMode: String,
});

const emit = defineEmits(['results-updated']);

// Estado
const raceResults = ref([]);
const availableDrivers = ref([]);
const availableConstructors = ref([]);
const isSaving = ref(false);
const fastestLapIndex = ref(null);
const notification = ref({
  show: false,
  message: '',
  type: 'success'
});
const internalLoading = ref(false); // Nueva variable para manejar el estado de carga interna
const showAddAllConfirmModal = ref(false);
const expandedRowId = ref(null);

// Comprobar si la carrera ya tiene resultados
const raceHasResults = computed(() => {
  return props.race?.has_results || false;
});

// Obtener nombre de piloto por ID
function getDriverName(driverId) {
  if (!driverId) return '-';
  const driver = availableDrivers.value.find(d => d.driver_id === parseInt(driverId));
  return driver ? `${driver.first_name} ${driver.last_name}` : '-';
}

// Obtener clase para posiciones especiales
function getPositionClass(position) {
  if (position === 1) {
    return 'text-yellow-600 dark:text-yellow-400';
  } else if (position === 2) {
    return 'text-gray-600 dark:text-gray-400';
  } else if (position === 3) {
    return 'text-amber-700 dark:text-amber-500';
  }
  return 'text-gray-900 dark:text-white';
}

// Actualizar posiciones después de drag & drop
function updatePositions() {
  // Actualizar todas las posiciones basadas en el nuevo orden
  raceResults.value.forEach((result, index) => {
    result.position = index + 1;
    result.position_text = `${index + 1}`;
    result.position_order = index + 1;
  });
  
  // También actualizar los puntos automáticamente según la posición
  // (opcional: puedes comentar esto si prefieres asignación manual de puntos)
  updatePointsBasedOnPosition();
}

// Actualizar los puntos según la posición
function updatePointsBasedOnPosition() {
  const pointsSystem = [25, 18, 15, 12, 10, 8, 6, 4, 2, 1];
  
  raceResults.value.forEach((result, index) => {
    // Primero asigna los puntos base por posición
    if (result.status === 'Finished' && result.position_order <= pointsSystem.length) {
      result.points = parseInt(pointsSystem[result.position_order - 1]);
    } else {
      result.points = 0;
    }
  });
}

// Cargar datos necesarios
onMounted(async () => {
  internalLoading.value = true;
  try {
    const seasonId = props.race.season.id; // Asumiendo que props.race.season.id siempre existe
    console.log('BulkRaceResults: Season ID:', seasonId);

    const [mainDriversResponse, constructorsResponse] = await Promise.all([
      axios.get(`/api/seasons/${seasonId}/main-drivers`),
      axios.get('/api/constructors?per_page=all')
    ]);

    availableDrivers.value = mainDriversResponse.data.data || [];
    availableConstructors.value = constructorsResponse.data.data || [];
    console.log('BulkRaceResults: Available Drivers:', JSON.parse(JSON.stringify(availableDrivers.value)));
    console.log('BulkRaceResults: Available Constructors:', JSON.parse(JSON.stringify(availableConstructors.value)));

    const existingRaceResultsData = props.race?.results;
    const qualifyingData = props.race?.qualifying_results;

    if (existingRaceResultsData && existingRaceResultsData.length > 0) {
      raceResults.value = existingRaceResultsData.map((r, index) => {
        return {
          ...r,
          tempId: `existing-${r.result_id || r.driver?.id || index}`,
          driver_id: r.driver?.id || r.driver_id,
          constructor_id: r.constructor?.id || r.constructor_id,
          // Formatear el tiempo al cargar los datos
          fastest_lap_time: r.fastest_lap_time ? formatLapTime(r.fastest_lap_time) : null,
        };
      }).sort((a, b) => a.position_order - b.position_order);
      findBestFastestLap();

    } else if (qualifyingData && qualifyingData.length > 0) {
      console.log('BulkRaceResults: Poblando con resultados de clasificación.');
      // console.log('Qualifying Data (props.race):', JSON.parse(JSON.stringify(props.race))); // Ya lo tienes arriba
      
      raceResults.value = qualifyingData
        .sort((a, b) => a.position - b.position)
        .map((q, index) => {
          // q es un elemento de props.race.qualifying_results
          // q.driver es el objeto driver, q.constructor es el objeto constructor
          
          const currentDriverId = q.driver?.id; // ID del piloto desde los datos de qualy
          let constructorIdFromQualy = q.constructor?.id; // ID del constructor desde los datos de qualy

          // Intentar encontrar el constructor del piloto para esta temporada desde availableDrivers
          // Esto es para asegurar que se usa el constructor con el que el piloto corrió esa temporada,
          // que podría ser diferente del constructor general del resultado de qualy si los datos son inconsistentes.
          const driverDetails = availableDrivers.value.find(d => d.id === currentDriverId); // Asumiendo que availableDrivers usa 'id'
          
          let constructorIdForDriverFromSeason = null;
          if (driverDetails && driverDetails.seasons && driverDetails.seasons.length > 0) {
            const seasonData = driverDetails.seasons.find(s => s.season_id === seasonId);
            if (seasonData && seasonData.pivot) {
                constructorIdForDriverFromSeason = seasonData.pivot.constructor_id;
            } else if (driverDetails.seasons[0] && driverDetails.seasons[0].pivot) { // Fallback a la primera temporada si no se encuentra la específica
                 constructorIdForDriverFromSeason = driverDetails.seasons[0].pivot.constructor_id;
            }
          }
          
          console.log(`Driver ID: ${currentDriverId}, Constructor from Qualy: ${constructorIdFromQualy}, Constructor from Season Logic: ${constructorIdForDriverFromSeason}`);

          return {
            tempId: `qly-${q.id || index}`, // q.id es el ID del resultado de qualy (ej. 71)
            driver_id: currentDriverId,
            // Prioriza el constructor de la temporada si se encontró, sino el de la qualy.
            constructor_id: constructorIdForDriverFromSeason || constructorIdFromQualy,
            grid_position: q.position,
            position: index + 1,
            position_text: `${index + 1}`,
            position_order: index + 1,
            points: 0,
            laps: props.race?.scheduled_laps || props.race?.total_laps || 0, // Usar scheduled_laps si está disponible
            race_time: null,
            status: 'Finished',
            fastest_lap: null,
            fastest_lap_time: null,
            fastest_lap_speed: null,
          };
        });
      updatePointsBasedOnPosition();
    } else {
      console.log('BulkRaceResults: Poblando con lista de pilotos principales (sin qualy ni resultados previos).');
      console.log('BulkRaceResults: Estructura de availableDrivers.value ANTES del map:', JSON.parse(JSON.stringify(availableDrivers.value)));
      const currentSeasonId = props.race.season.id; // ID de la temporada de la carrera actual

      raceResults.value = availableDrivers.value.map((driver, index) => {
        let constructorIdForDriver = null;
        let constructorForDriver = null;

        // La lógica ahora debe usar driver.constructors, que ya viene filtrado por la temporada actual
        // desde Driver::getMainDriversForSeason
        if (driver.constructors && driver.constructors.length > 0) {
          // Como la relación 'constructors' ya está filtrada por seasonId en el backend,
          // el primer elemento (si existe) es el correcto para la temporada actual.
          constructorForDriver = driver.constructors[0];
          if (constructorForDriver) {
            constructorIdForDriver = constructorForDriver.id;
          }
        }
        
        console.log(`BulkRaceResults - Driver: ${driver.last_name}, Constructor found:`, JSON.parse(JSON.stringify(constructorForDriver)));

        return {
          tempId: `new-${driver.id}-${index}`,
          race_result_id: null,
          driver_id: driver.id,
          constructor_id: constructorIdForDriver,
          number: driver.number || null, // Asumiendo que driver tiene 'number'
          grid_position: index + 1, // O obtener de qualy si se decide cargarla aquí
          position: index + 1,
          position_text: `${index + 1}`,
          position_order: index + 1,
          points: 0, // Calcular después
          laps: props.race?.total_laps || 0,
          race_time: null,
          status: 'Finished', // Estado por defecto
          fastest_lap: null,
          fastest_lap_time: null,
          fastest_lap_speed: null,
          driver: driver, // Guardar el objeto driver
          constructor: constructorForDriver, // Guardar el objeto constructor
        };
      });
      updatePointsBasedOnPosition(); // Asegúrate que esta función también maneje la nueva estructura si es necesario
    }
    // ... (resto de onMounted) ...
  } catch (error) {
    console.error('Error en onMounted de BulkRaceResults:', error);
    // ...
  } finally {
    internalLoading.value = false;
    // ...
  }
});

// Guardar todos los resultados
async function saveAllResults() {
  isSaving.value = true;
  
  try {
    const resultsToSave = raceResults.value
      .filter(r => r.driver_id && r.constructor_id)
      .map((result, index) => {
        const { tempId, ...cleanResult } = result; 
        
        cleanResult.position = index + 1;
        cleanResult.position_text = `${index + 1}`; 
        cleanResult.position_order = index + 1;

        if (!cleanResult.fastest_lap || cleanResult.fastest_lap <= 0) {
          cleanResult.fastest_lap = null;
          cleanResult.fastest_lap_time = null;
          cleanResult.fastest_lap_speed = null;
        }

        if (cleanResult.status !== 'Finished') {
            cleanResult.race_time = null;
        }

        return cleanResult;
      });

    if (resultsToSave.length === 0) {
      showNotification('No valid results to save (driver and team required).', 'error');
      isSaving.value = false;
      return;
    }
    
    await axios.post('/api/race-results/bulk', {
      race_id: props.race.id,
      results: resultsToSave
    });
    
    showNotification('Results saved successfully!', 'success');
    emit('results-updated');
    // loadExistingResults(); 
  } catch (error) {
    console.error('Error saving bulk race results:', error);
    if (error.response && error.response.data && error.response.data.errors) {
        let errorMessages = Object.values(error.response.data.errors).flat().join(' ');
        showNotification(`Validation error: ${errorMessages}`, 'error');
    } else {
        showNotification('Error saving results: ' + (error.response?.data?.message || error.message), 'error');
    }
  } finally {
    isSaving.value = false;
  }
}

// Mostrar notificación
function showNotification(message, type = 'success') {
  notification.value = {
    show: true,
    message,
    type
  };
  
  setTimeout(() => {
    notification.value.show = false;
  }, 5000);
}

// Actualizar posiciones después de cambio en draggable
function updatePositionsAfterChange(event) {
  console.log('Draggable change event:', event);
  raceResults.value.forEach((result, index) => {
    result.position = index + 1;
    result.position_text = `${index + 1}`;
    result.position_order = index + 1;
  });
  updatePointsBasedOnPosition();
}

// Agregar todos los pilotos con valores predeterminados
function addAllDefaultPilots() {
  if (!availableDrivers.value.length) {
    showNotification('No drivers available to add.', 'error');
    return;
  }
  const existingDriverIds = new Set(raceResults.value.map(r => r.driver_id));
  if (existingDriverIds.size === availableDrivers.value.length) {
    showNotification('All drivers are already present.', 'info');
    return;
  }
  if (raceResults.value.length > 0) {
    showAddAllConfirmModal.value = true;
  } else {
    proceedWithAddAll();
  }
}

// Función para ejecutar la lógica de "Add All" después de la confirmación del modal o si no hay datos previos
function proceedWithAddAll() {
  showAddAllConfirmModal.value = false;

  raceResults.value = availableDrivers.value.map((driver, index) => {
    let constructorIdForDriver = '';
    if (driver.seasons && driver.seasons.length > 0 && props.race?.season?.id) {
      const seasonData = driver.seasons.find(s => s.season_id === props.race.season.id);
      if (seasonData && seasonData.pivot) {
          constructorIdForDriver = seasonData.pivot.constructor_id;
      } else if (driver.seasons[0] && driver.seasons[0].pivot) {
           constructorIdForDriver = driver.seasons[0].pivot.constructor_id;
      }
    }
    return {
      tempId: `default-${driver.id}-${index}`,
      driver_id: driver.id,
      constructor_id: constructorIdForDriver,
      grid_position: index + 1,
      position: index + 1,
      position_text: `${index + 1}`,
      position_order: index + 1,
      points: 0,
      laps: props.race?.total_laps || 0,
      race_time: null,
      status: 'Finished',
      fastest_lap: null,
      fastest_lap_time: null,
      fastest_lap_speed: null,
    };
  });
  updatePointsBasedOnPosition();
  showNotification('All available drivers added with default values.', 'success');
}

// Agregar pilotos faltantes
function addMissingPilots() {
  const existingDriverIds = new Set(raceResults.value.map(r => r.driver_id));
  if (existingDriverIds.size === availableDrivers.value.length) {
    showNotification('All drivers are already present.', 'info');
    return;
  }
  console.log('Existing Driver IDs Set:', Array.from(existingDriverIds));
  console.log('Available Drivers (first 5):', JSON.parse(JSON.stringify(availableDrivers.value.slice(0,5))));
  let newPilotsAdded = 0;
  let currentGridPosition = raceResults.value.length + 1;

  availableDrivers.value.forEach((driver) => {
    if (!existingDriverIds.has(driver.id)) {
      let constructorIdForDriver = '';
      if (driver.seasons && driver.seasons.length > 0) {
        const seasonData = driver.seasons.find(s => s.season_id === props.race.season.id);
        if (seasonData && seasonData.pivot) {
            constructorIdForDriver = seasonData.pivot.constructor_id;
        } else if (driver.seasons[0] && driver.seasons[0].pivot) {
             constructorIdForDriver = driver.seasons[0].pivot.constructor_id;
        }
      }
      raceResults.value.push({
        tempId: `missing-${driver.id}-${raceResults.value.length}`,
        driver_id: driver.id,
        constructor_id: constructorIdForDriver,
        grid_position: currentGridPosition++,
        position: raceResults.value.length +1,
        position_text: `${raceResults.value.length + 1}`,
        position_order: raceResults.value.length + 1,
        points: 0,
        laps: props.race?.total_laps || 0,
        race_time: null,
        status: 'Finished',
        fastest_lap: null,
        fastest_lap_time: null,
        fastest_lap_speed: null,
      });
      newPilotsAdded++;
    }
  });

  if (newPilotsAdded > 0) {
    updatePointsBasedOnPosition();
    showNotification(`${newPilotsAdded} missing driver(s) added.`, 'success');
  } else {
    showNotification('No missing drivers to add.', 'info');
  }
}

// ...
function toggleRowExpansion(tempId) {
  expandedRowId.value = expandedRowId.value === tempId ? null : tempId;
}

function parseTimeToMilliseconds(timeStr) {
  if (!timeStr || typeof timeStr !== 'string') return Infinity;
  const parts = timeStr.match(/^(\d{1,2}):([0-5]\d)\.(\d{3})$/);
  if (!parts) return Infinity; 
  const minutes = parseInt(parts[1], 10);
  const seconds = parseInt(parts[2], 10);
  const milliseconds = parseInt(parts[3], 10);
  if (isNaN(minutes) || isNaN(seconds) || isNaN(milliseconds)) return Infinity;
  return (minutes * 60 * 1000) + (seconds * 1000) + milliseconds;
}

function findBestFastestLap() {
  let bestTime = Infinity;
  let bestIndex = -1;

  raceResults.value.forEach((result, index) => {
    if (result.fastest_lap && result.fastest_lap > 0 && result.fastest_lap_time) {
      const currentTime = parseTimeToMilliseconds(result.fastest_lap_time);
      if (currentTime < bestTime) {
        bestTime = currentTime;
        bestIndex = index;
      }
    }
  });

  if (bestIndex !== -1) {
    fastestLapIndex.value = bestIndex;
  } else {
    fastestLapIndex.value = null; 
  }
}

function handleFastestLapChange(changedElement, changedIndex) {
  if (changedElement.fastest_lap && changedElement.fastest_lap > 0) {
  } else {
    changedElement.fastest_lap = null; 
    changedElement.fastest_lap_time = null;
    changedElement.fastest_lap_speed = null;
    if (fastestLapIndex.value === changedIndex) {
      findBestFastestLap(); 
    }
  }
  updatePointsBasedOnPosition(); 
}

function updateFastestLapDetails(changedElement, changedIndex) {
    if (changedElement.fastest_lap && changedElement.fastest_lap > 0 && changedElement.fastest_lap_time) {
        findBestFastestLap();
    } else if (fastestLapIndex.value === changedIndex && (!changedElement.fastest_lap_time || !changedElement.fastest_lap || changedElement.fastest_lap <= 0 )) {
        findBestFastestLap();
    }
    updatePointsBasedOnPosition(); 
}

function clearFastestLapForRow(element, index) {
  element.fastest_lap = null;
  element.fastest_lap_time = null;
  element.fastest_lap_speed = null;
  if (fastestLapIndex.value === index) {
    findBestFastestLap();
  }
  updatePointsBasedOnPosition(); 
}

function handleDriverChange(element) {
  const driver = availableDrivers.value.find(d => d.driver_id === element.driver_id);
  if (driver && driver.seasons) {
    const seasonData = driver.seasons.find(s => s.season_id === props.race?.season?.id);
    element.constructor_id = seasonData?.pivot?.constructor_id || driver.seasons[0]?.pivot?.constructor_id || null;
  }
}

function formatLapTime(time) {
  if (!time) return '';
  
  // Si viene en formato 00:MM:SS.mmm, eliminar el prefijo 00:
  const match = time.match(/^00:(\d{2}:\d{2}\.\d{3})$/);
  if (match) {
    return match[1];
  }
  return time;
}
</script>

<style scoped>
.handle {
  cursor: move;
  cursor: -webkit-grabbing;
}
</style>
