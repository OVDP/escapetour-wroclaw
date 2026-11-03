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

Stel in `.env` een eigen `ADMIN_PASSPHRASE` in. Maak vervolgens de SQLite-database aan en laad de opdrachten:

```bash
touch database/database.sqlite
php artisan migrate
php artisan db:seed --class=WroclawSeeder
php artisan serve
```

Open http://127.0.0.1:8000. De spelleiding gebruikt `/admin`; de GPS-test staat op `/locatie-test`.
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
node tests/js/location-test.mjs
```

## Bestanden voor GitHub

Commit de broncode en `composer.lock`. Wachtwoorden, `.env`-bestanden, databases met spelersdata, ingezonden foto’s, sessies en lokale runtimes zijn uitgesloten via `.gitignore`.

Gebruik voor een online versie HTTPS voor GPS-toegang en versterk de huidige gedeelde adminlogin voordat je de app publiek inzet.
