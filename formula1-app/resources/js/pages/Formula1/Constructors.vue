<template>
  <AppLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <!-- Header with title and actions -->
          <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Teams</h2>
            <div class="flex items-center space-x-4">
              <Link v-if="isAdmin" href="/constructors/create" class="inline-flex items-center px-4 py-2 bg-f1-red text-white font-medium rounded-md hover:bg-red-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Team
              </Link>
            </div>
          </div>
          
          <!-- Filters section -->
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Year filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter by year</label>
                <select v-model="yearFilter" class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                  <option value="">All years</option>
                  <option v-for="season in availableSeasons" :key="season.id" :value="season.year">{{ season.year }}</option>
                </select>
              </div>
              
              <!-- Search box -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                <div class="relative">
                  <input type="text" v-model="searchQuery" placeholder="Search teams..." class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-10">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Teams grid -->
          <div class="p-6">
            <div v-if="loading" class="flex justify-center items-center py-8">
              <svg class="animate-spin h-8 w-8 text-f1-red" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </div>
            
            <div v-else-if="!filteredTeams.length" class="text-center py-8">
              <p class="text-gray-500 dark:text-gray-400">No teams found matching your criteria.</p>
            </div>
            
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <!-- Modifica la estructura de la tarjeta del equipo para mejorar la distribución vertical -->
              <div v-for="team in filteredTeams" :key="team.id" class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-600 hover:shadow-lg transition-shadow duration-200 h-[465px] flex flex-col">
                <!-- Sección superior con logo y datos básicos -->
                <div class="p-4 flex flex-col items-center flex-1">
                  <img v-if="team.logo" :src="`/storage/${team.logo}`" :alt="team.name" class="h-24 object-contain mb-4">
                  
                  <!-- Team card header with name and nationality flag -->
                  <div class="flex flex-col items-center mb-3">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white text-center mb-1">{{ team.name }}</h3>
                    <div class="flex items-center gap-2">
                      <span class="text-sm text-gray-500 dark:text-gray-400">Nationality:</span>
                      <CountryFlag 
                        v-if="team.nationality?.code" 
                        :countryCode="team.nationality.code" 
                        size="w-5 h-4" 
                        :rounded="true"
                      />
                      <span class="text-sm">{{ team.nationality?.name }}</span>
                    </div>
                  </div>

                  <!-- Base location as a separate item -->
                  <div class="text-center mb-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Base</p>
                    <p class="text-gray-600 dark:text-gray-300">{{ team.base }}</p>
                  </div>
                  
                  <!-- Team stats -->
                  <div class="w-full grid grid-cols-3 gap-2 mb-4">
                    <div class="text-center">
                      <p class="text-xs text-gray-500 dark:text-gray-400">Entry</p>
                      <p class="font-bold">{{ team.first_team_entry }}</p>
                    </div>
                    <div class="text-center">
                      <p class="text-xs text-gray-500 dark:text-gray-400">Chassis</p>
                      <p class="font-bold">{{ team.chassis || 'N/A' }}</p>
                    </div>
                    <div class="text-center">
                      <p class="text-xs text-gray-500 dark:text-gray-400">Power Unit</p>
                      <p class="font-bold">{{ team.power_unit || 'N/A' }}</p>
                    </div>
                  </div>
                </div>
                
                <!-- Sección inferior siempre alineada abajo -->
                <div class="mt-auto p-4 pt-0">
                  <!-- Current Drivers -->
                  <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 text-center">
                      Drivers {{ team.driver_season_year ? `(${team.driver_season_year})` : '' }}
                    </p>
                    <div class="flex flex-wrap justify-center gap-2">
                      <div 
                        v-for="driver in team.current_drivers" 
                        :key="driver.driver_id"
                        @click.stop="viewDriverFromTeamCard(driver, $event)"
                        class="flex items-center bg-gray-50 dark:bg-gray-600 rounded-full px-2 py-1 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors"
                      >
                        <img 
                          v-if="driver.image" 
                          :src="`/storage/${driver.image}`" 
                          :alt="`${driver.first_name} ${driver.last_name}`" 
                          class="w-6 h-6 rounded-full mr-1 object-cover"
                        />
                        <span class="text-xs font-medium">{{ driver.code || (driver.first_name?.charAt(0) + driver.last_name?.charAt(0)) }}</span>
                      </div>
                      <div v-if="!team.current_drivers?.length" class="text-xs text-gray-500">No drivers for this season</div>
                    </div>
                  </div>
                  
                  <!-- Actions -->
                  <div class="flex justify-center space-x-2 w-full mt-3">
                    <button @click="viewTeam(team)" class="px-3 py-1 bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-100 rounded hover:bg-gray-200 dark:hover:bg-gray-500 text-sm font-medium">
                      View
                    </button>
                    <Link 
                      v-if="isAdmin" 
                      :href="`/constructors/${team.id}/edit`" 
                      class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100 rounded hover:bg-blue-200 dark:hover:bg-blue-800 text-sm font-medium">
                      Edit
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Team Details Modal -->
    <div v-if="showModal" class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div v-if="currentTeam" class="px-4 pt-5 pb-4 sm:p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ currentTeam.name }}</h3>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div class="mb-4 flex justify-center">
              <img v-if="currentTeam.logo" :src="`/storage/${currentTeam.logo}`" :alt="currentTeam.name" class="h-32 object-contain">
            </div>
            <div class="space-y-4">
              <div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Base</h4>
                <p class="text-base text-gray-900 dark:text-white flex items-center">
                  <CountryFlag 
                    v-if="currentTeam.nationality?.code"
                    :countryCode="currentTeam.nationality.code"
                    size="w-5 h-4"
                    :rounded="true"
                    class="mr-2"
                  />
                  {{ currentTeam.base }}
                </p>
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Team Chief</h4>
                <p class="text-base text-gray-900 dark:text-white">{{ currentTeam.team_chief || 'N/A' }}</p>
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Technical Chief</h4>
                <p class="text-base text-gray-900 dark:text-white">{{ currentTeam.technical_chief || 'N/A' }}</p>
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Technical Information</h4>
                <div class="grid grid-cols-3 gap-4 mt-2">
                  <div class="text-center p-2 bg-gray-100 dark:bg-gray-700 rounded">
                    <p class="text-xs text-gray-500 dark:text-gray-400">First Entry</p>
                    <p class="font-bold text-gray-900 dark:text-white">{{ currentTeam.first_team_entry }}</p>
                  </div>
                  <div class="text-center p-2 bg-gray-100 dark:bg-gray-700 rounded">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Chassis</p>
                    <p class="font-bold text-gray-900 dark:text-white">{{ currentTeam.chassis || 'N/A' }}</p>
                  </div>
                  <div class="text-center p-2 bg-gray-100 dark:bg-gray-700 rounded">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Power Unit</p>
                    <p class="font-bold text-gray-900 dark:text-white">{{ currentTeam.power_unit || 'N/A' }}</p>
                  </div>
                </div>
              </div>

              <div v-if="currentTeam.official_website" class="mt-4">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Official Website</h4>
                <a 
                  :href="currentTeam.official_website" 
                  target="_blank" 
                  rel="noopener noreferrer" 
                  class="mt-1 inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                  </svg>
                  <span class="font-medium">
                    {{ currentTeam.official_website.replace(/^https?:\/\/(www\.)?/, '') }}
                  </span>
                </a>
              </div>

              <div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  Drivers {{ currentTeam.driver_season_year ? `(${currentTeam.driver_season_year})` : '' }}
                </h4>
                <div class="flex flex-wrap gap-2 mt-2">
                  <div 
                    v-for="driver in currentTeam.current_drivers || []" 
                    :key="driver.id" 
                    class="flex items-center space-x-2 bg-gray-100 dark:bg-gray-700 rounded-lg p-2"
                  >
                    <img 
                      v-if="driver.image" 
                      :src="`/storage/${driver.image}`" 
                      :alt="`${driver.first_name} ${driver.last_name}`" 
                      class="w-8 h-8 rounded-full object-cover"
                    />
                    <div>
                      <div class="flex items-center">
                        <p class="font-medium text-sm">{{ driver.first_name }} {{ driver.last_name }}</p>
                        <CountryFlag 
                          v-if="driver.nationality?.code"
                          :countryCode="driver.nationality.code"
                          size="w-4 h-3"
                          :rounded="true"
                          class="ml-2"
                        />
                      </div>
                      <p class="text-xs text-gray-500">{{ driver.code }} · #{{ driver.number || '?' }}</p>
                    </div>
                  </div>
                  <p v-if="!(currentTeam.current_drivers?.length)" class="text-gray-500 text-sm">No current drivers</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Driver Details Modal -->
    <div v-if="showDriverModal" class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeDriverModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div v-if="currentDriverModal" class="px-4 pt-5 pb-4 sm:p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ currentDriverModal.first_name }} {{ currentDriverModal.last_name }} 
                <span class="text-f1-red font-mono ml-2">{{ currentDriverModal.code }}</span>
              </h3>
              <button @click="closeDriverModal" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            
            <div class="mb-4 flex justify-center">
              <img v-if="currentDriverModal.image" :src="`/storage/${currentDriverModal.image}`" :alt="`${currentDriverModal.first_name} ${currentDriverModal.last_name}`" class="h-40 object-contain">
              <div v-else class="h-40 w-32 bg-gray-200 dark:bg-gray-700 flex items-center justify-center rounded-md">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zm-4 7a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
            </div>
            
            <div class="space-y-4">
              <!-- Basic Info -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Nationality</h4>
                  <p class="text-base text-gray-900 dark:text-white flex items-center">
                    <CountryFlag 
                      v-if="currentDriverModal.nationality?.code"
                      :countryCode="currentDriverModal.nationality.code"
                      size="w-5 h-4"
                      :rounded="true"
                      class="mr-2"
                    />
                    {{ currentDriverModal.nationality?.name || 'Unknown' }}
                  </p>
                </div>
                
                <div>
                  <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Date of Birth</h4>
                  <p class="text-base text-gray-900 dark:text-white">
                    {{ currentDriverModal.date_of_birth ? new Date(currentDriverModal.date_of_birth).toLocaleDateString() : 'N/A' }}
                  </p>
                </div>
              </div>
              
              <!-- Current Team -->
              <div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Team</h4>
                <div v-if="currentDriverModal.constructors && currentDriverModal.constructors.length > 0" class="flex items-center mt-1">
                  <img 
                    v-if="currentDriverModal.constructors[0].logo" 
                    :src="`/storage/${currentDriverModal.constructors[0].logo}`"
                    class="h-6 mr-2"
                    :alt="currentDriverModal.constructors[0].name"
                  />
                  <p class="text-base text-gray-900 dark:text-white">
                    {{ currentDriverModal.constructors[0].name }}
                  </p>
                </div>
                <p v-else class="text-base text-gray-500">No current team</p>
              </div>
              
              <!-- Statistics -->
              <div>
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Statistics</h4>
                <div class="grid grid-cols-4 gap-2 mt-2">
                  <div class="text-center p-2 bg-gray-100 dark:bg-gray-700 rounded">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Wins</p>
                    <p class="font-bold text-gray-900 dark:text-white">{{ currentDriverModal.wins || 0 }}</p>
                  </div>
                  <div class="text-center p-2 bg-gray-100 dark:bg-gray-700 rounded">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Podiums</p>
                    <p class="font-bold text-gray-900 dark:text-white">{{ currentDriverModal.podiums || 0 }}</p>
                  </div>
                  <div class="text-center p-2 bg-gray-100 dark:bg-gray-700 rounded">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Poles</p>
                    <p class="font-bold text-gray-900 dark:text-white">{{ currentDriverModal.poles || 0 }}</p>
                  </div>
                  <div class="text-center p-2 bg-gray-100 dark:bg-gray-700 rounded">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Titles</p>
                    <p class="font-bold text-gray-900 dark:text-white">{{ currentDriverModal.championships || 0 }}</p>
                  </div>
                </div>
              </div>
              
              <!-- Biography -->
              <div v-if="currentDriverModal.biography">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Biography</h4>
                <p class="text-sm text-gray-900 dark:text-white mt-1">{{ currentDriverModal.biography }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import CountryFlag from '@/components/CountryFlag.vue';

// State variables
const teams = ref([]);
const availableSeasons = ref([]);
const yearFilter = ref('');
const searchQuery = ref('');
const loading = ref(true);
const showModal = ref(false);
const currentTeam = ref(null);

// Driver modal state
const showDriverModal = ref(false);
const currentDriverModal = ref(null);

// Add this to determine if user is admin
const isAdmin = computed(() => {
  const user = usePage().props.auth.user;
  return user && user.role === 'admin';
});

// Fetch teams and seasons on component mount
onMounted(async () => {
  try {
    await Promise.all([fetchTeams(), fetchSeasons()]);
  } catch (error) {
    console.error('Error loading data:', error);
  } finally {
    loading.value = false;
  }
});

// Modify your fetchTeams function to use the year filter
async function fetchTeams() {
  try {
    const params = {};
    if (yearFilter.value) {
      params.year = yearFilter.value;
    }
    
    const response = await axios.get('/api/constructors', { params });
    teams.value = response.data.data || [];
    
    console.log(`Loaded ${teams.value.length} teams for year: ${yearFilter.value || 'latest'}`);
    
    // Add this debug to check driver data
    if (teams.value.length > 0) {
      teams.value.forEach(team => {
        console.log(`Team ${team.name} has ${team.current_drivers?.length || 0} drivers`, team.current_drivers);
      });
    }
  } catch (error) {
    console.error('Error fetching teams:', error);
  }
}

// Add a watcher to refetch teams when year filter changes
watch(yearFilter, async (newYear) => {
  loading.value = true;
  await fetchTeams();
  loading.value = false;
});

// Fetch seasons for filter
async function fetchSeasons() {
  try {
    const response = await axios.get('/api/seasons');
    availableSeasons.value = response.data.data;
    console.log('Seasons loaded:', availableSeasons.value.length);
  } catch (error) {
    console.error('Error fetching seasons:', error);
  }
}

// Computed property for filtered teams
const filteredTeams = computed(() => {
  return teams.value.filter(team => {
    // Year filter
    if (yearFilter.value && team.seasons) {
      const matchingSeason = team.seasons.find(season => 
        season.year.toString() === yearFilter.value.toString()
      );
      
      if (!matchingSeason) return false;
    }
    
    // Search filter
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase();
      if (!team.name.toLowerCase().includes(query) &&
          !team.base?.toLowerCase().includes(query)) {
        return false;
      }
    }
    
    return true;
  });
});

// View team details
const viewTeam = (team) => {
  currentTeam.value = JSON.parse(JSON.stringify(team));
  showModal.value = true;
};

// Close modal
const closeModal = () => {
  showModal.value = false;
  setTimeout(() => {
    currentTeam.value = null;
  }, 200);
};

// Navigate to driver
const navigateToDriver = (driver) => {
  router.visit(`/drivers/${driver.driver_id}`);
};

// View driver from team card
const viewDriverFromTeamCard = async (driver, event) => {
  // Stop event propagation
  event.stopPropagation();
  
  try {
    // Get full driver details with team info
    const response = await axios.get(`/api/drivers/${driver.driver_id}?include=currentConstructor,championships`);
    
    // Add debugging to see what's returned
    console.log('Driver data:', response.data.data);
    
    // Set modal data
    currentDriverModal.value = response.data.data;
    showDriverModal.value = true;
  } catch (error) {
    console.error('Error loading driver details:', error);
  }
};

// Close driver modal
const closeDriverModal = () => {
  showDriverModal.value = false;
  setTimeout(() => {
    currentDriverModal.value = null;
  }, 200);
};
</script>