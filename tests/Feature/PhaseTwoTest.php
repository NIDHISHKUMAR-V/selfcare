<?php

namespace Tests\Feature;

use App\Models\FoodLog;
use App\Models\User;
use App\Models\WaterLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhaseTwoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Foodie Tester',
            'email' => 'foodie@example.com',
            'password' => Hash::make('password123'),
            'daily_calorie_goal' => 2000,
            'daily_water_goal_ml' => 2500,
        ]);

        $this->otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_guest_cannot_access_food_or_water_routes(): void
    {
        $this->get('/food')->assertRedirect(route('login'));
        $this->get('/food/create')->assertRedirect(route('login'));
        $this->get('/food/reports')->assertRedirect(route('login'));
        $this->get('/water')->assertRedirect(route('login'));
    }

    public function test_user_can_view_daily_food_diary(): void
    {
        $today = Carbon::today();

        FoodLog::create([
            'user_id' => $this->user->id,
            'meal_type' => 'breakfast',
            'food_name' => 'Greek Yogurt & Berries',
            'quantity' => 1,
            'serving_size' => '1 cup',
            'calories' => 220,
            'protein' => 18,
            'carbohydrates' => 24,
            'fat' => 4,
            'meal_date' => $today,
        ]);

        $response = $this->actingAs($this->user)->get('/food');

        $response->assertStatus(200);
        $response->assertSee('Greek Yogurt', false);
        $response->assertSee('220');
        $response->assertSee('Breakfast');
    }

    public function test_user_can_log_food_item_with_optional_photo(): void
    {
        Storage::fake('public');

        $photo = UploadedFile::fake()->image('salad.jpg', 600, 600);

        $response = $this->actingAs($this->user)->post('/food', [
            'meal_type' => 'lunch',
            'food_name' => 'Quinoa Salad',
            'quantity' => 1.5,
            'serving_size' => '1 bowl',
            'calories' => 420,
            'protein' => 14,
            'carbohydrates' => 58,
            'fat' => 12,
            'meal_date' => Carbon::today()->toDateString(),
            'meal_time' => '13:00',
            'notes' => 'With lemon vinaigrette dressing',
            'image' => $photo,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('food_logs', [
            'user_id' => $this->user->id,
            'food_name' => 'Quinoa Salad',
            'meal_type' => 'lunch',
            'calories' => 420,
        ]);

        $created = FoodLog::where('food_name', 'Quinoa Salad')->first();
        $this->assertNotNull($created->image_path);
        Storage::disk('public')->assertExists($created->image_path);
    }

    public function test_user_can_edit_and_update_food_item(): void
    {
        $foodLog = FoodLog::create([
            'user_id' => $this->user->id,
            'meal_type' => 'dinner',
            'food_name' => 'Steak',
            'quantity' => 1,
            'calories' => 600,
            'protein' => 50,
            'carbohydrates' => 0,
            'fat' => 35,
            'meal_date' => Carbon::today(),
        ]);

        $response = $this->actingAs($this->user)->get("/food/{$foodLog->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Steak');

        $updateResponse = $this->actingAs($this->user)->put("/food/{$foodLog->id}", [
            'meal_type' => 'dinner',
            'food_name' => 'Grilled Ribeye Steak',
            'quantity' => 1,
            'calories' => 550,
            'protein' => 48,
            'carbohydrates' => 0,
            'fat' => 30,
            'meal_date' => Carbon::today()->toDateString(),
        ]);

        $updateResponse->assertRedirect();
        $foodLog->refresh();

        $this->assertEquals('Grilled Ribeye Steak', $foodLog->food_name);
        $this->assertEquals(550, $foodLog->calories);
    }

    public function test_user_can_delete_food_item(): void
    {
        $foodLog = FoodLog::create([
            'user_id' => $this->user->id,
            'meal_type' => 'snack',
            'food_name' => 'Almonds',
            'quantity' => 1,
            'calories' => 160,
            'meal_date' => Carbon::today(),
        ]);

        $response = $this->actingAs($this->user)->delete("/food/{$foodLog->id}");
        $response->assertRedirect();

        $this->assertDatabaseMissing('food_logs', ['id' => $foodLog->id]);
    }

    public function test_user_cannot_access_or_modify_another_users_food_record(): void
    {
        $otherLog = FoodLog::create([
            'user_id' => $this->otherUser->id,
            'meal_type' => 'lunch',
            'food_name' => 'Secret Recipe',
            'quantity' => 1,
            'calories' => 500,
            'meal_date' => Carbon::today(),
        ]);

        $this->actingAs($this->user)->get("/food/{$otherLog->id}/edit")->assertStatus(403);

        $this->actingAs($this->user)->put("/food/{$otherLog->id}", [
            'meal_type' => 'lunch',
            'food_name' => 'Hacked Food',
            'quantity' => 1,
            'calories' => 100,
            'meal_date' => Carbon::today()->toDateString(),
        ])->assertStatus(403);

        $this->actingAs($this->user)->delete("/food/{$otherLog->id}")->assertStatus(403);
    }

    public function test_user_can_view_food_reports(): void
    {
        $today = Carbon::today();
        for ($i = 0; $i < 5; $i++) {
            FoodLog::create([
                'user_id' => $this->user->id,
                'meal_type' => 'lunch',
                'food_name' => "Meal {$i}",
                'quantity' => 1,
                'calories' => 400 + ($i * 50),
                'protein' => 25,
                'carbohydrates' => 45,
                'fat' => 15,
                'meal_date' => $today->copy()->subDays($i),
            ]);
        }

        $responseWeekly = $this->actingAs($this->user)->get('/food/reports?period=weekly');
        $responseWeekly->assertStatus(200);
        $responseWeekly->assertSee('Daily Calorie Consumption Trend');
        $responseWeekly->assertSee('Macronutrient Breakdown');

        $responseMonthly = $this->actingAs($this->user)->get('/food/reports?period=monthly');
        $responseMonthly->assertStatus(200);
    }

    public function test_user_can_view_water_tracker_and_log_water(): void
    {
        $response = $this->actingAs($this->user)->get('/water');
        $response->assertStatus(200);
        $response->assertSee('Hydration Level');

        $postResponse = $this->actingAs($this->user)->post('/water', [
            'amount_ml' => 350,
            'log_date' => Carbon::today()->toDateString(),
            'notes' => 'Morning glass',
        ]);

        $postResponse->assertRedirect();
        $this->assertDatabaseHas('water_logs', [
            'user_id' => $this->user->id,
            'amount_ml' => 350,
        ]);
    }

    public function test_user_can_quick_add_water_presets(): void
    {
        $response = $this->actingAs($this->user)->post('/water/quick-add', [
            'amount_ml' => 500,
            'date' => Carbon::today()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('water_logs', [
            'user_id' => $this->user->id,
            'amount_ml' => 500,
        ]);
    }

    public function test_user_can_update_daily_water_goal(): void
    {
        $response = $this->actingAs($this->user)->put('/water/goal', [
            'daily_water_goal_ml' => 3200,
        ]);

        $response->assertRedirect();
        $this->user->refresh();

        $this->assertEquals(3200, $this->user->daily_water_goal_ml);
    }

    public function test_user_cannot_delete_another_users_water_log(): void
    {
        $otherWater = WaterLog::create([
            'user_id' => $this->otherUser->id,
            'amount_ml' => 250,
            'log_date' => Carbon::today(),
        ]);

        $this->actingAs($this->user)->delete("/water/{$otherWater->id}")->assertStatus(403);
    }
}
