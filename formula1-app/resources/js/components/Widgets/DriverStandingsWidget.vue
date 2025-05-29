<script setup>
defineProps({
  data: {
    type: Object,
    default: () => ({ driverStandings: [] })
  }
});

// Función para obtener un color según el constructor (copiada y adaptada)
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
    'Stake F1 Team Kick Sauber': '#00E700', // Verde brillante
    'MoneyGram Haas F1 Team': '#B6BABD' // Gris claro, antes #9C9FA2
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

// Función para determinar el color del texto basado en el color de fondo
function getTextColor(backgroundColor) {
  if (!backgroundColor) return '#FFFFFF'; // Blanco por defecto
  const color = backgroundColor.substring(1); // Quitar #
  const r = parseInt(color.substring(0, 2), 16);
  const g = parseInt(color.substring(2, 4), 16);
  const b = parseInt(color.substring(4, 6), 16);
  // Fórmula de luminancia
  const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
  return luminance > 0.5 ? '#000000' : '#FFFFFF'; // Negro para fondos claros, blanco para oscuros
}
</script>

<template>
  <div>
    <ul v-if="data.driverStandings && data.driverStandings.length">
      <li v-for="driver in data.driverStandings" :key="driver.id" class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
        <div class="flex items-center">
          <span class="font-semibold mr-2 w-6 text-center">{{ driver.position }}.</span>
          <span class="w-40 truncate" :title="driver.driver.full_name">{{ driver.driver.full_name }}</span>
          <span 
            v-if="driver.constructor_name"
            class="ml-2 text-xs px-2 py-0.5 rounded-md font-medium" 
            :style="{ 
              backgroundColor: getConstructorColor(driver.constructor_name), 
              color: getTextColor(getConstructorColor(driver.constructor_name)) 
            }"
            :title="driver.constructor_name"
          >
            {{ driver.constructor_abbreviation || driver.constructor_name.substring(0,3).toUpperCase() }}
          </span>
        </div>
        <span class="font-bold">{{ driver.points }} pts</span>
      </li>
    </ul>
    <p v-else class="text-gray-500 dark:text-gray-400">No driver standings available.</p>
  </div>
</template>