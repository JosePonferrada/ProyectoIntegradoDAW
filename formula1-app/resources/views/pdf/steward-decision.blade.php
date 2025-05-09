<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #000;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .header img {
            height: 50px;
            margin-bottom: 10px;
        }
        .header-text {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .grand-prix-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .grand-prix-dates {
            font-size: 14px;
            margin-bottom: 15px;
        }
        .document-info {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 10px 0;
        }
        .document-info-item {
            width: 50%;
        }
        .label {
            font-weight: bold;
        }
        .decision-details {
            margin-top: 20px;
        }
        .decision-row {
            display: flex;
            margin-bottom: 10px;
        }
        .decision-label {
            font-weight: bold;
            width: 100px;
        }
        .decision-content {
            flex: 1;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
            font-weight: bold;
        }
        .signature-item {
            text-align: center;
            width: 24%;
        }
        .rule-notice {
            margin-top: 20px;
            font-size: 12px;
        }
        .divider {
            border-bottom: 1px solid #000;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ storage_path('app/public/images/fia-f1-logo.png') }}" alt="FIA F1 Logo">
        <div class="header-text">FIA FORMULA 1 WORLD CHAMPIONSHIP</div>
        <div class="grand-prix-title">{{ $race->year }} {{ strtoupper($race->name) }} GRAND PRIX</div>
        <div class="grand-prix-dates">{{ \Carbon\Carbon::parse($race->race_date)->format('d') }} - {{ \Carbon\Carbon::parse($race->race_date)->format('d') }} {{ \Carbon\Carbon::parse($race->race_date)->format('F Y') }}</div>
    </div>

    <div class="document-info">
        <div class="document-info-item">
            <div><span class="label">From</span> The Stewards</div>
            <div><span class="label">To</span> 
                @if(isset($team))
                    The Team Manager, {{ $team->name }}
                @elseif(isset($driver))
                    Driver #{{ $driver->number }} - {{ $driver->full_name }}
                @else
                    All Teams and Officials
                @endif
            </div>
        </div>
        <div class="document-info-item" style="text-align: left;">
            <div><span class="label">Document</span> {{ $documentId }}</div>
            <div><span class="label">Date</span> {{ now()->format('d F Y') }}</div>
            <div><span class="label">Time</span> {{ now()->format('H:i') }}</div>
        </div>
    </div>

    <div class="decision-details">
        <p>
            The Stewards, having received a report from the Race Director,
            @if(isset($driver) || isset($team))
                summoned and heard from the 
                @if(isset($driver))driver @endif
                @if(isset($driver) && isset($team))and @endif
                @if(isset($team))team representative @endif,
            @endif
            have considered the following matter and determine the following:
        </p>

        <div class="decision-row">
            <div class="decision-label">No / Driver</div>
            <div class="decision-content">
                @if(isset($driver))
                    {{ $driver->number }} - {{ $driver->full_name }}
                @else
                    -
                @endif
            </div>
        </div>

        <div class="decision-row">
            <div class="decision-label">Competitor</div>
            <div class="decision-content">
                @if(isset($team))
                    {{ $team->name }}
                @else
                    -
                @endif
            </div>
        </div>

        <div class="decision-row">
            <div class="decision-label">Time</div>
            <div class="decision-content">{{ $incident_time ?? now()->format('H:i') }}</div>
        </div>

        <div class="decision-row">
            <div class="decision-label">Session</div>
            <div class="decision-content">{{ $session_type ?? 'Race' }}</div>
        </div>

        <div class="decision-row">
            <div class="decision-label">Fact</div>
            <div class="decision-content">{{ $title }}</div>
        </div>

        <div class="decision-row">
            <div class="decision-label">Infringement</div>
            <div class="decision-content">{{ $infringement_article ?? '' }}</div>
        </div>

        <div class="decision-row">
            <div class="decision-label">Decision</div>
            <div class="decision-content">
                @switch($type)
                    @case('penalty')
                        Time Penalty - {{ $penalty_time ?? '5 seconds' }}
                        @break
                    @case('reprimand')
                        Reprimand
                        @break
                    @case('fine')
                        Fine - {{ $fine_amount ?? '€0' }}
                        @break
                    @case('disqualification')
                        Disqualification
                        @break
                    @default
                        Warning
                @endswitch
            </div>
        </div>

        <div class="decision-row">
            <div class="decision-label">Reason</div>
            <div class="decision-content">{!! nl2br(e($content)) !!}</div>
        </div>

        <div class="rule-notice">
            Competitors are reminded that they have the right to appeal certain decisions of the Stewards, in accordance with Article 15 of the FIA International Sporting Code and Chapter 4 of the FIA Judicial and Disciplinary Rules, within the applicable time limits.
        </div>
    </div>

    <div class="signatures">
        <div class="signature-item">{{ $steward1_name ?? 'FIA Steward' }}</div>
        <div class="signature-item">{{ $steward2_name ?? 'FIA Steward' }}</div>
        <div class="signature-item">{{ $steward3_name ?? 'The Stewards' }}</div>
        <div class="signature-item">{{ $steward4_name ?? 'FIA Steward' }}</div>
    </div>
</body>
</html>