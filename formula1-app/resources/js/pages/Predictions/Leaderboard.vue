<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { onMounted } from 'vue';

const props = defineProps({
  topUsers: Array,
  currentUserRank: Number
});

onMounted(() => {
  // Console log to see what user data is available
  console.log('Top Users:', props.topUsers);
  console.log('Current User Rank:', props.currentUserRank);
  console.log('Auth User:', window.Inertia?.page?.props?.auth?.user);
});
</script>

<template>
  <AppLayout title="Prediction Leaderboard">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <!-- Header with Back Button -->
          <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold dark:text-white">Prediction Leaderboard</h1>
            <Link 
              :href="route('predictions.index')" 
              class="flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
            >
              <i class="fas fa-arrow-left mr-2"></i>
              Back to Predictions
            </Link>
          </div>
          
          <!-- User's Rank Card - Improved contrast for better readability -->
          <div class="bg-gradient-to-r from-red-600 to-red-800 p-4 rounded-lg mb-6 shadow-md">
            <p class="text-center text-lg font-semibold text-white">
              Your current rank: <span class="text-2xl font-bold text-white">#{{ currentUserRank }}</span>
            </p>
          </div>
          
          <!-- Leaderboard Table -->
          <div class="overflow-x-auto bg-gray-50 dark:bg-gray-700 rounded-lg shadow">
            <table class="min-w-full">
              <thead>
                <tr class="bg-gray-100 dark:bg-gray-600">
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Position
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    User
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Points
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <!-- Fixed hover color in dark mode -->
                <tr v-for="(user, index) in topUsers" :key="user.id" 
                    :class="[
                      user.id === $page.props.auth.user.id ? 'bg-yellow-50 dark:bg-yellow-900' : 'hover:bg-gray-50 dark:hover:bg-gray-600',
                      index < 3 ? 'font-semibold' : '' 
                    ]">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <!-- Special badges for top 3 -->
                      <span v-if="index === 0" class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-400 text-white mr-2">
                        <i class="fas fa-trophy"></i>
                      </span>
                      <span v-else-if="index === 1" class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-300 text-gray-700 mr-2">
                        <i class="fas fa-medal"></i>
                      </span>
                      <span v-else-if="index === 2" class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-700 text-white mr-2">
                        <i class="fas fa-award"></i>
                      </span>
                      <span v-else class="text-sm font-medium dark:text-white ml-2">#{{ index + 1 }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <!-- User avatar with fallback to initials -->
                      <div v-if="user.avatar" class="flex-shrink-0 h-8 w-8 rounded-full overflow-hidden">
                        <img :src="'/storage/' + user.avatar" alt="Avatar" class="h-full w-full object-cover">
                      </div>
                      <div v-else class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-gray-700 dark:text-gray-300">
                        {{ user.name.charAt(0).toUpperCase() }}
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                          {{ user.name }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-bold dark:text-white">
                      {{ user.total_points || 0 }} pts
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Empty State -->
          <div v-if="!topUsers || topUsers.length === 0" class="text-center py-12">
            <i class="fas fa-chart-bar text-gray-300 text-4xl mb-4"></i>
            <p class="text-gray-500 dark:text-gray-400">No leaderboard data available yet</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>