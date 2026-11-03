<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Groepsdashboard - Red de Żabka-kikker</title>
    <style>
        :root {
            --bg: #fdfdfd;
            --card: #ffffff;
            --line: #c8e5d3;
            --text: #1f2a44;
            --muted: #5774a8;
            --orange: #007b3d;
            --orange-soft: #e5f5e9;
            --blue: #006b35;
            --red: #a72335;
            --green: #2a6b45;
        }

        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 16px;
            font-family: "Avenir Next", "Trebuchet MS", "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 78% 8%, rgba(0,123,61,.14), transparent 36%),
                radial-gradient(circle at 12% 92%, rgba(49,91,156,.12), transparent 40%),
                var(--bg);
        }

        .shell { width: min(1040px, 100%); margin: 0 auto; }
        .card {
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--card);
            padding: 18px;
            margin-bottom: 14px;
            box-shadow: 0 8px 24px rgba(21,34,58,.06);
        }

        .muted { color: var(--muted); }
        .ok { color: var(--green); }
        .bad { color: var(--red); }

        h1, h2, h3 { margin-top: 0; }

        .bar {
            background: #edf2fb;
            border: 1px solid #d6e0f2;
            border-radius: 999px;
            height: 12px;
            overflow: hidden;
        }

        .bar > span {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, #008f45, #007b3d);
        }

        input, textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #c8d5ee;
            border-radius: 10px;
            padding: 10px;
            background: #fff;
            color: var(--text);
            margin-bottom: 12px;
        }

        .btn, .btn-secondary {
            border: 0;
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn { background: linear-gradient(135deg, #008f45, #007b3d); color: #ffffff; }
        .btn-secondary { background: #edf2fb; color: #005a2d; border: 1px solid #c8d5ee; }
        .btn[disabled] { opacity: .45; cursor: not-allowed; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .grid-2 .card, .grid-3 .card { margin-bottom: 0; }
        .inline { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }

        .task-shell {
            min-height: auto;
            display: block;
            padding: 0;
        }

        .task-card {
            animation: task-enter .45s ease-out;
        }

        .task-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .task-block {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fff;
            padding: 14px;
            margin-top: 12px;
        }

        .task-block.location {
            background: #f7fbff;
            border-color: #c8d5ee;
        }

        .task-block.question {
            background: #fff;
        }

        @keyframes task-enter {
            from {
                opacity: 0;
                transform: translateY(14px) scale(.99);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid #c8e5d3;
            background: var(--orange-soft);
            color: #28573a;
            font-size: 12px;
            font-weight: 700;
        }

        .player-popup {
            position: fixed;
            inset: 0;
            background: rgba(20, 26, 44, .42);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1400;
            padding: 16px;
        }

        .player-popup.show {
            display: flex;
        }

        .player-popup-card {
            width: min(520px, 100%);
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 12px 28px rgba(21,34,58,.16);
        }

        .confetti-layer {
            position: fixed;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
            z-index: 1500;
        }

        .confetti-piece {
            position: absolute;
            top: -24px;
            width: 10px;
            height: 16px;
            opacity: .95;
            animation: confetti-fall linear forwards;
            transform-origin: center;
        }

        .confetti-piece.cannon-left,
        .confetti-piece.cannon-right {
            top: auto;
            bottom: -20px;
            width: 9px;
            height: 14px;
        }

        .confetti-piece.cannon-left {
            left: 16px;
            animation-name: confetti-blast-left;
            animation-timing-function: cubic-bezier(.2,.85,.28,1);
        }

        .confetti-piece.cannon-right {
            right: 16px;
            animation-name: confetti-blast-right;
            animation-timing-function: cubic-bezier(.2,.85,.28,1);
        }

        @keyframes confetti-fall {
            0% {
                transform: translateY(-20px) rotate(0deg);
            }
            100% {
                transform: translateY(110vh) rotate(720deg);
                opacity: .9;
            }
        }

        @keyframes confetti-blast-left {
            0% {
                transform: translate(0, 0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translate(var(--x, 220px), calc(-1 * var(--y, 420px))) rotate(900deg);
                opacity: 0;
            }
        }

        @keyframes confetti-blast-right {
            0% {
                transform: translate(0, 0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translate(calc(-1 * var(--x, 220px)), calc(-1 * var(--y, 420px))) rotate(-900deg);
                opacity: 0;
            }
        }

        .success-overlay {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1550;
            pointer-events: none;
            background: radial-gradient(circle at center, rgba(73, 183, 122, .28), rgba(73, 183, 122, .08) 45%, transparent 70%);
            backdrop-filter: blur(1px);
        }

        .success-overlay.show {
            display: flex;
            animation: success-fade 3.6s ease-out forwards;
        }

        .success-card {
            width: min(360px, calc(100% - 40px));
            border: 1px solid #91d9ab;
            border-radius: 16px;
            background: #f2fff7;
            padding: 18px;
            text-align: center;
            box-shadow: 0 14px 28px rgba(32, 107, 69, .16);
            animation: success-pop .45s ease-out;
        }

        .success-check {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            margin: 0 auto 10px;
            background: linear-gradient(145deg, #73d497, #43af72);
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 40px;
            font-weight: 900;
            box-shadow: 0 8px 18px rgba(39, 125, 78, .26);
            animation: check-bounce .6s ease;
        }

        @keyframes success-pop {
            from { transform: scale(.85); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        @keyframes check-bounce {
            0% { transform: scale(.4); }
            60% { transform: scale(1.12); }
            100% { transform: scale(1); }
        }

        @keyframes success-fade {
            0% { opacity: 0; }
            15% { opacity: 1; }
            78% { opacity: 1; }
            100% { opacity: 0; }
        }

        @media (max-width: 900px) {
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .task-shell { min-height: auto; }
            body {
                padding: 10px;
            }
        }
    </style>
<link rel="stylesheet" href="{{ asset('css/zabka.css').'?v='.filemtime(public_path('css/zabka.css')) }}">
</head>
<body>
<div class="shell">
    @php
        $isTaskModeView = request()->boolean('task') && $currentStep;
    @endphp

    @if(!$isTaskModeView && session('success'))
        <div class="card"><p class="ok">{{ session('success') }}</p></div>
    @endif

    @if(!$isTaskModeView && $errors->any())
        <div class="card"><p class="bad">{{ $errors->first() }}</p></div>
    @endif

    <div id="live-notice" class="card" style="display:none;"></div>

    @if($isTaskModeView)
        @php
            $mustHideQuestion = !$locationLocked;
            $startLat = $currentStep->start_location_lat ?? $currentStep->location_lat;
            $startLng = $currentStep->start_location_lng ?? $currentStep->location_lng;
            $questionType = (string) ($currentStep->question_type ?? 'direct');
            $isNumericInput = $questionType === 'numeric'
                || ($questionType === 'direct' && preg_match('/^-?\d+([.,]\d+)?$/', trim((string) ($currentStep->correct_answer ?? ''))));
            $multipleOptions = $currentStep->multiple_choice_json ?? [];
        @endphp
        <div class="task-shell">
            <div class="card task-card">
                <div class="task-heading">
                    <h2 style="margin:0;">Opdracht · {{ $currentStep->title }}</h2>
                    <a class="btn-secondary" href="{{ route('play.dashboard') }}">Terug naar dashboard</a>
                </div>
                <p class="muted" style="margin-top:6px;">{{ $group->name }} · stap {{ $progress->current_sort_order }}</p>
            </div>

            @if($activeTimeout || $errors->has('timeout'))
                @php
                    $timeoutText = $questionType === 'location_submit'
                        ? 'Je hebt niet de juiste locatie ingezonden. Je mag over'
                        : 'Je antwoord is niet juist. Je mag over';
                    $timeoutTail = $questionType === 'location_submit'
                        ? 'seconden een nieuwe locatie inzenden.'
                        : 'seconden opnieuw proberen.';
                @endphp
                <div id="timeout-card" class="card task-card" style="background:#fff1f1; border-color:#efb3b3;">
                    <p class="bad" style="margin:0;">
                        {{ $timeoutText }}
                        <strong id="timeout-remaining">{{ $timeoutRemainingSeconds }}</strong>
                        {{ $timeoutTail }}
                    </p>
                </div>
            @endif

            <div class="card task-card">
                <p style="margin-top:0;"><strong>Titel:</strong> {{ $currentStep->title }}</p>
                @if($locationLocked && filled($currentStep->story_intro))
                    <p style="margin-bottom:0;">{{ $currentStep->story_intro }}</p>
                @endif
            </div>

            @if($mustHideQuestion)
                <div class="card task-card" style="background:#f7fbff; border-color:#c8d5ee;">
                    <h3>Startlocatie</h3>
                    <p><strong>Ga nu naar:</strong> {{ $startLocationAddress ?: 'Locatie ontbreekt. Vraag de spelleiding om hulp.' }}</p>
                    @if($startLat && $startLng)
                        <p>
                            <a class="btn-secondary" href="https://www.google.com/maps/dir/?api=1&destination={{ $startLat }},{{ $startLng }}" target="_blank" rel="noopener noreferrer">
                                Open Google Maps
                            </a>
                        </p>
                    @endif

                    <button id="share-location-btn" type="button" class="btn-secondary">Mijn GPS-locatie ophalen</button>
                    <p id="location-status" role="status">Deel je locatie en bevestig die zodra je dichtbij bent.</p>
                    <p>Afstand tot locatie: <strong id="distance-meters">{{ $distanceMeters !== null ? $distanceMeters.' meter' : 'onbekend' }}</strong></p>
                    <p id="radius-state" class="{{ $isInRadius ? 'ok' : 'bad' }}">{{ $isInRadius ? 'Binnen radius' : 'Buiten radius' }}</p>
                    <button id="lock-location-btn" type="button" class="btn" {{ $isInRadius ? '' : 'disabled' }}>
                        {{ $locationLocked ? 'Locatie bevestigd' : 'Bevestig locatie' }}
                    </button>
                </div>

                <div class="card task-card">
                    <p class="bad" style="margin:0;">Vraag wordt pas zichtbaar nadat je binnen de radius bent en locatie bevestigt.</p>
                </div>
            @else
                <div class="card task-card">
                    <p style="margin-top:0;"><strong>Vraag:</strong><br>{{ $currentStep->question_text ?: '-' }}</p>
                    @if($currentStep->question_image)
                        <a href="{{ asset($currentStep->question_image) }}" target="_blank" rel="noopener">
                            <img src="{{ asset($currentStep->question_image) }}" alt="Puzzel bij {{ $currentStep->title }}; tik om te vergroten" style="max-width:100%;height:auto;border-radius:12px;">
                        </a>
                    @endif
                    @if($currentStep->location_lat && $currentStep->location_lng)
                        <p><a href="https://www.google.com/maps/dir/?api=1&destination={{ $currentStep->location_lat }},{{ $currentStep->location_lng }}" target="_blank" rel="noopener">Navigeer naar deze locatie</a></p>
                    @endif
                    @if($currentStep->extra_explanation)
                        <p><strong>Toelichting:</strong><br>{{ $currentStep->extra_explanation }}</p>
                    @endif

                    @if($questionType === 'location_submit')
                        <p class="muted">Gebruik de aanwijzingen in de opdracht en dien daarna je huidige locatie in.</p>
                    @endif

                    @if($currentStep->accepts_whatsapp_upload)
                        <p>
                            <a class="btn-secondary" href="https://wa.me/31640997119?text={{ urlencode('Inzending opdracht: '.$currentStep->title.' - groep '.$group->name) }}" target="_blank" rel="noopener noreferrer">
                                Verstuur beeldmateriaal via WhatsApp
                            </a>
                        </p>
                    @endif

                    @if($latestPendingSubmission)
                        <div class="card task-card" style="background:#f7fbff; border-color:#c8d5ee; margin-top: 12px; margin-bottom: 0;">
                            <p class="ok" style="margin:0;">Je inzending is verzonden en wacht op goedkeuring van de spelleiding.</p>
                        </div>
                    @else
                        <form method="POST" action="{{ route('play.submit') }}" enctype="multipart/form-data" id="task-submit-form" data-question-type="{{ $questionType }}">
                            @csrf
                            @if($currentStep->step_type === 'photo_upload')
                                <label for="photo">Foto van jullie opdracht (maximaal 10 MB)</label>
                                <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" required>
                            @endif
                            <input type="hidden" name="submit_latitude" id="submit-latitude">
                            <input type="hidden" name="submit_longitude" id="submit-longitude">
                            @if($questionType === 'multiple_choice')
                                <label for="submitted_choice">Jouw keuze</label>
                                <select id="submitted_choice" name="submitted_choice" style="width:100%; box-sizing:border-box; border:1px solid #c8d5ee; border-radius:10px; padding:10px; margin-bottom:12px;">
                                    <option value="">Kies een antwoord</option>
                                    @foreach($multipleOptions as $option)
                                        <option value="{{ $option }}" @selected(old('submitted_choice') === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            @elseif($questionType === 'location_submit')
                                <p class="muted">Je huidige locatie wordt als antwoord ingezonden zodra je op versturen drukt.</p>
                            @elseif($isNumericInput)
                                <label for="answer_text">Jouw numerieke antwoord</label>
                                <input id="answer_text" name="answer_text" type="number" step="any" inputmode="decimal" placeholder="Vul een getal in" value="{{ old('answer_text') }}">
                            @else
                                <label for="answer_text">Jouw antwoord</label>
                                <textarea id="answer_text" name="answer_text" rows="4" placeholder="Typ hier je antwoord...">{{ old('answer_text') }}</textarea>
                            @endif
                            <button type="submit" id="submit-answer-btn" class="btn" {{ $activeTimeout ? 'disabled' : '' }}>Verstuur antwoord</button>
                        </form>
                    @endif
                </div>
            @endif

            @if(!$activeTimeout && $errors->any() && !$errors->has('timeout'))
                <div class="card task-card">
                    <p class="bad" style="margin:0;">{{ $errors->first() }}</p>
                </div>
            @endif
        </div>
    @else
        <div class="card">
            <h1>{{ $group->game?->name ?? 'Red de Żabka-kikker' }} · Groep {{ $group->name }}</h1>
            <p class="muted">Voortgang: {{ $progressPercent }}% ({{ max(0, min($stepCount, $progress->current_sort_order - 1)) }}/{{ $stepCount }} opdrachten)</p>
            <div class="bar"><span style="width: {{ $progressPercent }}%;"></span></div>

            @if($currentStep)
                <p style="margin-top:12px;"><strong>Huidige opdracht:</strong> {{ $currentStep->title }}</p>
                <a class="btn" href="{{ route('play.dashboard', ['task' => 1]) }}">Opdracht</a>
            @elseif($isFinished)
                <p class="ok">Alle opdrachten afgerond.</p>
                <p>Jullie hebben alle bewijsstukken verzameld. Meld je bij de spelleiding!</p>
            @endif

            <p id="location-subtle-status" class="muted" style="margin-top: 10px; {{ $locationEnabled ? '' : 'display:none;' }}">Locatie delen actief.</p>
        </div>

        <div class="card">
            <h3>Jullie route door Wrocław</h3>
            <p>{{ $group->game?->case_briefing }}</p>
            <ol>
                @foreach($group->questSteps as $routeStep)
                    <li style="margin-bottom:8px;">
                        {{ $routeStep->pivot->sort_order < $progress->current_sort_order ? '✓ ' : '' }}{{ $routeStep->title }}
                        @if($routeStep->pivot->sort_order === $progress->current_sort_order)<strong> — jullie volgende stop</strong>@endif
                    </li>
                @endforeach
            </ol>
            <p>Na alle opdrachten: meld je bij de spelleiding.</p>
        </div>
        <div id="location-required-card" class="card" style="{{ $locationEnabled ? 'display:none;' : '' }}">
            <h3>Locatie aanzetten</h3>
            <p><a href="{{ route('location.test') }}">Test je GPS-locatie in Utrecht</a></p>
            <p class="muted">Deel je locatie zodat de spelleiding jullie kan volgen. Gebruik de navigatielink bij elke opdracht.</p>
            <button class="btn-secondary" id="share-location-btn" type="button">Locatie aanzetten</button>
            <p style="margin-top:10px;"><span id="location-enabled-pill" class="pill">{{ $locationEnabled ? 'Locatie aan' : 'Locatie uit' }}</span></p>
            <p id="location-status" class="muted" style="margin-top: 10px;"></p>
        </div>

        @if($isFinished && $hiddenLettersCount > 0)
            <div class="card">
                <h3>Letters</h3>
                @if(!empty($visibleLetters))
                    <p class="ok">Alle letters zijn nu zichtbaar:</p>
                    <p style="font-size:24px; font-weight:700; letter-spacing:.16em;">{{ implode('', $visibleLetters) }}</p>
                    <p class="muted">Deze letters vormen samen het eindwoord naar de locatie van de Żabka-kikker.</p>
                @else
                    <p><strong>Aantal verzamelde letters:</strong> {{ $hiddenLettersCount }}</p>
                @endif
            </div>
        @endif

        <div class="grid-2">
            <div class="card">
                <h3>Vrijgespeelde verhaallijn</h3>
                @if(empty($storyEntries))
                    <p class="muted">Nog geen verhaallijnfragmenten vrijgespeeld.</p>
                @else
                    <ul>
                        @foreach($storyEntries as $entry)
                            <li><strong>{{ $entry['title'] ?? 'Opdracht' }}:</strong> {{ $entry['story'] ?? '' }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @if(!$isFinished && $hiddenLettersCount > 0)
                <div class="card">
                    <h3>Letters</h3>
                    @if(!empty($visibleLetters))
                        <p class="ok">Alle letters zijn nu zichtbaar:</p>
                        <p style="font-size:24px; font-weight:700; letter-spacing:.16em;">{{ implode('', $visibleLetters) }}</p>
                        <p class="muted">Deze letters vormen samen het eindwoord naar de locatie van de Żabka-kikker.</p>
                    @else
                        <p><strong>Aantal verzamelde letters:</strong> {{ $hiddenLettersCount }}</p>
                    @endif
                </div>
            @endif
        </div>

        @if($messages->isNotEmpty())
            <div class="card">
                <h3>Berichten van de spelleiding</h3>
                <ul>
                    @foreach($messages as $message)
                        <li><strong>{{ $message->title ?: 'Melding' }}:</strong> {{ $message->message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif
</div>

<div id="player-message-popup" class="player-popup">
    <div class="player-popup-card">
        <h3 id="player-message-title" style="margin-top:0;">Bericht van de spelleiding</h3>
        <p id="player-message-text"></p>
        <div class="inline" style="justify-content:flex-end;">
            <button type="button" class="btn-secondary" id="player-message-close">Sluiten</button>
        </div>
    </div>
</div>

<div id="confetti-layer" class="confetti-layer" aria-hidden="true"></div>
<div id="success-overlay" class="success-overlay" aria-hidden="true">
    <div class="success-card">
        <div class="success-check">✓</div>
        <h3 style="margin: 0 0 4px;">Perfect!</h3>
        <p class="muted" style="margin:0;">Opdracht succesvol afgerond.</p>
    </div>
</div>

<script>
(() => {
    const isTaskMode = {{ request()->boolean('task') ? 'true' : 'false' }};
    const shareBtn = document.getElementById('share-location-btn');
    const statusEl = document.getElementById('location-status');

    const lockBtn = document.getElementById('lock-location-btn');
    const distanceEl = document.getElementById('distance-meters');
    const radiusStateEl = document.getElementById('radius-state');

    const timeoutRemainingEl = document.getElementById('timeout-remaining');
    const timeoutCardEl = document.getElementById('timeout-card');
    const submitAnswerBtnEl = document.getElementById('submit-answer-btn');
    const locationEnabledPillEl = document.getElementById('location-enabled-pill');
    const locationRequiredCardEl = document.getElementById('location-required-card');
    const locationSubtleStatusEl = document.getElementById('location-subtle-status');

    const notifyEl = document.getElementById('live-notice');
    const popupEl = document.getElementById('player-message-popup');
    const popupTitleEl = document.getElementById('player-message-title');
    const popupTextEl = document.getElementById('player-message-text');
    const popupCloseEl = document.getElementById('player-message-close');
    const confettiLayerEl = document.getElementById('confetti-layer');
    const successOverlayEl = document.getElementById('success-overlay');
    const taskSubmitFormEl = document.getElementById('task-submit-form');
    const submitLatEl = document.getElementById('submit-latitude');
    const submitLngEl = document.getElementById('submit-longitude');
    const taskQuestionType = taskSubmitFormEl?.dataset?.questionType || '';
    const isFinished = {{ $isFinished ? 'true' : 'false' }};
    const shouldCelebrateSuccess = {{ session('celebrate_success') ? 'true' : 'false' }};

    let waitingManualReview = {{ $latestPendingSubmission ? 'true' : 'false' }};
    let currentSortOrder = {{ (int) $progress->current_sort_order }};
    let localTimeoutRemaining = {{ (int) $timeoutRemainingSeconds }};
    let localTimeoutActive = {{ $activeTimeout ? 'true' : 'false' }};
    const seenMessageIds = new Set(JSON.parse(window.sessionStorage.getItem('seen_player_messages') || '[]'));

    const setStatus = (msg, error = false) => {
        if (!statusEl) return;
        statusEl.textContent = msg;
        statusEl.className = error ? 'bad' : 'muted';
    };

    const setLocationEnabled = (enabled) => {
        if (locationEnabledPillEl) {
            locationEnabledPillEl.textContent = enabled ? 'Locatie aan' : 'Locatie uit';
            locationEnabledPillEl.className = `pill ${enabled ? 'ok' : ''}`;
        }
        if (locationRequiredCardEl) locationRequiredCardEl.style.display = enabled ? 'none' : 'block';
        if (locationSubtleStatusEl) locationSubtleStatusEl.style.display = enabled ? 'block' : 'none';
    };

    const rememberSeen = () => {
        window.sessionStorage.setItem('seen_player_messages', JSON.stringify([...seenMessageIds]));
    };

    const showPopup = (title, text) => {
        if (!popupEl || !popupTextEl || !popupTitleEl) return;
        popupTitleEl.textContent = title || 'Bericht van de spelleiding';
        popupTextEl.textContent = text || '';
        popupEl.classList.add('show');
    };

    const applyTimeoutState = (remaining, active = null) => {
        localTimeoutRemaining = Math.max(0, Number(remaining || 0));
        if (active === null) {
            localTimeoutActive = localTimeoutRemaining > 0;
        } else {
            localTimeoutActive = Boolean(active) && localTimeoutRemaining > 0;
        }
        if (timeoutRemainingEl) {
            timeoutRemainingEl.textContent = String(localTimeoutRemaining);
        }
        if (submitAnswerBtnEl) {
            submitAnswerBtnEl.disabled = localTimeoutActive;
        }
        if (timeoutCardEl) {
            timeoutCardEl.style.display = localTimeoutActive ? 'block' : 'none';
        }
    };

    applyTimeoutState(localTimeoutRemaining);

    const postLocation = (lat, lng) => fetch('{{ route('play.location') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ latitude: lat, longitude: lng }),
    }).then((r) => {
        if (r.status === 401) {
            window.location.href = '{{ route('landing') }}';
            return { ok: false };
        }
        return r.json();
    });

    const refreshGeo = (silent = false) => {
        if (!navigator.geolocation) {
            if (!silent) setStatus('Geolocatie wordt niet ondersteund.', true);
            return;
        }

        navigator.geolocation.getCurrentPosition((pos) => {
            postLocation(pos.coords.latitude, pos.coords.longitude)
                .then((data) => {
                    setLocationEnabled(!!data.ok);
                    if (!silent) setStatus(data.ok ? 'Locatie staat aan.' : 'Locatie aanzetten mislukt.', !data.ok);
                })
                .catch(() => {
                    setLocationEnabled(false);
                    if (!silent) setStatus('Locatie aanzetten mislukt.', true);
                });
        }, () => {
            setLocationEnabled(false);
            if (!silent) setStatus('Toestemming voor locatie geweigerd.', true);
        }, {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 15000,
        });
    };

    shareBtn?.addEventListener('click', () => refreshGeo(false));
    refreshGeo(true);
    setInterval(() => refreshGeo(true), 8000);

    lockBtn?.addEventListener('click', () => {
        if (!navigator.geolocation) { setStatus('Je browser ondersteunt geen GPS.', true); return; }
        lockBtn.disabled = true;
        navigator.geolocation.getCurrentPosition(async (pos) => {
            try {
                await postLocation(pos.coords.latitude, pos.coords.longitude);
                const response = await fetch('{{ route('play.location.lock') }}', {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                });
                const data = await response.json();
                if (data.ok) { window.location.reload(); return; }
                setStatus(data.message || 'Locatie bevestigen mislukt.', true);
            } catch { setStatus('Locatie bevestigen mislukt. Probeer opnieuw.', true); }
            lockBtn.disabled = false;
        }, () => { lockBtn.disabled = false; setStatus('Locatie niet beschikbaar. Geef GPS-toestemming en probeer opnieuw.', true); }, {enableHighAccuracy:true, maximumAge:0, timeout:15000});
    });

    const pollLive = () => {
        fetch('{{ route('play.live') }}')
            .then((r) => {
                if (r.status === 401) {
                    window.location.href = '{{ route('landing') }}';
                    return null;
                }
                return r.json();
            })
            .then((data) => {
                if (!data || !data.ok) return;
                if (isTaskMode && taskSubmitFormEl && !data.location_locked) { window.location.reload(); return; }

                if (distanceEl && typeof data.distance_meters !== 'undefined' && data.distance_meters !== null) {
                    distanceEl.textContent = `${data.distance_meters} meter`;
                }
                if (radiusStateEl) {
                    radiusStateEl.textContent = data.is_in_radius ? 'Binnen radius' : 'Buiten radius';
                    radiusStateEl.className = data.is_in_radius ? 'ok' : 'bad';
                }
                if (lockBtn && !data.location_locked) {
                    lockBtn.disabled = !data.is_in_radius;
                }

                if (typeof data.remaining_timeout_seconds !== 'undefined') {
                    applyTimeoutState(data.remaining_timeout_seconds, data.active_timeout);
                }

                if (Number(data.current_sort_order) > Number(currentSortOrder)) {
                    window.location.reload();
                    return;
                }

                if (waitingManualReview && Number(data.pending_current_count) === 0) {
                    window.location.reload();
                }

                if (Array.isArray(data.messages)) {
                    const newest = data.messages.find((message) => !seenMessageIds.has(Number(message.id)));
                    if (newest) {
                        seenMessageIds.add(Number(newest.id));
                        rememberSeen();
                        showPopup(newest.title || 'Bericht van de spelleiding', newest.message || '');
                    }
                }
            })
            .catch(() => {});
    };

    setInterval(pollLive, 3000);

    const tickTimeout = () => {
        fetch('{{ route('play.timeout.tick') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        })
            .then((r) => r.json())
            .then((data) => {
                if (!data || !data.ok) return;
                applyTimeoutState(data.remaining_seconds ?? 0, data.active_timeout);
            })
            .catch(() => {});
    };

    const pauseTimeout = () => {
        fetch('{{ route('play.timeout.pause') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        }).catch(() => {});
    };

    window.setInterval(() => {
        if (document.visibilityState === 'visible') {
            tickTimeout();
        }
    }, 1000);

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState !== 'visible') pauseTimeout();
    });
    window.addEventListener('beforeunload', pauseTimeout);

    popupCloseEl?.addEventListener('click', () => popupEl?.classList.remove('show'));
    popupEl?.addEventListener('click', (event) => {
        if (event.target === popupEl) popupEl.classList.remove('show');
    });

    taskSubmitFormEl?.addEventListener('submit', (event) => {
        if (taskQuestionType !== 'location_submit') return;

        event.preventDefault();
        if (!navigator.geolocation) {
            setStatus('Geolocatie wordt niet ondersteund.', true);
            return;
        }

        navigator.geolocation.getCurrentPosition((pos) => {
            if (submitLatEl) submitLatEl.value = String(pos.coords.latitude);
            if (submitLngEl) submitLngEl.value = String(pos.coords.longitude);
            taskSubmitFormEl.submit();
        }, () => {
            setStatus('Locatie kon niet worden opgehaald. Zet locatie aan en probeer opnieuw.', true);
        }, {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 12000,
        });
    });

    const runConfettiRain = () => {
        if (!confettiLayerEl) return;
        const colors = ['#007b3d', '#006b35', '#354b40', '#b0e18c', '#79c493'];
        const total = window.innerWidth < 720 ? 90 : 180;

        for (let i = 0; i < total; i += 1) {
            const piece = document.createElement('span');
            piece.className = 'confetti-piece';
            piece.style.left = `${Math.random() * 100}%`;
            piece.style.background = colors[Math.floor(Math.random() * colors.length)];
            piece.style.width = `${6 + Math.random() * 8}px`;
            piece.style.height = `${10 + Math.random() * 12}px`;
            piece.style.animationDuration = `${2.8 + Math.random() * 2.2}s`;
            piece.style.animationDelay = `${Math.random() * 1.2}s`;
            confettiLayerEl.appendChild(piece);
        }

        window.setTimeout(() => {
            confettiLayerEl.innerHTML = '';
        }, 7000);
    };

    const runConfettiCannons = () => {
        if (!confettiLayerEl) return;
        const colors = ['#007b3d', '#006b35', '#354b40', '#b0e18c', '#79c493', '#49b77a'];
        const burst = window.innerWidth < 720 ? 70 : 130;

        for (let i = 0; i < burst; i += 1) {
            const side = i % 2 === 0 ? 'left' : 'right';
            const piece = document.createElement('span');
            piece.className = `confetti-piece cannon-${side}`;
            piece.style.background = colors[Math.floor(Math.random() * colors.length)];
            piece.style.width = `${6 + Math.random() * 7}px`;
            piece.style.height = `${8 + Math.random() * 10}px`;
            piece.style.setProperty('--x', `${120 + Math.random() * 360}px`);
            piece.style.setProperty('--y', `${180 + Math.random() * 480}px`);
            piece.style.animationDuration = `${1.4 + Math.random() * 1.2}s`;
            piece.style.animationDelay = `${Math.random() * 0.26}s`;
            confettiLayerEl.appendChild(piece);
        }

        window.setTimeout(() => {
            confettiLayerEl.innerHTML = '';
        }, 4200);
    };

    const runSuccessCelebration = () => {
        if (successOverlayEl) {
            successOverlayEl.classList.add('show');
            window.setTimeout(() => successOverlayEl.classList.remove('show'), 3800);
        }
        runConfettiCannons();
    };

    if (isFinished && !window.sessionStorage.getItem('confetti_finished_shown')) {
        runConfettiRain();
        window.sessionStorage.setItem('confetti_finished_shown', '1');
    }

    if (shouldCelebrateSuccess) {
        runSuccessCelebration();
    }
})();
</script>
</body>
</html>
