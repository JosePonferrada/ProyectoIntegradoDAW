<script setup>
import { computed } from 'vue';

const props = defineProps({
  data: {
    type: Object,
    default: () => ({
      seasonInfo: {
        year: new Date().getFullYear(), // Año por defecto
        totalRaces: 0,
        completedRaces: 0,
        upcomingRaces: 0,
        leaderDriver: null,
        leaderTeam: null
      }
    })
  }
});

const seasonInfo = computed(() => props.data.seasonInfo || {
    year: new Date().getFullYear(),
    totalRaces: 0,
    completedRaces: 0,
    upcomingRaces: 0,
    leaderDriver: null,
    leaderTeam: null
});

const seasonProgress = computed(() => {
  const total = seasonInfo.value.totalRaces;
  const completed = seasonInfo.value.completedRaces;
  if (!total || total === 0) return 0;
  return Math.round((completed / total) * 100);
});

const displayYear = computed(() => {
  return seasonInfo.value.year || new Date().getFullYear();
});
</script>

<template>
  <div class="p-4 h-full flex flex-col">
    <div v-if="seasonInfo.totalRaces > 0" class="flex-grow">
      <div class="text-center mb-3">
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-200">
          {{ displayYear }} Season Overview
        </h3>
        <p class="text-2xl font-bold text-f1-red mt-1">{{ seasonProgress }}% <span class="text-lg font-medium text-gray-600 dark:text-gray-300">Complete</span></p>
        <div class="w-full bg-gray-200 dark:bg-gray-700 h-2.5 mt-2 rounded-full">
          <div class="bg-f1-red h-2.5 rounded-full" :style="{ width: seasonProgress + '%' }"></div>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
        <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg shadow-sm text-center">
          <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Races</h4>
          <p class="text-xl font-bold text-gray-800 dark:text-gray-100">
            {{ seasonInfo.completedRaces }} <span class="font-normal text-sm">/ {{ seasonInfo.totalRaces }}</span>
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
            {{ seasonInfo.upcomingRaces }} upcoming
          </p>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg shadow-sm">
          <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 text-center">Leaders</h4>
          <div v-if="seasonInfo.leaderDriver" class="mb-1.5 text-sm">
            <span class="font-medium text-gray-800 dark:text-gray-100 block truncate" :title="seasonInfo.leaderDriver.driver?.full_name || seasonInfo.leaderDriver.driver_name">
              <i class="fas fa-user fa-fw mr-1 text-f1-red"></i> {{ seasonInfo.leaderDriver.driver?.full_name || seasonInfo.leaderDriver.driver_name }}
            </span>
            <span class="text-xs text-gray-500 dark:text-gray-400 ml-4">({{ seasonInfo.leaderDriver.points }} pts)</span>
          </div>
          <div v-else>
             <p class="text-xs text-gray-500 dark:text-gray-400 text-center">No driver leader data.</p>
          </div>
          
          <div v-if="seasonInfo.leaderTeam" class="mt-1 text-sm">
            <span class="font-medium text-gray-700 dark:text-gray-300 block truncate" :title="seasonInfo.leaderTeam.constructor?.name || seasonInfo.leaderTeam.constructor_name">
              <i class="fas fa-users fa-fw mr-1 text-f1-red"></i> {{ seasonInfo.leaderTeam.constructor?.name || seasonInfo.leaderTeam.constructor_name }}
            </span>
            <span class="text-xs text-gray-500 dark:text-gray-400 ml-4">({{ seasonInfo.leaderTeam.points }} pts)</span>
          </div>
           <div v-else class="mt-1">
             <p class="text-xs text-gray-500 dark:text-gray-400 text-center">No team leader data.</p>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="flex-grow flex items-center justify-center">
        <p class="text-gray-500 dark:text-gray-400 text-center py-8">Season progress data not available.</p>
    </div>
  </div>
</template>