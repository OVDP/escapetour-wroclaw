export function gpsErrorMessage(error) {
    if (error instanceof Error && !('code' in error)) return error.message;
    return {
        1: 'Locatietoegang geweigerd. Sta locatie toe voor deze website in je browser én bij de locatievoorzieningen van je telefoon. Open de site rechtstreeks in Safari of Chrome.',
        2: 'Geen GPS-signaal beschikbaar. Zet locatievoorzieningen aan en probeer het buiten opnieuw.',
        3: 'GPS ophalen duurde te lang. Ga naar buiten en probeer opnieuw.',
    }[error?.code] || 'Je locatie kon niet worden bepaald. Probeer opnieuw.';
}

export function getFreshPosition(browser = globalThis) {
    if (!browser.isSecureContext) return Promise.reject(new Error('GPS vereist HTTPS. Open deze website via https://; een onbeveiligd serveradres werkt niet op je telefoon.'));
    const navigator = browser.navigator;
    if (!navigator?.geolocation) return Promise.reject(new Error('Deze browser ondersteunt geen locatiebepaling. Open de site in Safari of Chrome.'));
    const policy = browser.document?.permissionsPolicy || browser.document?.featurePolicy;
    if (policy?.allowsFeature && !policy.allowsFeature('geolocation')) return Promise.reject(new Error('GPS wordt geblokkeerd door de serverinstellingen of het ingesloten venster. Open de website rechtstreeks; controleer de Permissions-Policy op de server.'));
    return new Promise((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(position => {
            const {latitude, longitude, accuracy} = position.coords;
            if (![latitude, longitude, accuracy, position.timestamp].every(Number.isFinite)
                || Math.abs(latitude) > 90 || Math.abs(longitude) > 180 || accuracy < 0
                || Date.now() - position.timestamp > 120000) {
                reject(new Error('De GPS-meting is ongeldig of verouderd. Probeer opnieuw.'));
                return;
            }
            resolve(position);
        }, reject, {enableHighAccuracy: true, maximumAge: 0, timeout: 25000});
    });
}
