# Escapetour Wrocław

Start lokaal met `./scripts/run-local.sh` en open http://127.0.0.1:8000.
De lokale PHP 8.4-runtime staat in `.local-runtime`; eventueel kun je een andere PHP 8.4+ executable instellen met `PHP_BIN`.
De lokale instellingen staan in `.env.local`, de afzonderlijke database in `database/wroclaw-local.sqlite`.
Admin: http://127.0.0.1:8000/admin. Het lokale wachtwoord staat bij `ADMIN_PASSPHRASE` in `.env.local`.

De 12 locaties, vragen, coördinaten en antwoorden staan in `database/data/wroclaw.json`.
De inhoud is overgenomen uit brainstorm.docx. De route loopt van Krasnal WOTuś via de 12 opdrachten terug naar het startpunt. Navigatie is beschikbaar per opdracht. De vraag verschijnt pas na bevestiging van een recente GPS-locatie binnen 100 meter.

- Opdrachten 2, 6 en 9 vereisen een foto met toelichting. De spelleiding keurt deze goed onder Goedkeuringen. Bij 9 moet ook Het Zwanenmeer worden genoemd.
- Spelers zien alleen de puzzelafbeeldingen. Referentieafbeeldingen met oplossingen staan privé in `storage/app/private/brainstorm`.
- Opdracht 4 gebruikt `code dict`, overeenkomstig de voorbeelduitvoer in het document.
- Opdracht 5 gebruikt de uitgewerkte vraag over het openingsjaar (1908). De voortgangstabel noemt een afwijkend idee over de dieren op de gevel; dit staat als beheerdersnotitie bij de opdracht.
- Opdracht 8 accepteert zowel 1926 als 1924, omdat de brainstorm beide noemt.
- Na opdracht 12 verschijnt de instructie terug te gaan naar Krasnal WOTuś.

Inhoud opnieuw laden zonder voortgang te wissen:
```
.local-runtime/php artisan migrate --env=local
.local-runtime/php artisan db:seed --class=WroclawSeeder --env=local
```
De seeder actualiseert de Wrocław-opdrachten. Andere spellen worden niet aangepast.

Tests: `.local-runtime/php vendor/bin/phpunit` (tijdelijke database in geheugen).

## Groepen en startlocaties

Spelers maken op de startpagina een groep aan. Een blijvende browsercookie beperkt dit tot één aangemaakte groep per browserprofiel; gewiste cookies of andere browsers kunnen niet als hetzelfde apparaat worden herkend.
Nieuwe groepen wachten op de spelleiding. Kies bij Groepen → Bewerk een startlocatie. Bij plek 5 is de volgorde 5–12 en daarna 1–4. Zodra een groep een locatie bevestigt of een antwoord indient, staat de startvolgorde vast.
Voor iedere opdracht zijn een recente GPS-meting (maximaal 2 minuten oud), een positie binnen de ingestelde straal en expliciete locatiebevestiging nodig. De server controleert dit ook bij openen en antwoorden. GPS-coördinaten worden door de browser aangeleverd en zijn geen bescherming tegen doelbewust vervalsen.
Het adminpaneel bevat Dashboard, Groepen, Opdrachten en Goedkeuringen. Spelinstellingen, de algemene berichtenpagina, verdachten en live kaarten zijn uit de interface verwijderd. De gedeelde styling gebruikt groen, wit en neutrale kleuren. Fouten en waarschuwingen mogen rood gebruiken.
