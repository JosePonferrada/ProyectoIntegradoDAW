<?php

namespace App\Http\Controllers;

use App\Models\Race;
use Illuminate\Http\Response;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function downloadIcs(Race $race)
    {
        // Obtenemos la hora desde race_date y start_time, similar a como lo hace el componente Vue
        $datePart = substr($race->race_date, 0, 10); // YYYY-MM-DD
        
        // Verificar si tenemos start_time o usamos otro campo disponible para la hora
        if ($race->start_time) {
            $timePart = substr($race->start_time, 11, 5); // HH:MM
        } elseif ($race->race_time) {
            $timePart = $race->race_time;
        } else {
            $timePart = '15:00'; // Hora por defecto si no hay datos
        }
        
        // Crear objeto DateTime con la fecha y hora correctas
        $raceDateTime = new \DateTime("$datePart $timePart");
        
        // Fin del evento (2 horas después)
        $endDateTime = clone $raceDateTime;
        $endDateTime->modify('+2 hours');
        
        // Formatear fechas para ICS (según el estándar iCalendar)
        $now = date('Ymd\THis\Z');
        $start = $raceDateTime->format('Ymd\THis');
        $end = $endDateTime->format('Ymd\THis');
        
        // Zona horaria
        $timezone = $raceDateTime->getTimezone()->getName();
        
        // Generar archivo ICS
        $content = "BEGIN:VCALENDAR\r\n";
        $content .= "VERSION:2.0\r\n";
        $content .= "PRODID:-//Formula 1 App//EN\r\n";
        $content .= "CALSCALE:GREGORIAN\r\n";
        $content .= "METHOD:PUBLISH\r\n";
        
        // Definir la zona horaria en el archivo
        $content .= "BEGIN:VTIMEZONE\r\n";
        $content .= "TZID:$timezone\r\n";
        $content .= "END:VTIMEZONE\r\n";
        
        // Definir el evento
        $content .= "BEGIN:VEVENT\r\n";
        $content .= "DTSTAMP:$now\r\n";
        $content .= "DTSTART;TZID=$timezone:$start\r\n";
        $content .= "DTEND;TZID=$timezone:$end\r\n";
        $content .= "SUMMARY:Formula 1: {$race->name}\r\n";
        $content .= "DESCRIPTION:Formula 1 Grand Prix race\r\n";
        
        // Añadir ubicación si está disponible
        if ($race->circuit) {
            $location = $race->circuit->name;
            if ($race->circuit->location) {
                $location .= ", " . $race->circuit->location;
            }
            $content .= "LOCATION:$location\r\n";
        }
        
        $content .= "END:VEVENT\r\n";
        $content .= "END:VCALENDAR\r\n";
        
        // Generar nombre del archivo
        $filename = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $race->name)) . '.ics';
        
        return response($content)
            ->withHeaders([
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
    }
}
