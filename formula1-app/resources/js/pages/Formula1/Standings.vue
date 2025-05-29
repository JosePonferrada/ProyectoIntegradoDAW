<template>
  <AppLayout :title="`Standings`">
    <Head :title="`Standings`" />
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Championship Standings</h1>
          
          <!-- Tabs para cambiar entre pilotos y constructores -->
          <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6">
            <button 
              @click="activeTab = 'drivers'" 
              :class="[
                'py-2 px-4 text-sm font-medium border-b-2 focus:outline-none',
                activeTab === 'drivers' 
                  ? 'border-f1-red text-f1-red dark:text-red-400' 
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600'
              ]"
            >
              Drivers
            </button>
            <button 
              @click="activeTab = 'constructors'" 
              :class="[
                'py-2 px-4 text-sm font-medium border-b-2 focus:outline-none',
                activeTab === 'constructors' 
                  ? 'border-f1-red text-f1-red dark:text-red-400' 
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600'
              ]"
            >
              Constructors
            </button>
          </div>
          
          <!-- Contenido de las pestañas -->
          <div v-if="activeTab === 'drivers'">
            <DriverStandingsContent />
          </div>
          <div v-else>
            <ConstructorStandingsContent />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import DriverStandingsContent from '@/components/Standings/DriverStandingsContent.vue';
import ConstructorStandingsContent from '@/components/Standings/ConstructorStandingsContent.vue';
import { Head } from '@inertiajs/vue3';

// Si recibimos una pestaña por defecto en props, la usamos
const props = defineProps({
  defaultTab: {
    type: String,
    default: 'drivers'
  }
});

const activeTab = ref(props.defaultTab);

// Si la URL contiene un fragmento específico, cambiamos la pestaña
onMounted(() => {
  // Check for hash fragment first
  const hash = window.location.hash;
  if (hash === '#constructors') {
    activeTab.value = 'constructors';
  } else if (hash === '#drivers') {
    activeTab.value = 'drivers';
  } else {
    // If no hash, check for query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam === 'constructors') {
      activeTab.value = 'constructors';
    } else if (tabParam === 'drivers') {
      activeTab.value = 'drivers';
    }
  }
});
</script>