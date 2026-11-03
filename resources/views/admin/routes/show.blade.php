@extends('layouts.admin')

@section('content')
    <h1>Groep {{ $route->name }}</h1>

    <div class="card" style="margin-bottom: 20px;">
        <p><strong>Onderzoek:</strong> {{ $route->game?->name ?? '-' }}</p>
        <p><strong>Omschrijving:</strong> {{ $route->description ?: '-' }}</p>
        <p><strong>Voortgang:</strong> <span id="live-progress">{{ $progressPercent }}% ({{ $completedSteps }}/{{ $totalSteps }})</span></p>
        <p><strong>Groepstoken:</strong> <code>{{ $route->join_token }}</code></p>
        <p><strong>Join-link:</strong> <a href="{{ route('join.with-token', $route->join_token) }}" target="_blank" rel="noopener noreferrer">{{ route('join.with-token', $route->join_token) }}</a></p>

        <div class="actions" style="margin-top: 12px;">
            <button type="button" class="btn-secondary" id="open-qr-modal">QR-code</button>
            <a class="btn-secondary" href="{{ route('admin.routes.qr', $route) }}" target="_blank" rel="noopener noreferrer">QR pagina</a>
            <form method="POST" action="{{ route('admin.routes.regenerate-token', $route) }}" onsubmit="return confirm('Nieuwe token voor deze groep genereren? Oude QR-link werkt dan niet meer.');">
                @csrf
                <button type="submit" class="btn-secondary">Vernieuw token groep</button>
            </form>
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h2>Live groepslocatie</h2>
        <p><strong>Laatste locatie:</strong> <span id="live-location">{{ $progress->last_latitude && $progress->last_longitude ? $progress->last_latitude . ', ' . $progress->last_longitude : '-' }}</span></p>
        <p><strong>Laatst gezien:</strong> <span id="live-seen-at">{{ $progress->last_seen_at?->format('d-m-Y H:i:s') ?? '-' }}</span></p>
        <p class="muted" id="live-location-empty" @if($progress->last_latitude && $progress->last_longitude) style="display:none;" @endif>De groep heeft nog geen locatie gedeeld.</p>
        <div id="group-map" style="height: 320px; border-radius: 12px; border:1px solid var(--line); @if(!($progress->last_latitude && $progress->last_longitude)) display:none; @endif"></div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h2>Berichtencentrum</h2>
        <form method="POST" action="{{ route('admin.routes.messages.post', $route) }}">
            @csrf

            <label>Doelgroep</label>
            <select name="audience" required>
                <option value="group">Alleen deze groep</option>
                <option value="game">Alle groepen binnen dit onderzoek</option>
                <option value="all_active">Iedereen met actieve sessie</option>
            </select>

            <label>Titel (optioneel)</label>
            <input type="text" name="title" value="{{ old('title') }}">

            <label>Bericht</label>
            <textarea name="message" rows="4" required>{{ old('message') }}</textarea>

            <label>Vervalt op (optioneel)</label>
            <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}">

            <button type="submit" class="btn">Verstuur bericht</button>
        </form>

        <hr style="border-color: var(--line); margin: 24px 0;">
        <h3>Actieve berichten</h3>

        @if($activeMessages->isEmpty())
            <p>Geen actieve berichten.</p>
        @else
            <ul>
                @foreach($activeMessages as $message)
                    <li>
                        <strong>{{ $message->title ?: 'Melding' }}</strong>
                        ({{ $message->audience }})
                        : {{ $message->message }}
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h2>Handmatige goedkeuring</h2>

        @if($pendingSubmissions->isEmpty())
            <p>Er zijn geen inzendingen die wachten op goedkeuring.</p>
        @else
            @foreach($pendingSubmissions as $submission)
                <div style="border:1px solid var(--line); border-radius: 10px; padding: 12px; margin-bottom: 12px;">
                    <p><strong>Opdracht:</strong> {{ $submission->questStep?->title ?? '-' }} (stap {{ $submission->step_sort_order }})</p>
                    @if($submission->photo_path)
                        <p><a href="{{ route('admin.submissions.photo', $submission) }}" target="_blank" rel="noopener">Bekijk ingezonden foto</a></p>
                    @endif
                    <p>{{ $submission->questStep?->review_notes }}</p>
                    <p><strong>Antwoord:</strong> {{ $submission->submitted_answer ?: '-' }}</p>
                    <p><strong>Keuze:</strong> {{ $submission->submitted_choice ?: '-' }}</p>
                    <p><strong>Locatie:</strong> {{ $submission->submitted_latitude ?? '-' }}, {{ $submission->submitted_longitude ?? '-' }}</p>

                    <div class="actions">
                        <form method="POST" action="{{ route('admin.routes.submissions.approve', [$route, $submission]) }}">
                            @csrf
                            <button type="submit" class="btn">Goedkeuren</button>
                        </form>

                        <form method="POST" action="{{ route('admin.routes.submissions.reject', [$route, $submission]) }}">
                            @csrf
                            <button type="submit" class="btn-danger">Afkeuren</button>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h2>Opdrachten in groep volgorde</h2>
        <p style="color: var(--muted); margin-bottom: 16px;">Vink opdrachten aan en sleep de geselecteerde opdrachten in de juiste volgorde.</p>

        <form method="POST" action="{{ route('admin.routes.quest-steps.update', $route) }}" id="route-steps-form">
            @csrf
            @method('PUT')

            <div class="route-editor-grid">
                <div>
                    <h3 style="margin-top:0; margin-bottom: 10px;">Beschikbare opdrachten</h3>
                    <div class="step-picker" id="step-picker">
                        @foreach($availableQuestSteps as $step)
                            @php
                                $checkedFromOld = collect(old('quest_steps', $assignedStepIds))->map(fn ($id) => (int) $id)->all();
                                $isChecked = in_array($step->id, $checkedFromOld, true);
                            @endphp
                            <label class="step-option">
                                <input type="checkbox" data-step-checkbox value="{{ $step->id }}" @checked($isChecked) style="width:auto; margin:0;">
                                <span>
                                    <strong>{{ $step->title }}</strong>
                                    <small style="display:block; color: var(--muted);">{{ $step->step_type }}</small>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 style="margin-top:0; margin-bottom: 10px;">Geselecteerde volgorde</h3>
                    <div id="sort-empty" class="callout" style="display:none;">Nog geen opdrachten geselecteerd.</div>
                    <ul id="selected-steps" class="sortable-list"></ul>
                </div>
            </div>

            <div id="selected-hidden-inputs"></div>

            <div style="margin-top: 16px;">
                <button type="submit" class="btn">Opslaan</button>
            </div>
        </form>
    </div>

    <div id="qr-modal" style="display:none; position:fixed; inset:0; background: rgba(0,0,0,0.65); z-index: 999; align-items:center; justify-content:center; padding: 16px;">
        <div style="background:#111b2b; border:1px solid var(--line); border-radius: 14px; padding: 16px; max-width: 420px; width:100%;">
            <h3 style="margin-top:0;">QR-code voor groep {{ $route->name }}</h3>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=720x720&data={{ urlencode(route('join.with-token', $route->join_token)) }}" alt="QR-code" style="width:100%; border-radius: 10px; background: #fff; padding: 8px;">
            <div class="actions" style="margin-top: 12px;">
                <button type="button" class="btn-secondary" id="close-qr-modal">Sluiten</button>
                <a href="{{ route('admin.routes.qr', $route) }}" class="btn-secondary" target="_blank" rel="noopener noreferrer">Open als pagina</a>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (() => {
            const availableSteps = @json($availableStepsForJs);
            const initialSelected = @json($assignedSteps);
            const selectedListEl = document.getElementById('selected-steps');
            const hiddenInputsEl = document.getElementById('selected-hidden-inputs');
            const emptyEl = document.getElementById('sort-empty');
            const checkboxes = document.querySelectorAll('[data-step-checkbox]');

            if (selectedListEl && hiddenInputsEl && checkboxes.length > 0) {
                let selectedStepIds = [];
                const stepMap = new Map(availableSteps.map((step) => [step.id, step]));

                const createItem = (stepId) => {
                    const step = stepMap.get(stepId);
                    if (!step) return null;

                    const li = document.createElement('li');
                    li.className = 'sortable-item';
                    li.draggable = true;
                    li.dataset.stepId = String(step.id);
                    li.innerHTML = `<span class="drag-handle">::</span><div><strong>${step.title}</strong><div style="color: var(--muted); font-size: 13px;">${step.step_type}</div></div><span class="sort-badge"></span>`;

                    li.addEventListener('dragstart', () => li.classList.add('dragging'));
                    li.addEventListener('dragend', () => li.classList.remove('dragging'));

                    return li;
                };

                const render = () => {
                    selectedListEl.innerHTML = '';

                    selectedStepIds.forEach((stepId, index) => {
                        const item = createItem(stepId);
                        if (!item) return;
                        const badge = item.querySelector('.sort-badge');
                        if (badge) badge.textContent = `#${index + 1}`;
                        selectedListEl.appendChild(item);
                    });

                    hiddenInputsEl.innerHTML = selectedStepIds.map((stepId, index) => `
                        <input type="hidden" name="quest_steps[]" value="${stepId}">
                        <input type="hidden" name="sort_orders[${stepId}]" value="${index + 1}">
                    `).join('');

                    emptyEl.style.display = selectedStepIds.length === 0 ? 'block' : 'none';
                };

                const syncCheckboxes = () => {
                    checkboxes.forEach((checkbox) => {
                        const stepId = Number(checkbox.value);
                        checkbox.checked = selectedStepIds.includes(stepId);
                    });
                };

                const fromOld = Array.from(checkboxes).filter((c) => c.checked).map((c) => Number(c.value));
                selectedStepIds = fromOld.length > 0 ? fromOld : initialSelected.map((step) => Number(step.id));

                selectedListEl.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    const dragging = selectedListEl.querySelector('.dragging');
                    if (!dragging) return;
                    const siblings = [...selectedListEl.querySelectorAll('.sortable-item:not(.dragging)')];
                    const next = siblings.find((s) => {
                        const box = s.getBoundingClientRect();
                        return event.clientY <= box.top + box.height / 2;
                    });
                    selectedListEl.insertBefore(dragging, next ?? null);
                });

                selectedListEl.addEventListener('drop', () => {
                    selectedStepIds = [...selectedListEl.querySelectorAll('.sortable-item')].map((item) => Number(item.dataset.stepId));
                    render();
                    syncCheckboxes();
                });

                checkboxes.forEach((checkbox) => {
                    checkbox.addEventListener('change', () => {
                        const stepId = Number(checkbox.value);
                        if (checkbox.checked && !selectedStepIds.includes(stepId)) selectedStepIds.push(stepId);
                        if (!checkbox.checked) selectedStepIds = selectedStepIds.filter((id) => id !== stepId);
                        render();
                        syncCheckboxes();
                    });
                });

                render();
                syncCheckboxes();
            }

            const qrModal = document.getElementById('qr-modal');
            const openQr = document.getElementById('open-qr-modal');
            const closeQr = document.getElementById('close-qr-modal');
            if (qrModal && openQr && closeQr) {
                openQr.addEventListener('click', () => { qrModal.style.display = 'flex'; });
                closeQr.addEventListener('click', () => { qrModal.style.display = 'none'; });
                qrModal.addEventListener('click', (event) => {
                    if (event.target === qrModal) qrModal.style.display = 'none';
                });
            }

            let lat = @json($progress->last_latitude);
            let lng = @json($progress->last_longitude);
            const mapEl = document.getElementById('group-map');
            const liveProgressEl = document.getElementById('live-progress');
            const liveLocationEl = document.getElementById('live-location');
            const liveSeenAtEl = document.getElementById('live-seen-at');
            const liveEmptyEl = document.getElementById('live-location-empty');

            let map = null;
            let marker = null;
            if (mapEl && lat && lng && typeof L !== 'undefined') {
                map = L.map(mapEl).setView([Number(lat), Number(lng)], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(map);
                marker = L.marker([Number(lat), Number(lng)]).addTo(map).bindPopup('Groep {{ $route->name }}');
            }

            const refreshLive = () => {
                fetch('{{ route('admin.routes.live', $route) }}')
                    .then((response) => response.json())
                    .then((data) => {
                        if (liveProgressEl) {
                            liveProgressEl.textContent = `${data.progress_percent}% (${data.completed_steps}/${data.total_steps})`;
                        }
                        const hasLocation = !!(data.last_latitude && data.last_longitude);
                        if (liveLocationEl) {
                            liveLocationEl.textContent = hasLocation
                                ? `${data.last_latitude}, ${data.last_longitude}`
                                : '-';
                        }
                        if (liveSeenAtEl) {
                            liveSeenAtEl.textContent = data.last_seen_at ?? '-';
                        }
                        if (liveEmptyEl) {
                            liveEmptyEl.style.display = hasLocation ? 'none' : 'block';
                        }

                        if (mapEl && hasLocation && typeof L !== 'undefined' && !map) {
                            mapEl.style.display = 'block';
                            map = L.map(mapEl).setView([Number(data.last_latitude), Number(data.last_longitude)], 16);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; OpenStreetMap contributors',
                            }).addTo(map);
                            marker = L.marker([Number(data.last_latitude), Number(data.last_longitude)]).addTo(map).bindPopup('Groep {{ $route->name }}');
                        }

                        if (map && marker && hasLocation) {
                            const newLat = Number(data.last_latitude);
                            const newLng = Number(data.last_longitude);
                            marker.setLatLng([newLat, newLng]);
                            map.setView([newLat, newLng], map.getZoom());
                        }
                    })
                    .catch(() => {});
            };

            setInterval(refreshLive, 10000);
        })();
    </script>
@endsection
