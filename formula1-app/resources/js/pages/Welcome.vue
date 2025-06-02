<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue'; // Importar ref, onMounted, watch
import { Trophy, BarChart3, UserSquare2, Rocket, Users, BarChartHorizontalBig, CheckCircle, ShieldCheck, Sun, Moon } from 'lucide-vue-next'; // Añadir Sun y Moon

const user = usePage().props.auth.user;
const currentYear = new Date().getFullYear();

// --- Lógica para el modo oscuro ---
const darkMode = ref(false);

const applyTheme = (isDark) => {
  if (isDark) {
    document.documentElement.classList.add('dark');
    localStorage.theme = 'dark';
  } else {
    document.documentElement.classList.remove('dark');
    localStorage.theme = 'light';
  }
  darkMode.value = isDark;
};

const toggleDarkMode = () => {
  applyTheme(!darkMode.value);
};

onMounted(() => {
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  if (localStorage.theme === 'dark' || (!('theme' in localStorage) && prefersDark)) {
    applyTheme(true);
  } else {
    applyTheme(false);
  }
});
// --- Fin de la lógica para el modo oscuro ---

const features = [
  { icon: Trophy, title: 'Live Race Results', description: 'Up-to-the-minute results and classifications for every Grand Prix.' },
  { icon: BarChart3, title: 'Detailed Standings', description: 'Follow driver and constructor championships with in-depth data.' },
  { icon: UserSquare2, title: 'Driver & Team Profiles', description: 'Explore comprehensive profiles, career stats, and season performance.' },
  { icon: BarChartHorizontalBig, title: 'Historical Data', description: 'Journey through F1 history with access to past seasons and records.' },
  { icon: ShieldCheck, title: 'Personalized Experience', description: 'Customize your dashboard, follow favorites, and get tailored insights.' },
  { icon: Users, title: 'Community & Predictions', description: 'Join the discussion, make predictions, and compare with other fans.' },
];

</script>

<template>
  <div class="flex flex-col min-h-screen bg-gray-100 dark:bg-gray-900">
    <Head title="Welcome to F1 Stats" />

    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 shadow-lg sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
          <div class="flex-shrink-0">
            <Link :href="route('home')">
              <img class="h-16 w-auto" src="/img/logo-f1.png" alt="F1 Stats Logo" />
            </Link>
          </div>
          <div class="hidden md:flex items-center space-x-6">
            <template v-if="user">
              <Link :href="route('dashboard')" class="text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-500 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                Dashboard
              </Link>
            </template>
            <template v-else>
              <Link :href="route('login')" class="text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-500 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                Log In
              </Link>
              <Link :href="route('register')" class="bg-red-600 text-white px-5 py-2.5 rounded-md text-sm font-semibold hover:bg-red-700 shadow-md hover:shadow-lg transition-all duration-150 ease-in-out transform hover:scale-105">
                Sign Up
              </Link>
            </template>
            <button 
              @click="toggleDarkMode" 
              class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none"
              aria-label="Toggle dark mode"
            >
              <Sun v-if="darkMode" class="h-6 w-6" />
              <Moon v-else class="h-6 w-6" />
            </button>
          </div>
          <div class="md:hidden flex items-center">
             <button 
              @click="toggleDarkMode" 
              class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none mr-2"
              aria-label="Toggle dark mode"
            >
              <Sun v-if="darkMode" class="h-6 w-6" />
              <Moon v-else class="h-6 w-6" />
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main content -->
    <main class="flex-grow">
      <!-- Hero section -->
      <div class="relative ">
        <div class="absolute inset-0">
          <img 
            src="/img/f1-background.jpg" 
            alt="Formula 1 Background" 
            class="absolute inset-0 h-full w-full object-cover opacity-30"
          />
          <div class="absolute inset-0"></div>
        </div>
        
        <div class="relative h-[calc(100vh-5rem)] min-h-[500px] max-h-[700px] flex items-center justify-center text-center px-4 sm:px-6 lg:px-8">
          <div class="max-w-3xl">
            <h1 class="text-5xl font-extrabold tracking-tight text-black dark:text-white sm:text-6xl lg:text-7xl">
              <span class="block">F1 Stats:</span>
              <span class="block text-red-500 mt-2">Your Pit Wall Online</span>
            </h1>
            <p class="mt-8 max-w-2xl mx-auto text-xl text-gray-800 dark:text-gray-200 sm:text-2xl">
              Dive deep into Formula 1. Real-time results, driver stats, team performance, and historical data at your fingertips.
            </p>
            <div class="mt-12">
              <Link 
                :href="user ? route('dashboard') : route('register')" 
                class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-lg font-semibold rounded-lg shadow-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-red-500 transform transition-all duration-150 ease-in-out hover:scale-105"
              >
                {{ user ? 'Go to Dashboard' : 'Get Started Now' }}
                <Rocket class="ml-3 h-6 w-6" />
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Features -->
      <section class="py-20 bg-white dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="text-center mb-16">
            <h2 class="text-base font-semibold text-red-600 tracking-wide uppercase">Why F1 Stats?</h2>
            <p class="mt-2 text-4xl font-extrabold text-gray-900 dark:text-white sm:text-5xl">
              Everything You Need, All in One Place
            </p>
          </div>
          
          <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3">
            <div 
              v-for="feature in features" 
              :key="feature.title"
              class="bg-gray-50 dark:bg-gray-700/60 p-8 rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 ease-in-out ring-1 ring-gray-200 dark:ring-gray-700 hover:ring-red-500 dark:hover:ring-red-500"
            >
              <div class="flex-shrink-0 mb-5">
                <component :is="feature.icon" class="h-10 w-10 text-red-600" />
              </div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ feature.title }}</h3>
              <p class="text-gray-600 dark:text-gray-300 text-sm">{{ feature.description }}</p>
            </div>
          </div>
        </div>
      </section>
      
      <!-- Additional statistics section (Call to Action Style) -->
      <section class="py-20 bg-gray-100 dark:bg-gray-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <CheckCircle class="h-16 w-16 text-red-600 mx-auto mb-6" />
          <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl">
            Ready to Join the Race?
          </h2>
          <p class="mt-6 text-lg text-gray-600 dark:text-gray-300">
            Sign up today to unlock your personalized F1 dashboard, track your favorite drivers, and never miss a moment of the action. It's free to get started!
          </p>
          <div class="mt-10">
            <Link 
              v-if="!user"
              :href="route('register')" 
              class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-semibold rounded-md shadow-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 dark:focus:ring-offset-gray-900 focus:ring-red-500 transform transition-all duration-150 ease-in-out hover:scale-105"
            >
              Create Your Account
              <Rocket class="ml-2 h-5 w-5" />
            </Link>
            <Link 
              v-if="user"
              :href="route('dashboard')" 
              class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-semibold rounded-md shadow-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 dark:focus:ring-offset-gray-900 focus:ring-red-500 transform transition-all duration-150 ease-in-out hover:scale-105"
            >
              Explore Dashboard
              <Rocket class="ml-2 h-5 w-5" />
            </Link>
          </div>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div>
            <img class="h-20 w-auto mb-4" src="/img/logo-f1.png" alt="F1 Stats Logo" /> 
            <p class="text-sm text-gray-400">
              Your ultimate companion for the Formula 1 world.
            </p>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase mb-4">Quick Links</h3>
            <ul class="space-y-3">
              <li><Link :href="user ? route('dashboard') : route('home')" class="text-base text-gray-300 hover:text-white transition-colors">Dashboard</Link></li>
              <li><Link :href="route('drivers.index')" class="text-base text-gray-300 hover:text-white transition-colors">Drivers</Link></li>
              <li><Link :href="route('constructors')" class="text-base text-gray-300 hover:text-white transition-colors">Teams</Link></li>
              <li><Link :href="route('races.index')" class="text-base text-gray-300 hover:text-white transition-colors">Races</Link></li>
            </ul>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase mb-4">Legal</h3>
            <ul class="space-y-3">
              <li><Link href="#" class="text-base text-gray-300 hover:text-white transition-colors">Terms & Conditions</Link></li>
              <li><Link href="#" class="text-base text-gray-300 hover:text-white transition-colors">Privacy Policy</Link></li>
              <li><Link href="#" class="text-base text-gray-300 hover:text-white transition-colors">Cookie Policy</Link></li>
            </ul>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase mb-4">Connect</h3>
            <p class="text-base text-gray-300">contact@f1statsapp.com</p>
          </div>
        </div>
        <div class="mt-10 pt-8 border-t border-gray-700 text-center">
          <p class="text-sm text-gray-400">&copy; {{ currentYear }} F1 Stats. All rights reserved. Not affiliated with Formula One companies.</p>
        </div>
      </div>
    </footer>
  </div>
</template>