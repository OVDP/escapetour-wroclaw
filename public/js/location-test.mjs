export function distanceMeters(lat1, lng1, lat2, lng2) {
    const rad = degrees => degrees * Math.PI / 180;
    const a = Math.sin(rad(lat2 - lat1) / 2) ** 2
        + Math.cos(rad(lat1)) * Math.cos(rad(lat2)) * Math.sin(rad(lng2 - lng1) / 2) ** 2;
    return 6371000 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(Math.max(0, 1 - a)));
}

export function assessLocation(distance, accuracy, radius = 100) {
    if (![distance, accuracy, radius].every(Number.isFinite) || distance < 0 || accuracy < 0 || radius <= 0) {
        return 'unknown';
    }
    if (distance + accuracy <= radius) return 'inside';
    if (distance - accuracy > radius) return 'outside';
    return 'uncertain';
}

if (typeof document !== 'undefined') {
    const button = document.querySelector('#test-location');
    const status = document.querySelector('#gps-status');
    const details = document.querySelector('#gps-details');
    const cards = [...document.querySelectorAll('[data-lat]')];
    const reset = () => {
        details.textContent = '';
        for (const card of cards) {
            card.querySelector('.result').textContent = 'Nog niet gemeten';
            card.querySelector('.result').className = 'result';
            card.querySelector('.distance').textContent = 'Afstand: —';
        }
    };
    button.addEventListener('click', () => {
        reset();
        if (!window.isSecureContext) {
            status.textContent = 'GPS werkt alleen via HTTPS of localhost. Open op je telefoon een HTTPS-versie van deze pagina.';
            return;
        }
        if (!navigator.geolocation) {
            status.textContent = 'Deze browser ondersteunt geen locatiebepaling.';
            return;
        }
        button.disabled = true;
        status.textContent = 'Locatie ophalen… Geef je browser toestemming om je locatie te gebruiken.';
        navigator.geolocation.getCurrentPosition(position => {
            button.disabled = false;
            const {latitude, longitude, accuracy} = position.coords;
            if (![latitude, longitude, accuracy, position.timestamp].every(Number.isFinite)
                || Math.abs(latitude) > 90 || Math.abs(longitude) > 180 || accuracy < 0
                || Date.now() - position.timestamp > 30000) {
                status.textContent = 'Geen geldige, recente meting ontvangen. Probeer het opnieuw.';
                return;
            }
            status.textContent = 'Meting voltooid. Bekijk hieronder of je locatie overeenkomt.';
            details.textContent = `Jouw locatie: ${latitude.toFixed(6)}, ${longitude.toFixed(6)} · GPS-nauwkeurigheid: ±${Math.ceil(accuracy)} m · Gemeten om ${new Date(position.timestamp).toLocaleTimeString('nl-NL')}`;
            const labels = {
                inside: 'Locatie klopt — binnen 100 meter',
                outside: 'Buiten de testlocatie',
                uncertain: 'Onzeker — GPS-meting overlapt de grens',
                unknown: 'Meting niet bruikbaar',
            };
            for (const card of cards) {
                const distance = distanceMeters(latitude, longitude, Number(card.dataset.lat), Number(card.dataset.lng));
                const result = assessLocation(distance, accuracy);
                card.querySelector('.distance').textContent = `Afstand tot adrespunt: ${Math.round(distance)} meter`;
                card.querySelector('.result').textContent = labels[result];
                card.querySelector('.result').className = `result ${result}`;
            }
        }, error => {
            button.disabled = false;
            status.textContent = {
                1: 'Locatietoegang geweigerd. Sta locatie toe in de browserinstellingen en probeer opnieuw.',
                2: 'Je locatie is niet beschikbaar. Zet locatievoorzieningen aan en probeer het buiten opnieuw.',
                3: 'Het ophalen duurde te lang. Ga naar buiten en probeer opnieuw.',
            }[error.code] || 'Locatie ophalen is mislukt. Probeer opnieuw.';
        }, {enableHighAccuracy: true, maximumAge: 0, timeout: 20000});
    });
}
