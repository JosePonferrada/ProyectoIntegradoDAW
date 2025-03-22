<script setup>
import { ref, watch, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

// Estado para controlar el modo oscuro
const darkMode = ref(false);

// Estado para controlar el menú móvil
const mobileMenuOpen = ref(false);

// Navegación principal
const navigation = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'Drivers', href: '/drivers' },
  { name: 'Teams', href: '/constructors' },
  { name: 'Races', href: '/races' },
  { name: 'Standings', href: '/standings' }
];

// Al montar el componente, verificar si el usuario ya tenía preferencia de tema
onMounted(() => {
  // Comprobar localStorage
  if (localStorage.theme === 'dark' || 
      (!('theme' in localStorage) && 
       window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    darkMode.value = true;
    document.documentElement.classList.add('dark');
  } else {
    darkMode.value = false;
    document.documentElement.classList.remove('dark');
  }
});

// Observar cambios en darkMode para actualizar el DOM y localStorage
watch(darkMode, (newValue) => {
  if (newValue) {
    document.documentElement.classList.add('dark');
    localStorage.theme = 'dark';
  } else {
    document.documentElement.classList.remove('dark');
    localStorage.theme = 'light';
  }
});

// Función para alternar el modo oscuro
const toggleDarkMode = () => {
  darkMode.value = !darkMode.value;
};
</script>

<template>
  <!-- Nota la clase dark aquí - se aplicará automáticamente cuando darkMode sea true -->
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-200">
    <!-- Navegación superior -->
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <Link href="/" class="font-bold text-xl text-f1-red">
                Formula 1 App
              </Link>
            </div>
            
            <!-- Enlaces de navegación - Escritorio -->
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <Link 
                v-for="item in navigation" 
                :key="item.name"
                :href="item.href"
                class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white hover:border-gray-300 transition"
              >
                {{ item.name }}
              </Link>
            </div>
          </div>
          
          <!-- Botón de modo oscuro -->
          <div class="flex items-center">
            <button 
              @click="toggleDarkMode" 
              class="p-2 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none"
              aria-label="Toggle dark mode"
            >
              <!-- Sol para modo claro -->
              <svg v-if="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
              </svg>
              <!-- Luna para modo oscuro -->
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
              </svg>
            </button>

            <!-- Botón de menú móvil -->
            <button 
              @click="mobileMenuOpen = !mobileMenuOpen" 
              class="ml-2 inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-300 hover:text-gray-500 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none sm:hidden"
            >
              <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path v-if="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Menú móvil -->
      <div :class="{'block': mobileMenuOpen, 'hidden': !mobileMenuOpen}" class="sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
          <Link 
            v-for="item in navigation" 
            :key="item.name"
            :href="item.href"
            class="block py-2 pl-3 pr-4 text-base font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-800 dark:hover:text-white border-l-4 border-transparent"
          >
            {{ item.name }}
          </Link>
        </div>
      </div>
    </nav>
    
    <!-- Contenido principal -->
    <main class="py-10">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
          <slot></slot>
        </div>
      </div>
    </main>
  </div>
</template>