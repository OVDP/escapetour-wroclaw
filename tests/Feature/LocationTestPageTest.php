<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocationTestPageTest extends TestCase
{
    public function test_location_test_is_accessible_without_a_game_session(): void
    {
        $this->get('/locatie-test')->assertOk()
            ->assertSee('Heidelberglaan 15')
            ->assertSee('Heidelberglaan 1B')
            ->assertSee('52.08449308')
            ->assertSee('52.0842191')
            ->assertSee('Test mijn huidige locatie');
    }
}
