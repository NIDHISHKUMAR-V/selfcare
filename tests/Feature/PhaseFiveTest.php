<?php

namespace Tests\Feature;

use App\Models\GoalMilestone;
use App\Models\PersonalGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseFiveTest extends TestCase
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

    public function test_user_can_view_goals_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('goals.index'));
        $response->assertStatus(200);
    }

    public function test_user_can_create_goal(): void
    {
        $response = $this->actingAs($this->user)->post(route('goals.store'), [
            'title' => 'Run a 10K Marathon',
            'category' => 'Fitness',
            'goal_type' => 'short_term',
            'target_date' => now()->addMonths(3)->toDateString(),
            'description' => 'Train weekly to run 10k comfortably.',
        ]);

        $response->assertRedirect(route('goals.index'));
        $this->assertDatabaseHas('personal_goals', [
            'user_id' => $this->user->id,
            'title' => 'Run a 10K Marathon',
            'category' => 'Fitness',
            'goal_type' => 'short_term',
        ]);
    }

    public function test_user_can_view_goal_detail_with_milestones(): void
    {
        $goal = PersonalGoal::create([
            'user_id' => $this->user->id,
            'title' => 'Read 12 Books',
            'category' => 'Personal Development',
            'goal_type' => 'long_term',
        ]);

        $response = $this->actingAs($this->user)->get(route('goals.show', $goal));
        $response->assertStatus(200);
        $response->assertSee('Read 12 Books');
    }

    public function test_user_can_add_milestone_and_toggle_completion_with_auto_progress(): void
    {
        $goal = PersonalGoal::create([
            'user_id' => $this->user->id,
            'title' => 'Launch Personal Blog',
            'category' => 'Career',
            'goal_type' => 'short_term',
            'progress_percentage' => 0,
            'status' => 'in_progress',
        ]);

        // Add 2 milestones
        $this->actingAs($this->user)->post(route('goals.milestones.store', $goal), [
            'title' => 'Design layout',
        ]);
        $this->actingAs($this->user)->post(route('goals.milestones.store', $goal), [
            'title' => 'Write first 3 posts',
        ]);

        $milestones = $goal->fresh()->milestones;
        $this->assertCount(2, $milestones);

        // Toggle first milestone -> progress becomes 50%
        $this->actingAs($this->user)->post(route('goals.milestones.toggle', $milestones->first()));
        $this->assertEquals(50, $goal->fresh()->progress_percentage);
        $this->assertEquals('in_progress', $goal->fresh()->status);

        // Toggle second milestone -> progress becomes 100% and status becomes completed
        $this->actingAs($this->user)->post(route('goals.milestones.toggle', $milestones->last()));
        $this->assertEquals(100, $goal->fresh()->progress_percentage);
        $this->assertEquals('completed', $goal->fresh()->status);
        $this->assertNotNull($goal->fresh()->completed_at);
    }

    public function test_user_cannot_access_other_users_goal(): void
    {
        $otherGoal = PersonalGoal::create([
            'user_id' => $this->otherUser->id,
            'title' => 'Private Goal',
            'category' => 'Finance',
            'goal_type' => 'long_term',
        ]);

        $response = $this->actingAs($this->user)->get(route('goals.show', $otherGoal));
        $response->assertStatus(403);
    }
}
