<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ExpenseCategory;
use App\Models\Exercise;
use App\Models\FoodLog;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\IncomeCategory;
use App\Models\PersonalGoal;
use App\Models\SleepLog;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WaterLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create a demo user
        $user = User::updateOrCreate(
            ['email' => 'demo@myselfcare.app'],
            [
                'name' => 'Alex Johnson',
                'password' => Hash::make('password'),
                'phone' => '+91 98765 43210',
                'bio' => 'Living my best life, one habit at a time. 🌿',
                'daily_calorie_goal' => 2000,
                'daily_water_goal_ml' => 2500,
                'monthly_budget_limit' => 30000.00,
            ]
        );

        // Income & Expense categories
        $this->seedCategories($user);

        // Last 14 days of data
        $today = Carbon::today();
        for ($i = 13; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $this->seedFoodLogs($user, $date);
            $this->seedWaterLogs($user, $date);
            $this->seedTransactions($user, $date);
            $this->seedSleepLog($user, $date);
        }

        $this->seedActivities($user, $today);
        $this->seedHabits($user, $today);
        $this->seedExercises($user, $today);
        $this->seedGoals($user);
    }

    private function seedCategories(User $user): void
    {
        $incomeCategories = [
            ['name' => 'Salary', 'icon' => 'briefcase', 'color' => '#7FA68E'],
            ['name' => 'Freelance', 'icon' => 'laptop', 'color' => '#8E8CD8'],
            ['name' => 'Business', 'icon' => 'building', 'color' => '#F4A261'],
            ['name' => 'Investment', 'icon' => 'trending-up', 'color' => '#E8A598'],
            ['name' => 'Gift', 'icon' => 'gift', 'color' => '#A8D8B9'],
            ['name' => 'Other', 'icon' => 'plus-circle', 'color' => '#C8B8D8'],
        ];
        $expenseCategories = [
            ['name' => 'Food', 'icon' => 'utensils', 'color' => '#F4A261'],
            ['name' => 'Transportation', 'icon' => 'car', 'color' => '#8E8CD8'],
            ['name' => 'Shopping', 'icon' => 'shopping-bag', 'color' => '#E8A598'],
            ['name' => 'Bills', 'icon' => 'file-text', 'color' => '#C8B8D8'],
            ['name' => 'Entertainment', 'icon' => 'film', 'color' => '#A8D8B9'],
            ['name' => 'Healthcare', 'icon' => 'heart', 'color' => '#F4A261'],
            ['name' => 'Education', 'icon' => 'book', 'color' => '#7FA68E'],
            ['name' => 'Rent', 'icon' => 'home', 'color' => '#8E8CD8'],
            ['name' => 'Personal Care', 'icon' => 'smile', 'color' => '#E8A598'],
            ['name' => 'Other', 'icon' => 'more-horizontal', 'color' => '#C8B8D8'],
        ];

        foreach ($incomeCategories as $cat) {
            IncomeCategory::firstOrCreate(array_merge($cat, ['user_id' => $user->id]));
        }
        foreach ($expenseCategories as $cat) {
            ExpenseCategory::firstOrCreate(array_merge($cat, ['user_id' => $user->id]));
        }
    }

    private function seedFoodLogs(User $user, Carbon $date): void
    {
        $meals = [
            ['meal_type' => 'breakfast', 'food_name' => 'Oatmeal with Fruits', 'calories' => 320, 'protein' => 10, 'carbohydrates' => 55, 'fat' => 7],
            ['meal_type' => 'lunch', 'food_name' => 'Grilled Chicken Salad', 'calories' => 480, 'protein' => 42, 'carbohydrates' => 20, 'fat' => 18],
            ['meal_type' => 'dinner', 'food_name' => 'Brown Rice & Dal', 'calories' => 550, 'protein' => 20, 'carbohydrates' => 90, 'fat' => 10],
            ['meal_type' => 'snack', 'food_name' => 'Mixed Nuts', 'calories' => 180, 'protein' => 6, 'carbohydrates' => 8, 'fat' => 15],
        ];

        // Pick 2-4 meals
        $count = rand(2, 4);
        foreach (array_slice($meals, 0, $count) as $meal) {
            FoodLog::create(array_merge($meal, [
                'user_id' => $user->id,
                'quantity' => 1,
                'meal_date' => $date,
                'meal_time' => now()->format('H:i:s'),
            ]));
        }
    }

    private function seedWaterLogs(User $user, Carbon $date): void
    {
        $entries = rand(3, 6);
        for ($i = 0; $i < $entries; $i++) {
            WaterLog::create([
                'user_id' => $user->id,
                'amount_ml' => [250, 300, 350, 500][rand(0, 3)],
                'log_date' => $date,
                'log_time' => now()->format('H:i:s'),
            ]);
        }
    }

    private function seedTransactions(User $user, Carbon $date): void
    {
        $incomeCategory = IncomeCategory::where('user_id', $user->id)->where('name', 'Salary')->first();
        $expenseCategories = ExpenseCategory::where('user_id', $user->id)->get()->keyBy('name');

        // Add salary on 1st of month
        if ($date->day === 1) {
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'income',
                'title' => 'Monthly Salary',
                'amount' => 50000,
                'income_category_id' => $incomeCategory?->id,
                'payment_method' => 'Bank Transfer',
                'transaction_date' => $date,
            ]);
        }

        // Daily expenses
        $expenseOptions = [
            ['title' => 'Lunch', 'amount' => rand(80, 200), 'category' => 'Food'],
            ['title' => 'Auto Fare', 'amount' => rand(30, 80), 'category' => 'Transportation'],
            ['title' => 'Coffee', 'amount' => rand(50, 120), 'category' => 'Food'],
            ['title' => 'Grocery', 'amount' => rand(200, 500), 'category' => 'Food'],
            ['title' => 'Mobile Bill', 'amount' => 299, 'category' => 'Bills'],
        ];

        $expense = $expenseOptions[rand(0, count($expenseOptions) - 1)];
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'expense',
            'title' => $expense['title'],
            'amount' => $expense['amount'],
            'expense_category_id' => $expenseCategories[$expense['category']]?->id,
            'payment_method' => ['Cash', 'Card', 'UPI'][rand(0, 2)],
            'transaction_date' => $date,
        ]);
    }

    private function seedSleepLog(User $user, Carbon $date): void
    {
        $sleepHour = rand(21, 23);
        $sleepTime = $date->copy()->setTime($sleepHour, rand(0, 59));
        $duration = rand(360, 510); // 6–8.5 hours
        $wakeTime = $sleepTime->copy()->addMinutes($duration);

        SleepLog::create([
            'user_id' => $user->id,
            'sleep_date' => $date,
            'sleep_time' => $sleepTime,
            'wake_time' => $wakeTime,
            'duration_minutes' => $duration,
            'quality_rating' => rand(3, 5),
        ]);
    }

    private function seedActivities(User $user, Carbon $today): void
    {
        $activities = [
            ['title' => 'Morning Meditation', 'category' => 'Self-Care', 'priority' => 'high', 'status' => 'completed'],
            ['title' => 'Team Meeting', 'category' => 'Work', 'priority' => 'high', 'status' => 'completed'],
            ['title' => 'Read 30 mins', 'category' => 'Personal', 'priority' => 'medium', 'status' => 'in_progress'],
            ['title' => 'Evening Walk', 'category' => 'Health', 'priority' => 'medium', 'status' => 'pending'],
            ['title' => 'Plan tomorrow', 'category' => 'Work', 'priority' => 'low', 'status' => 'pending'],
        ];

        foreach ($activities as $activity) {
            Activity::create(array_merge($activity, [
                'user_id' => $user->id,
                'activity_date' => $today,
                'completed_at' => $activity['status'] === 'completed' ? now() : null,
            ]));
        }
    }

    private function seedHabits(User $user, Carbon $today): void
    {
        $habits = [
            ['name' => 'Wake up early', 'category' => 'Mindset', 'icon' => 'sunrise', 'color' => '#F4A261'],
            ['name' => 'Exercise 30 min', 'category' => 'Fitness', 'icon' => 'dumbbell', 'color' => '#7FA68E'],
            ['name' => 'Read books', 'category' => 'Productivity', 'icon' => 'book', 'color' => '#8E8CD8'],
            ['name' => 'Drink water', 'category' => 'Health', 'icon' => 'droplets', 'color' => '#3B82F6'],
            ['name' => 'Meditate', 'category' => 'Mindset', 'icon' => 'heart', 'color' => '#EC4899'],
            ['name' => 'Sleep on time', 'category' => 'Health', 'icon' => 'moon', 'color' => '#A78BFA'],
        ];

        foreach ($habits as $habitData) {
            $habit = Habit::create(array_merge($habitData, ['user_id' => $user->id]));

            // Complete most habits today
            if (rand(0, 4) > 0) {
                HabitLog::create([
                    'habit_id' => $habit->id,
                    'user_id' => $user->id,
                    'completed_date' => $today,
                    'is_completed' => true,
                ]);
            }
        }
    }

    private function seedExercises(User $user, Carbon $today): void
    {
        Exercise::create([
            'user_id' => $user->id,
            'activity_type' => 'Running',
            'duration_minutes' => 35,
            'distance_km' => 4.5,
            'calories_burned' => 310,
            'exercise_date' => $today,
            'exercise_time' => '06:30:00',
            'notes' => 'Morning run in the park',
        ]);

        Exercise::create([
            'user_id' => $user->id,
            'activity_type' => 'Yoga',
            'duration_minutes' => 20,
            'calories_burned' => 95,
            'exercise_date' => $today->copy()->subDay(),
            'exercise_time' => '07:00:00',
        ]);
    }

    private function seedGoals(User $user): void
    {
        $goals = [
            ['title' => 'Lose 5kg in 3 months', 'category' => 'Fitness', 'goal_type' => 'short_term', 'progress_percentage' => 40],
            ['title' => 'Save ₹1,00,000 this year', 'category' => 'Finance', 'goal_type' => 'long_term', 'progress_percentage' => 25],
            ['title' => 'Read 12 books this year', 'category' => 'Education', 'goal_type' => 'long_term', 'progress_percentage' => 58],
        ];

        foreach ($goals as $goal) {
            PersonalGoal::create(array_merge($goal, [
                'user_id' => $user->id,
                'status' => 'in_progress',
                'target_date' => now()->addMonths(rand(2, 8))->toDateString(),
            ]));
        }
    }
}
