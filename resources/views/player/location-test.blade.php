<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Locatietest · Escapetour</title>
    <style>
        * { box-sizing: border-box; }
        body { margin:0; padding:24px 16px; background:#f5f7fb; color:#1f2a44; font:16px/1.6 system-ui,sans-serif; }
        main { max-width:850px; margin:auto; }
        h1 { margin-bottom:8px; font-size:clamp(28px,5vw,40px); }
        h2 { margin-top:0; font-size:21px; }
        a { color:#244f91; }
        .card { padding:24px; background:white; border:1px solid #d6dfed; border-radius:16px; margin:20px 0; }
        .locations { display:grid; grid-template-columns:repeat(auto-fit,minmax(270px,1fr)); gap:16px; }
        .locations .card { margin:0; }
        button { background:#008f45; border:0; border-radius:10px; padding:14px 20px; font:inherit; font-weight:700; cursor:pointer; }
        button:disabled { opacity:.6; cursor:wait; }
        button:focus-visible,a:focus-visible { outline:3px solid #244f91; outline-offset:4px; }
        .result { padding:12px; background:#eef2f7; border-radius:8px; font-weight:700; }
        .inside { background:#e1f5e8; color:#175c32; }
        .outside { background:#fff1f1; color:#a72335; }
        .uncertain,.unknown { background:#fff2ce; color:#705000; }
        .muted { color:#526078; }
        #gps-details { overflow-wrap:anywhere; }
    </style>
    <script type="module" src="{{ asset('js/location-test.mjs') }}"></script>
<link rel="stylesheet" href="{{ asset('css/zabka.css').'?v='.filemtime(public_path('css/zabka.css')) }}">
</head>
<body>
<main>
    <a href="{{ route('landing') }}">← Naar de escapetour</a>
    <h1>Klopt je locatie?</h1>
    <p>Test je GPS bij een van deze twee adressen in Utrecht. Je staat goed als de gemeten locatie binnen <strong>100 meter</strong> van het adrespunt valt.</p>
    <section class="card" aria-label="GPS-meting">
        <button id="test-location" type="button">Test mijn huidige locatie</button>
        <div role="status" aria-live="polite">
            <p id="gps-status">Druk op de knop om een nieuwe meting te doen.</p>
            <p id="gps-details" class="muted"></p>
        </div>
        <p class="muted">Je locatie wordt alleen op deze pagina vergeleken, niet opgeslagen of naar de server verstuurd. Elke klik doet één nieuwe meting.</p>
        <noscript>Schakel JavaScript in om je locatie te testen.</noscript>
    </section>
    <div class="locations" aria-live="polite">
        <section class="card" data-lat="52.08449308" data-lng="5.17574052">
            <h2>Heidelberglaan 15</h2>
            <p>3584 CS Utrecht</p>
            <p class="muted">Je gaf 3584 CE op. Het adresregister vermeldt voor huisnummer 15 postcode 3584 CS.</p>
            <p class="distance">Afstand: —</p>
            <p class="result">Nog niet gemeten</p>
        </section>
        <section class="card" data-lat="52.0842191" data-lng="5.17182654">
            <h2>Utrecht University · Heidelberglaan 1B</h2>
            <p>3584 CS Utrecht</p>
            <p class="distance">Afstand: —</p>
            <p class="result">Nog niet gemeten</p>
        </section>
    </div>
    <section class="card">
        <h2>Een betrouwbare meting</h2>
        <p>Test bij voorkeur buiten met je telefoon. Bij “Onzeker” valt de grens van 100 meter binnen de opgegeven GPS-onzekerheid. Probeer dan opnieuw op een plek met vrij zicht op de lucht. Ook een groene uitslag blijft een GPS-inschatting.</p>
        <p>Op je telefoon moet deze pagina via HTTPS bereikbaar zijn. Locatiebepaling via een onbeveiligd lokaal netwerkadres werkt meestal niet; localhost werkt alleen op het apparaat waarop de server draait.</p>
        <p class="muted">Adrespunten: <a href="https://api.pdok.nl/bzk/locatieserver/search/v3_1/free?q=Heidelberglaan%20Utrecht&amp;fq=type:adres&amp;rows=50" target="_blank" rel="noopener">PDOK Locatieserver</a>. De test heeft geen invloed op je spelvoortgang.</p>
    </section>
</main>
</body>
</html>
