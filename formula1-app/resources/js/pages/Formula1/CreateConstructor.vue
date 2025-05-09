<template>
  <AppLayout>
    <!-- Notificación -->
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
          <h1 class="text-2xl font-bold mb-6">Create Team</h1>
          
          <form @submit.prevent="submit" class="space-y-6">
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
              <div v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</div>
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
                <option value="" disabled selected>Select a country</option>
                <option v-for="country in countries" :key="country.country_id" :value="country.country_id">
                  {{ country.name }}
                </option>
              </select>
              <div v-if="errors.nationality" class="text-red-500 text-sm mt-1">{{ errors.nationality[0] }}</div>
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
              <div v-if="errors.base" class="text-red-500 text-sm mt-1">{{ errors.base[0] }}</div>
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
                <div v-if="errors.team_chief" class="text-red-500 text-sm mt-1">{{ errors.team_chief[0] }}</div>
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
                <div v-if="errors.technical_chief" class="text-red-500 text-sm mt-1">{{ errors.technical_chief[0] }}</div>
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
                <div v-if="errors.first_team_entry" class="text-red-500 text-sm mt-1">{{ errors.first_team_entry[0] }}</div>
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
                <div v-if="errors.chassis" class="text-red-500 text-sm mt-1">{{ errors.chassis[0] }}</div>
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
                <div v-if="errors.power_unit" class="text-red-500 text-sm mt-1">{{ errors.power_unit[0] }}</div>
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
              <div v-if="errors.official_website" class="text-red-500 text-sm mt-1">{{ errors.official_website[0] }}</div>
            </div>

            <!-- Team Logo - Modificado para usar el enfoque de pilotos -->
            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Team Logo</h2>
              
              <!-- Área de carga de imagen -->
              <div class="flex flex-col space-y-4">
                <!-- Vista previa de imagen si existe -->
                <div v-if="logoPreview" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                  <div class="relative mx-auto h-48 w-48">
                    <img 
                      :src="logoPreview" 
                      class="object-contain h-full w-full rounded-md" 
                      alt="Team logo preview"
                      @error="(e) => {
                        e.target.style.display = 'none';
                        const fallback = e.target.nextElementSibling;
                        if (fallback) fallback.style.display = 'flex';
                      }"
                    >
                    <!-- Fallback para cuando la imagen falla -->
                    <div 
                      class="hidden absolute inset-0 items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-md"
                      style="height: 100%;"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
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
                
                <!-- Zona para arrastrar/cargar archivos - mostrar cuando no hay preview -->
                <div 
                  v-if="!logoPreview"
                  class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md bg-gray-50 dark:bg-gray-800"
                >
                  <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                      <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                      <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-f1-red hover:text-red-700 focus-within:outline-none">
                        <span>Upload a file</span>
                        <input 
                          id="file-upload" 
                          name="file-upload" 
                          type="file" 
                          accept="image/*"
                          @change="handleFileUpload" 
                          class="sr-only" 
                        />
                      </label>
                      <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      SVG, PNG, JPG up to 2MB
                    </p>
                  </div>
                </div>
              </div>
              <div v-if="errors.logo" class="text-red-500 text-sm mt-1">{{ errors.logo[0] }}</div>
              <div v-if="errors.image_file" class="text-red-500 text-sm mt-1">{{ errors.image_file[0] }}</div>
            </div>
            
            <!-- Submit and Cancel Buttons -->
            <div class="flex justify-end space-x-4 pt-4">
              <button
                type="button" 
                @click="router.visit('/constructors')"
                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white rounded-md hover:bg-gray-300 dark:hover:bg-gray-600"
              >
                Cancel
              </button>
              
              <button 
                type="submit" 
                class="px-4 py-2 bg-f1-red text-white rounded-md hover:bg-red-700 flex items-center justify-center min-w-[120px]"
                :disabled="loading"
              >
                <span v-if="loading" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Saving...
                </span>
                <span v-else>Create Team</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';

// Props
const props = defineProps({
  countries: Array,
});

// Estados - usando ref como en CreateDriver
const form = ref({
  name: '',
  nationality: '',
  base: '',
  first_team_entry: '',
  team_chief: '',
  technical_chief: '',
  chassis: '',
  power_unit: '',
  official_website: '',
  logo: '' // Cambiado a string
});

// UI state
const logoPreview = ref(null);
const loading = ref(false);
const errors = ref({});
const logoFile = ref(null); // Nuevo ref para manejar el archivo

// Notificaciones
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

// Handle file upload - modificado para seguir el patrón de drivers
const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  // Guardar el archivo para subir después
  logoFile.value = file;
  
  // Generar nombre temporal para la previsualización
  const extensión = file.name.split('.').pop().toLowerCase();
  form.value.logo = `constructors/${Date.now()}.${extensión}`;
  
  // Crear preview
  const reader = new FileReader();
  reader.onload = (e) => {
    logoPreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

// Eliminar imagen
const removeImage = () => {
  logoFile.value = null;
  form.value.logo = '';
  logoPreview.value = null;
};

// Form submission - rehecho siguiendo el patrón de CreateDriver
const submit = async () => {
  try {
    loading.value = true;
    errors.value = {};
    
    // Crear FormData para envío multipart
    const formData = new FormData();
    
    // Añadir todos los campos básicos del constructor
    Object.keys(form.value).forEach(key => {
      // No incluir 'logo' ya que lo manejaremos con logoFile
      if (key !== 'logo' && form.value[key] !== null && form.value[key] !== undefined) {
        formData.append(key, form.value[key]);
      }
    });
    
    // Agregar el logo si existe
    if (logoFile.value) {
      formData.append('image_file', logoFile.value);
    }
    
    console.log('Enviando datos:', Object.fromEntries(formData));
    
    // Realizar la petición POST
    const response = await axios.post('/api/constructors', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    console.log('Respuesta del servidor:', response.data);
    
    // Mostrar notificación de éxito
    showNotification('Team created successfully!', 'success');
    
    // Redirigir después de un breve retraso
    setTimeout(() => {
      router.visit('/constructors');
    }, 1500);
  } catch (error) {
    console.error('Error creating team:', error);
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
      console.error('Validation errors:', errors.value);
    }
    
    // Mostrar notificación de error
    showNotification('Failed to create team. Please check the form.', 'error');
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Estilos para mejorar la apariencia del componente */
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
</style>