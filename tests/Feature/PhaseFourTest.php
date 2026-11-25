<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Exercise;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\SleepLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseFourTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    public function test_user_can_view_activities_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('activities.index'));
        $response->assertStatus(200);
    }

    public function test_user_can_create_activity(): void
    {
        $response = $this->actingAs($this->user)->post(route('activities.store'), [
            'title' => 'Morning Mindfulness Meditation',
            'category' => 'Self-Care',
            'priority' => 'high',
            'status' => 'pending',
            'activity_date' => now()->toDateString(),
            'start_time' => '07:00',
            'end_time' => '07:30',
            'description' => '15 mins breathing and stretching.',
        ]);

        $response->assertRedirect(route('activities.index', ['date' => now()->toDateString()]));
        $this->assertDatabaseHas('activities', [
            'user_id' => $this->user->id,
            'title' => 'Morning Mindfulness Meditation',
            'priority' => 'high',
            'status' => 'pending',
        ]);
    }

    public function test_user_can_toggle_activity_status(): void
    {
        $activity = Activity::create([
            'user_id' => $this->user->id,
            'title' => 'Evening Walk',
            'priority' => 'medium',
            'status' => 'pending',
            'activity_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->user)->post(route('activities.toggle', $activity));
        $response->assertRedirect();
        $this->assertEquals('completed', $activity->fresh()->status);
        $this->assertNotNull($activity->fresh()->completed_at);

        // Toggle back to pending
        $this->actingAs($this->user)->post(route('activities.toggle', $activity));
        $this->assertEquals('pending', $activity->fresh()->status);
        $this->assertNull($activity->fresh()->completed_at);
    }

    public function test_user_cannot_access_other_users_activity(): void
    {
        $otherActivity = Activity::create([
            'user_id' => $this->otherUser->id,
            'title' => 'Secret Activity',
            'priority' => 'low',
            'status' => 'pending',
            'activity_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->user)->put(route('activities.update', $otherActivity), [
            'title' => 'Hacked Activity',
            'category' => 'Work',
            'priority' => 'high',
            'status' => 'completed',
            'activity_date' => now()->toDateString(),
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_view_habits_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('habits.index'));
        $response->assertStatus(200);
    }

    public function test_user_can_create_habit(): void
    {
        $response = $this->actingAs($this->user)->post(route('habits.store'), [
            'name' => 'Drink 2L Water Daily',
            'category' => 'Health',
            'frequency' => 'daily',
            'target_days_per_week' => 7,
            'color' => '#3B82F6',
            'icon' => 'droplets',
            'description' => 'Stay hydrated throughout the workday.',
        ]);

        $response->assertRedirect(route('habits.index'));
        $this->assertDatabaseHas('habits', [
            'user_id' => $this->user->id,
            'name' => 'Drink 2L Water Daily',
            'category' => 'Health',
            'frequency' => 'daily',
        ]);
    }

    public function test_user_can_toggle_habit_completion(): void
    {
        $habit = Habit::create([
            'user_id' => $this->user->id,
            'name' => 'Read Book',
            'category' => 'Productivity',
            'frequency' => 'daily',
            'target_days_per_week' => 7,
        ]);

        $today = now()->toDateString();

        // Toggle ON
        $response = $this->actingAs($this->user)->post(route('habits.toggle', $habit), [
            'date' => $today,
        ]);
        $response->assertRedirect();
        $this->assertTrue($habit->isCompletedOn($today));

        // Toggle OFF
        $this->actingAs($this->user)->post(route('habits.toggle', $habit), [
            'date' => $today,
        ]);
        $this->assertFalse($habit->isCompletedOn($today));
    }

    public function test_user_can_view_exercise_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('exercise.index'));
        $response->assertStatus(200);
    }

    public function test_user_can_log_exercise(): void
    {
        $today = now()->toDateString();
        $response = $this->actingAs($this->user)->post(route('exercise.store'), [
            'activity_type' => 'Running',
            'duration_minutes' => 30,
            'calories_burned' => 280,
            'distance_km' => 4.25,
            'exercise_date' => $today,
            'exercise_time' => '08:00',
            'notes' => 'Felt strong, good cadence.',
        ]);

        $response->assertRedirect(route('exercise.index', ['date' => $today]));
        $this->assertDatabaseHas('exercises', [
            'user_id' => $this->user->id,
            'activity_type' => 'Running',
            'duration_minutes' => 30,
            'calories_burned' => 280,
        ]);
    }

    public function test_user_can_view_sleep_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('sleep.index'));
        $response->assertStatus(200);
    }

    public function test_user_can_log_sleep_with_duration_calculation(): void
    {
        $bedtime = now()->subDay()->setTime(23, 0)->format('Y-m-d\TH:i');
        $wakeTime = now()->setTime(7, 30)->format('Y-m-d\TH:i');

        $response = $this->actingAs($this->user)->post(route('sleep.store'), [
            'sleep_date' => now()->subDay()->toDateString(),
            'sleep_time' => $bedtime,
            'wake_time' => $wakeTime,
            'quality_rating' => 5,
            'notes' => 'Deep restful sleep without interruption.',
        ]);

        $response->assertRedirect(route('sleep.index'));
        $this->assertDatabaseHas('sleep_logs', [
            'user_id' => $this->user->id,
            'duration_minutes' => 510, // 8.5 hours = 510 minutes
            'quality_rating' => 5,
        ]);
    }
}
