<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';
import CountryFlag from '@/components/CountryFlag.vue';

// Estados
const drivers = ref([]);
const loading = ref(true);
const showModal = ref(false);
const currentDriver = ref(null);
const searchQuery = ref('');

// Cargar datos
const fetchDrivers = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/drivers');
    drivers.value = response.data.data || [];
  } catch (error) {
    console.error('Error fetching drivers:', error);
  } finally {
    loading.value = false;
  }
};

// Marcar imagen como fallida para mostrar SVG
const markImageAsFailed = (driverId) => {
  failedImages.value.add(driverId);
};

// Verificar si una imagen falló
const hasImageFailed = (driverId) => {
  return failedImages.value.has(driverId);
};

// Función mejorada para obtener URL de imagen
const getImageUrl = (driver) => {
  if (!driver) return null;
  
  // Imagen del driver en la DB
  if (driver.image) {
    return driver.image.startsWith('http') 
      ? driver.image 
      : `/storage/${driver.image}`;
  }
  
  return null; // Retornar null para mostrar el SVG
};

// Filtrar pilotos
const filteredDrivers = computed(() => {
  if (!searchQuery.value) return drivers.value;
  
  const query = searchQuery.value.toLowerCase();
  return drivers.value.filter(driver => 
    driver.first_name.toLowerCase().includes(query) ||
    driver.last_name.toLowerCase().includes(query) ||
    driver.code?.toLowerCase().includes(query)
  );
});

// Acciones
const viewDriver = (driver) => {
  currentDriver.value = driver;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  setTimeout(() => {
    currentDriver.value = null;
  }, 200);
};

// Inicializar datos
onMounted(() => {
  fetchDrivers();
});
</script>

<template>
  <AppLayout>
    <Head title="Drivers" />
    
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Formula 1 Drivers</h1>
          
          <div class="flex space-x-4">
            <!-- Búsqueda -->
            <div class="relative">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search drivers..."
                class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              />
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
            </div>
            
            <!-- Botón de agregar -->
            <button class="bg-f1-red hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
              Add Driver
            </button>
          </div>
        </div>
        
        <!-- Estado de carga -->
        <div v-if="loading" class="text-center py-12">
          <svg class="animate-spin h-10 w-10 text-gray-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="mt-2 text-gray-500 dark:text-gray-400">Loading drivers...</p>
        </div>
        
        <!-- Mensaje sin resultados -->
        <div v-else-if="filteredDrivers.length === 0" class="text-center py-12">
          <p class="text-gray-500 dark:text-gray-400">No drivers found.</p>
        </div>
        
        <!-- Grid de pilotos -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <div 
            v-for="driver in filteredDrivers" 
            :key="driver.driver_id"
            class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden hover:shadow-md transition-shadow cursor-pointer border border-gray-200 dark:border-gray-700"
            @click="viewDriver(driver)"
          >
          <div class="relative pb-2/3">
            <img 
              :src="driver.image || '/drivers/driver-placeholder.avif'" 
              :alt="driver.first_name + ' ' + driver.last_name"
              class="w-full h-48 object-cover"
              onerror="this.style.display='none'; this.insertAdjacentHTML('afterend', '<div class=\'flex items-center justify-center w-full h-48 bg-gray-100 dark:bg-gray-700\'><svg xmlns=\'http://www.w3.org/2000/svg\' class=\'h-24 w-24 text-gray-400\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\' /></svg></div>');"
            />
            <div class="absolute top-0 right-0 bg-f1-red text-white px-2 py-1 text-sm font-bold rounded-bl-lg">
              {{ driver.code || '#' + driver.number }}
            </div>
          </div>
            
            <div class="p-4">
              <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                {{ driver.first_name }} {{ driver.last_name }}
              </h2>
              <p class="text-gray-600 dark:text-gray-300 text-sm flex items-center">
                <!-- Bandera del país usando el componente -->
                <CountryFlag 
                  v-if="driver.nationality?.code"
                  :countryCode="driver.nationality.code"
                  class="mr-1"
                />
                {{ driver.nationality?.name || 'Unknown' }}
              </p>
              <div class="flex justify-between items-center mt-2">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                  <span v-if="driver.active" class="text-green-600 dark:text-green-400">Active</span>
                  <span v-else class="text-red-600 dark:text-red-400">Inactive</span>
                </div>
                <div v-if="driver.championships > 0" class="font-bold text-f1-red">
                  {{ driver.championships }}x WDC
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Modal de detalles de piloto -->
        <div 
          v-if="showModal && currentDriver" 
          class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
          @click.self="closeModal"
        >
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-3xl w-full max-h-90vh overflow-y-auto">
            <div class="p-6">
              <div class="flex justify-between items-start mb-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ currentDriver.first_name }} {{ currentDriver.last_name }}
                </h2>
                <button 
                  @click="closeModal" 
                  class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <img 
                    :src="currentDriver.image" 
                    :alt="currentDriver.first_name + ' ' + currentDriver.last_name"
                    class="w-full h-auto rounded-lg"
                    onerror="this.style.display='none'; this.insertAdjacentHTML('afterend', '<div class=\'flex items-center justify-center w-full h-64 bg-gray-100 dark:bg-gray-700 rounded-lg\'><svg xmlns=\'http://www.w3.org/2000/svg\' class=\'h-24 w-24 text-gray-400\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\' /></svg></div>');"
                  />
                </div>
                
                <div class="space-y-3">
                  <div>
                    <span class="text-gray-500 dark:text-gray-400">Number:</span>
                    <span class="font-bold text-gray-900 dark:text-white ml-2">{{ currentDriver.number || 'N/A' }}</span>
                  </div>
                  
                  <div>
                    <span class="text-gray-500 dark:text-gray-400">Code:</span>
                    <span class="font-bold text-gray-900 dark:text-white ml-2">{{ currentDriver.code || 'N/A' }}</span>
                  </div>
                  
                  <div>
                    <span class="text-gray-500 dark:text-gray-400">Nationality:</span>
                    <span class="font-bold text-gray-900 dark:text-white ml-2 flex items-center">
                      <CountryFlag 
                        v-if="currentDriver.nationality?.code"
                        :countryCode="currentDriver.nationality.code"
                        size="w-5 h-4"
                        :rounded="true"
                        class="mr-2"
                      />
                      {{ currentDriver.nationality?.name || 'Unknown' }}
                    </span>
                  </div>
                  
                  <div>
                    <span class="text-gray-500 dark:text-gray-400">Date of Birth:</span>
                    <span class="font-bold text-gray-900 dark:text-white ml-2">{{ new Date(currentDriver.date_of_birth).toLocaleDateString() }}</span>
                  </div>
                  
                  <div>
                    <span class="text-gray-500 dark:text-gray-400">Championships:</span>
                    <span class="font-bold text-gray-900 dark:text-white ml-2">{{ currentDriver.championships || 0 }}</span>
                  </div>
                  
                  <div>
                    <span class="text-gray-500 dark:text-gray-400">Wins:</span>
                    <span class="font-bold text-gray-900 dark:text-white ml-2">{{ currentDriver.wins || 0 }}</span>
                  </div>
                  
                  <div>
                    <span class="text-gray-500 dark:text-gray-400">Podiums:</span>
                    <span class="font-bold text-gray-900 dark:text-white ml-2">{{ currentDriver.podiums || 0 }}</span>
                  </div>
                  
                  <div>
                    <span class="text-gray-500 dark:text-gray-400">Status:</span>
                    <span 
                      class="ml-2 font-bold"
                      :class="currentDriver.active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                    >
                      {{ currentDriver.active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                </div>
              </div>
              
              <div class="mt-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Biography</h3>
                <p class="text-gray-700 dark:text-gray-300">
                  {{ currentDriver.biography || 'No biography available for this driver.' }}
                </p>
              </div>
              
              <div class="mt-6 flex justify-end space-x-3">
                <button class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-600">
                  Edit
                </button>
                <button class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>