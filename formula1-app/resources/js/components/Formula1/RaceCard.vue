<template>
  <div @click="$emit('click')" 
       class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-hidden hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors cursor-pointer flex flex-col h-[200px]">
    <div class="p-4 flex flex-col flex-1">
      <div class="flex justify-between items-center mb-2">
        <span class="text-sm text-gray-500 dark:text-gray-400">
          {{ props.formatDateFn(race.race_date, race.start_time, props.userTimezone) }}
        </span>
        <span class="px-2 py-1 rounded-full text-xs font-medium" :class="getStatusClass(race.status)">
          {{ race.status || 'Scheduled' }}
        </span>
      </div>
      
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
        {{ race.name }}
      </h3>
      
      <div class="mt-2 flex items-center">
        <CountryFlag v-if="race.circuit?.country" :countryCode="race.circuit.country.code" class="h-4 w-5 mr-2" />
        <span class="text-sm text-gray-700 dark:text-gray-300">
          {{ race.circuit?.name }}, {{ race.circuit?.country?.name }}
        </span>
      </div>
      
      <div class="flex-grow"></div>
      
      <div v-if="race.weekend_format === 'sprint'" class="mt-auto mb-2 inline-flex items-center">
        <span class="bg-red-100 dark:bg-red-900 dark:bg-opacity-20 text-red-600 dark:text-red-400 text-xs px-2 py-0.5 rounded-full">
          <i class="fas fa-bolt mr-1"></i> Sprint
        </span>
      </div>
      
      <div v-if="isUpcoming && countdownText" class="text-xs text-gray-500 dark:text-gray-400">
        <div class="flex items-center">
          <i class="fas fa-flag-checkered mr-1 text-red-600 dark:text-red-500"></i>
          <span>{{ countdownText }}</span>
        </div>
      </div>
    </div>
    
    <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 py-2 mt-auto">
      <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400">
        <span>Round {{ race.round }}</span>
        <span class="flex items-center">
          <i class="fas fa-chevron-right"></i>
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import CountryFlag from '@/components/CountryFlag.vue';

const props = defineProps({
  race: {
    type: Object,
    required: true
  },
  userTimezone: {
    type: String,
    required: true
  },
  formatDateFn: {
    type: Function,
    required: true
  }
});

const timeRemaining = ref(null);
const timerId = ref(null);

function getRaceDateTimeUTC(raceDateStr, raceTimeStr) {
  if (!raceDateStr) return null;

  try {
    const dateMatch = String(raceDateStr).match(/(\d{4})[-/](\d{1,2})[-/](\d{1,2})/);
    if (!dateMatch) return null;
    const year = parseInt(dateMatch[1]);
    const month_1_indexed = parseInt(dateMatch[2]);
    const day = parseInt(dateMatch[3]);

    let sourceHour = 0;
    let sourceMinute = 0;
    if (raceTimeStr) {
      const timeMatch = String(raceTimeStr).match(/(\d{1,2}):(\d{1,2})/);
      if (timeMatch) {
        sourceHour = parseInt(timeMatch[1]);
        sourceMinute = parseInt(timeMatch[2]);
      }
    }

    const refDateUTCForOffset = new Date(Date.UTC(year, month_1_indexed - 1, day, 12, 0, 0));
    const getPartsInTimezone = (dateObj, tz) => {
      const formatter = new Intl.DateTimeFormat('en-US', {
        year: 'numeric', month: 'numeric', day: 'numeric',
        hour: 'numeric', hourCycle: 'h23', timeZone: tz
      });
      const parts = formatter.formatToParts(dateObj);
      return {
        year: parseInt(parts.find(p => p.type === 'year').value),
        month: parseInt(parts.find(p => p.type === 'month').value),
        day: parseInt(parts.find(p => p.type === 'day').value),
        hour: parseInt(parts.find(p => p.type === 'hour').value)
      };
    };
    const partsAtUTCRef = getPartsInTimezone(refDateUTCForOffset, 'UTC');
    const partsAtMadridRef = getPartsInTimezone(refDateUTCForOffset, 'Europe/Madrid');
    let hourOffset = partsAtMadridRef.hour - partsAtUTCRef.hour;
    const madridRefDateObj = new Date(partsAtMadridRef.year, partsAtMadridRef.month - 1, partsAtMadridRef.day);
    const utcRefDateObj = new Date(partsAtUTCRef.year, partsAtUTCRef.month - 1, partsAtUTCRef.day);
    if (madridRefDateObj.getTime() > utcRefDateObj.getTime()) hourOffset += 24;
    else if (madridRefDateObj.getTime() < utcRefDateObj.getTime()) hourOffset -= 24;

    return new Date(Date.UTC(year, month_1_indexed - 1, day, sourceHour - hourOffset, sourceMinute, 0));
  } catch (e) {
    console.error("Error in getRaceDateTimeUTC:", e);
    return null;
  }
}

const raceDateTimeUTC = computed(() => getRaceDateTimeUTC(props.race.race_date, props.race.start_time));

const isUpcoming = computed(() => {
  if (!raceDateTimeUTC.value) return false;
  return raceDateTimeUTC.value > new Date();
});

function calculateTimeRemaining() {
  if (!raceDateTimeUTC.value) {
    timeRemaining.value = null;
    return;
  }

  const targetDate = raceDateTimeUTC.value;
  const now = new Date();
  const diff = targetDate.getTime() - now.getTime();

  if (diff <= 0) {
    timeRemaining.value = null;
    if (timerId.value) clearInterval(timerId.value);
    return;
  }

  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

  timeRemaining.value = { days, hours, minutes };
}

const countdownText = computed(() => {
  if (!timeRemaining.value || !isUpcoming.value) return '';
  
  if (timeRemaining.value.days > 0) {
    return `${timeRemaining.value.days}d until race`;
  } else if (timeRemaining.value.hours > 0) {
    return `${timeRemaining.value.hours}h ${timeRemaining.value.minutes}m until race`;
  } else if (timeRemaining.value.minutes >= 0) {
    return `${timeRemaining.value.minutes}m until race`;
  }
  return '';
});

function getStatusClass(status) {
  if (!status) return 'bg-blue-600 text-white';
  const statusLower = status.toLowerCase();
  if (statusLower === 'completed' || statusLower === 'finished') {
    return 'bg-green-600 dark:bg-green-800 text-white';
  }
  if (statusLower === 'in progress' || statusLower === 'ongoing') {
    return 'bg-yellow-500 dark:bg-yellow-700 text-white';
  }
  if (statusLower === 'cancelled' || statusLower === 'postponed') {
    return 'bg-red-600 dark:bg-red-800 text-white';
  }
  return 'bg-blue-600 dark:bg-blue-800 text-white';
}

onMounted(() => {
  if (isUpcoming.value) {
    calculateTimeRemaining();
    timerId.value = setInterval(calculateTimeRemaining, 60000);
  }
});

onBeforeUnmount(() => {
  if (timerId.value) {
    clearInterval(timerId.value);
  }
});
</script>