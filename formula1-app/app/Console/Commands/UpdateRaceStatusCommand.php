<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Race;
use Carbon\Carbon;

class UpdateRaceStatusCommand extends Command
{
    protected $signature = 'race:update-status-from-fp1';
    protected $description = 'Updates race status: In Progress from FP1, Completed after Race (Spain Timezone Aware)';

    /**
     * Helper function to safely extract HH:MM:SS from a time string or Carbon instance.
     */
    private function formatToTimeOnly($timeValue): ?string
    {
        if (empty($timeValue)) {
            return null;
        }
        try {
            // If it's already a Carbon instance, format it.
            // If it's a string, parse it then format it.
            return Carbon::parse($timeValue)->format('H:i:s');
        } catch (\Exception $e) {
            // If parsing fails, it might not be a valid time string or Carbon object.
            // We can try a more direct regex if it's expected to be a simple string like "HH:MM:SS"
            // or "YYYY-MM-DD HH:MM:SS".
            if (is_string($timeValue) && preg_match('/\d{2}:\d{2}:\d{2}/', $timeValue, $matches)) {
                return $matches[0];
            }
            $this->warn("Could not format value to time only: " . (is_object($timeValue) ? get_class($timeValue) : $timeValue));
            return null; // Or handle error as appropriate
        }
    }

    public function handle()
    {
        $timezoneSpain = 'Europe/Madrid';
        $nowSpain = Carbon::now($timezoneSpain);
        $this->info("Current Spain time ({$timezoneSpain}): " . $nowSpain->toDateTimeString());

        // --- Marcar carreras como 'In Progress' o 'Live' desde FP1 ---
        $racesToStartWeekend = Race::where('status', 'Scheduled')->get();

        foreach ($racesToStartWeekend as $race) {
            $raceIdForLog = $race->race_id ?? 'N/A';
            $this->info("Checking race '{$race->name}' (ID: {$raceIdForLog}) for FP1 start...");

            if (empty($race->practice1_date) || empty($race->practice1_time)) {
                $this->warn("Race '{$race->name}' (ID: {$raceIdForLog}) is missing practice1_date or practice1_time. Skipping for 'In Progress' check based on FP1.");
                continue;
            }

            $fp1StartDateTimeString = null; // Initialize for catch block
            try {
                // Asegurarse de que practice1_date solo sea la parte de la fecha
                $datePart = Carbon::parse($race->practice1_date)->format('Y-m-d');
                
                // --- INICIO CAMBIO PARA TIME ---
                // Asegurarse de que practice1_time solo sea la parte de la hora
                $timePart = $this->formatToTimeOnly($race->practice1_time);
                if (!$timePart) {
                    $this->error("Could not extract time from practice1_time for race '{$race->name}' (ID: {$raceIdForLog}). Value: " . $race->practice1_time);
                    continue;
                }
                // --- FIN CAMBIO PARA TIME ---
                
                $fp1StartDateTimeString = $datePart . ' ' . $timePart;
                
                $this->info("Attempting to parse FP1 datetime string for race '{$race->name}' (ID: {$raceIdForLog}): '{$fp1StartDateTimeString}'");
                $fp1StartDateTimeSpain = Carbon::parse($fp1StartDateTimeString, $timezoneSpain);

            } catch (\Exception $e) {
                $this->error("Error parsing FP1 datetime for race '{$race->name}' (ID: {$raceIdForLog}) from '{$fp1StartDateTimeString}': " . $e->getMessage());
                continue;
            }

            if ($nowSpain->gte($fp1StartDateTimeSpain)) {
                $race->status = 'In Progress';
                $race->save();
                $this->info("Race '{$race->name}' (ID: {$raceIdForLog}) marked as 'In Progress' (Weekend Started). FP1 Start: " . $fp1StartDateTimeSpain->toDateTimeString());
            } else {
                $this->line("--- Race '{$race->name}' (ID: {$raceIdForLog}) ---");
                $this->info("  FP1 Start (Spain TZ): " . $fp1StartDateTimeSpain->toDateTimeString());
                $this->info("  Current Spain Time: " . $nowSpain->toDateTimeString());
                $this->info("  Weekend not yet started (based on FP1).");
                $this->line("-------------------------------------------");
            }
        }

        // --- Marcar carreras como 'Completed' ---
        $racesInProgress = Race::whereIn('status', ['In Progress', 'Live'])->get();

        foreach ($racesInProgress as $race) {
            $raceIdForLog = $race->id ?? 'N/A'; // Usar $race->id
            $this->info("Checking race '{$race->name}' (ID: {$raceIdForLog}) for completion...");

            if (empty($race->race_date) || empty($race->start_time)) {
                $this->warn("Race '{$race->name}' (ID: {$raceIdForLog}) is 'In Progress' but missing race_date or start_time. Skipping for 'Completed' check.");
                continue;
            }
            
            $raceStartDateTimeString = null; // Initialize for catch block
            try {
                $raceDatePart = Carbon::parse($race->race_date)->format('Y-m-d');
                
                // --- INICIO CAMBIO PARA TIME ---
                $raceTimePart = $this->formatToTimeOnly($race->start_time);
                if (!$raceTimePart) {
                    $this->error("Could not extract time from start_time for race '{$race->name}' (ID: {$raceIdForLog}). Value: " . $race->start_time);
                    continue;
                }
                // --- FIN CAMBIO PARA TIME ---
                
                $raceStartDateTimeString = $raceDatePart . ' ' . $raceTimePart;

                $this->info("Attempting to parse Race datetime string for race '{$race->name}' (ID: {$raceIdForLog}): '{$raceStartDateTimeString}'");
                $raceStartDateTimeSpain = Carbon::parse($raceStartDateTimeString, $timezoneSpain);

            } catch (\Exception $e) {
                $this->error("Error parsing Race datetime for race '{$race->name}' (ID: {$raceIdForLog}) from '{$raceStartDateTimeString}': " . $e->getMessage());
                continue;
            }

            $estimatedRaceEndDateTimeSpain = $raceStartDateTimeSpain->copy()->addHours(2);

            if ($nowSpain->gte($estimatedRaceEndDateTimeSpain)) {
                $race->status = 'Completed';
                $race->save();
                $this->info("Race '{$race->name}' (ID: {$raceIdForLog}) marked as 'Completed'. Race Start: " . $raceStartDateTimeSpain->toDateTimeString() . ", Estimated End (+2h): " . $estimatedRaceEndDateTimeSpain->toDateTimeString());
            } else {
                $this->line("--- Race '{$race->name}' (ID: {$raceIdForLog}) ---");
                $this->info("  Race Start (Spain TZ): " . $raceStartDateTimeSpain->toDateTimeString());
                $this->info("  Estimated End +2h (Spain TZ): " . $estimatedRaceEndDateTimeSpain->toDateTimeString());
                $this->info("  Current Spain Time: " . $nowSpain->toDateTimeString());
                $this->info("  Race not yet completed (based on race start + 2 hours).");
                $this->line("-------------------------------------------");
            }
        }

        $this->info('Detailed race status update process completed (FP1 & Race End).');
        return Command::SUCCESS;
    }
}
