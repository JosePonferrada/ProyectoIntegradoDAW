<script setup>
defineProps({
  data: {
    type: Object,
    default: () => ({ constructorStandings: [] })
  }
});

// Función para obtener un color según el constructor
function getConstructorColor(constructorName) {
  const colors = {
    'Oracle Red Bull Racing': '#0600EF',
    'Mercedes-AMG PETRONAS Formula One Team': '#00D2BE',
    'Scuderia Ferrari HP': '#DC0000',
    'McLaren F1 Team': '#FF8700',
    'Aston Martin Aramco Cognizant F1 Team': '#006F62',
    'BWT Alpine Formula One Team': '#0090FF',
    'Williams Racing': '#005AFF',
    'Visa Cash App Racing Bulls Formula One Team': '#2B4562',
    'Stake F1 Team Kick Sauber': '#00E700',
    'MoneyGram Haas F1 Team': '#B6BABD'
  };
  
  if (constructorName) {
    for (const [key, value] of Object.entries(colors)) {
      if (key.toLowerCase().includes(constructorName.toLowerCase()) || 
          constructorName.toLowerCase().includes(key.toLowerCase())) {
        return value;
      }
    }
  }
  return '#6B7280'; // Color gris por defecto si no se encuentra
}
</script>

<template>
  <div>
    <ul v-if="data.constructorStandings && data.constructorStandings.length">
      <li v-for="team in data.constructorStandings" :key="team.id" class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
        <div class="flex items-center">
          <span class="font-semibold mr-2 w-6 text-center">{{ team.position }}.</span>
          <span 
            class="h-3 w-3 rounded-full mr-2 flex-shrink-0" 
            :style="{ backgroundColor: getConstructorColor(team.constructor.name) }"
            :title="team.constructor.name + ' color'"
          ></span>
          <span class="font-medium text-gray-800 dark:text-gray-200 truncate" :title="team.constructor.name">
            {{ team.constructor.name }}
          </span>
        </div>
        <span class="font-bold text-gray-800 dark:text-gray-200">{{ team.points }} pts</span>
      </li>
    </ul>
    <p v-else class="text-gray-500 dark:text-gray-400">No constructor standings available.</p>
  </div>
</template>