<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;

// Año actual para el footer
const currentYear = new Date().getFullYear();
</script>

<template>
  <div class="flex flex-col min-h-screen">
    <Head title="Formula 1 App" />

    <!-- Navegación -->
    <nav class="bg-white shadow-lg dark:bg-gray-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <span class="font-bold text-xl text-red-600">Formula 1 App</span>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <template v-if="user">
              <Link :href="route('dashboard')" class="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                Dashboard
              </Link>
            </template>
            <template v-else>
              <Link :href="route('login')" class="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                Iniciar sesión
              </Link>
              <Link :href="route('register')" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                Registrarse
              </Link>
            </template>
          </div>
        </div>
      </div>
    </nav>

    <!-- Contenido principal -->
    <main class="flex-grow">
      <!-- Hero section -->
      <div class="relative">
        <div class="absolute inset-0 bg-black opacity-40"></div>
        <div class="relative h-[500px] flex items-center">
          <img 
            src="/img/f1-background.jpg" 
            alt="Formula 1 Background" 
            class="absolute inset-0 h-full w-full object-cover opacity-40"
          />
          
          <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">
              Formula 1 Stats
            </h1>
            <p class="mt-6 max-w-3xl mx-auto text-xl text-gray-100">
              La aplicación definitiva para seguir los resultados, pilotos, constructores y estadísticas de la Fórmula 1.
            </p>
            <div class="mt-10">
              <Link :href="user ? route('dashboard') : route('register')" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700">
                {{ user ? 'Ir al Dashboard' : 'Comienza ahora' }}
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Características -->
      <div class="py-16 bg-white dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white text-center mb-12">
            Características principales
          </h2>
          
          <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow-md">
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">Resultados de carreras</h3>
              <p class="mt-2 text-gray-600 dark:text-gray-300">
                Accede a los resultados actualizados de todas las carreras de la temporada.
              </p>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow-md">
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">Clasificaciones</h3>
              <p class="mt-2 text-gray-600 dark:text-gray-300">
                Sigue las clasificaciones de pilotos y constructores de la temporada actual.
              </p>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow-md">
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">Información de pilotos</h3>
              <p class="mt-2 text-gray-600 dark:text-gray-300">
                Conoce los detalles de tus pilotos favoritos y su rendimiento.
              </p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Sección adicional de estadísticas -->
      <div class="py-16 bg-gray-100 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white text-center mb-12">
            Estadísticas en tiempo real
          </h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <div>
              <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Todas las carreras</h3>
              <ul class="space-y-4">
                <li class="flex items-center">
                  <span class="h-8 w-8 rounded-full bg-red-600 flex items-center justify-center text-white font-bold mr-3">
                    1
                  </span>
                  <span class="text-gray-800 dark:text-white">Seguimiento de todos los grandes premios</span>
                </li>
                <li class="flex items-center">
                  <span class="h-8 w-8 rounded-full bg-red-600 flex items-center justify-center text-white font-bold mr-3">
                    2
                  </span>
                  <span class="text-gray-800 dark:text-white">Tiempos de clasificación y carrera</span>
                </li>
                <li class="flex items-center">
                  <span class="h-8 w-8 rounded-full bg-red-600 flex items-center justify-center text-white font-bold mr-3">
                    3
                  </span>
                  <span class="text-gray-800 dark:text-white">Análisis de estrategias de carrera</span>
                </li>
              </ul>
            </div>
            <div>
              <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Perfil personal</h3>
              <ul class="space-y-4">
                <li class="flex items-center">
                  <span class="h-8 w-8 rounded-full bg-red-600 flex items-center justify-center text-white font-bold mr-3">
                    1
                  </span>
                  <span class="text-gray-800 dark:text-white">Selecciona tus pilotos y equipos favoritos</span>
                </li>
                <li class="flex items-center">
                  <span class="h-8 w-8 rounded-full bg-red-600 flex items-center justify-center text-white font-bold mr-3">
                    2
                  </span>
                  <span class="text-gray-800 dark:text-white">Recibe notificaciones personalizadas</span>
                </li>
                <li class="flex items-center">
                  <span class="h-8 w-8 rounded-full bg-red-600 flex items-center justify-center text-white font-bold mr-3">
                    3
                  </span>
                  <span class="text-gray-800 dark:text-white">Comparte tus predicciones con otros fans</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div>
            <h3 class="text-xl font-bold mb-4">Formula 1 App</h3>
            <p class="text-gray-300">
              La mejor forma de seguir toda la acción de la Fórmula 1 en tiempo real.
            </p>
          </div>
          <div>
            <h3 class="text-xl font-bold mb-4">Enlaces</h3>
            <ul class="space-y-2">
              <li><Link href="/drivers" class="text-gray-300 hover:text-white">Pilotos</Link></li>
              <li><Link href="/constructors" class="text-gray-300 hover:text-white">Equipos</Link></li>
              <li><Link href="/races" class="text-gray-300 hover:text-white">Carreras</Link></li>
              <li><Link href="/standings" class="text-gray-300 hover:text-white">Clasificaciones</Link></li>
            </ul>
          </div>
          <div>
            <h3 class="text-xl font-bold mb-4">Legal</h3>
            <ul class="space-y-2">
              <li><Link href="#" class="text-gray-300 hover:text-white">Términos y condiciones</Link></li>
              <li><Link href="#" class="text-gray-300 hover:text-white">Política de privacidad</Link></li>
            </ul>
          </div>
        </div>
        <div class="mt-8 pt-8 border-t border-gray-700 text-center text-gray-400">
          <p>&copy; {{ currentYear }} Formula 1 App. Todos los derechos reservados.</p>
        </div>
      </div>
    </footer>
  </div>
</template>