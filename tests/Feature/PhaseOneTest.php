<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\FoodLog;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WaterLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhaseOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_registration_pages_are_accessible(): void
    {
        $this->get('/login')->assertStatus(200)->assertSee('Sign In');
        $this->get('/register')->assertStatus(200)->assertSee('Create Account');
        $this->get('/forgot-password')->assertStatus(200)->assertSee('Forgot password');
    }

    public function test_guest_is_redirected_from_protected_routes(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/profile')->assertRedirect(route('login'));
        $this->get('/profile/edit')->assertRedirect(route('login'));
    }

    public function test_new_user_can_register_and_categories_are_automatically_created(): void
    {
        $response = $this->post('/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'jane@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Jane Doe', $user->name);

        // Verify default categories seeded for new user
        $this->assertGreaterThan(0, $user->incomeCategories()->count());
        $this->assertGreaterThan(0, $user->expenseCategories()->count());
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Login with correct credentials
        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        // Logout
        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_dashboard_displays_real_user_data_and_widgets(): void
    {
        $user = User::create([
            'name' => 'Alex Tester',
            'email' => 'alex@example.com',
            'password' => Hash::make('password123'),
            'daily_calorie_goal' => 2200,
            'daily_water_goal_ml' => 3000,
        ]);

        $today = Carbon::today();

        // Add food log
        FoodLog::create([
            'user_id' => $user->id,
            'meal_type' => 'breakfast',
            'food_name' => 'Avocado Toast',
            'calories' => 350,
            'protein' => 12,
            'carbohydrates' => 30,
            'fat' => 18,
            'meal_date' => $today,
        ]);

        // Add water log
        WaterLog::create([
            'user_id' => $user->id,
            'amount_ml' => 750,
            'log_date' => $today,
        ]);

        // Add expense transaction
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'expense',
            'title' => 'Fresh Juice',
            'amount' => 120.00,
            'transaction_date' => $today,
        ]);

        // Add activity
        Activity::create([
            'user_id' => $user->id,
            'title' => 'Daily Meditation',
            'category' => 'Self-Care',
            'priority' => 'high',
            'status' => 'completed',
            'activity_date' => $today,
        ]);

        // Add habit
        $habit = Habit::create([
            'user_id' => $user->id,
            'name' => 'Evening Stretch',
            'category' => 'Fitness',
        ]);
        HabitLog::create([
            'user_id' => $user->id,
            'habit_id' => $habit->id,
            'completed_date' => $today,
            'is_completed' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Alex Tester');
        $response->assertSee('350'); // calories
        $response->assertSee('750'); // water
        $response->assertSee('120.00'); // expenses
        $response->assertSee('Fresh Juice');
    }

    public function test_user_can_update_profile_and_goals(): void
    {
        $user = User::create([
            'name' => 'Sam Smith',
            'email' => 'sam@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)->put('/profile/update', [
            'name' => 'Samantha Smith',
            'email' => 'sam@example.com',
            'phone' => '+1 555-0199',
            'bio' => 'Mindfulness and wellness journey.',
            'daily_calorie_goal' => 1850,
            'daily_water_goal_ml' => 2800,
            'monthly_budget_limit' => 25000.00,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();

        $this->assertEquals('Samantha Smith', $user->name);
        $this->assertEquals('+1 555-0199', $user->phone);
        $this->assertEquals(1850, $user->daily_calorie_goal);
        $this->assertEquals(2800, $user->daily_water_goal_ml);
        $this->assertEquals(25000.00, $user->monthly_budget_limit);
    }

    public function test_user_can_change_password(): void
    {
        $user = User::create([
            'name' => 'Charlie',
            'email' => 'charlie@example.com',
            'password' => Hash::make('oldpassword'),
        ]);

        $response = $this->actingAs($user)->put('/profile/change-password', [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();

        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }
}
