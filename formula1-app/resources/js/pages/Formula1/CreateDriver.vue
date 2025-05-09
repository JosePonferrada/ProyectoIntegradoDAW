<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';

// Props
const props = defineProps({
  countries: Array,
  constructors: Array,
  currentSeason: Object
});

// Estados
const form = ref({
  first_name: '',
  last_name: '',
  code: '',
  number: null,
  date_of_birth: '',
  debut_year: new Date().getFullYear(), // Año de debut, por defecto el actual
  nationality: '',
  biography: '',
  championships: 0,
  wins: 0,
  podiums: 0,
  poles: 0,
  active: true,
  image: '',
  constructor_id: '', // Inicializar como string vacío
  position_number: 1,
  season_id: null // Cambiado a null para mayor claridad
});

const loading = ref(false);
const errors = ref({});
const imagePreview = ref(null);
const imageFile = ref(null);
const useExistingImage = ref(true);
const selectedDriver = ref(null);
const currentIndex = ref(0);
const imageLoaded = ref({});
const previewSize = ref(window.innerWidth >= 640 ? 'w-full h-28' : 'w-full h-20');

// Lista de pilotos con su nombre y apellido
const drivers = [
  { name: 'Max', lastName: 'Verstappen', id: 'verstappen', imageUrl: '/storage/drivers/verstappen.avif' },
  { name: 'Lewis', lastName: 'Hamilton', id: 'hamilton', imageUrl: '/storage/drivers/hamilton.avif' },
  { name: 'Lando', lastName: 'Norris', id: 'norris', imageUrl: '/storage/drivers/norris.avif' },
  { name: 'Charles', lastName: 'Leclerc', id: 'leclerc', imageUrl: '/storage/drivers/leclerc.avif' },
  { name: 'Carlos', lastName: 'Sainz', id: 'sainz', imageUrl: '/storage/drivers/sainz.avif' },
  { name: 'Oscar', lastName: 'Piastri', id: 'piastri', imageUrl: '/storage/drivers/piastri.avif' },
  { name: 'George', lastName: 'Russell', id: 'russell', imageUrl: '/storage/drivers/russell.avif' },
  { name: 'Sergio', lastName: 'Perez', id: 'perez', imageUrl: '/storage/drivers/perez.avif' },
  { name: 'Fernando', lastName: 'Alonso', id: 'alonso', imageUrl: '/storage/drivers/alonso.avif' },
  { name: 'Lance', lastName: 'Stroll', id: 'stroll', imageUrl: '/storage/drivers/stroll.avif' }
];

// Datos para el componente
const constructors = ref(props.constructors || []);
const constructorDrivers = ref([]);
const isLoadingConstructors = ref(false);
const isLoadingDrivers = ref(false);

// Anticipar tamaños de ventana para responsive
onMounted(() => {
  const handleResize = () => {
    previewSize.value = window.innerWidth >= 640 ? 'w-full h-28' : 'w-full h-20';
  };
  window.addEventListener('resize', handleResize);
  return () => window.removeEventListener('resize', handleResize);
});

// Cálculos para el carrusel
const driversPerPage = 4;
const visibleDrivers = computed(() => {
  const start = currentIndex.value;
  const end = start + driversPerPage;
  return drivers.slice(start, Math.min(end, drivers.length));
});

// Navegación del carrusel
const nextDrivers = () => {
  if (currentIndex.value + driversPerPage < drivers.length) {
    currentIndex.value += driversPerPage;
  } else {
    currentIndex.value = 0;
  }
};

const prevDrivers = () => {
  if (currentIndex.value - driversPerPage >= 0) {
    currentIndex.value -= driversPerPage;
  } else {
    currentIndex.value = Math.max(0, Math.floor(drivers.length / driversPerPage) * driversPerPage - driversPerPage);
  }
};

// Manejar carga de imagen fallida
const handleImageError = (event, driver) => {
  if (driver) {
    imageLoaded.value[driver.id] = false;
    
    // Reemplazar la imagen con el fallback SVG
    const img = event.target;
    const parent = img.parentNode;
    
    // Ocultar la imagen
    img.style.display = 'none';
    
    // Crear contenedor de fallback con las mismas dimensiones
    const fallback = document.createElement('div');
    fallback.className = 'flex items-center justify-center w-full h-full bg-gray-100 dark:bg-gray-700';
    fallback.style.height = '100%'; // Asegurar que mantenga la altura
    
    fallback.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
      </svg>
    `;
    
    parent.appendChild(fallback);
  }
};

// Manejar carga de imagen exitosa
const handleImageLoad = (driverId) => {
  imageLoaded.value[driverId] = true;
};

// Manejar selección de piloto con previsualización
const selectDriver = (driverId) => {
  if (selectedDriver.value === driverId) {
    selectedDriver.value = null;
    form.value.image = '';
    imagePreview.value = null;
    return;
  }
  
  // Buscar el driver seleccionado
  const selectedDriverObj = drivers.find(d => d.id === driverId);
  if (!selectedDriverObj) return;
  
  selectedDriver.value = driverId;
  // Guardar la ruta de imagen para usarla después
  form.value.image = `${driverId.toLowerCase()}.avif`;
  
  // Usar la URL de la imagen para la previsualización
  imagePreview.value = selectedDriverObj.imageUrl;
  
  // Resetear el archivo subido ya que estamos usando una imagen existente
  imageFile.value = null;
};

// Nueva función simplificada para manejar la carga de imágenes
const handleImageUpload = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  
  // Guardar el archivo
  imageFile.value = file;
  
  // Crear vista previa inmediata
  const reader = new FileReader();
  reader.onload = (event) => {
    imagePreview.value = event.target.result;
  };
  reader.readAsDataURL(file);
};

// Función para eliminar la imagen
const removeImage = () => {
  imageFile.value = null;
  imagePreview.value = null;
};

// Añadir esta nueva función al componente
const chooseAnotherImage = () => {
  // Primero limpiamos la imagen actual
  imageFile.value = null;
  form.value.image = '';
  imagePreview.value = null;
  
  // Esperamos a que Vue actualice el DOM
  nextTick(() => {
    // Luego activamos el diálogo de selección de archivo
    document.getElementById('file-upload').click();
  });
};

// Aplicar mayúsculas a code
const formatCode = () => {
  if (form.value.code) {
    form.value.code = form.value.code.toUpperCase();
  }
};

// Función para cargar constructores
const loadConstructors = async () => {
  // Only load if we don't already have constructors
  if (constructors.value.length === 0) {
    try {
      isLoadingConstructors.value = true;
      const response = await axios.get('/api/constructors');
      constructors.value = response.data.data || [];
      
      // Debug the first constructor to see the structure
      if (constructors.value.length > 0) {
        console.log('First constructor:', constructors.value[0]);
        console.log('Constructor properties:', Object.keys(constructors.value[0]));
      }
    } catch (error) {
      console.error('Error loading constructors:', error);
    } finally {
      isLoadingConstructors.value = false;
    }
  }
};

// En onConstructorChange
const onConstructorChange = (event) => {
  // Get constructor ID from the select value
  const selectedId = event.target.value;
  
  console.log('Selected constructor ID:', selectedId);
  
  // Set the ID directly
  form.value.constructor_id = selectedId;
  
  // Log the selected constructor for debugging - CORREGIDO
  if (selectedId) {
    const selectedConstructor = constructors.value.find(c => c.id == selectedId);
    console.log('Constructor found:', selectedConstructor);
  }
};

// Función para cargar la temporada actual - asegúrate de que este código se ejecuta
const loadSeasonId = async () => {
  try {
    console.log("Cargando ID de temporada...");
    // Si se pasa como prop, utilízalo
    if (props.currentSeason?.season_id) {
      form.value.season_id = Number(props.currentSeason.season_id);
      console.log("ID de temporada desde props:", form.value.season_id);
      return;
    }
    
    // Si no, cargar todas las temporadas y encontrar la actual
    const response = await axios.get('/api/seasons');
    console.log("Temporadas cargadas:", response.data);
    
    if (response.data && response.data.data && response.data.data.length > 0) {
      // NOTA: La API devuelve "id", no "season_id"
      const sortedSeasons = [...response.data.data].sort((a, b) => b.year - a.year);
      form.value.season_id = Number(sortedSeasons[0].id); // Cambiar a "id"
      console.log("ID de temporada seleccionada:", form.value.season_id);
    }
  } catch (error) {
    console.error('Error cargando temporadas:', error);
  }
};

// En el hook onMounted
onMounted(async () => {
  // Código existente...
  await Promise.all([loadConstructors(), loadSeasonId()]);
});

// Watcher para constructor_id
watch(
  () => form.value.constructor_id,
  async (newValue) => {
    console.log('Constructor seleccionado:', newValue, typeof newValue);
    
    // SOLO cargar datos, NO modificar el valor
    if (newValue) {
      try {
        isLoadingDrivers.value = true;
        const response = await axios.get(`/api/constructors/${newValue}`);
        if (response.data?.data?.drivers) {
          constructorDrivers.value = response.data.data.drivers;
        }
      } catch (error) {
        console.error('Error cargando pilotos del constructor:', error);
      } finally {
        isLoadingDrivers.value = false;
      }
    } else {
      constructorDrivers.value = [];
    }
  }
);

// Función para obtener el texto de la posición del piloto
const getDriverPositionText = (position) => {
  const positions = {
    1: '1st Driver',
    2: '2nd Driver',
    3: 'Reserve Driver',
    4: 'Development Driver'
  };
  return positions[position] || `Position ${position}`;
};

// Simplificar el envío del formulario
const submit = async () => {
  try {
    loading.value = true;
    errors.value = {};
    
    // Crear FormData para envío multipart
    const formData = new FormData();
    
    // Agregar todos los campos básicos del piloto
    Object.keys(form.value).forEach(key => {
      // No incluir 'image' ya que lo manejaremos por separado
      if (key !== 'image' && form.value[key] !== null && form.value[key] !== undefined) {
        // Conversión de tipos según sea necesario
        if (['constructor_id', 'season_id', 'position_number'].includes(key) && form.value[key] !== '') {
          formData.append(key, Number(form.value[key]));
        } else if (key === 'active') {
          formData.append(key, form.value[key] ? '1' : '0');
        } else {
          formData.append(key, form.value[key]);
        }
      }
    });
    
    // Agregar la imagen si existe
    if (imageFile.value) {
      formData.append('image_file', imageFile.value);
    }
    
    // Realizar la petición POST
    const response = await axios.post('/api/drivers', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    console.log("Respuesta del servidor:", response.data);
    
    // Redirigir a la lista de pilotos
    router.visit('/drivers');
  } catch (error) {
    console.error('Error creando piloto:', error);
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else if (error.response?.data?.message) {
      alert('Error: ' + error.response.data.message);
    }
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <AppLayout>
    <Head title="Create Driver" />
    
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Add New Driver</h1>
          
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Sección de información personal -->
            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Personal Information</h2>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <!-- Nombre -->
                <div>
                  <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    First Name *
                  </label>
                  <input 
                    id="first_name" 
                    v-model="form.first_name" 
                    type="text" 
                    required
                    class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                  />
                  <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">{{ errors.first_name[0] }}</p>
                </div>
                
                <!-- Apellido -->
                <div>
                  <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Last Name *
                  </label>
                  <input 
                    id="last_name" 
                    v-model="form.last_name" 
                    type="text" 
                    required
                    class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                  />
                  <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">{{ errors.last_name[0] }}</p>
                </div>
                
                <!-- Código -->
                <div>
                  <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Code (3 letters) *
                  </label>
                  <input 
                    id="code" 
                    v-model="form.code" 
                    type="text" 
                    maxlength="3"
                    required
                    @blur="formatCode"
                    class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm uppercase"
                  />
                  <p v-if="errors.code" class="mt-1 text-sm text-red-600">{{ errors.code[0] }}</p>
                </div>
                
                <!-- Número -->
                <div>
                  <label for="number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Number (0-99) *
                  </label>
                  <input 
                    id="number" 
                    v-model="form.number" 
                    type="number" 
                    min="0"
                    max="99"
                    required
                    class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                  />
                  <p v-if="errors.number" class="mt-1 text-sm text-red-600">{{ errors.number[0] }}</p>
                </div>
                
                <!-- Fecha de nacimiento -->
                <div>
                  <label for="date_of_birth" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Date of Birth *
                  </label>
                  <input 
                    id="date_of_birth" 
                    v-model="form.date_of_birth" 
                    type="date" 
                    required
                    class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                  />
                  <p v-if="errors.date_of_birth" class="mt-1 text-sm text-red-600">{{ errors.date_of_birth[0] }}</p>
                </div>
                
                <!-- Año de debut -->
                <div>
                  <label for="debut_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Debut Year *
                  </label>
                  <input 
                    id="debut_year" 
                    v-model="form.debut_year" 
                    type="number" 
                    :min="1950"
                    :max="new Date().getFullYear()"
                    required
                    class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                  />
                  <p v-if="errors.debut_year" class="mt-1 text-sm text-red-600">{{ errors.debut_year[0] }}</p>
                </div>
                
                <!-- País -->
                <div>
                  <label for="nationality" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Nationality *
                  </label>
                  <select 
                    id="nationality" 
                    v-model="form.nationality" 
                    required
                    class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                  >
                    <option value="" disabled>Select country</option>
                    <option v-for="country in countries" :key="country.country_id" :value="country.country_id">
                      {{ country.name }}
                    </option>
                  </select>
                  <p v-if="errors.nationality" class="mt-1 text-sm text-red-600">{{ errors.nationality[0] }}</p>
                </div>
              </div>
            </div>

            <!-- Nueva sección de asignación de equipo -->
            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600 mb-6">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Team Assignment</h2>
              
              <div class="flex flex-col md:flex-row gap-6">
                <div class="flex-1">
                  <label for="constructor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Team</label>
                  <div class="relative">
                    <select 
                      id="constructor_id" 
                      v-model="form.constructor_id" 
                      @change="onConstructorChange"
                      class="w-full px-4 py-3 pr-10 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm appearance-none"
                    >
                      <option value="">No team currently</option>
                      <option v-for="constructor in constructors" :key="constructor.id" :value="constructor.id">
                        {{ constructor.name }}
                      </option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                      <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                      </svg>
                    </div>
                  </div>
                </div>
                
                <div class="flex-1" v-if="form.constructor_id !== ''">
                  <label for="position_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Position in Team
                  </label>
                  <div class="relative">
                    <select 
                      id="position_number" 
                      v-model="form.position_number"
                      class="w-full px-4 py-3 pr-10 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm appearance-none"
                    >
                      <option value="1">1st Driver</option>
                      <option value="2">2nd Driver</option>
                      <option value="3">Reserve Driver</option>
                      <option value="4">Development Driver</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                      <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                      </svg>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Sección de estadísticas -->
            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Career Statistics</h2>
              
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Campeonatos -->
                <div>
                  <label for="championships" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Championships
                  </label>
                  <input 
                    id="championships" 
                    v-model="form.championships" 
                    type="number" 
                    min="0"
                    class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                  />
                  <p v-if="errors.championships" class="mt-1 text-sm text-red-600">{{ errors.championships[0] }}</p>
                </div>
                
                <!-- Victorias -->
                <div>
                  <label for="wins" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Wins
                  </label>
                  <input 
                    id="wins" 
                    v-model="form.wins" 
                    type="number" 
                    min="0"
                    class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                  />
                  <p v-if="errors.wins" class="mt-1 text-sm text-red-600">{{ errors.wins[0] }}</p>
                </div>
                
                <!-- Podios -->
                <div>
                  <label for="podiums" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Podiums
                  </label>
                  <input 
                    id="podiums" 
                    v-model="form.podiums" 
                    type="number" 
                    min="0"
                    class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                  />
                  <p v-if="errors.podiums" class="mt-1 text-sm text-red-600">{{ errors.podiums[0] }}</p>
                </div>
                
                <!-- Poles -->
                <div>
                  <label for="poles" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Pole Positions
                  </label>
                  <input 
                    id="poles" 
                    v-model="form.poles" 
                    type="number" 
                    min="0"
                    class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                  />
                  <p v-if="errors.poles" class="mt-1 text-sm text-red-600">{{ errors.poles[0] }}</p>
                </div>
              </div>
              
              <!-- Activo - asegurándonos que es un checkbox correcto -->
              <div class="mt-4">
                <div class="flex items-center">
                  <input 
                    id="active" 
                    v-model="form.active" 
                    type="checkbox"
                    class="h-5 w-5 text-f1-red focus:ring-f1-red border-gray-300 rounded"
                  />
                  <label for="active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Currently Active Driver
                  </label>
                </div>
                <p v-if="errors.active" class="mt-1 text-sm text-red-600">{{ errors.active[0] }}</p>
              </div>
            </div>
            
            <!-- Biografía -->
            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Biography</h2>
              
              <div>
                <label for="biography" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Driver Biography
                </label>
                <textarea 
                  id="biography" 
                  v-model="form.biography" 
                  rows="4"
                  class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                ></textarea>
                <p v-if="errors.biography" class="mt-1 text-sm text-red-600">{{ errors.biography[0] }}</p>
              </div>
            </div>
            
            <!-- Sección de imagen simplificada -->
            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Driver Image</h2>
              
              <!-- Sección para elegir imágenes existentes -->
              <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-3">
                  Select existing driver image
                </h3>
                
                <!-- Carrusel de imágenes existentes -->
                <div class="relative">
                  <!-- Botón Anterior -->
                  <button 
                    @click="prevDrivers" 
                    class="absolute left-0 top-1/2 transform -translate-y-1/2 z-10 bg-white dark:bg-gray-800 rounded-full p-2 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700"
                    aria-label="Previous drivers"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                  </button>
                  
                  <!-- Grid de imágenes -->
                  <div class="grid grid-cols-4 gap-2 py-2 px-8">
                    <div 
                      v-for="driver in visibleDrivers" 
                      :key="driver.id"
                      class="cursor-pointer relative"
                    >
                      <div 
                        class="border-2 rounded overflow-hidden"
                        :class="selectedDriver === driver.id ? 'border-f1-red' : 'border-transparent hover:border-gray-300 dark:hover:border-gray-600'"
                        @click="selectDriver(driver.id)"
                      >
                        <div class="aspect-w-1 aspect-h-1">
                          <img 
                            :src="driver.imageUrl"
                            :alt="`${driver.name} ${driver.lastName}`"
                            class="object-contain w-full h-full"
                            @error="handleImageError($event, driver)"
                          />
                        </div>
                      </div>
                      <p class="text-xs text-center mt-1 text-gray-600 dark:text-gray-400 truncate">
                        {{ driver.lastName }}
                      </p>
                    </div>
                  </div>
                  
                  <!-- Botón Siguiente -->
                  <button 
                    @click="nextDrivers" 
                    class="absolute right-0 top-1/2 transform -translate-y-1/2 z-10 bg-white dark:bg-gray-800 rounded-full p-2 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700"
                    aria-label="Next drivers"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                </div>
                
                <div class="flex justify-center mt-2">
                  <button 
                    v-if="selectedDriver"
                    @click="selectedDriver = null; imagePreview = null"
                    type="button"
                    class="text-sm text-f1-red hover:text-red-700"
                  >
                    Clear selection
                  </button>
                </div>
              </div>
              
              <!-- Área de carga de imagen -->
              <div class="flex flex-col space-y-4">
                <!-- Zona para arrastrar/cargar archivos - mostrar cuando no hay preview -->
                <div 
                  v-if="!imagePreview"
                  class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md bg-gray-50 dark:bg-gray-800"
                >
                  <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                      <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex text-sm text-gray-600 dark:text-gray-400">
                      <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-f1-red hover:text-red-700 focus-within:outline-none">
                        <span>Upload a file</span>
                        <input 
                          id="file-upload" 
                          name="file-upload" 
                          type="file" 
                          accept="image/*"
                          @change="handleImageUpload" 
                          class="sr-only" 
                        />
                      </label>
                      <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      PNG, JPG, GIF up to 10MB
                    </p>
                  </div>
                </div>
                
                <!-- Vista previa de imagen subida por el usuario -->
                <div v-if="imagePreview" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                  <div class="relative mx-auto h-48 w-48"> <!-- Tamaño más contenido -->
                    <img 
                      :src="imagePreview" 
                      class="object-contain h-full w-full rounded-md" 
                      alt="Driver image preview"
                      @error="(e) => {
                        e.target.style.display = 'none';
                        const fallback = e.target.nextElementSibling;
                        fallback.style.display = 'flex';
                      }"
                    >
                    <!-- Fallback que se muestra cuando la imagen falla -->
                    <div 
                      class="hidden absolute inset-0 items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-md"
                      style="height: 100%;"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                    </div>
                    <!-- Botón para eliminar imagen -->
                    <button 
                      type="button" 
                      @click="removeImage"
                      class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 focus:outline-none"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
              
              <p v-if="errors.image_file" class="mt-2 text-sm text-red-600">{{ errors.image_file[0] }}</p>
            </div>
            
            <!-- Botones de acción -->
            <div class="flex justify-end space-x-4">
              <button 
                type="button"
                @click="router.visit('/drivers')"
                class="px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-f1-red"
              >
                Cancel
              </button>
              <button 
                type="submit"
                :disabled="loading"
                class="px-6 py-3 rounded-md shadow-sm text-white bg-f1-red hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-f1-red flex items-center justify-center min-w-[120px]"
              >
                <span v-if="loading">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Saving...
                </span>
                <span v-else>Save Driver</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Transición suave para el cambio entre tarjetas */
.grid {
  transition: all 0.3s ease;
}

input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}

input[type=number] {
  -moz-appearance: textfield;
}

/* Transiciones para las previsualizaciones */
.relative img {
  transition: all 0.2s ease-in-out;
}

/* Estilo para el botón de eliminar al pasar el ratón */
.relative:hover button {
  opacity: 1;
}

.relative button {
  transition: opacity 0.2s ease;
  opacity: 0.7;
}

/* Añadir estos estilos para controlar mejor el tamaño de las imágenes */

/* Contenedor con altura fija para todas las imágenes */
.aspect-w-1.aspect-h-1 {
  height: 80px;  /* Altura fija más pequeña */
  width: 100%;
  position: relative;
  overflow: hidden;
}

/* Asegurar que todas las imágenes se muestren correctamente dentro del contenedor */
.aspect-w-1.aspect-h-1 img {
  object-fit: contain;  /* Mantiene la proporción sin recortar */
  height: 100%;
  width: 100%;
}

/* Estilo para el fallback SVG para mantener consistencia */
.aspect-w-1.aspect-h-1 div {
  height: 100%;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>