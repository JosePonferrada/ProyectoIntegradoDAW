<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

// Importar Head correctamente
import { Head } from '@inertiajs/vue3';

// Estado para controlar el modo oscuro
const darkMode = ref(false);

// Estado para controlar el menú móvil
const mobileMenuOpen = ref(false);
// Estado para el dropdown de usuario
const userDropdownOpen = ref(false);

// Obtener el usuario autenticado
const user = computed(() => usePage().props.auth.user);
const isAdmin = computed(() => user.value?.role === 'admin');

const props = defineProps({
  title: {
    type: String,
    required: false,
    default: 'Formula 1 App'
  }
});

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

// Función para cerrar sesión
const logout = () => {
  router.post(route('logout'));
};
</script>

<template>
  <div>
    <Head :title="title" />

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
                  :class="[
                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition',
                    $page.url.startsWith(item.href) 
                      ? 'border-f1-red text-gray-900 dark:text-white' 
                      : 'border-transparent text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white hover:border-gray-300'
                  ]"
                >
                  {{ item.name }}
                </Link>
              </div>
            </div>
            
            <!-- Área de usuario y modo oscuro -->
            <div class="flex items-center">
              <!-- Menú de usuario (si está autenticado) -->
              <div class="ml-3 relative" v-if="user">
                <div>
                  <button @click="userDropdownOpen = !userDropdownOpen" type="button" class="flex text-sm rounded-full focus:outline-none" id="user-menu-button">
                    <span class="sr-only">Open user menu</span>
                    <div class="flex items-center">
                      <!-- Avatar o iniciales del usuario -->
                      <div v-if="user.avatar" class="h-8 w-8 rounded-full overflow-hidden border border-gray-600 mr-2">
                        <img 
                          :src="`/storage/${user.avatar}`"
                          class="h-full w-full object-cover"
                          alt="Avatar"
                        />
                      </div>
                      <div v-else class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 mr-2">
                        {{ user.name.charAt(0).toUpperCase() }}
                      </div>
                      
                      <!-- Nombre del usuario (una sola vez) -->
                      <span class="text-gray-700 dark:text-gray-200">{{ user.name }}</span>
                      
                      <!-- Flecha del dropdown -->
                      <svg class="ml-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                      </svg>
                    </div>
                  </button>
                </div>
                
                <!-- Dropdown menú -->
                <div 
                  v-show="userDropdownOpen" 
                  class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 focus:outline-none z-50" 
                  role="menu"
                >
                  <Link v-if="isAdmin" :href="route('dashboard')" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" role="menuitem">
                    Dashboard
                  </Link>
                  <Link :href="'/profile'" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" role="menuitem">
                    Your Profile
                  </Link>
                  <button @click="logout" class="w-full text-left block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" role="menuitem">
                    Sign out
                  </button>
                </div>
              </div>
              
              <!-- Enlaces de login/register si no está autenticado -->
              <div v-else class="flex items-center space-x-4">
                <Link :href="route('login')" class="text-sm text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                  Login
                </Link>
                <Link :href="route('register')" class="text-sm bg-f1-red hover:bg-red-700 text-white px-4 py-2 rounded-md">
                  Register
                </Link>
              </div>
              
              <!-- Botón de modo oscuro -->
              <button 
                @click="toggleDarkMode" 
                class="ml-4 p-2 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none"
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
  </div>
</template>