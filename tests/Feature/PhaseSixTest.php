<?php

namespace Tests\Feature;

use App\Models\FoodLog;
use App\Models\PersonalGoal;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WaterLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseSixTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['daily_water_goal_ml' => 2500]);
    }

    public function test_user_can_view_reports_index_default_preset(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));
        $response->assertStatus(200);
        $response->assertSee('Life Analytics &amp; Reports', false);
    }

    public function test_user_can_filter_reports_by_preset_and_custom_dates(): void
    {
        $today = now()->toDateString();
        // Seed some data
        FoodLog::create([
            'user_id' => $this->user->id,
            'meal_type' => 'lunch',
            'food_name' => 'Grilled Chicken Salad',
            'calories' => 450,
            'meal_date' => $today,
        ]);

        WaterLog::create([
            'user_id' => $this->user->id,
            'amount_ml' => 1000,
            'log_date' => $today,
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.index', [
            'preset' => 'today',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Reporting period:');
    }
}
