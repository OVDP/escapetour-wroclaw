# Escapetour Wrocław

Een Laravel-app voor de escapetour **Red de Żabka-kikker**, met 12 locaties in Wrocław, GPS-controle, foto-opdrachten en een paneel voor de spelleiding.

De Laravel-app staat direct in deze repository: `artisan`, `app`, `database`, `public`, `resources` en `routes`.

## Installeren

Vereist: PHP 8.4 of hoger met SQLite, mbstring, XML en fileinfo, en Composer.

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Stel in `.env` een eigen `ADMIN_PASSPHRASE` en je HTTPS-domein bij `APP_URL` in. Het voorbeeld staat standaard op productie (`APP_ENV=production`, `APP_DEBUG=false`). Voor lokaal ontwikkelen gebruik je `APP_ENV=local`, `APP_DEBUG=true`, `APP_URL=http://127.0.0.1:8000` en `SESSION_SECURE_COOKIE=false`. Maak vervolgens de SQLite-database aan en laad de opdrachten:

```bash
touch database/database.sqlite
php artisan migrate
php artisan db:seed --class=WroclawSeeder
php artisan serve
```

Open http://127.0.0.1:8000. De spelleiding gebruikt `/admin`.
De huidige pagina’s gebruiken gewone CSS en JavaScript uit `public`; hiervoor is geen frontendbuild nodig.

## Bestaande lokale testomgeving

Op deze computer staan een aparte `.env.local`, een testdatabase en een PHP-runtime klaar. Start deze met:

```bash
./scripts/run-local.sh
```

Deze lokale bestanden worden niet meegenomen naar GitHub. Op een andere computer volg je de installatie hierboven.

## Spelverloop

Spelers maken een groep aan. De spelleiding kiest onder **Groepen** de startlocatie. De route loopt vanaf die locatie rond langs alle 12 opdrachten. Een vraag wordt pas zichtbaar na bevestiging van een recente GPS-locatie binnen de ingestelde straal.

De opdrachten staan in `database/data/wroclaw.json`. Verdere spelinformatie en keuzes uit de brainstorm staan in [WROCLAW.md](WROCLAW.md).

## Tests

```bash
php vendor/bin/phpunit
```

## Bestanden voor GitHub

Commit de broncode en `composer.lock`. Wachtwoorden, `.env`-bestanden, databases met spelersdata, ingezonden foto’s, sessies en lokale runtimes zijn uitgesloten via `.gitignore`.

Gebruik voor een online versie HTTPS voor GPS-toegang en versterk de huidige gedeelde adminlogin voordat je de app publiek inzet.

## GPS op de productieserver

Open de app op een telefoon rechtstreeks in Safari of Chrome via **HTTPS**. Geef de website locatietoestemming en zet locatievoorzieningen op de telefoon aan. De GPS-knoppen tonen nu afzonderlijke meldingen voor geweigerde toegang, geen signaal, een timeout of een verlopen serversessie.

Stel op de server in `.env` in:

```dotenv
APP_URL=https://jouw-domein.nl
SESSION_SECURE_COOKIE=true
```

Laravel genereert bij een HTTPS-`APP_URL` HTTPS-links. De GPS-verzoeken gebruiken daarnaast hetzelfde domein en protocol als de geopende pagina. Gebruik bij een reverse proxy `TRUSTED_PROXIES` met de daadwerkelijke proxy-IP-adressen (komma-gescheiden); laat dit leeg als je geen proxy gebruikt. Zorg dat de proxy `X-Forwarded-Proto` correct doorgeeft. De webroot moet naar `public` wijzen.

Een serverheader `Permissions-Policy: geolocation=()` blokkeert GPS. Verwijder zo’n blokkade of stel in de webserver `Permissions-Policy: geolocation=(self)` in. Open de app als gewone pagina, niet in een extern iframe.

GPS-clienttests: `node tests/js/gps.mjs`. De gehoste configuratie en echte telefoonmeting moeten op de draaiende server worden gecontroleerd.

Bij opdrachten 4, 9 en 12 wordt een exact gelijk antwoord automatisch goedgekeurd. Een ander, niet-leeg antwoord gaat naar de spelleiding voor handmatige beoordeling. De Utrechtse GPS-testpagina is verwijderd.

## Productie uitrollen

Stel `.env` in met een unieke `APP_KEY`, een eigen `ADMIN_PASSPHRASE`, het echte HTTPS-domein en je databaseverbinding. Bewaar dezelfde `APP_KEY` bij updates. Installeer op de server met:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
```

Laad bij een nieuwe installatie de opdrachten met `php artisan db:seed --class=WroclawSeeder --force`. Laat de webserver naar `public` wijzen en geef PHP schrijfrechten op `storage` en `bootstrap/cache`. Serveer productie via de webserver, niet via `artisan serve`.

De locatietestmodus en skiproute zijn volledig verwijderd. Ook oude skips in browsersessies geven geen toegang meer. Iedere vraag vereist echte GPS-bevestiging binnen de ingestelde straal. De gehoste telefoonmeting moet nog op jouw draaiende server worden gecontroleerd.

De browser-GPS-module staat in `public/js/gps.js`. Serveer dit bestand als `text/javascript` of `application/javascript`; bij `application/octet-stream` blokkeren browsers de module en werken de GPS-knoppen niet.
