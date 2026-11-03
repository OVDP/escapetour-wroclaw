<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Database\Seeders\WroclawSeeder;
use App\Models\GameRoute;
use App\Models\StepSubmission;
class GroupFlowTest extends TestCase {
    use RefreshDatabase;

    public function test_one_created_group_per_device_survives_leaving_and_repeated_posts(): void {
        $this->seed(WroclawSeeder::class);
        $device = str_repeat('a', 64);
        $this->withCookie('tour_device', $device)->post('/groepen', ['group_name'=>'Kikkers'])->assertRedirect('/play');
        $group = GameRoute::where('creator_device_hash', hash('sha256', $device))->firstOrFail();
        $this->assertFalse($group->start_assigned);
        $this->get('/play')->assertSee('De spelleiding kiest nu jullie startlocatie');
        $this->post('/play/leave');
        $this->withCookie('tour_device', $device)->post('/groepen', ['group_name'=>'Tweede groep'])->assertRedirect('/play');
        $this->assertDatabaseCount('game_routes', 2); // Seeded original plus one player group.
        $this->assertSame('Kikkers', $group->fresh()->name);
        $this->get('/')->assertSee('Verder met mijn groep')->assertDontSee('Groep aanmaken');
        $this->getJson('/play/live')->assertJson(['start_assigned'=>false]);
        $this->post('/play/submit', ['answer_text'=>'1908'])->assertRedirect('/play');
    }

    public function test_start_five_wraps_through_all_twelve_and_cannot_be_changed_after_start(): void {
        Storage::fake('local');
        $this->seed(WroclawSeeder::class);
        $this->post('/groepen', ['group_name'=>'Rondje vijf']);
        $route = GameRoute::whereNotNull('creator_device_hash')->firstOrFail();
        $five = $route->game->questSteps()->where('title','like','05%')->firstOrFail();
        $this->put('/admin/routes/'.$route->id.'/startlocatie', ['starting_quest_step_id'=>$five->id])->assertRedirect('/admin/login');
        $this->withSession(['admin_authenticated'=>true])->put('/admin/routes/'.$route->id.'/startlocatie', ['starting_quest_step_id'=>$five->id])->assertSessionHasNoErrors();
        $route->refresh();
        $this->assertTrue($route->start_assigned);
        $this->assertSame(['05','06','07','08','09','10','11','12','01','02','03','04'], $route->questSteps->map(fn($s)=>substr($s->title,0,2))->all());
        foreach ($route->questSteps as $step) {
            $this->get('/play?task=1')->assertDontSee($step->question_text)->assertSee('Bevestig locatie');
            $this->post('/play/submit', ['answer_text'=>$step->correct_answer])->assertSessionHasErrors('location');
            $this->postJson('/play/location', ['latitude'=>$step->location_lat, 'longitude'=>$step->location_lng])->assertOk();
            $this->get('/play?task=1')->assertDontSee($step->question_text);
            $this->postJson('/play/location/lock')->assertOk()->assertJson(['ok'=>true]);
            $this->get('/play?task=1')->assertSee($step->question_text);
            if ($step->requiresManualApproval()) {
                $this->post('/play/submit', ['answer_text'=>'Het Zwanenmeer', 'photo'=>UploadedFile::fake()->image('bewijs.jpg')])->assertSessionHasNoErrors();
                $submission = StepSubmission::latest('id')->first();
                $this->post('/admin/routes/'.$route->id.'/submissions/'.$submission->id.'/approve')->assertSessionHasNoErrors();
            } else {
                $this->post('/play/submit', ['answer_text'=>$step->correct_answer])->assertSessionHasNoErrors();
            }
            $this->assertEquals($step->pivot->sort_order + 1, $route->progress()->first()->current_sort_order);
        }
        $this->get('/play')->assertSee('Alle opdrachten afgerond')->assertDontSee('Vrijgespeelde verdachten');
        $this->put('/admin/routes/'.$route->id.'/startlocatie', ['starting_quest_step_id'=>$five->id])->assertSessionHasErrors('start');
        $this->assertEquals(13, $route->progress()->first()->current_sort_order);
        $this->get('/admin')->assertOk()->assertDontSee('groups-map')->assertDontSee('leaflet')->assertDontSee('Spelinstellingen');
        $this->get('/admin/games/1/edit')->assertRedirect('/admin');
        $this->get('/admin/berichten')->assertRedirect('/admin');
        $this->get('/admin/routes/'.$route->id.'/edit')->assertOk()->assertDontSee('group-map');
    }

    public function test_location_must_be_nearby_recent_and_confirmed(): void {
        $this->seed(WroclawSeeder::class);
        $route = GameRoute::first();
        $step = $route->questSteps->first();
        $this->post('/join/'.$route->join_token);
        $this->postJson('/play/location', ['latitude'=>52, 'longitude'=>5])->assertOk();
        $this->postJson('/play/location/lock')->assertUnprocessable();
        $this->get('/play?task=1')->assertDontSee($step->question_text);
        $this->postJson('/play/location', ['latitude'=>999, 'longitude'=>5])->assertUnprocessable();
        $this->postJson('/play/location', ['latitude'=>$step->location_lat, 'longitude'=>$step->location_lng])->assertOk();
        $this->travel(3)->minutes();
        $this->postJson('/play/location/lock')->assertUnprocessable();
        $this->postJson('/play/location', ['latitude'=>$step->location_lat, 'longitude'=>$step->location_lng])->assertOk();
        $this->postJson('/play/location/lock')->assertOk();
        $this->get('/play?task=1')->assertSee($step->question_text);
        $this->postJson('/play/location', ['latitude'=>52, 'longitude'=>5])->assertOk();
        $this->get('/play?task=1')->assertDontSee($step->question_text);
        $this->post('/play/submit', ['answer_text'=>'16'])->assertSessionHasErrors('location');
        $this->assertEquals(1, $route->progress()->first()->current_sort_order);
    }
}
