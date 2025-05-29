<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';
import CountryFlag from '@/components/CountryFlag.vue';
import DeleteConfirmationModal from '@/components/DeleteConfirmationModal.vue';

// Estados
const drivers = ref([]);
const loading = ref(true);
const showModal = ref(false);
const currentDriver = ref(null);
const searchQuery = ref('');
const statusFilter = ref('all');
const seasonFilter = ref(null);
const availableSeasons = ref([]);

// Agregar estas variables para controlar el modal de eliminación
const showDeleteModal = ref(false);
const driverToDelete = ref(null);
const isDeleting = ref(false);

// Añade estos estados al inicio del script
const showCompareModal = ref(false);
const selectedDriver1Id = ref('');
const selectedDriver2Id = ref('');
const compareDriver1 = ref({});
const compareDriver2 = ref({});
const failedImages = ref(new Set());
const loadingComparison = ref(false);

// Añadir en la sección de refs/estado
const notification = ref({
  show: false,
  message: '',
  type: 'success' // 'success' o 'error'
});

// Variables para paginación
const currentPage = ref(1);
const itemsPerPage = ref(16); // 4 filas de 4 elementos en pantallas grandes
const totalPages = computed(() => {
  return Math.ceil(filteredDrivers.value.length / itemsPerPage.value);
});

const paginatedDrivers = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return filteredDrivers.value.slice(startIndex, endIndex);
});

// Obtener información de usuario actual
const user = computed(() => usePage().props.auth.user);
const isAdmin = computed(() => user.value?.role === 'admin');

// Función mejorada para obtener URL de imagen con mejor manejo de errores
const getImageUrl = (driver) => {
  if (!driver || !driver.image) return null;
  
  const image = driver.image;
  
  // Para URLs completas
  if (image.startsWith('http')) {
    return image;
  }
  
  // Para imágenes con prefijo drivers/ (evitar duplicación)
  if (image.startsWith('drivers/')) {
    const fileName = image.replace('drivers/', '');
    return `/storage/drivers/${fileName}`;
  }
  
  // Para imágenes sin prefijo
  return `/storage/${image}`;
};

// Variable para trackear imágenes que fallan
const handleImageError = (driver) => {
  if (!driver || !driver.driver_id) return;
  
  console.log(`❌ Error cargando imagen para: ${driver.first_name} ${driver.last_name}`);
  
  // Crear un nuevo Set (importante para reactividad)
  const newFailedImages = new Set(failedImages.value);
  newFailedImages.add(driver.driver_id);
  failedImages.value = newFailedImages;
};

// Comprobar si una imagen ha fallado
const hasImageFailed = (driver) => {
  return driver && driver.driver_id && failedImages.value.has(driver.driver_id);
};

// Cargar datos
const fetchDrivers = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/drivers');
    drivers.value = response.data.data || [];
    console.log('Ejemplo de driver con seasons:', 
      drivers.value.length > 0 ? JSON.stringify(drivers.value[0].seasons, null, 2) : 'No hay pilotos');
  } catch (error) {
    console.error('Error fetching drivers:', error);
  } finally {
    loading.value = false;
  }
};

// Añadir esta función para cargar temporadas específicamente
const fetchSeasons = async () => {
  try {
    const response = await axios.get('/api/seasons');
    availableSeasons.value = response.data.data || [];
    console.log('Temporadas cargadas:', availableSeasons.value);
  } catch (error) {
    console.error('Error al cargar temporadas:', error);
  }
};

// Función para mostrar notificaciones
const showNotification = (message, type = 'success') => {
  notification.value = {
    show: true,
    message,
    type
  };
  
  // Auto-cerrar después de 5 segundos
  setTimeout(() => {
    notification.value.show = false;
  }, 5000);
};

// Filtrar pilotos
const filteredDrivers = computed(() => {
  return drivers.value.filter(driver => {
    // Replace your current season filtering logic with this improved version
    if (seasonFilter.value) {
      // If a season filter is set, the driver must have seasons data
      if (!driver.seasons || driver.seasons.length === 0) {
        return false;  // Filter out drivers with no seasons
      }
      
      // Try to find a matching season by ID first
      const matchingSeason = driver.seasons.find(season => 
        Number(season.id) === Number(seasonFilter.value)
      );
      
      // If no direct ID match, try matching by year
      if (!matchingSeason) {
        const selectedYear = availableSeasons.value.find(s => 
          Number(s.id) === Number(seasonFilter.value)
        )?.year;
        
        if (selectedYear) {
          const matchingByYear = driver.seasons.find(season => 
            Number(season.year) === Number(selectedYear)
          );
          
          if (!matchingByYear) return false;
        } else {
          return false;  // No matching year found
        }
      }
    }
    
    // Filtrar por estado activo/inactivo
    if (statusFilter.value !== 'all') {
      if (statusFilter.value === 'active' && !driver.active) return false;
      if (statusFilter.value === 'inactive' && driver.active) return false;
    }

    // Filtro de búsqueda
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase();
      if (!driver.first_name.toLowerCase().includes(query) &&
          !driver.last_name.toLowerCase().includes(query) &&
          !driver.code?.toLowerCase().includes(query)) {
        return false;
      }
    }

    return true;
  });
});

// Ver piloto en modal
const viewDriver = (driver) => {
  currentDriver.value = JSON.parse(JSON.stringify(driver));
  showModal.value = true;
};

// Cerrar modal
const closeModal = () => {
  showModal.value = false;
  setTimeout(() => {
    currentDriver.value = null;
  }, 200);
};

// Filtros
const setStatusFilter = (status) => {
  statusFilter.value = status;
  currentPage.value = 1; // Reset to first page
};

const setSeasonFilter = (season) => {
  seasonFilter.value = season;
  currentPage.value = 1; // Reset to first page
};

const getSeasonYear = (seasonId) => {
  const season = availableSeasons.value.find(s => s.id === seasonId);
  return season ? season.year : 'Unknown';
};

const hasActiveFilters = computed(() => {
  return statusFilter.value !== 'all' || seasonFilter.value !== null;
});

const clearAllFilters = () => {
  statusFilter.value = 'all';
  seasonFilter.value = null;
  searchQuery.value = '';
};

// Función para mostrar el modal de confirmación de eliminación
const confirmDelete = (driver, event) => {
  // Evitar que se propague el evento (para no abrir el modal de vista)
  event.stopPropagation();
  
  driverToDelete.value = driver;
  showDeleteModal.value = true;
};

// Función para eliminar un piloto
const deleteDriver = async () => {
  if (!driverToDelete.value) return;
  
  isDeleting.value = true;
  try {
    await axios.delete(`/api/drivers/${driverToDelete.value.id}`);
    
    // Actualizar la lista de pilotos sin recargar la página
    fetchDrivers();
    
    // Mostrar notificación de éxito
    showNotification(`${driverToDelete.value.first_name} ${driverToDelete.value.last_name} was successfully deleted`, 'success');
    
    // Cerrar ambos modales
    showDeleteModal.value = false;
    showModal.value = false;
    driverToDelete.value = null;
    currentDriver.value = null;
    
  } catch (error) {
    console.error('Error deleting driver:', error);
    // Mostrar notificación de error
    showNotification(`Failed to delete driver: ${error.response?.data?.message || error.message}`, 'error');
  } finally {
    isDeleting.value = false;
  }
};

// Añade estas funciones
const openCompareModal = () => {
  showCompareModal.value = true;
  
  // Reset selections
  selectedDriver1Id.value = '';
  selectedDriver2Id.value = '';
  compareDriver1.value = {};
  compareDriver2.value = {};
};

const closeCompareModal = () => {
  showCompareModal.value = false;
};

const markImageAsFailed = (driverId) => {
  failedImages.value.add(driverId);
};

const imageFailedToLoad = (driverId) => {
  return failedImages.value.has(driverId);
};

const loadDriverDetails = async (driverId, position) => {
  if (!driverId) return;
  
  loadingComparison.value = true;
  try {
    const response = await axios.get(`/api/drivers/${driverId}`);
    if (position === 1) {
      compareDriver1.value = response.data.data;
    } else {
      compareDriver2.value = response.data.data;
    }
  } catch (error) {
    console.error(`Error loading driver ${driverId}:`, error);
  } finally {
    loadingComparison.value = false;
  }
};

const calculateStatsPercentage = (val1, val2) => {
  val1 = Number(val1) || 0;
  val2 = Number(val2) || 0;
  
  if (val1 === 0 && val2 === 0) return 50;
  
  const total = val1 + val2;
  return Math.round((val1 / total) * 100);
};

const getCountryName = (nationality) => {
  // If there's no nationality data
  if (!nationality) return '';
  
  // If the driver has a nationality object with a name property
  if (typeof nationality === 'object' && nationality.name) {
    return nationality.name;
  }
  
  // If looking up by driver.nationality_id
  const country = drivers.value.find(d => d.nationality?.country_id === nationality)?.nationality;
  return country?.name || '';
};

const compareDrivers = (driver) => {
  if (!selectedDriver1Id.value) {
    // If no first driver selected, set this one as first
    selectedDriver1Id.value = driver.driver_id;
    loadDriverDetails(driver.driver_id, 1);
  } else if (selectedDriver1Id.value === driver.driver_id) {
    // If clicking on the already selected first driver, deselect it
    selectedDriver1Id.value = '';
    compareDriver1.value = {};
  } else {
    // Otherwise set as second driver and open modal
    selectedDriver2Id.value = driver.driver_id;
    loadDriverDetails(driver.driver_id, 2);
    showCompareModal.value = true;
  }
};

// Add this new method to replace the previous comparison approach
const runComparison = async () => {
  console.log("Starting comparison with:", {
    driver1Id: selectedDriver1Id.value,
    driver2Id: selectedDriver2Id.value
  });
  
  if (!selectedDriver1Id.value || !selectedDriver2Id.value || selectedDriver1Id.value === selectedDriver2Id.value) {
    console.log("Invalid selection, not proceeding");
    return;
  }
  
  loadingComparison.value = true;
  compareDriver1.value = {};
  compareDriver2.value = {};
  
  try {
    // Convert IDs to numbers to ensure consistent format
    const id1 = Number(selectedDriver1Id.value);
    const id2 = Number(selectedDriver2Id.value);
    
    console.log(`Making API calls for drivers ${id1} and ${id2}`);
    
    // Load both drivers in parallel
    const [response1, response2] = await Promise.all([
      axios.get(`/api/drivers/${id1}`),
      axios.get(`/api/drivers/${id2}`)
    ]);
    
    console.log("API responses received");
    
    // Check the structure of the response data
    if (!response1.data.data || !response2.data.data) {
      console.error("Unexpected API response format:", {
        response1: response1.data,
        response2: response2.data
      });
    }
    
    compareDriver1.value = response1.data.data;
    compareDriver2.value = response2.data.data;
    
    console.log('Drivers loaded:', {
      driver1: compareDriver1.value,
      driver2: compareDriver2.value
    });
  } catch (error) {
    console.error('Error loading drivers for comparison:', error);
  } finally {
    loadingComparison.value = false;
  }
};

// Añade esta propiedad computada
const isCompareButtonDisabled = computed(() => {
  // Ensure IDs are converted to the same type (Number) before comparison
  const id1 = Number(selectedDriver1Id.value);
  const id2 = Number(selectedDriver2Id.value);
  
  // For debugging
  console.log('Button check:', { 
    id1: id1, 
    id2: id2, 
    hasId1: Boolean(id1), 
    hasId2: Boolean(id2), 
    sameIds: id1 === id2,
    loading: loadingComparison.value
  });
  
  return !id1 || !id2 || id1 === id2 || loadingComparison.value;
});

// Añade esta función para calcular los años de carrera
const calculateCareerYears = (debutYear, isActive) => {
  if (!debutYear) return '-';
  const currentYear = new Date().getFullYear();
  return isActive ? currentYear - debutYear : debutYear;
};

// Añade estos watchers para todos los filtros
watch(searchQuery, () => {
  currentPage.value = 1; // Reset a la primera página cuando cambia la búsqueda
});

watch(seasonFilter, () => {
  currentPage.value = 1; // Reset a la primera página cuando cambia la temporada
});

watch(statusFilter, () => {
  currentPage.value = 1; // Reset a la primera página cuando cambia el estado
});

// Inicializar datos
onMounted(() => {
  fetchDrivers();
  fetchSeasons(); // Cargar temporadas de forma independiente
});
</script>

<template>
  <AppLayout>
    <Head title="Drivers" />
    
    <!-- Sistema de notificaciones -->
    <div 
      v-if="notification.show" 
      :class="[
        'fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 transition-all duration-300',
        notification.type === 'success' ? 'bg-green-500' : 'bg-red-500',
        'text-white'
      ]"
    >
      <div class="flex items-center">
        <span>{{ notification.message }}</span>
        <button @click="notification.show = false" class="ml-3 text-white">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Cabecera y búsqueda -->
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
            
            <!-- Solo para admins -->
            <button v-if="isAdmin"
            @click="router.visit('/drivers/create')"
            class="bg-f1-red hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
              Add Driver
            </button>

            <!-- Botón para comparar pilotos -->
            <button 
              @click="openCompareModal"
              class="px-4 py-2 bg-f1-red text-white rounded-md hover:bg-red-700 flex items-center"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path d="M5 4a1 1 0 00-2 0v7.268a2 2 0 000 3.464V16a1 1 0 102 0v-1.268a2 2 0 000-3.464V4zM11 4a1 1 0 10-2 0v1.268a2 2 0 000 3.464V16a1 1 0 102 0V8.732a2 2 0 000-3.464V4zM16 3a1 1 0 011 1v7.268a2 2 0 010 3.464V16a1 1 0 11-2 0v-1.268a2 2 0 010-3.464V4a1 1 0 011-1z" />
              </svg>
              Compare Drivers
            </button>
          </div>
        </div>

        <!-- Filtros sutiles en línea -->
        <div class="mb-4 flex flex-wrap items-center gap-x-4 gap-y-2">
          <!-- Segmento de estatus -->
          <div class="inline-flex rounded-md shadow-sm">
            <button 
              @click="setStatusFilter('all')" 
              class="px-3 py-1.5 text-sm font-medium border rounded-l-md"
              :class="statusFilter === 'all' ? 
                'bg-f1-red text-white border-f1-red' : 
                'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'"
            >
              All
            </button>
            <button 
              @click="setStatusFilter('active')" 
              class="px-3 py-1.5 text-sm font-medium border-t border-b"
              :class="statusFilter === 'active' ? 
                'bg-f1-red text-white border-f1-red' : 
                'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'"
            >
              Active
            </button>
            <button 
              @click="setStatusFilter('inactive')" 
              class="px-3 py-1.5 text-sm font-medium border rounded-r-md"
              :class="statusFilter === 'inactive' ? 
                'bg-f1-red text-white border-f1-red' : 
                'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'"
            >
              Inactive
            </button>
          </div>
          
          <!-- Selector de temporada -->
          <div class="inline-flex items-center">
            <select 
              v-model="seasonFilter" 
              class="text-sm border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:ring-f1-red focus:border-f1-red py-1.5 pr-8 pl-3"
            >
              <option :value="null">All Seasons</option>
              <option v-for="season in availableSeasons" :key="season.id" :value="season.id">
                {{ season.year }}
              </option>
            </select>
          </div>
          
          <!-- Filtros activos como pequeños chips -->
          <div v-if="hasActiveFilters" class="flex items-center flex-wrap gap-2 ml-auto">
            <button 
              v-if="hasActiveFilters" 
              @click="clearAllFilters"
              class="text-xs text-f1-red hover:text-red-700 font-medium"
            >
              Clear filters
            </button>
          </div>
        </div>
        
        <!-- Cargando -->
        <div v-if="loading" class="text-center py-12">
          <svg class="animate-spin h-10 w-10 text-gray-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="mt-2 text-gray-500 dark:text-gray-400">Loading drivers...</p>
        </div>
        
        <!-- Sin resultados -->
        <div v-else-if="filteredDrivers.length === 0" class="text-center py-12">
          <p class="text-gray-500 dark:text-gray-400">No drivers found.</p>
        </div>
        
        <!-- Grid de pilotos -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <div 
            v-for="driver in paginatedDrivers" 
            :key="driver.driver_id"
            class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden hover:shadow-md transition-shadow cursor-pointer border border-gray-200 dark:border-gray-700"
            @click="viewDriver(driver)"
          >
            <!-- Contenedor de imagen con altura fija -->
            <div class="relative h-48 bg-gray-100 dark:bg-gray-700 overflow-hidden">
              <div class="w-full h-full flex items-center justify-center">
                <!-- Usar v-if y v-else para asegurar que solo uno se muestra -->
                <template v-if="getImageUrl(driver) && !hasImageFailed(driver)">
                  <img 
                    :src="getImageUrl(driver)"
                    :alt="driver.first_name + ' ' + driver.last_name"
                    class="w-full h-full object-contain"
                    loading="lazy"
                    @error="handleImageError(driver)"
                  />
                </template>
                <template v-else>
                  <!-- Fallback SVG -->
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-gray-400"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </template>
              </div>
              
              <!-- Código en esquina (sin cambios) -->
              <div v-if="driver.code || driver.number" 
                class="absolute top-0 right-0 bg-f1-red text-white px-2 py-1 text-sm font-bold rounded-bl-lg">
                {{ driver.code || '#' + driver.number }}
              </div>
            </div>
            
            <!-- Información del piloto -->
            <div class="p-4 relative">
              <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                {{ driver.first_name }} {{ driver.last_name }}
              </h2>
              <p class="text-gray-600 dark:text-gray-300 text-sm flex items-center">
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

        <!-- Paginación -->
        <div v-if="totalPages > 1" class="flex justify-center items-center space-x-2 mt-8">
          <!-- Botón Anterior -->
          <button 
            @click="currentPage > 1 && (currentPage--)"
            :disabled="currentPage === 1"
            class="px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600"
            :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
          >
            <i class="fas fa-chevron-left"></i>
          </button>

          <!-- Números de página -->
          <template v-if="totalPages <= 7">
            <button 
              v-for="page in totalPages" 
              :key="page"
              @click="currentPage = page"
              class="px-3 py-1 rounded-md"
              :class="currentPage === page ? 
                'bg-f1-red text-white' : 
                'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
            >
              {{ page }}
            </button>
          </template>
          <template v-else>
            <!-- Primera página -->
            <button 
              @click="currentPage = 1"
              class="px-3 py-1 rounded-md"
              :class="currentPage === 1 ? 'bg-f1-red text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
            >
              1
            </button>
            
            <!-- Indicador de páginas omitidas -->
            <span v-if="currentPage > 3" class="px-2">...</span>
            
            <!-- Páginas centrales -->
            <template v-for="page in totalPages" :key="page">
              <button 
                v-if="page !== 1 && page !== totalPages && Math.abs(page - currentPage) < 2"
                @click="currentPage = page"
                class="px-3 py-1 rounded-md"
                :class="currentPage === page ? 'bg-f1-red text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
              >
                {{ page }}
              </button>
            </template>
            
            <!-- Indicador de páginas omitidas -->
            <span v-if="currentPage < totalPages - 2" class="px-2">...</span>
            
            <!-- Última página -->
            <button 
              @click="currentPage = totalPages"
              class="px-3 py-1 rounded-md"
              :class="currentPage === totalPages ? 'bg-f1-red text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
            >
              {{ totalPages }}
            </button>
          </template>

          <!-- Botón Siguiente -->
          <button 
            @click="currentPage < totalPages && (currentPage++)"
            :disabled="currentPage === totalPages"
            class="px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600"
            :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
          >
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>

        <!-- Información de paginación -->
        <div v-if="filteredDrivers.length > 0" class="text-sm text-gray-500 dark:text-gray-400 text-center mt-4">
          Showing {{ Math.min((currentPage - 1) * itemsPerPage + 1, filteredDrivers.length) }}-{{ Math.min(currentPage * itemsPerPage, filteredDrivers.length) }} of {{ filteredDrivers.length }} drivers
        </div>
        
        <!-- Modal - Independiente de las cards -->
        <div 
          v-if="showModal && currentDriver" 
          class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
          @click.self="closeModal"
        >
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-3xl w-full overflow-y-auto max-h-[90vh]">
            <div class="p-6">
              <!-- Header modal -->
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
              
              <!-- Contenido modal -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Imagen -->
                <div class="h-64 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
                  <img 
                    v-if="getImageUrl(currentDriver) && !hasImageFailed(currentDriver)"
                    :src="getImageUrl(currentDriver)"
                    :alt="currentDriver.first_name + ' ' + currentDriver.last_name"
                    class="w-full h-full object-contain"
                    loading="eager"
                    @error="handleImageError(currentDriver)"
                    style="display: block;"
                  />
                  
                  <!-- Fallback simple -->
                  <div v-if="!getImageUrl(currentDriver) || hasImageFailed(currentDriver)" class="w-full h-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-gray-400"
                      fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                </div>
                
                <!-- Detalles del piloto -->
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
                    <span class="font-bold text-gray-900 dark:text-white ml-2">
                      {{ new Date(currentDriver.date_of_birth).toLocaleDateString() }}
                    </span>
                  </div>
                  
                  <div>
                    <span class="text-gray-500 dark:text-gray-400">Championships:</span>
                    <span class="font-bold text-gray-900 dark:text-white ml-2">
                      {{ currentDriver.championships || 0 }}
                    </span>
                  </div>
                  
                  <div>
                    <span class="text-gray-500 dark:text-gray-400">Wins:</span>
                    <span class="font-bold text-gray-900 dark:text-white ml-2">
                      {{ currentDriver.wins || 0 }}
                    </span>
                  </div>
                  
                  <div>
                    <span class="text-gray-500 dark:text-gray-400">Podiums:</span>
                    <span class="font-bold text-gray-900 dark:text-white ml-2">
                      {{ currentDriver.podiums || 0 }}
                    </span>
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
              
              <!-- Biografía -->
              <div class="mt-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Biography</h3>
                <p class="text-gray-700 dark:text-gray-300">
                  {{ currentDriver.biography || 'No biography available for this driver.' }}
                </p>
              </div>
              
              <!-- Botones admin (solo visibles para admin) -->
              <div v-if="isAdmin" class="mt-6 flex justify-end space-x-3">
                <a 
                  :href="`/drivers/${currentDriver.id}/edit`"
                  class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-600">
                  Edit
                </a>
                <button 
                  @click="confirmDelete(currentDriver, $event)" 
                  class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal de confirmación de eliminación -->
        <DeleteConfirmationModal
          v-if="showDeleteModal"
          :show="showDeleteModal"
          :isDeleting="isDeleting"
          @confirm="deleteDriver"
          @cancel="showDeleteModal = false"
        />

        <!-- Añade este modal al final del template, justo antes de cerrar AppLayout -->
        <div 
          v-if="showCompareModal" 
          class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
          @click.self="closeCompareModal"
        >
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-4xl p-6">
            <!-- Modal Header -->
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-xl font-bold text-gray-900 dark:text-white">Compare Drivers</h2>
              <button 
                @click="closeCompareModal"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Driver Selection -->
            <div class="grid grid-cols-2 gap-6 mb-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Driver</label>
                <select 
                  v-model="selectedDriver1Id"
                  class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                  @change="console.log('Driver 1 selected:', selectedDriver1Id)"
                >
                  <option value="" disabled>Select first driver</option>
                  <option v-for="driver in drivers" :key="`left-${driver.id}`" :value="driver.id">
                    {{ driver.first_name }} {{ driver.last_name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Second Driver</label>
                <select 
                  v-model="selectedDriver2Id"
                  class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                  @change="console.log('Driver 2 selected:', selectedDriver2Id)"
                >
                  <option value="" disabled>Select second driver</option>
                  <option v-for="driver in drivers" :key="`right-${driver.id}`" :value="driver.id">
                    {{ driver.first_name }} {{ driver.last_name }}
                  </option>
                </select>
              </div>

              <!-- Add a dedicated compare button -->
              <div class="col-span-2 flex justify-center mt-2">
                <button 
                  @click="runComparison"
                  class="px-6 py-2 bg-f1-red text-white rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  :disabled="isCompareButtonDisabled"
                >
                  <span v-if="loadingComparison" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading...
                  </span>
                  <span v-else>Compare</span>
                </button>
              </div>
            </div>

            <!-- Comparison Section (only shown when both drivers selected) -->
            <div v-if="compareDriver1.id && compareDriver2.id" class="grid grid-cols-3 gap-6 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
              <!-- Driver 1 -->
              <div class="text-center">
                <div class="w-32 h-32 mx-auto rounded-full overflow-hidden border-4 border-f1-red">
                  <img 
                    v-if="getImageUrl(compareDriver1) && !imageFailedToLoad(compareDriver1.id)"
                    :src="getImageUrl(compareDriver1)" 
                    :alt="`${compareDriver1.first_name} ${compareDriver1.last_name}`"
                    class="w-full h-full object-cover"
                    @error="markImageAsFailed(compareDriver1.id)"
                  />
                  <div v-else class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                </div>
                <h3 class="mt-2 font-bold text-gray-900 dark:text-white">
                  {{ compareDriver1.first_name }} {{ compareDriver1.last_name }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ compareDriver1.nationality?.name || '' }}
                </p>
              </div>
              
              <!-- Stats -->
              <div class="border-x border-gray-200 dark:border-gray-700 px-4">
                <h4 class="text-center mb-4 text-gray-500">Statistics</h4>
                
                <div class="space-y-4">
                  <!-- Championships -->
                  <div>
                    <div class="flex justify-between mb-1">
                      <span class="font-bold">{{ compareDriver1.championships || 0 }}</span>
                      <span class="text-sm text-gray-600 dark:text-gray-400">Championships</span>
                      <span class="font-bold">{{ compareDriver2.championships || 0 }}</span>
                    </div>
                    <div class="relative h-2 bg-gray-200 dark:bg-gray-700 rounded-full">
                      <div 
                        class="absolute left-0 top-0 h-full bg-f1-red rounded-full"
                        :style="{
                          width: calculateStatsPercentage(compareDriver1.championships, compareDriver2.championships) + '%'
                        }"
                      ></div>
                    </div>
                  </div>
                  
                  <!-- Wins -->
                  <div>
                    <div class="flex justify-between mb-1">
                      <span class="font-bold">{{ compareDriver1.wins || 0 }}</span>
                      <span class="text-sm text-gray-600 dark:text-gray-400">Wins</span>
                      <span class="font-bold">{{ compareDriver2.wins || 0 }}</span>
                    </div>
                    <div class="relative h-2 bg-gray-200 dark:bg-gray-700 rounded-full">
                      <div 
                        class="absolute left-0 top-0 h-full bg-f1-red rounded-full"
                        :style="{
                          width: calculateStatsPercentage(compareDriver1.wins, compareDriver2.wins) + '%'
                        }"
                      ></div>
                    </div>
                  </div>
                  
                  <!-- Podiums -->
                  <div>
                    <div class="flex justify-between mb-1">
                      <span class="font-bold">{{ compareDriver1.podiums || 0 }}</span>
                      <span class="text-sm text-gray-600 dark:text-gray-400">Podiums</span>
                      <span class="font-bold">{{ compareDriver2.podiums || 0 }}</span>
                    </div>
                    <div class="relative h-2 bg-gray-200 dark:bg-gray-700 rounded-full">
                      <div 
                        class="absolute left-0 top-0 h-full bg-f1-red rounded-full"
                        :style="{
                          width: calculateStatsPercentage(compareDriver1.podiums, compareDriver2.podiums) + '%'
                        }"
                      ></div>
                    </div>
                  </div>
                  
                  <!-- Poles -->
                  <div>
                    <div class="flex justify-between mb-1">
                      <span class="font-bold">{{ compareDriver1.poles || 0 }}</span>
                      <span class="text-sm text-gray-600 dark:text-gray-400">Pole Positions</span>
                      <span class="font-bold">{{ compareDriver2.poles || 0 }}</span>
                    </div>
                    <div class="relative h-2 bg-gray-200 dark:bg-gray-700 rounded-full">
                      <div 
                        class="absolute left-0 top-0 h-full bg-f1-red rounded-full"
                        :style="{
                          width: calculateStatsPercentage(compareDriver1.poles, compareDriver2.poles) + '%'
                        }"
                      ></div>
                    </div>
                  </div>
                  
                  <!-- Fastest Laps -->
                  <div>
                    <div class="flex justify-between mb-1">
                      <span class="font-bold">{{ compareDriver1.fastest_laps || 0 }}</span>
                      <span class="text-sm text-gray-600 dark:text-gray-400">Fastest Laps</span>
                      <span class="font-bold">{{ compareDriver2.fastest_laps || 0 }}</span>
                    </div>
                    <div class="relative h-2 bg-gray-200 dark:bg-gray-700 rounded-full">
                      <div 
                        class="absolute left-0 top-0 h-full bg-f1-red rounded-full"
                        :style="{
                          width: calculateStatsPercentage(compareDriver1.fastest_laps, compareDriver2.fastest_laps) + '%'
                        }"
                      ></div>
                    </div>
                  </div>
                  
                  <!-- Debut Year -->
                  <div>
                    <div class="flex justify-between mb-1">
                      <span class="font-bold">{{ compareDriver1.debut_year || '-' }}</span>
                      <span class="text-sm text-gray-600 dark:text-gray-400">Debut Year</span>
                      <span class="font-bold">{{ compareDriver2.debut_year || '-' }}</span>
                    </div>
                  </div>
                  
                  <!-- Career Span -->
                  <div v-if="compareDriver1.debut_year || compareDriver2.debut_year">
                    <div class="flex justify-between mb-1">
                      <span class="font-bold">
                        {{ calculateCareerYears(compareDriver1.debut_year, compareDriver1.active) }}
                      </span>
                      <span class="text-sm text-gray-600 dark:text-gray-400">Career Years</span>
                      <span class="font-bold">
                        {{ calculateCareerYears(compareDriver2.debut_year, compareDriver2.active) }}
                      </span>
                    </div>
                  </div>
                  
                  <!-- Driver Number -->
                  <div>
                    <div class="flex justify-between mb-1">
                      <span class="font-bold">{{ compareDriver1.number || '-' }}</span>
                      <span class="text-sm text-gray-600 dark:text-gray-400">Driver Number</span>
                      <span class="font-bold">{{ compareDriver2.number || '-' }}</span>
                    </div>
                  </div>
                  
                  <!-- Status -->
                  <div>
                    <div class="flex justify-between mb-1">
                      <span class="font-medium" :class="compareDriver1.active ? 'text-green-500' : 'text-red-500'">
                        {{ compareDriver1.active ? 'Active' : 'Inactive' }}
                      </span>
                      <span class="text-sm text-gray-600 dark:text-gray-400">Status</span>
                      <span class="font-medium" :class="compareDriver2.active ? 'text-green-500' : 'text-red-500'">
                        {{ compareDriver2.active ? 'Active' : 'Inactive' }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Driver 2 -->
              <div class="text-center">
                <div class="w-32 h-32 mx-auto rounded-full overflow-hidden border-4 border-gray-600">
                  <img 
                    v-if="getImageUrl(compareDriver2) && !imageFailedToLoad(compareDriver2.id)"
                    :src="getImageUrl(compareDriver2)" 
                    :alt="`${compareDriver2.first_name} ${compareDriver2.last_name}`"
                    class="w-full h-full object-cover"
                    @error="markImageAsFailed(compareDriver2.id)"
                  />
                  <div v-else class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                </div>
                <h3 class="mt-2 font-bold text-gray-900 dark:text-white">
                  {{ compareDriver2.first_name }} {{ compareDriver2.last_name }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ compareDriver2.nationality?.name || '' }}
                </p>
              </div>
            </div>

            <!-- If no comparison has been made yet, show this message -->
            <div v-else-if="!loadingComparison" class="text-center text-gray-500 dark:text-gray-400 py-8">
              Select two different drivers and click "Compare" to see their statistics side by side.
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
