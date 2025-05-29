<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, onMounted, defineProps, watch } from 'vue';
import axios from 'axios'; // Para llamar a tu API
import { useForm } from '@inertiajs/vue3'; // Útil si quieres reusar para modales/formularios

const props = defineProps({
  countries: Array, // Países pasados desde el Formula1ViewController
  errors: Object, // Errores de validación si los pasas desde el controlador (menos común con API)
});

const circuits = ref([]);
const loading = ref(true);
const showFormModal = ref(false);
const editingCircuit = ref(null); // Para saber si estamos creando o editando

// Añade este estado junto a tus otros refs
const confirmDialog = ref({
  show: false,
  title: '',
  message: '',
  action: null,
  type: 'danger'
});

// Añade este estado para gestionar notificaciones
const notification = ref({
  show: false,
  type: 'success', // success, error, warning, info
  message: '',
  timeout: null
});

// Estado para la búsqueda
const searchQuery = ref('');

// Circuitos filtrados por búsqueda
const filteredCircuits = computed(() => {
  if (!searchQuery.value) return circuits.value;
  
  const query = searchQuery.value.toLowerCase();
  return circuits.value.filter(circuit => 
    circuit.name.toLowerCase().includes(query) || 
    circuit.location.toLowerCase().includes(query) || 
    circuit.country?.name.toLowerCase().includes(query)
  );
});

// Mapa para registrar circuitos con imágenes fallidas
const failedImages = ref(new Set());

const perPage = ref(8); // Circuitos por página
const currentPage = ref(1);

// Computed property para paginar circuitos
const paginatedCircuits = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  return filteredCircuits.value.slice(start, start + perPage.value);
});

// Computed para el total de páginas
const totalPages = computed(() => Math.ceil(filteredCircuits.value.length / perPage.value));

// Función para determinar si mostrar un número de página específico
function shouldShowPageNumber(page) {
  // Siempre mostrar primera y última página
  if (page === 1 || page === totalPages.value) return true;
  
  // Mostrar páginas cercanas a la página actual
  if (Math.abs(page - currentPage.value) <= 1) return true;
  
  return false;
}

// Función para determinar si mostrar puntos suspensivos
function shouldShowEllipsis(page) {
  // Mostrar puntos después de la página 1 si estamos en una página > 3
  if (page === 2 && currentPage.value > 3) return true;
  
  // Mostrar puntos antes de la última página si hay una brecha
  if (page === totalPages.value - 1 && currentPage.value < totalPages.value - 2) return true;
  
  return false;
}

// Si filteredCircuits cambia (por búsqueda), volver a página 1
watch(filteredCircuits, () => {
  currentPage.value = 1;
});

// Funciones para manejar la confirmación
function confirmAction() {
  if (confirmDialog.value.action) {
    confirmDialog.value.action();
  }
  confirmDialog.value.show = false;
}

function cancelConfirmation() {
  confirmDialog.value.show = false;
}

// Función para mostrar notificación
function showNotification(message, type = 'success', duration = 4000) {
  // Limpiar cualquier timeout previo
  if (notification.value.timeout) {
    clearTimeout(notification.value.timeout);
  }
  
  // Establecer la nueva notificación
  notification.value = {
    show: true,
    type,
    message,
    timeout: setTimeout(() => {
      notification.value.show = false;
    }, duration)
  };
}

// Formulario para crear/editar circuito
// Usamos useForm aquí porque facilita el manejo de estado del formulario,
// pero las llamadas se harán con axios a la API.
const circuitForm = useForm({
  id: null,
  name: '',
  location: '',
  country_id: null,
  length: '',
  lap_record: '',
  lap_record_driver: '',
  lap_record_year: null,
  description: '',
  map_image: null,
  layout_image: null,
});

// Cargar circuitos al montar el componente
async function fetchCircuits() {
  loading.value = true;
  try {
    const response = await axios.get('/api/circuits'); // Llama a tu API existente
    circuits.value = response.data.data; // Asumiendo que tu API Resource devuelve en 'data'
  } catch (error) {
    console.error('Error fetching circuits:', error);
    // Aquí podrías mostrar un mensaje de error al usuario
  } finally {
    loading.value = false;
  }
}

onMounted(fetchCircuits);

function openCreateModal() {
  editingCircuit.value = null;
  circuitForm.reset(); // Limpia el formulario
  circuitForm.id = null;
  showFormModal.value = true;
}

function openEditModal(circuit) {
  editingCircuit.value = circuit;
  circuitForm.id = circuit.id;
  circuitForm.name = circuit.name;
  circuitForm.location = circuit.location;
  circuitForm.country_id = circuit.country?.id || null; // Asumiendo que 'country' es un objeto en tu circuit
  circuitForm.length = circuit.length;
  circuitForm.lap_record = circuit.lap_record;
  circuitForm.lap_record_driver = circuit.lap_record_driver;
  circuitForm.lap_record_year = circuit.lap_record_year;
  circuitForm.description = circuit.description;
  // Para las imágenes, es más complejo pre-llenar un input file.
  // Generalmente se muestra la imagen actual y se permite subir una nueva.
  circuitForm.map_image = null;
  circuitForm.layout_image = null;
  showFormModal.value = true;
}

function closeModal() {
  showFormModal.value = false;
  circuitForm.reset();
  editingCircuit.value = null;
}

async function handleSubmit() {
  // Necesitas construir FormData para enviar archivos
  const formData = new FormData();
  formData.append('name', circuitForm.name);
  formData.append('location', circuitForm.location);
  formData.append('country_id', circuitForm.country_id);
  if (circuitForm.length) formData.append('length', circuitForm.length);
  if (circuitForm.lap_record) formData.append('lap_record', circuitForm.lap_record);
  if (circuitForm.lap_record_driver) formData.append('lap_record_driver', circuitForm.lap_record_driver);
  if (circuitForm.lap_record_year) formData.append('lap_record_year', circuitForm.lap_record_year);
  if (circuitForm.description) formData.append('description', circuitForm.description);

  if (circuitForm.map_image instanceof File) {
    formData.append('map_image', circuitForm.map_image);
  }
  if (circuitForm.layout_image instanceof File) {
    formData.append('layout_image', circuitForm.layout_image);
  }

  if (circuitForm.remove_map_image) {
    formData.append('remove_map_image', '1');
  }
  if (circuitForm.remove_layout_image) {
    formData.append('remove_layout_image', '1');
  }

  circuitForm.processing = true;
  circuitForm.errors = {}; // Limpiar errores previos

  try {
    if (editingCircuit.value) {
      // Para actualizar con archivos y método PUT/PATCH, axios necesita un truco
      formData.append('_method', 'PUT'); // Laravel reconocerá esto como PUT
      await axios.post(`/api/circuits/${editingCircuit.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      showNotification('Circuit updated successfully', 'success');
    } else {
      await axios.post('/api/circuits', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      showNotification('New circuit created successfully', 'success');
    }
    await fetchCircuits(); // Recargar la lista
    closeModal();
  } catch (error) {
    console.error('Error submitting circuit form:', error);
    if (error.response && error.response.status === 422) {
      circuitForm.errors = error.response.data.errors; // Errores de validación de Laravel
      showNotification('Please check the form for errors', 'error');
    } else {
      // Otro tipo de error
      showNotification('An unexpected error occurred', 'error');
    }
  } finally {
    circuitForm.processing = false;
  }
}

function deleteCircuit(circuitId) {
  confirmDialog.value = {
    show: true,
    title: 'Delete Circuit',
    message: 'Are you sure you want to delete this circuit? This action cannot be undone.',
    type: 'danger',
    action: async () => {
      try {
        await axios.delete(`/api/circuits/${circuitId}`);
        showNotification('Circuit deleted successfully', 'success');
        await fetchCircuits(); // Recargar la lista
      } catch (error) {
        console.error('Error deleting circuit:', error);
        showNotification('Error deleting circuit', 'error');
      }
    }
  };
}

function handleFileChange(event, fieldName) {
  const file = event.target.files[0];
  if (file) {
    circuitForm[fieldName] = file;
  }
}

function removeExistingImage(field) {
  if (editingCircuit.value) {
    // Marcar la imagen para eliminarla en el backend
    circuitForm[`remove_${field}`] = true;
    
    // Ocultar la imagen en la UI
    if (field === 'map_image') {
      editingCircuit.value.map_image_url = null;
    } else if (field === 'layout_image') {
      editingCircuit.value.layout_image_url = null;
    }
    
    showNotification(`Image will be removed when you save the changes`, 'info');
  }
}

// Añade esto junto a tus otras funciones
function getFilePreviewUrl(file) {
  if (file && typeof window !== 'undefined') {
    return window.URL.createObjectURL(file);
  }
  return null;
}

// Añade esta función para obtener una imagen predeterminada si no hay map_image
function getDefaultCircuitImage() {
  return 'https://via.placeholder.com/800x400/1A2137/FFFFFF?text=Formula+1+Circuit';
}

// Manejar errores de carga de imágenes
function handleImageError(e, circuitId) {
  // Marca este circuito como con imagen fallida
  failedImages.value.add(circuitId);
  
  // Reemplaza solo la imagen, no el contenedor completo
  const img = e.target;
  const parent = img.parentElement;
  
  // Crear el div de reemplazo
  const replacementDiv = document.createElement('div');
  replacementDiv.className = 'w-full h-full flex items-center justify-center';
  replacementDiv.innerHTML = '<i class="fas fa-road text-6xl text-gray-400 dark:text-gray-500"></i>';
  
  // Reemplazar solo la imagen, manteniendo el resto del contenido
  parent.replaceChild(replacementDiv, img);
}

</script>

<template>
  <AppLayout title="Manage Circuits">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header con búsqueda y filtros -->
        <div class="bg-gradient-to-r from-red-800 to-red-600 dark:from-red-900 dark:to-red-700 rounded-lg shadow-lg mb-6 p-6">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <h1 class="text-2xl font-bold text-white">Formula 1 Circuits</h1>
              <p class="text-red-100">Manage all Formula 1 circuit information</p>
            </div>
            <div class="flex items-center gap-3">
              <div class="relative">
                <input 
                  type="text" 
                  v-model="searchQuery" 
                  placeholder="Search circuits..." 
                  class="pl-10 pr-4 py-2 rounded-full bg-red-700/50 dark:bg-red-800/70 text-white border border-red-400 focus:outline-none focus:ring-2 focus:ring-white" 
                />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-search text-red-300"></i>
                </div>
              </div>
              <button @click="openCreateModal" 
                class="px-4 py-2 bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-red-600 dark:text-red-400 rounded-full font-semibold text-sm inline-flex items-center shadow-md transition-all">
                <i class="fas fa-plus mr-2"></i> Add Circuit
              </button>
            </div>
          </div>
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="flex justify-center py-20">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-500"></div>
        </div>

        <!-- Grid de tarjetas -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="circuit in paginatedCircuits" :key="circuit.id" 
               class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden transition-transform hover:scale-[1.02] hover:shadow-xl">
            <!-- Imagen del circuito (si existe) con overlay de país -->
            <div class="relative h-48 bg-gray-100 dark:bg-gray-700">
              <img v-if="circuit.map_image_url" 
                   :src="circuit.map_image_url" 
                   class="w-full h-full object-cover" 
                   alt="Circuit map"
                   @error="handleImageError($event, circuit.id)" />
              <div v-else class="w-full h-full flex items-center justify-center bg-gray-200 dark:bg-gray-700">
                <i class="fas fa-road text-6xl text-gray-400 dark:text-gray-500"></i>
              </div>
              
              <!-- Layout superpuesto - SIEMPRE VISIBLE -->
              <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent flex items-end">
                <div class="p-4 w-full">
                  <h3 class="text-lg font-bold text-white truncate">{{ circuit.name }}</h3>
                  <p class="text-gray-300 text-sm">{{ circuit.location }}</p>
                  <span class="inline-block mt-1 bg-red-600 text-xs text-white px-2 py-0.5 rounded-full">
                    {{ circuit.country?.name || 'Unknown' }}
                  </span>
                </div>
              </div>
            </div>
            
            <!-- Información del circuito -->
            <div class="p-4">
              <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <p class="text-gray-500 dark:text-gray-400 text-xs">Length</p>
                  <p class="text-gray-900 dark:text-white font-semibold">{{ circuit.length }} km</p>
                </div>
                <div>
                  <p class="text-gray-500 dark:text-gray-400 text-xs">Lap Record</p>
                  <p class="text-gray-900 dark:text-white font-semibold">{{ circuit.lap_record || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-gray-500 dark:text-gray-400 text-xs">Record Holder</p>
                  <p class="text-gray-900 dark:text-white font-semibold">{{ circuit.lap_record_driver || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-gray-500 dark:text-gray-400 text-xs">Year</p>
                  <p class="text-gray-900 dark:text-white font-semibold">{{ circuit.lap_record_year || 'N/A' }}</p>
                </div>
              </div>
              
              <!-- Acciones -->
              <div class="flex justify-end space-x-2 pt-3 border-t border-gray-200 dark:border-gray-700">
                <button @click="openEditModal(circuit)" 
                        class="px-3 py-1.5 border border-gray-500 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded text-sm">
                  <i class="fas fa-edit mr-1"></i> Edit
                </button>
                <button @click="deleteCircuit(circuit.id)" 
                        class="px-3 py-1.5 border border-red-500 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded text-sm">
                  <i class="fas fa-trash-alt mr-1"></i> Delete
                </button>
              </div>
            </div>
          </div>
          
          <!-- Tarjeta para añadir nuevo circuito (siempre visible) -->
          <div @click="openCreateModal" 
               class="bg-white/50 dark:bg-gray-800/50 border-2 border-dashed border-red-400/50 rounded-xl flex items-center justify-center h-full min-h-[350px] cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors">
            <div class="text-center p-6">
              <div class="bg-red-500/10 rounded-full p-4 mx-auto mb-4 w-16 h-16 flex items-center justify-center">
                <i class="fas fa-plus text-red-500 dark:text-red-400 text-xl"></i>
              </div>
              <h3 class="text-lg font-medium text-red-600 dark:text-red-400">Add New Circuit</h3>
              <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">Click to create a new Formula 1 circuit</p>
            </div>
          </div>
          
          <!-- Mensaje si no hay circuitos o no hay resultados de búsqueda -->
          <div v-if="!loading && filteredCircuits.length === 0" class="col-span-full">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-8 text-center">
              <div class="bg-red-500/10 rounded-full p-4 mx-auto mb-4 w-16 h-16 flex items-center justify-center">
                <i class="fas fa-road text-red-500 dark:text-red-400 text-xl"></i>
              </div>
              <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">
                {{ searchQuery ? 'No circuits found for your search' : 'No circuits found' }}
              </h3>
              <p class="text-gray-500 dark:text-gray-400">
                {{ searchQuery ? 'Try with a different search term or clear the search' : 'Start by creating your first Formula 1 circuit' }}
              </p>
              <div class="mt-4 flex justify-center space-x-3">
                <button v-if="searchQuery" @click="searchQuery = ''" 
                  class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-md">
                  <i class="fas fa-times mr-2"></i> Clear Search
                </button>
                <button @click="openCreateModal" 
                  class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md">
                  <i class="fas fa-plus mr-2"></i> Add Circuit
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Controles de paginación -->
        <div v-if="!loading && filteredCircuits.length > perPage" class="mt-8 flex flex-col sm:flex-row justify-between items-center gap-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
          <!-- Información de paginación -->
          <div class="text-sm text-gray-600 dark:text-gray-400">
            Showing 
            <span class="font-medium text-gray-900 dark:text-white">
              {{ (currentPage - 1) * perPage + 1 }}
            </span> 
            to 
            <span class="font-medium text-gray-900 dark:text-white">
              {{ Math.min(currentPage * perPage, filteredCircuits.length) }}
            </span> 
            of 
            <span class="font-medium text-gray-900 dark:text-white">
              {{ filteredCircuits.length }}
            </span> 
            circuits
          </div>
          
          <!-- Botones de paginación -->
          <div class="flex space-x-2">
            <!-- Anterior -->
            <button 
              @click="currentPage > 1 && currentPage--" 
              :disabled="currentPage === 1"
              class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 disabled:opacity-50 disabled:cursor-not-allowed text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
              :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }"
            >
              <i class="fas fa-chevron-left"></i>
            </button>
            
            <!-- Números de página -->
            <div class="flex space-x-1">
              <template v-for="page in totalPages" :key="page">
                <!-- Sólo mostrar algunos números de página si hay muchas -->
                <button 
                  v-if="shouldShowPageNumber(page)"
                  @click="currentPage = page" 
                  class="px-3 py-1 rounded"
                  :class="{ 
                    'bg-red-600 text-white': currentPage === page, 
                    'border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': currentPage !== page
                  }"
                >
                  {{ page }}
                </button>
                <!-- Puntos suspensivos para páginas omitidas -->
                <span 
                  v-else-if="shouldShowEllipsis(page)" 
                  class="px-3 py-1 text-gray-500 dark:text-gray-400"
                >
                  ...
                </span>
              </template>
            </div>
            
            <!-- Siguiente -->
            <button 
              @click="currentPage < totalPages && currentPage++" 
              :disabled="currentPage === totalPages"
              class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 disabled:opacity-50 disabled:cursor-not-allowed text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
              :class="{ 'opacity-50 cursor-not-allowed': currentPage === totalPages }"
            >
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para Crear/Editar Circuito -->
    <div v-if="showFormModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-75 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
          {{ editingCircuit ? 'Edit Circuit' : 'Create New Circuit' }}
        </h2>
        <form @submit.prevent="handleSubmit">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Name -->
            <div>
              <label for="form-name" class="block text-xs font-semibold text-gray-400 mb-1">Name</label>
              <input
                id="form-name"
                type="text"
                v-model="circuitForm.name"
                class="w-full rounded bg-gray-900 border-0 text-white focus:ring-2 focus:ring-blue-500 text-sm px-3 py-2"
                autocomplete="off"
              />
              <div v-if="circuitForm.errors.name" class="text-red-500 text-xs mt-1">{{ circuitForm.errors.name[0] }}</div>
            </div>
            <!-- Location -->
            <div>
              <label for="form-location" class="block text-xs font-semibold text-gray-400 mb-1">Location</label>
              <input
                id="form-location"
                type="text"
                v-model="circuitForm.location"
                class="w-full rounded bg-gray-900 border-0 text-white focus:ring-2 focus:ring-blue-500 text-sm px-3 py-2"
                autocomplete="off"
              />
              <div v-if="circuitForm.errors.location" class="text-red-500 text-xs mt-1">{{ circuitForm.errors.location[0] }}</div>
            </div>
          </div>

          <!-- Country (ocupa toda la fila) -->
          <div class="mt-4">
            <label for="form-country" class="block text-xs font-semibold text-gray-400 mb-1">Country</label>
            <select
              v-model="circuitForm.country_id"
              id="form-country"
              class="w-full rounded bg-gray-900 border-0 text-white focus:ring-2 focus:ring-blue-500 text-sm px-3 py-2"
            >
              <option :value="null" disabled>Select a country</option>
              <option v-for="country in props.countries" :key="country.country_id" :value="country.country_id">
                {{ country.name }}
              </option>
            </select>
            <div v-if="circuitForm.errors.country_id" class="text-red-500 text-xs mt-1">{{ circuitForm.errors.country_id[0] }}</div>
          </div>

          <!-- Resto de campos en dos columnas -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <!-- Length -->
            <div>
              <label for="form-length" class="block text-xs font-semibold text-gray-400 mb-1">Length (km)</label>
              <input
                id="form-length"
                type="text"
                v-model="circuitForm.length"
                class="w-full rounded bg-gray-900 border-0 text-white focus:ring-2 focus:ring-blue-500 text-sm px-3 py-2"
                autocomplete="off"
              />
              <div v-if="circuitForm.errors.length" class="text-red-500 text-xs mt-1">{{ circuitForm.errors.length[0] }}</div>
            </div>
            <!-- Lap Record -->
            <div>
              <label for="form-lap_record" class="block text-xs font-semibold text-gray-400 mb-1">Lap Record</label>
              <input
                id="form-lap_record"
                type="text"
                v-model="circuitForm.lap_record"
                class="w-full rounded bg-gray-900 border-0 text-white focus:ring-2 focus:ring-blue-500 text-sm px-3 py-2"
                autocomplete="off"
              />
              <div v-if="circuitForm.errors.lap_record" class="text-red-500 text-xs mt-1">{{ circuitForm.errors.lap_record[0] }}</div>
            </div>
            <!-- Lap Record Driver -->
            <div>
              <label for="form-lap_record_driver" class="block text-xs font-semibold text-gray-400 mb-1">Lap Record Driver</label>
              <input
                id="form-lap_record_driver"
                type="text"
                v-model="circuitForm.lap_record_driver"
                class="w-full rounded bg-gray-900 border-0 text-white focus:ring-2 focus:ring-blue-500 text-sm px-3 py-2"
                autocomplete="off"
              />
              <div v-if="circuitForm.errors.lap_record_driver" class="text-red-500 text-xs mt-1">{{ circuitForm.errors.lap_record_driver[0] }}</div>
            </div>
            <!-- Lap Record Year -->
            <div>
              <label for="form-lap_record_year" class="block text-xs font-semibold text-gray-400 mb-1">Lap Record Year</label>
              <input
                id="form-lap_record_year"
                type="number"
                v-model="circuitForm.lap_record_year"
                class="w-full rounded bg-gray-900 border-0 text-white focus:ring-2 focus:ring-blue-500 text-sm px-3 py-2"
                autocomplete="off"
              />
              <div v-if="circuitForm.errors.lap_record_year" class="text-red-500 text-xs mt-1">{{ circuitForm.errors.lap_record_year[0] }}</div>
            </div>
          </div>

          <!-- Description (ocupa toda la fila) -->
          <div class="mt-4">
            <label for="form-description" class="block text-xs font-semibold text-gray-400 mb-1">Description</label>
            <textarea
              id="form-description"
              v-model="circuitForm.description"
              rows="3"
              class="w-full rounded bg-gray-900 border-0 text-white focus:ring-2 focus:ring-blue-500 text-sm px-3 py-2"
            ></textarea>
            <div v-if="circuitForm.errors.description" class="text-red-500 text-xs mt-1">{{ circuitForm.errors.description[0] }}</div>
          </div>

          <!-- Map Image -->
          <div class="mt-4">
            <label class="block text-xs font-semibold text-gray-400 mb-1">Map Image</label>
            <input
              type="file"
              @change="handleFileChange($event, 'map_image')"
              class="block w-full text-sm text-gray-400 cursor-pointer file:cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-700 file:text-white hover:file:bg-gray-600"
            />
            <div v-if="circuitForm.errors.map_image" class="text-red-500 text-xs mt-1">{{ circuitForm.errors.map_image[0] }}</div>
            
            <!-- Preview de la nueva imagen seleccionada -->
            <div v-if="circuitForm.map_image" class="mt-2 relative inline-block">
              <img :src="getFilePreviewUrl(circuitForm.map_image)" alt="Preview" class="max-h-32 rounded shadow" />
              <button type="button" @click="circuitForm.map_image = null"
                class="absolute top-1 right-1 bg-red-600 hover:bg-red-700 text-white rounded-full w-6 h-6 flex items-center justify-center z-10">
                <i class="fas fa-times text-xs"></i>
              </button>
            </div>
            
            <!-- Preview de la imagen actual y opción de eliminar -->
            <div v-else-if="editingCircuit && editingCircuit.map_image_url" class="mt-2 relative inline-block">
              <img 
                :src="editingCircuit.map_image_url" 
                alt="Current map" 
                class="max-h-32 rounded shadow"
                @error="e => e.target.parentElement.style.display = 'none'"
              />
              <button type="button" @click="removeExistingImage('map_image')"
                class="absolute top-1 right-1 bg-red-600 hover:bg-red-700 text-white rounded-full w-6 h-6 flex items-center justify-center z-10">
                <i class="fas fa-times text-xs"></i>
              </button>
            </div>
          </div>

          <!-- Layout Image -->
          <div class="mt-4">
            <label class="block text-xs font-semibold text-gray-400 mb-1">Layout Image</label>
            <input
              type="file"
              @change="handleFileChange($event, 'layout_image')"
              class="block w-full text-sm text-gray-400 cursor-pointer file:cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-700 file:text-white hover:file:bg-gray-600"
            />
            <div v-if="circuitForm.errors.layout_image" class="text-red-500 text-xs mt-1">{{ circuitForm.errors.layout_image[0] }}</div>
            
            <!-- Preview de la nueva imagen seleccionada -->
            <div v-if="circuitForm.layout_image" class="mt-2 relative inline-block">
              <img :src="getFilePreviewUrl(circuitForm.layout_image)" alt="Preview" class="max-h-32 rounded shadow" />
              <button type="button" @click="circuitForm.layout_image = null"
                class="absolute top-1 right-1 bg-red-600 hover:bg-red-700 text-white rounded-full w-6 h-6 flex items-center justify-center z-10">
                <i class="fas fa-times text-xs"></i>
              </button>
            </div>
            
            <!-- Preview de la imagen actual y opción de eliminar -->
            <div v-else-if="editingCircuit && editingCircuit.layout_image_url" class="mt-2 relative inline-block">
              <img :src="editingCircuit.layout_image_url" alt="Current layout" class="max-h-32 rounded shadow" />
              <button type="button" @click="removeExistingImage('layout_image')"
                class="absolute top-1 right-1 bg-red-600 hover:bg-red-700 text-white rounded-full w-6 h-6 flex items-center justify-center z-10">
                <i class="fas fa-times text-xs"></i>
              </button>
            </div>
          </div>

          <!-- Botones con color rojo para el principal -->
          <div class="flex items-center justify-end space-x-3 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
            <button type="button" @click="closeModal" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
              Cancel
            </button>
            <button
              type="submit"
              :disabled="circuitForm.processing"
              class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md font-medium text-sm inline-flex items-center disabled:opacity-50"
            >
              <i class="fas fa-save mr-1" v-if="!circuitForm.processing"></i>
              <i class="fas fa-spinner fa-spin mr-1" v-if="circuitForm.processing"></i>
              {{ editingCircuit ? 'Update Circuit' : 'Create Circuit' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal de confirmación personalizado -->
    <div v-if="confirmDialog.show" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-75 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl w-full max-w-md">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ confirmDialog.title }}</h3>
        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ confirmDialog.message }}</p>
        <div class="mt-6 flex justify-end space-x-3">
          <button 
            @click="cancelConfirmation" 
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            Cancel
          </button>
          <button 
            @click="confirmAction" 
            :class="{
              'px-4 py-2 text-white rounded-md font-medium text-sm': true,
              'bg-red-600 hover:bg-red-700': confirmDialog.type === 'danger',
              'bg-blue-600 hover:bg-blue-700': confirmDialog.type !== 'danger'
            }"
          >
            Confirm
          </button>
        </div>
      </div>
    </div>

    <!-- Sistema de notificaciones -->
    <div v-if="notification.show" 
         class="fixed bottom-4 right-4 z-50 max-w-md shadow-lg rounded-md overflow-hidden transition-all transform"
         :class="{
           'bg-green-600': notification.type === 'success',
           'bg-red-600': notification.type === 'error',
           'bg-blue-600': notification.type === 'info',
           'bg-yellow-600': notification.type === 'warning',
         }"
         style="animation: slide-up 0.3s ease-out forwards;">
      <div class="flex items-center p-4">
        <div class="flex-shrink-0 mr-3">
          <i :class="{
              'fas fa-check-circle': notification.type === 'success',
              'fas fa-exclamation-circle': notification.type === 'error',
              'fas fa-info-circle': notification.type === 'info',
              'fas fa-exclamation-triangle': notification.type === 'warning'
            }" class="text-white text-lg"></i>
        </div>
        <div class="flex-1 mr-2">
          <p class="text-white text-sm">{{ notification.message }}</p>
        </div>
        <button @click="notification.show = false" class="flex-shrink-0 text-white hover:text-gray-200">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <!-- Barra de progreso -->
      <div class="h-1 bg-white/30 relative">
        <div class="absolute top-0 left-0 h-1 bg-white/70"
             :style="{ 
               width: '100%', 
               animation: `shrink ${notification.timeout ? notification.timeout / 1000 : 4}s linear forwards` 
             }">
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes shrink {
  from { width: 100%; }
  to { width: 0%; }
}
</style>
``` 
