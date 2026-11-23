<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
            ]);

            $this->seedDefaultCategories($user);

            return $user;
        });

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Welcome to My Self-Care! Your account has been created.');
    }

    /**
     * Seed default income and expense categories for a new user.
     */
    private function seedDefaultCategories(User $user): void
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

        foreach ($incomeCategories as $category) {
            IncomeCategory::create(array_merge($category, ['user_id' => $user->id]));
        }

        foreach ($expenseCategories as $category) {
            ExpenseCategory::create(array_merge($category, ['user_id' => $user->id]));
        }
    }
}
