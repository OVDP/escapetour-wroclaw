@php
    $defaultQuestionType = old('question_type', $quest_step?->question_type ?? (($quest_step?->location_submission_required ?? false) ? 'location_submit' : 'direct'));
    $rawOptions = old('multiple_choice_options', $quest_step?->multiple_choice_json ?? []);
    $choiceOptions = is_array($rawOptions) ? array_values(array_filter(array_map('strval', $rawOptions), fn ($value) => trim($value) !== '')) : [];
    if (count($choiceOptions) < 4) {
        $choiceOptions = array_pad($choiceOptions, 4, '');
    }
@endphp

<label>Titel</label>
<input type="text" name="title" value="{{ old('title', $quest_step?->title ?? '') }}" required>

<label>Verhaalinleiding</label>
<textarea name="story_intro" rows="4">{{ old('story_intro', $quest_step?->story_intro ?? $quest_step?->story_text ?? '') }}</textarea>

<label>Vraagtekst</label>
<textarea name="question_text" rows="4" required>{{ old('question_text', $quest_step?->question_text ?? '') }}</textarea>

<label>Extra toelichting</label>
<textarea name="extra_explanation" rows="3">{{ old('extra_explanation', $quest_step?->extra_explanation ?? '') }}</textarea>

<label>Vraagtype</label>
<select name="question_type" id="question_type" required>
    <option value="direct" @selected($defaultQuestionType === 'direct')>Open vraag</option>
    <option value="numeric" @selected($defaultQuestionType === 'numeric')>Numeriek antwoord</option>
    <option value="multiple_choice" @selected($defaultQuestionType === 'multiple_choice')>Multiple choice</option>
    <option value="location_submit" @selected($defaultQuestionType === 'location_submit')>Locatie inzenden als antwoord</option>
</select>

<div id="answer_block">
    <label>Correct antwoord</label>
    <input type="text" name="correct_answer" value="{{ old('correct_answer', $quest_step?->correct_answer ?? $quest_step?->answer_text ?? '') }}">
</div>

<div id="multiple_choice_block" style="display:none;">
    <label>Antwoordopties (minimaal 2)</label>
    @foreach($choiceOptions as $index => $choiceOption)
        <input type="text" name="multiple_choice_options[]" value="{{ $choiceOption }}" placeholder="Optie {{ $index + 1 }}">
    @endforeach

    <label>Juiste optie (exact overnemen)</label>
    <input type="text" name="correct_choice" value="{{ old('correct_choice', $quest_step?->correct_choice ?? '') }}">
</div>

<label style="display:flex; align-items:center; gap:10px; margin-top: 12px;">
    <input type="checkbox" id="manual_approval" name="manual_approval" value="1" @checked(old('manual_approval', $quest_step?->manual_approval ?? $quest_step?->manual_approval_required ?? false)) style="width:auto; margin:0;">
    Handmatige goedkeuring
</label>

<label style="display:flex; align-items:center; gap:10px; margin-top: 12px;">
    <input type="checkbox" name="accepts_whatsapp_upload" value="1" @checked(old('accepts_whatsapp_upload', $quest_step?->accepts_whatsapp_upload ?? false)) style="width:auto; margin:0;">
    Materiaalinzending via WhatsApp toelaten
</label>

<label>Timeout bij fout antwoord (seconden)</label>
<input type="number" name="timeout_seconds" min="1" max="3600" value="{{ old('timeout_seconds', $quest_step?->timeout_seconds ?? $quest_step?->cooldown_seconds ?? 30) }}">

<hr style="border-color:var(--line); margin: 24px 0;">
<h2 style="font-size: 18px;">Beloning na goed antwoord</h2>

<label>Beloning: verhaallijntekst</label>
<textarea name="reward_story_text" rows="4">{{ old('reward_story_text', $quest_step?->reward_story_text ?? $quest_step?->clue_reveal_text ?? '') }}</textarea>

<label>Beloning: letter</label>
<input type="text" name="reward_letter" maxlength="1" value="{{ old('reward_letter', $quest_step?->reward_letter ?? '') }}">

<label>Beloning: verdachte vrijspelen</label>
<input type="text" name="reward_suspect_name" value="{{ old('reward_suspect_name', $quest_step?->reward_suspect_name ?? '') }}" placeholder="Bijv. Hofmaarschalk Van Doren">

<hr style="border-color:var(--line); margin: 24px 0;">
<h2 style="font-size: 18px;">Locatie-instellingen</h2>

<label style="display:flex; align-items:center; gap:10px; margin-top: 12px;">
    <input type="checkbox" id="requires_location" name="requires_location" value="1" @checked(old('requires_location', $quest_step?->requires_location ?? false)) style="width:auto; margin:0;">
    Locatie nodig om opdracht te starten
</label>
<p class="muted" style="margin-top: 6px; margin-bottom: 12px;">Voor deze locatie moet de groep in de buurt zijn voordat de opdracht verschijnt.</p>

<div id="start_location_fields">
    <label>Startlocatie adres</label>
    <input type="text" id="start_location_address" name="start_location_address" value="{{ old('start_location_address', $quest_step?->start_location_address ?? $quest_step?->location_address ?? '') }}">

    <label>Startlocatie kaart (klik om locatie te kiezen)</label>
    <div style="border:1px solid var(--line); border-radius: 10px; overflow:hidden; margin-bottom: 12px;">
        <div id="admin-start-location-map" style="height: 280px;"></div>
    </div>

    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom: 10px;">
        <input type="text" id="admin-start-location-search" placeholder="Zoek startadres" style="margin:0; flex:1; min-width: 240px;">
        <button type="button" id="admin-start-location-search-btn" class="btn-secondary">Zoek adres</button>
        <button type="button" id="admin-start-use-current-location-btn" class="btn-secondary">Gebruik mijn locatie</button>
    </div>

    <label>Startlocatie latitude</label>
    <input type="text" id="start-location-lat" name="start_location_lat" value="{{ old('start_location_lat', $quest_step?->start_location_lat ?? $quest_step?->location_lat ?? $quest_step?->latitude ?? '') }}">

    <label>Startlocatie longitude</label>
    <input type="text" id="start-location-lng" name="start_location_lng" value="{{ old('start_location_lng', $quest_step?->start_location_lng ?? $quest_step?->location_lng ?? $quest_step?->longitude ?? '') }}">

    <label>Startlocatie straal in meters</label>
    <input type="number" name="start_location_radius_meters" min="1" value="{{ old('start_location_radius_meters', $quest_step?->start_location_radius_meters ?? $quest_step?->location_radius_meters ?? $quest_step?->radius_meters ?? '') }}">

    <label style="display:flex; align-items:center; gap:10px; margin-top: 12px;">
        <input type="checkbox" id="show_question_only_when_in_radius" name="show_question_only_when_in_radius" value="1" @checked(old('show_question_only_when_in_radius', $quest_step?->show_question_only_when_in_radius ?? false)) style="width:auto; margin:0;">
        Vraag pas tonen als groep binnen startstraal is
    </label>
</div>

<div id="submission_location_fields" style="display:none; margin-top: 22px;">
    <h3 style="font-size: 16px; margin-bottom: 8px;">Inzendlocatie (voor locatie-inzendopdracht)</h3>
    <p class="muted" style="margin-top: 0; margin-bottom: 12px;">Bij dit vraagtype moet de groep op deze locatie staan om te kunnen inzenden.</p>

    <label>Inzendlocatie adres</label>
    <input type="text" id="location_address" name="location_address" value="{{ old('location_address', $quest_step?->location_address ?? '') }}">

    <label>Inzendlocatie kaart (klik om locatie te kiezen)</label>
    <div style="border:1px solid var(--line); border-radius: 10px; overflow:hidden; margin-bottom: 12px;">
        <div id="admin-location-map" style="height: 280px;"></div>
    </div>

    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom: 10px;">
        <input type="text" id="admin-location-search" placeholder="Zoek inzendadres" style="margin:0; flex:1; min-width: 240px;">
        <button type="button" id="admin-location-search-btn" class="btn-secondary">Zoek adres</button>
        <button type="button" id="admin-use-current-location-btn" class="btn-secondary">Gebruik mijn locatie</button>
    </div>

    <label>Inzendlocatie latitude</label>
    <input type="text" id="location-lat" name="location_lat" value="{{ old('location_lat', $quest_step?->location_lat ?? $quest_step?->latitude ?? '') }}">

    <label>Inzendlocatie longitude</label>
    <input type="text" id="location-lng" name="location_lng" value="{{ old('location_lng', $quest_step?->location_lng ?? $quest_step?->longitude ?? '') }}">

    <label>Inzendlocatie straal in meters</label>
    <input type="number" name="location_radius_meters" min="1" value="{{ old('location_radius_meters', $quest_step?->location_radius_meters ?? $quest_step?->radius_meters ?? '') }}">
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(() => {
    const questionTypeEl = document.getElementById('question_type');
    const requiresLocationEl = document.getElementById('requires_location');
    const startLocationFieldsEl = document.getElementById('start_location_fields');
    const submissionLocationFieldsEl = document.getElementById('submission_location_fields');
    const answerBlockEl = document.getElementById('answer_block');
    const multipleChoiceBlockEl = document.getElementById('multiple_choice_block');

    const bindLocationPicker = ({ latId, lngId, addressId, mapId, searchId, searchBtnId, currentBtnId }) => {
        const latInput = document.getElementById(latId);
        const lngInput = document.getElementById(lngId);
        const addressInput = document.getElementById(addressId);
        const mapEl = document.getElementById(mapId);
        const searchInput = document.getElementById(searchId);
        const searchBtn = document.getElementById(searchBtnId);
        const currentLocationBtn = document.getElementById(currentBtnId);

        let map = null;
        let marker = null;

        const setPoint = (lat, lng) => {
            latInput.value = Number(lat).toFixed(6);
            lngInput.value = Number(lng).toFixed(6);

            if (!map) return;
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
            }
            map.setView([lat, lng], 16);
        };

        const ensureMap = () => {
            if (map || !mapEl || typeof L === 'undefined') return;

            map = L.map(mapEl).setView([52.0, 5.3], 7);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map);

            const lat = Number(latInput.value);
            const lng = Number(lngInput.value);
            if (!Number.isNaN(lat) && !Number.isNaN(lng) && latInput.value !== '' && lngInput.value !== '') {
                setPoint(lat, lng);
            }

            map.on('click', (event) => {
                setPoint(event.latlng.lat, event.latlng.lng);
            });
        };

        if (searchBtn && searchInput) {
            searchBtn.addEventListener('click', () => {
                const q = searchInput.value.trim();
                if (!q) return;

                fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(q)}`)
                    .then((response) => response.json())
                    .then((results) => {
                        if (!Array.isArray(results) || results.length === 0) return;
                        const lat = Number(results[0].lat);
                        const lng = Number(results[0].lon);
                        setPoint(lat, lng);
                        if (addressInput && !addressInput.value) {
                            addressInput.value = results[0].display_name || q;
                        }
                    })
                    .catch(() => {});
            });
        }

        if (currentLocationBtn) {
            currentLocationBtn.addEventListener('click', () => {
                if (!navigator.geolocation) return;
                navigator.geolocation.getCurrentPosition((position) => {
                    setPoint(position.coords.latitude, position.coords.longitude);
                }, () => {});
            });
        }

        return {
            ensureVisible: () => {
                ensureMap();
                setTimeout(() => map?.invalidateSize(), 0);
            },
        };
    };

    const startPicker = bindLocationPicker({
        latId: 'start-location-lat',
        lngId: 'start-location-lng',
        addressId: 'start_location_address',
        mapId: 'admin-start-location-map',
        searchId: 'admin-start-location-search',
        searchBtnId: 'admin-start-location-search-btn',
        currentBtnId: 'admin-start-use-current-location-btn',
    });

    const submissionPicker = bindLocationPicker({
        latId: 'location-lat',
        lngId: 'location-lng',
        addressId: 'location_address',
        mapId: 'admin-location-map',
        searchId: 'admin-location-search',
        searchBtnId: 'admin-location-search-btn',
        currentBtnId: 'admin-use-current-location-btn',
    });

    const render = () => {
        const isLocationSubmission = questionTypeEl?.value === 'location_submit';
        const isMultipleChoice = questionTypeEl?.value === 'multiple_choice';

        if (answerBlockEl) {
            answerBlockEl.style.display = isMultipleChoice || isLocationSubmission ? 'none' : 'block';
        }
        if (multipleChoiceBlockEl) {
            multipleChoiceBlockEl.style.display = isMultipleChoice ? 'block' : 'none';
        }

        startLocationFieldsEl.style.display = requiresLocationEl.checked ? 'block' : 'none';
        submissionLocationFieldsEl.style.display = isLocationSubmission ? 'block' : 'none';

        if (requiresLocationEl.checked) {
            startPicker.ensureVisible();
        }
        if (isLocationSubmission) {
            submissionPicker.ensureVisible();
        }
    };

    questionTypeEl?.addEventListener('change', render);
    requiresLocationEl?.addEventListener('change', render);
    render();
})();
</script>
