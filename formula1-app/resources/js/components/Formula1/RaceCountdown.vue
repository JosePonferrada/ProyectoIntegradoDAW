<template>
  <div 
    class="relative bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg shadow-lg overflow-hidden"
    :style="race && race.circuit && race.circuit.map_image ? { '--bg-image-url': `url('/storage/${race.circuit.map_image}')` } : {}"
  >
    <!-- Pseudo-elemento para el fondo con opacidad -->
    <div 
      v-if="race && race.circuit && race.circuit.map_image"
      class="absolute inset-0 bg-cover bg-center opacity-20 dark:opacity-10 pointer-events-none"
      :style="{ backgroundImage: `var(--bg-image-url)` }"
    ></div>

    <!-- Contenido principal (debe estar por encima del fondo) -->
    <div class="relative z-10">
      <div class="p-4 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
          Next Race Weekend
        </h3>
        <span :class="statusBadgeClass">
          {{ currentDisplayStatus.badgeText }}
        </span>
      </div>
      
      <div v-if="race" class="px-4 pb-4">
        <!-- Contenedor Flex para detalles de carrera y mensajes de estado -->
        <div class="flex justify-between items-start mb-3">
          <!-- Columna Izquierda: Detalles de la Carrera -->
          <div class="flex-grow">
            <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ race.name }}</h4>
            <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
              <CountryFlag v-if="race.circuit?.country" :countryCode="race.circuit.country.code" class="h-4 w-5 mr-2" />
              {{ race.circuit?.name }}, {{ race.circuit?.country?.name }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              {{ formatEventDate(race) }}
            </div>
          </div>

          <!-- Columna Derecha: Mensajes de Estado (Live/Concluded) -->
          <div class="ml-4 text-right">
            <!-- Mensaje "WEEKEND IS LIVE!" -->
            <div v-if="currentDisplayStatus.showLiveElements" class="mt-0">
              <div class="text-lg font-semibold text-green-500 dark:text-green-400 animate-pulse flex items-center justify-end">
                <i class="fas fa-broadcast-tower mr-2"></i>
                WEEKEND IS LIVE!
              </div>
              <div class="mt-1">
                <Link 
                  :href="route('live-timing.wip')" 
                  class="inline-block bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-1 px-3 rounded-md shadow-sm transition duration-150 ease-in-out"
                >
                  <i class="fas fa-stopwatch mr-1"></i>
                  Go to Live Timing (WIP)
                </Link>
              </div>
            </div>
            
            <!-- Mensaje de Concluido -->
            <div v-else-if="currentDisplayStatus.mode === 'completed_from_props' || currentDisplayStatus.mode === 'concluded_by_time'" class="mt-0">
              <div class="text-lg font-semibold text-gray-700 dark:text-gray-300 flex items-center justify-end">
                <i :class="statusIconClass" class="mr-2"></i>
                {{ currentDisplayStatus.message }}
              </div>
            </div>
          </div>
        </div>
        
        <!-- Contador para la próxima sesión específica -->
        <div v-if="currentDisplayStatus.showCountdownNumbers && timeRemaining" class="mt-2">
          <div class="grid grid-cols-4 gap-2 text-center">
            <div class="bg-gray-100 dark:bg-gray-800 rounded-md p-2">
              <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ timeRemaining.days }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-400">DAYS</div>
            </div>
            <div class="bg-gray-100 dark:bg-gray-800 rounded-md p-2">
              <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ timeRemaining.hours }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-400">HOURS</div>
            </div>
            <div class="bg-gray-100 dark:bg-gray-800 rounded-md p-2">
              <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ timeRemaining.minutes }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-400">MINS</div>
            </div>
            <div class="bg-gray-100 dark:bg-gray-800 rounded-md p-2">
              <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ timeRemaining.seconds }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-400">SECS</div>
            </div>
          </div>
          <div class="mt-3 text-center text-sm text-gray-500 dark:text-gray-400">
            {{ currentDisplayStatus.message }}
          </div>
        </div>
      </div>
      
      <div v-if="currentDisplayStatus.mode === 'no_data'" class="text-center p-4">
        <div class="text-gray-500 dark:text-gray-400 py-8">
          <i class="fas fa-calendar-times text-2xl mb-2"></i>
          <p>{{ currentDisplayStatus.message }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import CountryFlag from '@/components/CountryFlag.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  race: {
    type: Object,
    default: null
  }
});

const timeRemaining = ref(null);
const timerId = ref(null);
const currentTargetSessionForCountdown = ref(null);
const currentTimeTick = ref(new Date());

const RACE_DURATION_HOURS = 2;

// --- INICIO: Lógica de Zona Horaria para parseSessionDate ---
// Helper para obtener el offset de Madrid para una fecha específica
// Esta lógica es similar a la usada en Races.vue y RaceCard.vue
function getMadridOffsetForDate(year, month_1_indexed, day) {
    const refDateUTCForOffset = new Date(Date.UTC(year, month_1_indexed - 1, day, 12, 0, 0)); // Mediodía UTC
    const getParts = (dateObj, tz) => {
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
    const partsAtUTCRef = getParts(refDateUTCForOffset, 'UTC');
    const partsAtMadridRef = getParts(refDateUTCForOffset, 'Europe/Madrid');
    let hourOffset = partsAtMadridRef.hour - partsAtUTCRef.hour;
    
    const madridRefDateObj = new Date(partsAtMadridRef.year, partsAtMadridRef.month - 1, partsAtMadridRef.day);
    const utcRefDateObj = new Date(partsAtUTCRef.year, partsAtUTCRef.month - 1, partsAtUTCRef.day);

    if (madridRefDateObj.getTime() > utcRefDateObj.getTime()) { // Madrid está en un día posterior
      hourOffset += 24;
    } else if (madridRefDateObj.getTime() < utcRefDateObj.getTime()) { // Madrid está en un día anterior
      hourOffset -= 24;
    }
    return hourOffset;
}

function parseSessionDate(sessionTimeValue, sessionDateValue) {
  // sessionTimeValue: e.g., "15:00:00" (hora de Madrid)
  // sessionDateValue: e.g., "2025-05-25" (fecha de Madrid)
  if (typeof sessionTimeValue !== 'string' || !sessionTimeValue.trim() ||
      typeof sessionDateValue !== 'string' || !sessionDateValue.trim()) {
    // console.log('[RaceCountdown] parseSessionDate: Invalid input', { sessionTimeValue, sessionDateValue });
    return null;
  }

  const dateMatch = String(sessionDateValue).match(/(\d{4})[-/](\d{1,2})[-/](\d{1,2})/);
  if (!dateMatch) {
    // console.log('[RaceCountdown] parseSessionDate: Invalid date format', sessionDateValue);
    return null;
  }
  const year = parseInt(dateMatch[1]);
  const month_1_indexed = parseInt(dateMatch[2]); // Mes 1-12
  const day = parseInt(dateMatch[3]);

  let sourceHour = 0;
  let sourceMinute = 0;
  // Intentar parsear HH:MM de sessionTimeValue
  const timeMatch = String(sessionTimeValue).match(/(\d{1,2}):(\d{1,2})/); 
  if (timeMatch) {
    sourceHour = parseInt(timeMatch[1]);
    sourceMinute = parseInt(timeMatch[2]);
  } else {
    // Fallback si sessionTimeValue es parte de una cadena de fecha-hora ISO (ej. de un campo datetime)
    const timeOnlyMatchFromDateTime = String(sessionTimeValue).match(/T(\d{1,2}):(\d{1,2})/);
    if (timeOnlyMatchFromDateTime) {
        sourceHour = parseInt(timeOnlyMatchFromDateTime[1]);
        sourceMinute = parseInt(timeOnlyMatchFromDateTime[2]);
    } else {
        // console.log('[RaceCountdown] parseSessionDate: Invalid time format', sessionTimeValue);
        return null; // No se puede parsear la hora
    }
  }
  
  try {
    const madridOffset = getMadridOffsetForDate(year, month_1_indexed, day);
    // Crear el objeto Date usando Date.UTC con la hora ajustada por el offset
    const utcDate = new Date(Date.UTC(year, month_1_indexed - 1, day, sourceHour - madridOffset, sourceMinute, 0));
    
    if (isNaN(utcDate.getTime())) {
        // console.log('[RaceCountdown] parseSessionDate: Resulting UTC date is NaN');
        return null;
    }
    // console.log(`[RaceCountdown] parseSessionDate: Input Madrid (${year}-${month_1_indexed}-${day} ${sourceHour}:${sourceMinute}), Offset: ${madridOffset}, Output UTC: ${utcDate.toISOString()}`);
    return utcDate;
  } catch (e) {
    console.error("[RaceCountdown] Error in parseSessionDate creating UTC date:", e, {year, month_1_indexed, day, sourceHour, sourceMinute});
    return null;
  }
}
// --- FIN: Lógica de Zona Horaria ---

const sessionMappings = [
    { key: 'practice1_time', dateKey: 'practice1_date', name: 'Practice 1' },
    { key: 'practice2_time', dateKey: 'practice2_date', name: 'Practice 2' },
    { key: 'practice3_time', dateKey: 'practice3_date', name: 'Practice 3' },
    { key: 'sprint_qualifying_time', dateKey: 'sprint_qualifying_date', name: 'Sprint Qualifying' },
    { key: 'sprint_time', dateKey: 'sprint_date', name: 'Sprint Race' },
    { key: 'qualifying_time', dateKey: 'qualifying_date', name: 'Qualifying' },
    { key: 'start_time', dateKey: 'race_date', name: 'Race' },
];

const allSessionsSorted = computed(() => {
    if (!props.race) {
        console.log('[RaceCountdown] allSessionsSorted: props.race is null, returning empty array.');
        return [];
    }
    const sessions = sessionMappings.map(mapping => {
        const sessionTime = props.race[mapping.key];
        const sessionDateStr = props.race[mapping.dateKey];
        // console.log(`[RaceCountdown] allSessionsSorted - Mapping: key=${mapping.key}, timeVal=${sessionTime}, dateVal=${sessionDateStr}`);
        if (!sessionTime) return null;
        
        const date = parseSessionDate(sessionTime, sessionDateStr);
        if (!date) {
            // console.log(`[RaceCountdown] allSessionsSorted - Failed to parse date for ${mapping.name}`);
            return null;
        }
        return { type: mapping.name, date, originalTime: sessionTime, originalDate: sessionDateStr };
    }).filter(s => s !== null).sort((a, b) => a.date - b.date);
    
    console.log('%c[RaceCountdown] allSessionsSorted - Final Processed Sessions:', 'color: green; font-weight: bold;', 
        JSON.parse(JSON.stringify(sessions.map(s => ({ 
            type: s.type, 
            date: s.date.toISOString(), 
            // originalTime: s.originalTime, // Descomenta para más detalle
            // originalDate: s.originalDate  // Descomenta para más detalle
        })))));
    return sessions;
});

const firstSessionStartTime = computed(() => {
    return allSessionsSorted.value.length > 0 ? allSessionsSorted.value[0].date : null;
});

const raceSession = computed(() => {
    return allSessionsSorted.value.find(s => s.type === 'Race');
});

const raceEndTime = computed(() => {
    if (!raceSession.value?.date) return null;
    const endTime = new Date(raceSession.value.date.getTime());
    endTime.setHours(endTime.getHours() + RACE_DURATION_HOURS);
    return endTime;
});

const isWeekendLiveByTimeCalculation = computed(() => {
    if (!firstSessionStartTime.value) return false;
    const now = currentTimeTick.value;
    if (raceEndTime.value) {
        return now >= firstSessionStartTime.value && now <= raceEndTime.value;
    }
    return now >= firstSessionStartTime.value;
});

function findNextSessionForCountdown() {
   const now = currentTimeTick.value;
   let nextSession = null;
   for (const session of allSessionsSorted.value) {
     if (session.date > now) {
       nextSession = session;
       break;
     }
   }
   currentTargetSessionForCountdown.value = nextSession;
}

const currentDisplayStatus = computed(() => {
  if (!props.race) {
    return { mode: 'no_data', message: 'No race data provided.', badgeText: 'N/A', showCountdownNumbers: false, showLiveElements: false };
  }

  const backendStatus = props.race.status?.toLowerCase();
  const now = currentTimeTick.value;
  // Usaremos 'currentTargetSession' para claridad, ya que es la sesión para la que podríamos estar contando.
  const currentTargetSession = currentTargetSessionForCountdown.value; 

  // Prioridad 1: Estados finales o de interrupción del backend
  if (backendStatus === 'completed' || backendStatus === 'finished') {
    return { mode: 'completed_from_props', message: `${props.race.name} has concluded.`, badgeText: 'Completed', showCountdownNumbers: false, showLiveElements: false };
  }
  if (backendStatus === 'cancelled' || backendStatus === 'postponed') {
    return { mode: backendStatus, message: `${props.race.name} is ${backendStatus}.`, badgeText: backendStatus.charAt(0).toUpperCase() + backendStatus.slice(1), showCountdownNumbers: false, showLiveElements: false };
  }

  // Prioridad 2: Si el backend dice que está 'in progress' o 'live'
  if (backendStatus === 'in progress' || backendStatus === 'live') {
    let countdownMsg = "Session in progress.";
    // Si tenemos una sesión objetivo, intentamos mostrar la cuenta atrás para ella.
    const showNumbers = !!currentTargetSession; // <-- CAMBIO CLAVE
    if (showNumbers && currentTargetSession.date > now) {
      countdownMsg = `until ${currentTargetSession.type} start`;
    } else if (showNumbers) { 
      countdownMsg = `${currentTargetSession.type} in progress`;
    }
    return { mode: 'live_from_props', message: countdownMsg, badgeText: 'LIVE', showCountdownNumbers: showNumbers, showLiveElements: true };
  }

  // Prioridad 3: Cálculos basados en el tiempo
  if (isWeekendLiveByTimeCalculation.value) {
    let countdownMsg = "Weekend in progress.";
    const showNumbers = !!currentTargetSession; // <-- CAMBIO CLAVE
    // console.log(`%c[CDS] live_by_time: currentTarget='${currentTargetSession?.type}', showNumbers=${showNumbers}, date=${currentTargetSession?.date?.toLocaleTimeString()}, now=${now.toLocaleTimeString()}`, 'color: purple');

    if (showNumbers && currentTargetSession.date > now) {
      countdownMsg = `until ${currentTargetSession.type} start`;
    } else if (showNumbers) { 
        countdownMsg = `${currentTargetSession.type} likely in progress or just ended.`;
    }
    return { mode: 'live_by_time', message: countdownMsg, badgeText: 'Live', showCountdownNumbers: showNumbers, showLiveElements: true };
  }

  // El fin de semana aún no ha comenzado, pero hay una próxima sesión
  if (currentTargetSession && currentTargetSession.date > now) {
    // console.log(`%c[CDS] upcoming: currentTarget='${currentTargetSession?.type}'`, 'color: purple');
    return { 
      mode: 'upcoming', 
      message: `until ${currentTargetSession.type} start`, 
      badgeText: 'Upcoming', 
      showCountdownNumbers: true, 
      showLiveElements: false 
    };
  }

  // El fin de semana ha concluido según el cálculo de tiempo
  if (raceEndTime.value && now > raceEndTime.value) {
    return { mode: 'concluded_by_time', message: `${props.race.name} has concluded.`, badgeText: 'Completed', showCountdownNumbers: false, showLiveElements: false };
  }
  
  // console.log(`%c[CDS] scheduled_default: currentTarget='${currentTargetSession?.type}'`, 'color: purple');
  return { mode: 'scheduled_default', message: `${props.race.name} is scheduled.`, badgeText: props.race.status || 'Scheduled', showCountdownNumbers: false, showLiveElements: false };
});

function calculateTimeRemaining() {
  if (!currentDisplayStatus.value.showCountdownNumbers || !currentTargetSessionForCountdown.value || !currentTargetSessionForCountdown.value.date) {
    timeRemaining.value = null;
    return;
  }

  const targetDate = currentTargetSessionForCountdown.value.date;
  const now = currentTimeTick.value;
  const diff = targetDate - now;

  if (diff <= 0) {
    timeRemaining.value = { days: 0, hours: 0, minutes: 0, seconds: 0 };
    findNextSessionForCountdown(); 
    return;
  }
  
  timeRemaining.value = {
    days: Math.floor(diff / (1000 * 60 * 60 * 24)),
    hours: Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
    minutes: Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)),
    seconds: Math.floor((diff % (1000 * 60)) / 1000)
  };
}

watch(() => props.race, (newRace) => {
  console.log('[RaceCountdown] props.race changed:', newRace?.name, 'Status:', newRace?.status);
  findNextSessionForCountdown();
  calculateTimeRemaining(); 
}, { deep: true, immediate: true });

onMounted(() => {
  console.log('[RaceCountdown] Mounted. Initial race:', props.race?.name, 'Status:', props.race?.status);
  findNextSessionForCountdown();
  calculateTimeRemaining();

  timerId.value = setInterval(() => {
    currentTimeTick.value = new Date();
    calculateTimeRemaining();
  }, 1000);
});

onBeforeUnmount(() => {
  if (timerId.value) {
    clearInterval(timerId.value);
  }
});

function formatEventDate(race) {
  if (!race?.race_date) return 'Date TBA';
  
  // Parsear la fecha de race.race_date para asegurar que tomamos los componentes correctos
  const dateMatch = String(race.race_date).match(/(\d{4})[-/](\d{1,2})[-/](\d{1,2})/);
  if (!dateMatch) return 'Date TBA'; // No se pudo parsear la fecha de entrada
  
  const year = parseInt(dateMatch[1]);
  const month_1_indexed = parseInt(dateMatch[2]); // Mes 1-12
  const day = parseInt(dateMatch[3]);

  // Crear un objeto Date solo para formateo. Usar Date.UTC para evitar problemas de zona horaria local del navegador.
  // La hora no importa aquí ya que solo queremos mostrar la fecha.
  const dateObj = new Date(Date.UTC(year, month_1_indexed - 1, day)); 

  const options = { month: 'long', day: 'numeric' };
  // Añadir el año solo si no es el año actual.
  // Comparamos el año de la carrera con el año actual UTC para ser consistentes.
  if (year !== new Date().getUTCFullYear()) {
    options.year = 'numeric';
  }
  // Usar 'en-GB' para formato "25 May" o 'en-US' para "May 25"
  return dateObj.toLocaleDateString('en-GB', options); 
}

const statusBadgeClass = computed(() => {
  const mode = currentDisplayStatus.value.mode;
  let baseClasses = 'px-2 py-1 rounded-full text-xs font-medium text-white';
  if (mode === 'live_from_props' || mode === 'live_by_time') {
    return `${baseClasses} bg-green-500 dark:bg-green-700`;
  }
  if (mode === 'completed_from_props' || mode === 'concluded_by_time') {
    return `${baseClasses} bg-gray-500 dark:bg-gray-600`;
  }
  if (mode === 'cancelled' || mode === 'postponed') {
    return `${baseClasses} bg-red-500 dark:bg-red-700`;
  }
  return `${baseClasses} bg-blue-600 dark:bg-blue-800`;
});

const statusIconClass = computed(() => {
    const mode = currentDisplayStatus.value.mode;
    if (mode === 'live_from_props' || mode === 'live_by_time') return 'fas fa-broadcast-tower';
    if (mode === 'completed_from_props' || mode === 'concluded_by_time') return 'fas fa-flag-checkered';
    if (mode === 'cancelled' || mode === 'postponed') return 'fas fa-times-circle';
    return 'fas fa-info-circle';
});
</script>
