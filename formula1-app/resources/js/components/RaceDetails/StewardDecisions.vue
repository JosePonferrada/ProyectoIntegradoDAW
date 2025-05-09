<template>
  <div class="space-y-6">
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

    <!-- Cabecera -->
    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-6">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
        <i class="fas fa-gavel mr-3 text-f1-red"></i>
        Stewards Decisions
      </h2>
      <p class="text-gray-600 dark:text-gray-300">
        Official documents and decisions published by the FIA stewards for this race.
      </p>
    </div>
    
    <!-- Listado de documentos -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
      <div v-if="loading" class="py-12 flex flex-col items-center justify-center">
        <div class="w-12 h-12 border-4 border-gray-200 dark:border-gray-700 border-t-f1-red rounded-full animate-spin"></div>
        <p class="mt-4 text-gray-600 dark:text-gray-400">Loading documents...</p>
      </div>
      
      <div v-else-if="decisions.length === 0" class="flex flex-col items-center justify-center py-12">
        <div class="flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-gray-100 dark:bg-gray-700">
          <i class="fas fa-file-alt text-xl text-gray-400 dark:text-gray-500"></i>
        </div>
        <h3 class="mb-1 text-lg font-medium text-gray-900 dark:text-white">No documents yet</h3>
        <p class="text-center text-gray-500 dark:text-gray-400 max-w-sm">
          No official documents have been published for this race yet.
          {{ isAdmin ? 'Use the forms below to add documents.' : '' }}
        </p>
      </div>
      
      <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
        <li v-for="decision in decisions" :key="decision.id" class="group hover:bg-gray-50 dark:hover:bg-gray-800/80 transition duration-150">
          <div class="flex items-center p-4">
            <div class="flex-shrink-0 mr-4">
              <div class="w-10 h-10 flex items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                <i class="fas fa-file-pdf text-red-600 dark:text-red-400"></i>
              </div>
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-900 dark:text-white truncate flex items-center">
                <span v-if="decision.document_number" class="font-mono text-xs px-1.5 py-0.5 mr-2 rounded bg-gray-100 dark:bg-gray-700">
                  #{{ decision.document_number }}
                </span>
                {{ decision.fact }}
              </p>
              <p class="mt-1 flex items-center text-xs text-gray-500 dark:text-gray-400 space-x-2">
                <span>{{ formatDate(decision.upload_date) }}</span>
                
                <span v-if="decision.session_type" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-100">
                  {{ decision.session_type }}
                </span>
                
                <span v-if="decision.decision_type" 
                  :class="[
                    'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                    decision.decision_type === 'penalty' ? 'bg-red-100 dark:bg-red-800 text-red-800 dark:text-red-100' : 
                    decision.decision_type === 'reprimand' ? 'bg-yellow-100 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-100' :
                    decision.decision_type === 'warning' ? 'bg-orange-100 dark:bg-orange-800 text-orange-800 dark:text-orange-100' :
                    decision.decision_type === 'fine' ? 'bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-100' :
                    decision.decision_type === 'disqualification' ? 'bg-purple-100 dark:bg-purple-800 text-purple-800 dark:text-purple-100' :
                    'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100'
                  ]">
                  {{ {'penalty': 'Time Penalty', 'reprimand': 'Reprimand', 'fine': 'Fine', 'disqualification': 'Disqualification', 'investigation': 'Investigation', 'warning': 'Warning'}[decision.decision_type] || decision.decision_type }}
                </span>
              </p>
            </div>
            
            <div class="ml-4 flex-shrink-0">
              <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <a :href="`/storage/${decision.document_path}`" target="_blank" 
                   class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                  <i class="fas fa-eye mr-1.5"></i>
                  View
                </a>
                <a :href="`/api/steward-decisions/${decision.id}/download`" 
                   class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                  <i class="fas fa-download mr-1.5"></i>
                  Download
                </a>
                <button v-if="isAdmin"
                  @click.prevent="confirmDeleteDecision(decision)"
                  class="inline-flex items-center px-3 py-1.5 border border-red-300 dark:border-red-800 text-xs font-medium rounded-md text-red-700 dark:text-red-300 bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-900/30">
                  <i class="fas fa-trash-alt mr-1.5"></i>
                  Delete
                </button>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>
    
    <!-- Panel de administrador para subir documentos -->
    <div v-if="isAdmin" class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-6">
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
        <i class="fas fa-upload mr-2 text-f1-red"></i>
        Upload New Decision
      </h3>
      
      <form @submit.prevent="uploadDecision" class="space-y-4">
        <div>
          <label for="fact" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Description
          </label>
          <input 
            id="fact"
            v-model="form.fact"
            type="text"
            required
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
          />
        </div>
        
        <div>
          <label for="document" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Document (PDF)
          </label>
          <div class="mt-1 flex items-center">
            <label for="document" class="cursor-pointer bg-white dark:bg-gray-800 py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
              <span>Select file</span>
              <input 
                id="document"
                type="file"
                @change="handleFileChange"
                accept=".pdf"
                required
                class="sr-only"
              />
            </label>
            <span class="ml-3 text-sm text-gray-500 dark:text-gray-400">
              {{ form.document ? form.document.name : 'No file selected' }}
            </span>
          </div>
        </div>
        
        <div class="flex justify-end">
          <button
            type="submit"
            :disabled="uploading"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-f1-red hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-f1-red disabled:opacity-50"
          >
            <i v-if="uploading" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-upload mr-2"></i>
            {{ uploading ? 'Uploading...' : 'Upload Document' }}
          </button>
        </div>
      </form>
    </div>
    
    <!-- Opción para crear documento desde plantilla (solo admin) -->
    <div v-if="isAdmin" class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-6">
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
        <i class="fas fa-file-alt mr-2 text-f1-red"></i>
        Create Decision from Template
      </h3>
      
      <form @submit.prevent="createFromTemplate" class="space-y-4">
        <div>
          <label for="template-type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Template Type
          </label>
          <select 
            id="template-type"
            v-model="template.type"
            required
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
          >
            <option value="penalty">Time Penalty</option>
            <option value="reprimand">Reprimand</option>
            <option value="fine">Fine</option>
            <option value="disqualification">Disqualification</option>
            <option value="investigation">Investigation</option>
            <option value="warning">Warning</option>
          </select>
        </div>

        <div v-if="template.type === 'fine'" class="mt-4">
          <label for="fine-amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Fine Amount
          </label>
          <div class="mt-1 relative rounded-md shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <span class="text-gray-500 dark:text-gray-400 sm:text-sm">€</span>
            </div>
            <input 
              id="fine-amount"
              v-model="template.fineAmount"
              type="number"
              min="0"
              step="1000"
              required
              placeholder="20000"
              class="w-full pl-8 px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
            />
            <div class="absolute inset-y-0 right-10 flex items-center">
              <button type="button" @click="template.fineAmount = 5000" class="text-gray-500 dark:text-gray-400 hover:text-f1-red px-2">5K</button>
              <button type="button" @click="template.fineAmount = 10000" class="text-gray-500 dark:text-gray-400 hover:text-f1-red px-2">10K</button>
              <button type="button" @click="template.fineAmount = 20000" class="text-gray-500 dark:text-gray-400 hover:text-f1-red px-2">20K</button>
            </div>
          </div>
        </div>
        
        <div>
          <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Document Title
          </label>
          <input 
            id="title"
            v-model="template.title"
            type="text"
            required
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
          />
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="driver" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Driver
            </label>
            <select 
              id="driver"
              v-model="template.driverId"
              class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
            >
              <option value="">Select Driver</option>
              <option v-for="driver in drivers" :key="driver.id" :value="driver.id">
                {{ driver.full_name }}
              </option>
            </select>
          </div>
          
          <div>
            <label for="team" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Team
            </label>
            <select 
              id="team"
              v-model="template.teamId"
              class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
            >
              <option value="">Select Team</option>
              <option v-for="team in teams" :key="team.id" :value="team.id">
                {{ team.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="session-type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Session Type
            </label>
            <select 
              id="session-type"
              v-model="template.sessionType"
              class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
            >
              <option value="Race">Race</option>
              <option value="Qualifying">Qualifying</option>
              <option value="Practice 1">Practice 1</option>
              <option value="Practice 2">Practice 2</option>
              <option value="Practice 3">Practice 3</option>
              <option value="Sprint">Sprint</option>
              <option value="Sprint Qualifying">Sprint Qualifying</option>
            </select>
          </div>
          
          <div>
            <label for="incident-time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Incident Time
            </label>
            <input 
              id="incident-time"
              v-model="template.incidentTime"
              type="time"
              placeholder="e.g. 14:35"
              class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
            />
          </div>
        </div>

        <div>
          <label for="infringement" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Infringement Article
          </label>
          <input 
            id="infringement"
            v-model="template.infringementArticle"
            type="text"
            placeholder="e.g. Breach of Appendix L, Article 2.5.5 b)"
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
          />
        </div>

        <div>
          <label for="penalty-detail" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Penalty Detail
          </label>
          <input 
            id="penalty-detail"
            v-model="template.penaltyDetail"
            type="text"
            placeholder="e.g. 5 second time penalty"
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
          />
        </div>
        
        <div>
          <label for="decision-text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Decision Details
          </label>
          <textarea 
            id="decision-text"
            v-model="template.content"
            rows="4"
            required
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent"
          ></textarea>
        </div>
        
        <div class="flex justify-end">
          <button
            type="submit"
            :disabled="generating"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-f1-red hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-f1-red disabled:opacity-50"
          >
            <i v-if="generating" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-file-pdf mr-2"></i>
            {{ generating ? 'Generating...' : 'Generate PDF' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div v-if="showConfirmDialog" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg px-6 py-4 max-w-md w-full">
        <div class="sm:flex sm:items-start">
          <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
            <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
          </div>
          <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
              Delete Document
            </h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Are you sure you want to delete this document? This action cannot be undone.
                <br><br>
                <strong>{{ decisionToDelete?.fact }}</strong>
              </p>
            </div>
          </div>
        </div>
        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
          <button
            type="button"
            @click="deleteDecision"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
          >
            Delete
          </button>
          <button
            type="button"
            @click="cancelDelete"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-f1-red sm:mt-0 sm:w-auto sm:text-sm"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { format } from 'date-fns';

const props = defineProps({
  race: {
    type: Object,
    required: true
  },
  isAdmin: {
    type: Boolean,
    default: false
  }
});

// Referencias reactivas
const decisions = ref([]);
const loading = ref(true);
const uploading = ref(false);
const generating = ref(false);
const drivers = ref([]);
const teams = ref([]);
const showConfirmDialog = ref(false);
const decisionToDelete = ref(null);

// Añadir sistema de notificaciones
const notification = ref({
  show: false,
  message: '',
  type: 'success' // 'success' o 'error'
});

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

// Formulario para subir documentos
const form = ref({
  fact: '',
  document: null
});

// Plantilla para crear documentos
const template = ref({
  type: 'penalty',
  title: '',
  driverId: '',
  teamId: '',
  sessionType: 'Race',
  incidentTime: '',
  infringementArticle: '',
  penaltyDetail: '',
  fineAmount: 0, // Nuevo campo para la cantidad de multa
  content: ''
});

// Cargar decisiones al montar el componente
onMounted(async () => {
  console.log('Race object:', props.race);
  await loadDecisions();
  if (props.isAdmin) {
    await Promise.all([
      loadDrivers(),
      loadTeams()
    ]);
  }
});

// Métodos
const loadDecisions = async () => {
  loading.value = true;
  try {
    // Asegúrate de estar usando el ID correcto, sea id o race_id
    const raceId = props.race.race_id || props.race.id;
    
    console.log('Cargando decisiones para carrera con ID:', raceId);
    
    // Usa la ruta correcta
    const response = await axios.get(`/api/races/${raceId}/steward-decisions`);
    console.log('Decisiones cargadas:', response.data);
    
    decisions.value = response.data.data || [];
  } catch (error) {
    console.error('Error cargando decisiones:', error);
  } finally {
    loading.value = false;
  }
};

const loadDrivers = async () => {
  try {
    const response = await axios.get('/api/drivers');
    drivers.value = response.data.data;
  } catch (error) {
    console.error('Error loading drivers:', error);
  }
};

const loadTeams = async () => {
  try {
    const response = await axios.get('/api/constructors');
    teams.value = response.data.data;
  } catch (error) {
    console.error('Error loading teams:', error);
  }
};

const handleFileChange = (event) => {
  if (event.target.files && event.target.files.length > 0) {
    form.value.document = event.target.files[0];
    console.log('Archivo seleccionado:', form.value.document.name);
  } else {
    form.value.document = null;
  }
};

const uploadDecision = async () => {
  if (!form.value.document) return;
  
  uploading.value = true;
  
  const formData = new FormData();
  formData.append('document', form.value.document);
  formData.append('fact', form.value.fact);
  formData.append('race_id', props.race.id);
  
  try {
    await axios.post('/api/steward-decisions', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    // Limpiar formulario y recargar decisiones
    form.value.fact = '';
    form.value.document = null;
    document.getElementById('document').value = '';
    
    await loadDecisions();
    showNotification('Document uploaded successfully', 'success');
  } catch (error) {
    console.error('Error uploading document:', error);
    showNotification('Failed to upload document. Please try again.', 'error');
  } finally {
    uploading.value = false;
  }
};

const createFromTemplate = async () => {
  generating.value = true;
  
  try {
    const raceId = props.race?.race_id || props.race?.id;
    
    if (!raceId) {
      throw new Error('Race ID not found. Cannot generate document.');
    }
    
    // Crear el objeto de datos
    const requestData = {
      race_id: raceId,
      template_type: template.value.type,
      title: template.value.title,
      driver_id: template.value.driverId || null,
      team_id: template.value.teamId || null,
      session_type: template.value.sessionType,
      incident_time: template.value.incidentTime,
      infringement_article: template.value.infringementArticle,
      penalty_detail: template.value.penaltyDetail,
      content: template.value.content
    };
    
    // Añadir fine_amount solo si el tipo es 'fine'
    if (template.value.type === 'fine') {
      requestData.fine_amount = template.value.fineAmount;
    }
    
    console.log('Enviando datos:', requestData);
    
    const response = await axios.post('/api/steward-decisions/generate', requestData);
    
    // Limpiar formulario y recargar decisiones
    template.value.title = '';
    template.value.driverId = '';
    template.value.teamId = '';
    template.value.sessionType = 'Race';
    template.value.incidentTime = '';
    template.value.infringementArticle = '';
    template.value.penaltyDetail = '';
    template.value.fineAmount = 0;
    template.value.content = '';
    
    await loadDecisions();
    showNotification('Document generated successfully', 'success');
  } catch (error) {
    console.error('Error generating document:', error);
    // Código para manejar errores...
  } finally {
    generating.value = false;
  }
};

const formatDate = (dateString) => {
  try {
    return format(new Date(dateString), 'MMM d, yyyy HH:mm');
  } catch (error) {
    return dateString;
  }
};

const confirmDeleteDecision = (decision) => {
  decisionToDelete.value = decision;
  showConfirmDialog.value = true;
};

const deleteDecision = async () => {
  if (!decisionToDelete.value) return;
  
  try {
    await axios.delete(`/api/steward-decisions/${decisionToDelete.value.id}`);
    showConfirmDialog.value = false;
    decisionToDelete.value = null;
    
    // Recargar la lista de decisiones
    await loadDecisions();
    showNotification('Document deleted successfully', 'success');
  } catch (error) {
    console.error('Error deleting document:', error);
    showNotification('Failed to delete document. Please try again.', 'error');
  }
};

const cancelDelete = () => {
  showConfirmDialog.value = false;
  decisionToDelete.value = null;
};
</script>