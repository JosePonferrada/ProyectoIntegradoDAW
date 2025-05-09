<template>
  <AppLayout>
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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
          <h1 class="text-2xl font-bold mb-6">Edit Team</h1>
          
          <div v-if="loading" class="flex justify-center py-8">
            <svg class="animate-spin h-8 w-8 text-f1-red" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
          
          <form v-else @submit.prevent="submit" class="space-y-6">
            <!-- Team name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Team Name</label>
              <input 
                type="text" 
                id="name"
                v-model="form.name"
                class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
                required
              >
              <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
            </div>
            
            <!-- Nationality -->
            <div>
              <label for="nationality" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nationality</label>
              <select 
                id="nationality"
                v-model="form.nationality"
                class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
                required
              >
                <option value="" disabled>Select a country</option>
                <option v-for="country in countries" :key="country.country_id" :value="country.country_id">
                  {{ country.name }}
                </option>
              </select>
              <div v-if="form.errors.nationality" class="text-red-500 text-sm mt-1">{{ form.errors.nationality }}</div>
            </div>
            
            <!-- Base -->
            <div>
              <label for="base" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Base Location</label>
              <input 
                type="text" 
                id="base"
                v-model="form.base"
                class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
              >
              <div v-if="form.errors.base" class="text-red-500 text-sm mt-1">{{ form.errors.base }}</div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Team Chief -->
              <div>
                <label for="team_chief" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Team Principal</label>
                <input 
                  type="text" 
                  id="team_chief"
                  v-model="form.team_chief"
                  class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
                >
              </div>
              
              <!-- Technical Chief -->
              <div>
                <label for="technical_chief" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Technical Director</label>
                <input 
                  type="text" 
                  id="technical_chief"
                  v-model="form.technical_chief"
                  class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
                >
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <!-- First Entry -->
              <div>
                <label for="first_team_entry" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Entry Year</label>
                <input 
                  type="number" 
                  id="first_team_entry"
                  v-model="form.first_team_entry"
                  min="1950"
                  :max="new Date().getFullYear()"
                  class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
                >
              </div>
              
              <!-- Chassis -->
              <div>
                <label for="chassis" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Chassis</label>
                <input 
                  type="text" 
                  id="chassis"
                  v-model="form.chassis"
                  class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
                >
              </div>
              
              <!-- Power Unit -->
              <div>
                <label for="power_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Power Unit</label>
                <input 
                  type="text" 
                  id="power_unit"
                  v-model="form.power_unit"
                  class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
                >
              </div>
            </div>
            
            <!-- Official Website -->
            <div>
              <label for="official_website" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Official Website</label>
              <input 
                type="url" 
                id="official_website"
                v-model="form.official_website"
                class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
                placeholder="https://"
              >
              <div v-if="form.errors.official_website" class="text-red-500 text-sm mt-1">{{ form.errors.official_website }}</div>
            </div>

            <!-- Team Logo -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Team Logo</label>
              
              <div v-if="form.logo || logoPreview" class="mb-3">
                <div class="relative w-40 h-40 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                  <img :src="logoPreview || `/storage/${form.logo}`" alt="Team Logo" class="w-full h-full object-contain">
                  <button 
                    type="button"
                    @click="removeImage"
                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
              
              <div v-if="!form.logo && !logoPreview">
                <label for="logo" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600">
                  <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">Click to upload team logo</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">SVG, PNG, JPG (MAX. 2MB)</p>
                  </div>
                  <input id="logo" type="file" class="hidden" @change="handleFileUpload" accept="image/*" />
                </label>
              </div>
            </div>
            
            <!-- Submit and Cancel Buttons -->
            <div class="flex justify-end space-x-4 pt-4">
              <Link 
                href="/constructors" 
                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white rounded-md hover:bg-gray-300 dark:hover:bg-gray-600"
              >
                Cancel
              </Link>
              
              <button 
                type="button" 
                @click="confirmDelete"
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
              >
                Delete
              </button>
              
              <button 
                type="submit" 
                class="px-4 py-2 bg-f1-red text-white rounded-md hover:bg-red-700"
                :disabled="processing"
              >
                <span v-if="processing" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Saving...
                </span>
                <span v-else>Update Team</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Delete confirmation modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDeleteModal = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-red-600 dark:text-red-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Delete Team</h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500 dark:text-gray-400">Are you sure you want to delete this team? This action cannot be undone.</p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              type="button" 
              @click="deleteTeam"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Delete
            </button>
            <button 
              type="button" 
              @click="showDeleteModal = false"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';

// Props
const props = defineProps({
  constructor_id: {
    type: [Number, String],
    required: true
  },
  countries: {
    type: Array,
    required: true
  },
});

// UI state
const loading = ref(true);
const logoPreview = ref(null);
const processing = ref(false);
const showDeleteModal = ref(false);
const notification = ref({
  show: false,
  message: '',
  type: 'success'
});

// Función para mostrar notificación
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

// Form setup
const form = useForm({
  name: '',
  nationality: '',
  base: '',
  first_team_entry: '',
  team_chief: '',
  technical_chief: '',
  chassis: '',
  power_unit: '',
  official_website: '',
  logo: null,
  _method: 'put'
});

// Load constructor data
onMounted(async () => {
  try {
    const response = await axios.get(`/api/constructors/${props.constructor_id}`);
    const team = response.data.data;
    
    console.log('Team data:', team);
    console.log('Team nationality:', team.nationality);
    console.log('props:', props);
    
    // Fill form with existing data
    form.name = team.name || '';
    form.base = team.base || '';
    form.team_chief = team.team_chief || '';
    form.technical_chief = team.technical_chief || '';
    form.first_team_entry = team.first_team_entry || '';
    
    // Asignación corregida - el problema probablemente está aquí
    // Asegúrate de que el ID sea del mismo tipo (string o número)
    if (team.nationality && team.nationality.id) {
      console.log('Setting nationality to:', team.nationality.id);
      form.nationality = String(team.nationality.id);
    }
    
    form.chassis = team.chassis || '';
    form.power_unit = team.power_unit || '';
    form.official_website = team.official_website || '';
    form.logo = team.logo || null;
  } catch (error) {
    console.error('Error loading team:', error);
  } finally {
    loading.value = false;
  }
});

// Handle file upload
const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  form.logo = file;
  
  // Create preview
  const reader = new FileReader();
  reader.onload = (e) => {
    logoPreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

// Remove image
const removeImage = () => {
  form.logo = null;
  logoPreview.value = null;
};

// Form submission
const submit = () => {
  try {
  processing.value = true;
  
  // Usar axios en su lugar, ya que estás trabajando con una API JSON
  const formData = new FormData();
  
  // Añadir todos los campos del formulario al FormData
  Object.keys(form).forEach(key => {
    // Omitir _method y campos vacíos
    if (key !== '_method' && form[key] !== null && form[key] !== undefined) {
      formData.append(key, form[key]);
    }
  });
  
  // Añadir el método PUT para actualización
  formData.append('_method', 'PUT');
  
  // Enviar con axios
  axios.post(`/api/constructors/${props.constructor_id}`, formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  });

  showNotification('Team updated successfully!', 'success');

  setTimeout(() => {
    router.visit('/constructors');
  }, 2000);
  } catch (error) {
    console.error('Error updating team:', error);
    
    if (error.response?.data?.errors) {
      // Actualizar errores del formulario
      Object.keys(error.response.data.errors).forEach(key => {
        form.errors[key] = error.response.data.errors[key][0];
      });
    }
    
    // Mostrar notificación de error
    showNotification('Failed to update team. Please check the form for errors.', 'error');
    processing.value = false;
  }
};

// Delete confirmation
const confirmDelete = () => {
  showDeleteModal.value = true;
};

// Delete the team
const deleteTeam = async () => {
  try {
    processing.value = true;
    showDeleteModal.value = false;
    
    await axios.delete(`/api/constructors/${props.constructor_id}`);
    
    // Mostrar notificación de éxito
    showNotification('Team deleted successfully!', 'success');
    
    // Redireccionar después de un breve retraso
    setTimeout(() => {
      router.visit('/constructors');
    }, 1000);
  } catch (error) {
    console.error('Error deleting team:', error);
    showNotification('Failed to delete team.', 'error');
    processing.value = false;
  }
};
</script>