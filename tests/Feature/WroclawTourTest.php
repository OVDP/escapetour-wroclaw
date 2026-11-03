<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\GameRoute;
use App\Models\QuestStep;
use App\Models\StepSubmission;
use App\Services\Gameplay\StepProgressService;
use Database\Seeders\WroclawSeeder;
class WroclawTourTest extends TestCase {
    use RefreshDatabase;
    public function test_complete_tour_with_photo_review_and_final_return(): void {
        Storage::fake('local');
        $this->seed(WroclawSeeder::class);
        $this->seed(WroclawSeeder::class);
        $this->assertDatabaseCount('quest_steps', 12);
        $route = GameRoute::first();
        $this->assertCount(12, $route->questSteps);
        $this->get('/join/'.$route->join_token)->assertOk()->assertSee('Żabka');
        $this->post('/join/'.$route->join_token, ['group_name'=>'Testteam'])->assertRedirect('/play');
        foreach ($route->questSteps as $step) {
            $this->postJson('/play/location', ['latitude'=>$step->location_lat, 'longitude'=>$step->location_lng])->assertOk();
            $this->postJson('/play/location/lock')->assertOk();
            $this->get('/play?task=1')->assertOk()->assertSee($step->title);
            $this->withSession(['admin_authenticated'=>true])->get('/admin/quest-steps/'.$step->id)->assertOk();
            if ($step->step_type === 'photo_upload') {
                $this->post('/play/submit', ['answer_text'=>'Het Zwanenmeer'])->assertSessionHasErrors('photo');
                $this->post('/play/submit', ['answer_text'=>'Het Zwanenmeer', 'photo'=>UploadedFile::fake()->image('selfie.jpg')])->assertSessionHasNoErrors();
                $submission = StepSubmission::latest('id')->first();
                $this->assertEquals('pending', $submission->status);
                Storage::disk('local')->assertExists($submission->photo_path);
                $this->get('/admin/goedkeuringen')->assertOk()->assertSee('Bekijk foto');
                $this->post('/admin/routes/'.$route->id.'/submissions/'.$submission->id.'/approve')->assertSessionHasNoErrors();
            } else {
                $answer = $step->correct_answer === '1926' ? '1924' : strtolower($step->correct_answer);
                $this->post('/play/submit', ['answer_text'=>$answer])->assertSessionHasNoErrors();
            }
            $this->assertEquals($step->pivot->sort_order + 1, $route->progress()->first()->current_sort_order);
        }
        $this->get('/play')->assertOk()->assertSee('Meld je bij de spelleiding!');
        $this->assertNotNull($route->progress()->first()->completed_at);
    }
    public function test_bastion_alternatives_and_wrong_answers(): void {
        $this->seed(WroclawSeeder::class);
        $step = QuestStep::where('title', 'like', '08%')->firstOrFail();
        $service = app(StepProgressService::class);
        foreach (['1926','1924','1924,0'] as $answer) $this->assertTrue($service->acceptsAnswer($step,$answer,true));
        foreach (['','abc','1925'] as $answer) $this->assertFalse($service->acceptsAnswer($step,$answer,true));
        $route = GameRoute::first();
        $this->post('/join/'.$route->join_token);
        $first = $route->questSteps->first();
        $this->postJson('/play/location', ['latitude'=>$first->location_lat, 'longitude'=>$first->location_lng]);
        $this->postJson('/play/location/lock')->assertOk();
        $this->post('/play/submit',['answer_text'=>'17'])->assertSessionHasErrors('timeout');
        $this->assertEquals(1,$route->progress()->first()->current_sort_order);
        $this->assertEquals(30,$route->progress()->first()->timeout_remaining_seconds);
    }
    public function test_submission_photos_are_admin_only(): void {
        $this->seed(WroclawSeeder::class);
        $route = GameRoute::first();
        $this->post('/join/'.$route->join_token);
        $submission = StepSubmission::create([
            'game_route_id'=>$route->id, 'route_player_id'=>$route->players()->first()->id,
            'quest_step_id'=>$route->questSteps->first()->id, 'step_sort_order'=>1,
            'status'=>'pending', 'photo_path'=>'submissions/private.jpg',
        ]);
        $this->get('/admin/inzendingen/'.$submission->id.'/foto')->assertRedirect('/admin/login');
    }
}
