<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { VueDraggableNext as draggable } from 'vue-draggable-next';
import axios from 'axios';

const props = defineProps({
  race: Object,
  existingQualifyingResults: Array,
  availableDrivers: Array,
  availableConstructors: Array,
  loading: Boolean,
});

const emit = defineEmits(['qualifying-results-updated', 'cancel-edit']);

const qualifyingEntries = ref([]);
const internalLoading = ref(false);
const isSaving = ref(false);

// --- Funciones de Formato de Tiempo para la Vista/Edición ---
function displayTime(timeString) {
  if (!timeString) return '';
  if (timeString.startsWith('00:')) {
    return timeString.substring(3); // Quita "00:"
  }
  return timeString;
}

function modelTime(displayString) {
  if (!displayString) return '';
  return displayString;
}

// --- Lógica de Inicialización ---
onMounted(() => {
  initializeEntries();
  console.log('props', props);
});

watch(() => props.existingQualifyingResults, () => {
  initializeEntries();
}, { deep: true });

function initializeEntries() {
  internalLoading.value = true;
  if (props.existingQualifyingResults && props.existingQualifyingResults.length > 0) {
    qualifyingEntries.value = props.existingQualifyingResults.map((q, index) => ({
      tempId: `existing-${q.qualifying_id || q.id || index}`,
      qualifying_id: q.qualifying_id || q.id,
      driver_id: q.driver_id || q.driver?.id,
      constructor_id: q.constructor_id || q.constructor?.id,
      position: q.position,
      q1_time: q.q1_time || '',
      q2_time: q.q2_time || '',
      q3_time: q.q3_time || '',
      driver: props.availableDrivers?.find(d => d.id === (q.driver_id || q.driver?.id)),
      constructor: props.availableConstructors?.find(c => c.id === (q.constructor_id || q.constructor?.id)),
    })).sort((a,b) => a.position - b.position);
  } else {
    qualifyingEntries.value = [];
  }
  internalLoading.value = false;
}

// --- Lógica de Botones Superiores ---
function addAllDriversLogic() {
  internalLoading.value = true;
  const currentDriverIds = new Set(qualifyingEntries.value.map(e => e.driver_id));
  let newPosition = qualifyingEntries.value.length + 1;

  // Obtener el ID de la temporada de la carrera actual
  const currentRaceSeasonId = props.race?.season?.id || props.race?.season_id;
  // CONSOLE LOGS PARA DEPURACIÓN (ya los tenías o similares)
  console.log('Props', props);
  console.log("BulkQualifyingResults: AddAll - Current Race Season ID:", currentRaceSeasonId);
  console.log("BulkQualifyingResults: AddAll - First available driver's constructors pivot season_id:", props.availableDrivers?.[0]?.constructors?.[0]?.pivot?.season_id);

  props.availableDrivers?.forEach(driver => {
    if (!currentDriverIds.has(driver.id)) {
      const constructorForDriver = driver.constructors?.find(c => c.pivot?.season_id === currentRaceSeasonId) || driver.constructors?.[0];
      
      // CONSOLE LOG PARA VER QUÉ SE ENCUENTRA
      console.log(`BulkQualifyingResults: AddAll - Driver: ${driver.last_name}, Constructor found:`, JSON.parse(JSON.stringify(constructorForDriver)));

      qualifyingEntries.value.push({
        tempId: `new-${driver.id}-${Date.now()}`,
        qualifying_id: null,
        driver_id: driver.id,
        constructor_id: constructorForDriver?.id || null, // Se asigna el ID
        position: newPosition++,
        q1_time: '',
        q2_time: '',
        q3_time: '',
        driver: driver, // Se asigna el objeto driver
        constructor: constructorForDriver, // Se asigna el objeto constructor
      });
    }
  });
  updatePositionsFromOrder();
  internalLoading.value = false;
}

// --- Lógica de Guardado ---
async function saveAllQualifyingResults() {
  isSaving.value = true;
  
  const resultsToSave = qualifyingEntries.value.map((entry, index) => {
    return {
      race_id: props.race.id,
      driver_id: entry.driver_id,
      constructor_id: entry.constructor_id,
      position: index + 1, 
      q1_time: entry.q1_time || null,
      q2_time: entry.q2_time || null,
      q3_time: entry.q3_time || null,
    };
  });

  if (resultsToSave.some(r => !r.driver_id || !r.constructor_id)) {
      console.error("Validation Error: Driver and Constructor required for all entries.");
      isSaving.value = false;
      return;
  }

  try {
    await axios.post('/api/qualifying-results/bulk', { 
      race_id: props.race.id,
      results: resultsToSave,
    });
    emit('qualifying-results-updated');
  } catch (error) {
    console.error('Error saving bulk qualifying results:', error.response?.data || error.message);
  } finally {
    isSaving.value = false;
  }
}

// --- Lógica de Draggable ---
function updatePositionsFromOrder() {
  qualifyingEntries.value.forEach((entry, index) => {
    entry.position = index + 1;
  });
}

// --- Funciones de Utilidad ---
function handleTimeInput(event, entry, fieldName) {
  let value = event.target.value;
  // value = value.replace(/[^0-9:.]/g, '');

  // if (value.length > 8 && !value.startsWith('00:')) {
  //     value = value.substring(0,8);
  // } else if (value.length > 11 && value.startsWith('00:')) {
  //     value = value.substring(0,11);
  // }
  
  entry[fieldName] = value;
}

function getDriverById(driverId) {
    return props.availableDrivers?.find(d => d.id === driverId);
}
function getConstructorById(constructorId) {
    return props.availableConstructors?.find(c => c.id === constructorId);
}

function handleDriverChange(entry) {
    const driver = getDriverById(entry.driver_id);
    if (driver) {
        entry.driver = driver;
        const constructorForDriver = driver.constructors?.find(c => c.pivot?.season_id === props.race.season_id) || driver.constructors?.[0];
        if (constructorForDriver) {
            entry.constructor_id = constructorForDriver.id;
            entry.constructor = constructorForDriver;
        } else {
            entry.constructor_id = null;
            entry.constructor = null;
        }
    }
}

function handleConstructorChange(entry) {
    const constructor = getConstructorById(entry.constructor_id);
    if (constructor) {
        entry.constructor = constructor;
    }
}

</script>

<template>
  <div>
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold text-gray-900 dark:text-white">Manage Qualifying Results</h2>
      <div class="flex space-x-2">
        <button 
          @click="addAllDriversLogic" 
          :disabled="internalLoading || !availableDrivers?.length" 
          class="px-3 py-1.5 bg-gray-700 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-500 text-white rounded-md text-sm flex items-center" 
          title="Add all available drivers from the season"
        >
          <i class="fas fa-users mr-2"></i>Add All Drivers
        </button>
        <button 
          @click="saveAllQualifyingResults" 
          :disabled="isSaving || internalLoading || !qualifyingEntries.length" 
          class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm flex items-center"
          title="Save all qualifying results"
        >
          <i v-if="isSaving" class="fas fa-spinner fa-spin mr-1"></i>
          <i v-else class="fas fa-save mr-1"></i>
          Save Results
        </button>
      </div>
    </div>

    <div class="mb-4 p-3 bg-blue-100 dark:bg-blue-900/30 border border-blue-300 dark:border-blue-700 rounded-md text-sm text-blue-700 dark:text-blue-300 flex items-start">
      <i class="fas fa-info-circle mr-2 mt-1"></i>
      <p>
        Drag and drop drivers to set the qualifying order. Position will be assigned automatically. 
        Times for Q1, Q2, and Q3 should be entered in MM:SS.mmm format (e.g., 01:23.456).
      </p>
    </div>

    <div v-if="loading || internalLoading" class="text-center py-8">
      <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-f1-red mx-auto mb-2"></div>
      <p class="text-gray-600 dark:text-gray-400">Loading qualifying data...</p>
    </div>
    <div v-else>
      <div class="grid grid-cols-12 gap-2 px-2 py-2 bg-gray-100 dark:bg-gray-800 text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 rounded-t-md">
        <div class="col-span-1 text-center">POS</div>
        <div class="col-span-3">Driver</div>
        <div class="col-span-3">Constructor</div>
        <div class="col-span-4 flex">
          <div class="flex-1 text-center">Q1</div>
          <div class="flex-1 text-center">Q2</div>
          <div class="flex-1 text-center">Q3</div>
        </div>
        <div class="col-span-1 text-right pr-1">Del</div>
      </div>

      <draggable
        :list="qualifyingEntries"
        item-key="tempId"
        handle=".handle"
        class="divide-y divide-gray-200 dark:divide-gray-700"
        @change="updatePositionsFromOrder"
      >
        <div 
          v-for="(entry, index) in qualifyingEntries" 
          :key="entry.tempId"
          class="hover:bg-gray-50 dark:hover:bg-gray-900/50"
        >
          <div class="p-2 grid grid-cols-12 gap-2 items-center">
            <div class="col-span-1 flex items-center justify-center">
              <span class="handle cursor-move text-gray-400 hover:text-gray-600 mr-2"><i class="fas fa-grip-vertical"></i></span>
              <span>{{ index + 1 }}</span>
            </div>
            <div class="col-span-3">
              <select v-model="entry.driver_id" disabled @change="() => handleDriverChange(entry)" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring-red-200 text-sm p-1.5 dark:text-white">
                <option :value="null" disabled>Select Driver</option>
                <option v-for="driver in availableDrivers" :key="driver.id" :value="driver.id">
                  {{ driver.first_name }} {{ driver.last_name }}
                </option>
              </select>
            </div>
            <div class="col-span-3">
               <select v-model="entry.constructor_id" disabled @change="() => handleConstructorChange(entry)" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring-red-200 text-sm p-1.5 dark:text-white">
                <option :value="null" disabled>Select Team</option>
                <option v-for="constructorItem in availableConstructors" :key="constructorItem.id" :value="constructorItem.id">
                  {{ constructorItem.name }}
                </option>
              </select>
            </div>
            <div class="col-span-4 flex space-x-1">
              <div class="flex-1">
                <input 
                  type="text" 
                  :value="displayTime(entry.q1_time)"
                  @input="handleTimeInput($event, entry, 'q1_time')"
                  placeholder="MM:SS.mmm" 
                  v-mask="'##:##.###'"
                  title="Format: MM:SS.mmm"
                  maxlength="9"
                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring-red-200 text-sm p-1.5 dark:text-white"/>
              </div>
              <div class="flex-1">
                <input 
                  type="text" 
                  :value="displayTime(entry.q2_time)"
                  @input="handleTimeInput($event, entry, 'q2_time')"
                  placeholder="MM:SS.mmm" 
                  v-mask="'##:##.###'"
                  title="Format: MM:SS.mmm"
                  maxlength="9"
                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring-red-200 text-sm p-1.5 dark:text-white"/>
              </div>
              <div class="flex-1">
                <input 
                  type="text" 
                  :value="displayTime(entry.q3_time)"
                  @input="handleTimeInput($event, entry, 'q3_time')"
                  placeholder="MM:SS.mmm" 
                  v-mask="'##:##.###'"
                  title="Format: MM:SS.mmm"
                  maxlength="9"
                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring-red-200 text-sm p-1.5 dark:text-white"/>
              </div>
            </div>
            <div class="col-span-1 flex justify-end pr-1">
                <button @click="qualifyingEntries.splice(index, 1); updatePositionsFromOrder()" class="text-red-500 hover:text-red-700" title="Delete entry">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
          </div>
        </div>
      </draggable>
       <div v-if="!qualifyingEntries.length && !internalLoading && !loading" class="p-4 text-center text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700">
            No qualifying entries yet. Use "Add All Drivers" to start.
        </div>
    </div>
  </div>
</template>