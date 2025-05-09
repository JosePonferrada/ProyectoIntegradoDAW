<template>
  <AppLayout>
    <!-- Añade esto justo después de la etiqueta AppLayout de apertura -->
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

    <Head title="Edit Driver" />
    
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
          <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Driver: {{ driver.first_name }} {{ driver.last_name }}</h1>
            <Link :href="route('drivers')" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
              &larr; Back to Drivers
            </Link>
          </div>
          
          <!-- Formulario con estructura similar a CreateDriver -->
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
                    Driver Code *
                  </label>
                  <input 
                    id="code" 
                    v-model="form.code" 
                    type="text" 
                    required
                    maxlength="3"
                    class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm uppercase"
                  />
                  <p v-if="errors.code" class="mt-1 text-sm text-red-600">{{ errors.code[0] }}</p>
                </div>
                
                <!-- Número -->
                <div>
                  <label for="number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Driver Number *
                  </label>
                  <input 
                    id="number" 
                    v-model="form.number" 
                    type="number" 
                    required
                    min="0" 
                    max="99"
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
                
                <!-- Nacionalidad -->
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
                    <option value="" disabled>Select a country</option>
                    <option v-for="country in countries" :key="country.country_id" :value="country.country_id">
                      {{ country.name }}
                    </option>
                  </select>
                  <p v-if="errors.nationality" class="mt-1 text-sm text-red-600">{{ errors.nationality[0] }}</p>
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
                    required
                    min="1950"
                    :max="new Date().getFullYear()"
                    class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                  />
                  <p v-if="errors.debut_year" class="mt-1 text-sm text-red-600">{{ errors.debut_year[0] }}</p>
                </div>
                
                <!-- Estado (activo/inactivo) -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                  <div class="mt-2 flex items-center space-x-6">
                    <label class="inline-flex items-center">
                      <input 
                        type="radio" 
                        v-model="form.active" 
                        :value="true"
                        name="active"
                        class="h-4 w-4 text-f1-red focus:ring-f1-red border-gray-300 dark:border-gray-600"
                      />
                      <span class="ml-2 text-gray-700 dark:text-gray-300">Active</span>
                    </label>
                    <label class="inline-flex items-center">
                      <input 
                        type="radio" 
                        v-model="form.active" 
                        :value="false"
                        name="active"
                        class="h-4 w-4 text-f1-red focus:ring-f1-red border-gray-300 dark:border-gray-600"
                      />
                      <span class="ml-2 text-gray-700 dark:text-gray-300">Inactive</span>
                    </label>
                  </div>
                  <p v-if="errors.active" class="mt-1 text-sm text-red-600">{{ errors.active[0] }}</p>
                </div>
              </div>
            </div>
            
            <!-- Sección de estadísticas -->
            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600 mt-6">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Driver Statistics</h2>
              
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-x-6 gap-y-4">
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
                
                <!-- Vueltas rápidas -->
                <div>
                  <label for="fastest_laps" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fastest Laps
                  </label>
                  <input 
                    id="fastest_laps" 
                    v-model="form.fastest_laps" 
                    type="number" 
                    min="0"
                    class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                  />
                  <p v-if="errors.fastest_laps" class="mt-1 text-sm text-red-600">{{ errors.fastest_laps[0] }}</p>
                </div>
              </div>
            </div>

            <!-- Sección de información del equipo -->
            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600 mt-6">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Team Information</h2>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <div>
                  <label for="constructor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Current Team
                  </label>
                  <select 
                    id="constructor_id" 
                    v-model="form.constructor_id"
                    class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                  >
                    <option value="">No team</option>
                    <option v-for="constructor in availableConstructors" :key="constructor.constructor_id || constructor.id" :value="constructor.constructor_id || constructor.id">
                      {{ constructor.name }}
                    </option>
                  </select>
                </div>
                
                <div>
                  <label for="position_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Driver Position in Team
                  </label>
                  <select 
                    id="position_number" 
                    v-model="form.position_number"
                    :disabled="!form.constructor_id"
                    class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                  >
                    <option value="">Select position</option>
                    <option value="1">1st Driver</option>
                    <option value="2">2nd Driver</option>
                    <option value="3">Reserve Driver</option>
                    <option value="4">Test Driver</option>
                  </select>
                </div>
              </div>
            </div>
            
            <!-- Biografía -->
            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600 mt-6">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Biography</h2>
              
              <div>
                <textarea 
                  id="biography" 
                  v-model="form.biography" 
                  rows="5"
                  class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                  placeholder="Enter driver biography..."
                ></textarea>
                <p v-if="errors.biography" class="mt-1 text-sm text-red-600">{{ errors.biography[0] }}</p>
              </div>
            </div>
            
            <!-- Sección para imagen -->
            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600">
              <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Driver Image</h2>
              
              <!-- Mostrar imagen actual SOLO si existe y NO se ha marcado para eliminar y NO hay vista previa nueva -->
              <div v-if="driver.image && !removeCurrentImage && !imagePreview" class="mb-4">
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">Current Image</h3>
                <div class="relative w-40 h-40 border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                  <img 
                    :src="getImageUrl(driver)"
                    :alt="driver.first_name + ' ' + driver.last_name"
                    class="w-full h-full object-contain"
                    @error="handleImageError"
                  />
                  <div 
                    v-if="imageError" 
                    class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-700"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                  <button 
                    type="button"
                    @click="removeImage"
                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
              
              <!-- Vista previa de imagen nueva -->
              <div v-if="imagePreview" class="mb-4">
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">New Image Preview</h3>
                <div class="relative w-40 h-40 border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                  <img 
                    :src="imagePreview" 
                    class="h-full w-full object-contain" 
                    alt="Preview"
                  />
                  <button 
                    type="button" 
                    @click="cancelNewImage" 
                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
              
              <!-- Área de carga de imagen nueva - mostrar solo si no hay imagen nueva seleccionada -->
              <div 
                v-if="!imagePreview" 
                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md bg-gray-50 dark:bg-gray-800"
              >
                <div class="space-y-1 text-center">
                  <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  
                  <!-- Botones de carga -->
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
                  
                  <!-- Mostrar mensaje si la imagen fue eliminada -->
                  <p v-if="removeCurrentImage && !driver.image" class="mt-2 text-sm text-yellow-600">
                    The current image will be removed. Upload a new one or leave empty.
                  </p>
                </div>
              </div>
            </div>

            <!-- Añadir una nueva sección para gestionar temporadas -->
            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600 mt-6">
              <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Season History</h2>
                <button 
                  type="button" 
                  @click="addSeasonEntry" 
                  class="px-3 py-1 bg-f1-red text-white rounded-md hover:bg-red-700"
                >
                  Add Season
                </button>
              </div>
              
              <div v-if="seasonEntries.length === 0" class="text-gray-500 italic">
                No season history available
              </div>
              
              <!-- Update the season entry section -->
              <div v-for="(entry, index) in seasonEntries" :key="index" class="mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Season</label>
                    <select 
                      v-model="entry.season_id" 
                      class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                    >
                      <option value="" disabled>Select season</option>
                      <option v-for="season in availableSeasons" :key="season.id" :value="season.id">
                        {{ season.year }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Team</label>
                    <select 
                      v-model="entry.constructor_id" 
                      class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
                    >
                      <option value="">No team</option>
                      <option v-for="constructor in availableConstructors" :key="constructor.constructor_id || constructor.id" :value="constructor.constructor_id || constructor.id">
                        {{ constructor.name }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Driver Position</label>
                    <select
                      v-model="entry.position_number" 
                      class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm" 
                    >
                      <option value="">Select position</option>
                      <option value="1">1st Driver</option>
                      <option value="2">2nd Driver</option>
                      <option value="3">Reserve Driver</option>
                      <option value="4">Test Driver</option>
                    </select>
                  </div>
                </div>
                <button 
                  @click="removeSeasonEntry(index)" 
                  class="mt-2 px-2 py-1 text-red-600 hover:text-red-800"
                >
                  Remove
                </button>
              </div>
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
                <span v-else>Save Changes</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Overlay de carga durante operaciones largas -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg">
        <div class="flex items-center">
          <svg class="animate-spin h-8 w-8 text-f1-red mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span class="text-gray-800 dark:text-white font-medium">Saving driver information...</span>
        </div>
      </div>
    </div>

    <!-- Add the confirmation dialog -->
    <ConfirmationDialog
      v-model:show="confirmDialog.show"
      :title="confirmDialog.title"
      :message="confirmDialog.message"
      :type="confirmDialog.type"
      @confirm="confirmAction"
      @cancel="cancelConfirmation"
    />
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { router, Link, Head } from '@inertiajs/vue3';
import axios from 'axios';
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';

// Cargar las propiedades con los países
const props = defineProps({
  driver_id: Number,
  countries: Array,
  constructors: Array,
  seasons: Array
});

// Estados
const driver = ref({});
const form = ref({});
const errors = ref({});
const loading = ref(false);
const imageError = ref(false);
const imageFile = ref(null);
const removeCurrentImage = ref(false);
const imagePreview = ref(null);
const seasonEntries = ref([]);
const notification = ref({
  show: false,
  message: '',
  type: 'success'
});

// Add states for confirmation dialogs
const confirmDialog = ref({
  show: false,
  title: '',
  message: '',
  action: null,
  type: 'primary'
});

// Add these state variables at the beginning of your script
const availableSeasons = ref([]);
const availableConstructors = ref([]);

// Add these loading functions to fetch seasons and constructors if not available in props
const loadSeasons = async () => {
  try {
    console.log("Loading seasons from API...");
    const response = await axios.get('/api/seasons');
    if (response.data && response.data.data) {
      availableSeasons.value = response.data.data;
      console.log("Seasons loaded:", availableSeasons.value.length);
    }
  } catch (error) {
    console.error('Error loading seasons:', error);
    showNotification('Failed to load seasons', 'error');
  }
};

const loadConstructors = async () => {
  try {
    console.log("Loading constructors from API...");
    const response = await axios.get('/api/constructors');
    if (response.data && response.data.data) {
      availableConstructors.value = response.data.data;
      console.log("Constructors loaded:", availableConstructors.value.length);
    }
  } catch (error) {
    console.error('Error loading constructors:', error);
    showNotification('Failed to load constructors', 'error');
  }
};

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

// Replace the fetchDriver function with this improved version
const fetchDriver = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/drivers/${props.driver_id}`);
    driver.value = response.data.data;
    
    console.log("Driver data received:", driver.value);
    console.log("Available seasons:", props.seasons);
    console.log("Available constructors:", props.constructors);
    // Countries
    console.log("Countries:", props.countries);
    
    // Formatear fecha correctamente para el input type="date"
    let formattedDate = driver.value.date_of_birth;
    if (formattedDate && formattedDate.includes('T')) {
      formattedDate = formattedDate.split('T')[0];
    }
    
    // Inicializar el formulario con los datos del piloto
    form.value = {
      first_name: driver.value.first_name,
      last_name: driver.value.last_name,
      code: driver.value.code,
      number: driver.value.number,
      date_of_birth: formattedDate,
      nationality: driver.value.nationality?.id,
      active: driver.value.active,
      biography: driver.value.biography,
      debut_year: driver.value.debut_year,
      championships: driver.value.championships,
      wins: driver.value.wins,
      podiums: driver.value.podiums,
      poles: driver.value.poles,
      fastest_laps: driver.value.fastest_laps,
      constructor_id: driver.value.constructor ? driver.value.constructor.constructor_id : '',
      position_number: driver.value.position_number || ''
    };

    // Process season entries from driver.seasons data
    if (driver.value.seasons && Array.isArray(driver.value.seasons)) {
      console.log("Driver seasons data:", driver.value.seasons);
      
      // Force numeric type for the constructor_id to ensure proper selection in the dropdown
      seasonEntries.value = driver.value.seasons.map(season => {
        // Check if season has pivot data
        if (!season.pivot) {
          console.warn(`Season ${season.id} has no pivot data!`);
          return {
            season_id: '',
            constructor_id: '',
            position_number: ''
          };
        }
        
        // Convert constructor_id to number to match dropdown values
        const constructorId = season.pivot.constructor_id ? Number(season.pivot.constructor_id) : '';
        
        // Since season.id is null, find the season by year in availableSeasons
        let seasonId = season.id;
        if (seasonId === null && season.year) {
          const matchingSeason = availableSeasons.value.find(s => s.year === season.year);
          if (matchingSeason) {
            seasonId = matchingSeason.id;
            console.log(`Found season ID ${seasonId} for year ${season.year}`);
          }
        }
        
        console.log(`Processing season ${season.year} (Year matched ID: ${seasonId}):`, {
          constructorId: constructorId,
          positionNumber: season.pivot.position_number
        });
        
        return {
          season_id: seasonId,
          constructor_id: constructorId,
          position_number: season.pivot.position_number || ''
        };
      });
      
      console.log("Processed season entries:", seasonEntries.value);
    } else {
      console.log("No seasons data available or invalid format");
      seasonEntries.value = [];
    }

    // Add these debug logs in your fetchDriver function after getting driver data
    if (driver.value.seasons && Array.isArray(driver.value.seasons)) {
      console.log("Driver seasons raw data:");
      driver.value.seasons.forEach((season, index) => {
        console.log(`Season ${index}:`, season);
        console.log(`Season ${index} pivot:`, season.pivot);
        if (season.pivot) {
          console.log(`Constructor ID: ${season.pivot.constructor_id}, Position: ${season.pivot.position_number}`);
        }
      });
    }
    
  } catch (error) {
    console.error('Error fetching driver:', error);
    showNotification('Failed to load driver data', 'error');
  } finally {
    loading.value = false;
  }
};

// Función para manejar la imagen
const getImageUrl = (driver) => {
  if (!driver || !driver.image) return null;
  
  const image = driver.image;
  
  if (image.startsWith('http')) {
    return image;
  }
  
  if (image.startsWith('drivers/')) {
    return `/storage/${image}`;
  }
  
  return `/storage/${image}`;
};

// Manejar error de imagen
const handleImageError = () => {
  imageError.value = true;
};

// Replace the removeImage function
const removeImage = () => {
  confirmDialog.value = {
    show: true,
    title: 'Remove Image',
    message: 'Are you sure you want to remove this image? This action cannot be undone.',
    type: 'danger',
    action: () => {
      console.log("Marking image for removal");
      removeCurrentImage.value = true;
      imagePreview.value = null;
      imageFile.value = null;
      
      showNotification('Image marked for removal');
    }
  };
};

// Manejar carga de nueva imagen
const handleImageUpload = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  
  // Guardar archivo
  imageFile.value = file;
  removeCurrentImage.value = false;
  
  // Crear preview
  const reader = new FileReader();
  reader.onload = (event) => {
    imagePreview.value = event.target.result;
  };
  reader.readAsDataURL(file);
};

// Cancelar nueva imagen
const cancelNewImage = () => {
  console.log("Cancelando nueva imagen");
  imagePreview.value = null;
  imageFile.value = null;
  
  // NO restablecer removeCurrentImage si la intención era eliminar la imagen original
};

// Añadir entrada de temporada
const addSeasonEntry = () => {
  seasonEntries.value.push({
    season_id: '',
    constructor_id: '',
    position_number: '' // Changed from final_position to position_number
  });
};

// Replace the removeSeasonEntry function
const removeSeasonEntry = (index) => {
  confirmDialog.value = {
    show: true,
    title: 'Remove Season Entry',
    message: 'Are you sure you want to remove this season entry? This action cannot be undone.',
    type: 'danger',
    action: () => {
      seasonEntries.value.splice(index, 1);
      showNotification('Season entry removed');
    }
  };
};

// Add a function to execute the confirmed action
const confirmAction = () => {
  if (confirmDialog.value.action) {
    confirmDialog.value.action();
  }
  confirmDialog.value.show = false;
};

// Add a function to cancel the confirmation
const cancelConfirmation = () => {
  confirmDialog.value.show = false;
};

// Enviar formulario
const submit = async () => {
  loading.value = true;
  errors.value = {};
  const startTime = Date.now();
  
  try {
    const formData = new FormData();

    // Agregar campos manualmente para asegurar formato correcto
    formData.append('first_name', form.value.first_name || '');
    formData.append('last_name', form.value.last_name || '');
    formData.append('code', form.value.code || '');
    formData.append('number', String(form.value.number || '0'));
    formData.append('date_of_birth', form.value.date_of_birth || '');
    formData.append('nationality', String(form.value.nationality || '0'));
    formData.append('active', form.value.active ? '1' : '0');
    formData.append('biography', form.value.biography || '');
    formData.append('debut_year', String(form.value.debut_year || '0'));
    formData.append('championships', String(form.value.championships || '0'));
    formData.append('wins', String(form.value.wins || '0'));
    formData.append('podiums', String(form.value.podiums || '0'));
    formData.append('poles', String(form.value.poles || '0'));
    formData.append('fastest_laps', String(form.value.fastest_laps || '0'));
    formData.append('constructor_id', String(form.value.constructor_id || ''));
    formData.append('position_number', String(form.value.position_number || ''));

    // Agregar entradas de temporada
    formData.append('season_entries', JSON.stringify(seasonEntries.value));

    // Agregar imagen nueva si existe
    if (imageFile.value) {
      formData.append('image_file', imageFile.value);
    }

    // Indicar si hay que eliminar imagen actual
    if (removeCurrentImage.value) {
      formData.append('remove_image', '1');
    }
    
    // Mostrar el contenido del FormData (para debug)
    console.log("FormData contenido:");
    for (let pair of formData.entries()) {
      console.log(pair[0] + ': ', 
                 pair[1] instanceof File ? `File(${pair[1].name}, ${pair[1].type})` : pair[1]);
    }
    
    // Realizar la petición POST con _method=PUT
    console.log(`Haciendo petición POST a /api/drivers/${props.driver_id}?_method=PUT`);
    
    const response = await axios.post(`/api/drivers/${props.driver_id}?_method=PUT`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });
    
    console.log('Respuesta del servidor:', response.data);
    
    // Mostrar notificación de éxito
    showNotification('Driver information updated successfully!', 'success');
    
    // Redireccionar con recarga completa
    console.log('Redirigiendo a /drivers...');
    const loadingTime = Date.now() - startTime;
    if (loadingTime < 1000) {
      await new Promise(resolve => setTimeout(resolve, 1000 - loadingTime));
      // Redirigir
      router.visit('/drivers', { method: 'get', preserveState: true });
    }
  } catch (error) {
    console.error('Error detallado:', error);
    
    if (error.response) {
      console.error('Error response data:', error.response.data);
      console.error('Error response status:', error.response.status);
      console.error('Error response headers:', error.response.headers);
    }
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
    
    // Mostrar notificación de error
    showNotification('Failed to update driver information.', 'error');
  } finally {
    loading.value = false;
  }
};

// Update the onMounted hook to load the required data
onMounted(async () => {
  console.log("Component mounted. Available data:");
  console.log("- Seasons:", props.seasons?.length || 0, "items");
  console.log("- Constructors:", props.constructors?.length || 0, "items");
  
  // Load data if not provided in props
  const loadPromises = [];
  
  if (!props.seasons || props.seasons.length === 0) {
    loadPromises.push(loadSeasons());
  } else {
    availableSeasons.value = props.seasons;
  }
  
  if (!props.constructors || props.constructors.length === 0) {
    loadPromises.push(loadConstructors());
  } else {
    availableConstructors.value = props.constructors;
  }
  
  // Wait for all data to be loaded before fetching driver
  await Promise.all(loadPromises);
  
  fetchDriver();
});
</script>