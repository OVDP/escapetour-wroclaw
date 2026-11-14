<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\WroclawSeeder;
use App\Models\GameRoute;
use App\Models\StepSubmission;
class AnswerReviewTest extends TestCase {
    use RefreshDatabase;
    public function test_exact_answers_advance_and_other_answers_wait_for_approval(): void {
        $this->seed(WroclawSeeder::class);
        $route = GameRoute::first();
        $this->post('/join/'.$route->join_token);
        foreach ($route->questSteps->filter(fn($s)=>$s->review_on_mismatch) as $step) {
            $order = (int) $step->pivot->sort_order;
            $route->progress()->update(['current_sort_order'=>$order,'completed_at'=>null]);
            $this->postJson('/play/location', ['latitude'=>$step->location_lat,'longitude'=>$step->location_lng])->assertOk();
            $this->postJson('/play/location/lock')->assertOk();
            $this->post('/play/submit',['answer_text'=>$step->correct_answer])->assertSessionHasNoErrors();
            $this->assertEquals($order+1,$route->progress()->first()->current_sort_order);
            $this->assertEquals('auto_passed',StepSubmission::latest('id')->first()->status);
            $route->progress()->update(['current_sort_order'=>$order,'completed_at'=>null]);
            $this->postJson('/play/location', ['latitude'=>$step->location_lat,'longitude'=>$step->location_lng])->assertOk();
            $this->postJson('/play/location/lock')->assertOk();
            $this->post('/play/submit',['answer_text'=>''])->assertSessionHasErrors('answer');
            $this->post('/play/submit',['answer_text'=>'alternatieve spelling'])->assertSessionHasNoErrors();
            $pending = StepSubmission::latest('id')->first();
            $this->assertEquals('pending',$pending->status);
            $this->assertEquals($order,$route->progress()->first()->current_sort_order);
            $this->assertEquals(0,$route->progress()->first()->timeout_remaining_seconds);
            $count = StepSubmission::count();
            $this->post('/play/submit',['answer_text'=>$step->correct_answer]);
            $this->assertEquals($count,StepSubmission::count());
            $this->assertEquals($order,$route->progress()->first()->current_sort_order);
            $this->withSession(['admin_authenticated'=>true])->post('/admin/routes/'.$route->id.'/submissions/'.$pending->id.'/approve')->assertSessionHasNoErrors();
            $this->assertEquals($order+1,$route->progress()->first()->current_sort_order);
        }
        $this->assertCount(3,$route->questSteps->filter(fn($s)=>$s->review_on_mismatch));
        $this->get('/play')->assertDontSee('Vrijgespeelde verhaallijn')->assertDontSee('Test je GPS-locatie in Utrecht');
        $this->get('/admin/quest-steps/create')->assertOk()->assertDontSee('Materiaalinzending via WhatsApp')->assertDontSee('Beloning: letter')->assertDontSee('Beloning: verdachte');
        $this->get('/admin/quest-steps/'.$step->id.'/edit')->assertOk()->assertDontSee('Materiaalinzending via WhatsApp')->assertDontSee('Beloning: letter')->assertDontSee('Beloning: verdachte');
    }
}
