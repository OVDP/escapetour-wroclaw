<?php
namespace Database\Seeders;
use App\Models\Game;
use App\Models\GameRoute;
use App\Models\QuestStep;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class WroclawSeeder extends Seeder {
    public function run(): void {
        DB::transaction(function () {
            $intro = 'Het Biedronka lieveheersbeestje heeft de Żabka-kikker ontvoerd. Verzamel zo veel mogelijk informatie voor de policja, zodat ze de Żabka-kikker kunnen redden.';
            $game = Game::updateOrCreate(['slug' => 'red-de-zabka-kikker'], [
                'name' => 'Red de Żabka-kikker', 'status' => 'live',
                'incident_location' => 'Plac Solny, Wrocław', 'case_briefing' => $intro,
                'story_intro' => $intro,
                'story_final' => 'Jullie hebben alle 12 opdrachten afgerond! Ga terug naar Krasnal WOTuś, het startpunt, en geef jullie bevindingen door aan de spelleiding. De policja kan hiermee verder zoeken naar de Żabka-kikker.',
                'possible_suspects' => ['Biedronka lieveheersbeestje'],
            ]);
            $route = GameRoute::firstOrCreate(['game_id' => $game->id], [
                'start_assigned' => true, 'name' => 'Wrocław', 'description' => '12 locaties door Wrocław; na het marktplein terug naar Krasnal WOTuś. Route: https://www.komoot.com/tour/3363999922',
            ]);
            $links = [];
            foreach (json_decode(file_get_contents(database_path('data/wroclaw.json')), true, flags: JSON_THROW_ON_ERROR) as $item) {
                $step = QuestStep::updateOrCreate(['game_id' => $game->id, 'title' => $item['title']], [
                    'question_text' => $item['question_text'], 'correct_answer' => $item['correct_answer'],
                    'acceptable_answers' => $item['acceptable_answers'], 'question_image' => $item['question_image'],
                    'review_notes' => $item['review_notes'], 'manual_approval' => $item['manual_approval'],
                    'step_type' => $item['manual_approval'] ? 'photo_upload' : 'text_answer',
                    'question_type' => in_array($item['number'], [1,3,5,8,10]) ? 'numeric' : 'direct',
                    'requires_location' => true, 'location_lat' => $item['latitude'], 'location_lng' => $item['longitude'],
                    'location_address' => $item['title'].', Wrocław', 'location_radius_meters' => 100,
                    'timeout_seconds' => 30,
                    'reward_story_text' => 'Bewijs verzameld bij '.$item['title'].'.',
                ]);
                $links[$step->id] = ['sort_order' => $item['number']];
            }
            if ($route->start_assigned && ($route->wasRecentlyCreated || $route->questSteps()->doesntExist())) {
                $route->questSteps()->sync($links);
            }
        });
    }
}
