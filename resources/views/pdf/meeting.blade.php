<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Compte rendu de réunion - {{ $meeting->title }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 40px;
        }
        h1 {
            color: #0091CD;
            border-bottom: 2px solid #0091CD;
            padding-bottom: 10px;
        }
        h2 {
            color: #444;
            margin-top: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .meta-info {
            margin-bottom: 30px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
        .meta-info p {
            margin: 5px 0;
        }
        .badge {
            background: #eee;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.9em;
            margin-right: 5px;
        }
        ul {
            padding-left: 20px;
        }
        li {
            margin-bottom: 10px;
        }
        .action-item {
            margin-bottom: 15px;
            padding: 10px;
            border-left: 4px solid #0091CD;
            background: #f4fbfe;
        }
        .action-converted {
            border-left-color: #16a34a;
            background: #f0fdf4;
        }
        .action-text {
            font-weight: bold;
        }
        .action-meta {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>

    <h1>{{ $meeting->title }}</h1>

    <div class="meta-info">
        <p><strong>Date :</strong> {{ \Carbon\Carbon::parse($meeting->date)->isoFormat('D MMMM YYYY') }}</p>
        <p><strong>Participants :</strong>
            @if(!empty($meeting->attendees))
                @foreach($meeting->attendees as $attendee)
                    <span class="badge">{{ $attendee }}</span>
                @endforeach
            @else
                <em>Aucun participant renseigné</em>
            @endif
        </p>
    </div>

    <h2>Points abordés</h2>
    @if(!empty($meeting->bilan))
        <ul>
            @foreach($meeting->bilan as $point)
                @if(trim($point) !== '')
                    <li>{{ $point }}</li>
                @endif
            @endforeach
        </ul>
    @else
        <p><em>Aucun point renseigné.</em></p>
    @endif

    <h2>Recommandations</h2>
    @if(!empty($meeting->recommendations))
        <ul>
            @foreach($meeting->recommendations as $rec)
                @if(trim($rec) !== '')
                    <li>{{ $rec }}</li>
                @endif
            @endforeach
        </ul>
    @else
        <p><em>Aucune recommandation renseignée.</em></p>
    @endif

    <h2>Actions attendues</h2>
    @if(!empty($meeting->actions))
        @foreach($meeting->actions as $action)
            @if(trim($action['text'] ?? '') !== '')
                <div class="action-item {{ ($action['converted'] ?? false) ? 'action-converted' : '' }}">
                    <div class="action-text">{{ $action['text'] }}</div>
                    <div class="action-meta">
                        @if(!empty($action['assignee_id']))
                            Assigné à : {{ \App\Models\User::find($action['assignee_id'])?->name ?? 'Inconnu' }}
                        @else
                            Non assigné
                        @endif
                        @if(!empty($action['deadline']))
                            | Échéance : {{ \Carbon\Carbon::parse($action['deadline'])->isoFormat('D MMM YYYY') }}
                        @endif
                        @if($action['converted'] ?? false)
                            | <strong>Tâche créée</strong>
                        @endif
                    </div>
                </div>
            @endif
        @endforeach
    @else
        <p><em>Aucune action attendue renseignée.</em></p>
    @endif

</body>
</html>
