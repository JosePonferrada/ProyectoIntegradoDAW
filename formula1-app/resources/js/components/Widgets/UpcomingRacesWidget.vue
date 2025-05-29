<template>
  <div class="space-y-4">
    <div v-if="!races || races.length === 0" class="text-center py-8">
      <p class="text-gray-500 dark:text-gray-400">No upcoming races scheduled.</p>
    </div>
    <div v-for="race in races" :key="race.id" 
         class="flex items-center p-3 bg-gray-50 dark:bg-gray-700/60 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-150">
      <div class="mr-4 text-center w-16 flex-shrink-0">
        <div class="text-sm font-semibold text-f1-red">{{ getMonth(race.race_date) }}</div>
        <div class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ getDay(race.race_date) }}</div>
        <div class="text-xs text-gray-500 dark:text-gray-400">{{ getYear(race.race_date) }}</div>
      </div>
      <div class="flex-1 min-w-0">
        <h4 class="text-md font-semibold text-gray-900 dark:text-white truncate" :title="race.name">
          {{ race.name }}
        </h4>
        <p class="text-sm text-gray-600 dark:text-gray-300 truncate" :title="race.circuit?.name || 'Circuit details unavailable'">
          {{ race.circuit?.name || 'Circuit details unavailable' }}
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
          {{ getFullRaceDate(race.race_date) }}
        </p>
      </div>
      <div class="ml-4 flex-shrink-0">
        <img v-if="race.circuit?.map_image_url" :src="race.circuit.map_image_url" alt="Circuit Photo" class="w-32 h-16 object-cover rounded-lg shadow" />
        </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  data: {
    type: Object,
    default: () => ({ upcomingRaces: [] })
  }
});

const races = computed(() => props.data.upcomingRaces || []);

function getMonth(dateString) {
  return new Date(dateString).toLocaleDateString('en-US', { month: 'short' }).toUpperCase();
}

function getDay(dateString) {
  return new Date(dateString).getDate();
}

function getYear(dateString) {
  return new Date(dateString).getFullYear();
}

function getFullRaceDate(dateString) {
  return new Date(dateString).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
}
</script>