<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoalMilestoneRequest;
use App\Http\Requests\PersonalGoalRequest;
use App\Models\GoalMilestone;
use App\Models\PersonalGoal;
use App\Services\GoalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalGoalController extends Controller
{
    public function __construct(
        protected GoalService $goalService
    ) {}

    /**
     * Display goals dashboard.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $selectedCategory = $request->query('category', 'All');
        $selectedType = $request->query('type', 'All');

        $data = $this->goalService->getGoalsDashboard($user, $selectedCategory, $selectedType);

        $categories = ['Health', 'Finance', 'Fitness', 'Career', 'Education', 'Personal Development'];

        return view('goals.index', [
            'data' => $data,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'selectedType' => $selectedType,
        ]);
    }

    /**
     * Store a newly created goal.
     */
    public function store(PersonalGoalRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['progress_percentage'] = $data['progress_percentage'] ?? 0;
        $data['status'] = $data['status'] ?? 'in_progress';

        if ($data['status'] === 'completed') {
            $data['completed_at'] = now();
            $data['progress_percentage'] = 100;
        }

        $goal = PersonalGoal::create($data);

        return redirect()->route('goals.index')->with('success', "Goal '{$goal->title}' created successfully!");
    }

    /**
     * Display goal detail view with milestones.
     */
    public function show(PersonalGoal $goal): View
    {
        if ($goal->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to goal.');
        }

        $goal->load('milestones');

        return view('goals.show', [
            'goal' => $goal,
        ]);
    }

    /**
     * Update an existing goal.
     */
    public function update(PersonalGoalRequest $request, PersonalGoal $goal): RedirectResponse
    {
        if ($goal->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to goal.');
        }

        $data = $request->validated();

        if (isset($data['status'])) {
            if ($data['status'] === 'completed' && $goal->status !== 'completed') {
                $data['completed_at'] = now();
                $data['progress_percentage'] = 100;
            } elseif ($data['status'] !== 'completed' && $goal->status === 'completed') {
                $data['completed_at'] = null;
            }
        }

        $goal->update($data);

        return redirect()->route('goals.index')->with('success', 'Goal updated successfully!');
    }

    /**
     * Delete a goal.
     */
    public function destroy(PersonalGoal $goal): RedirectResponse
    {
        if ($goal->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to goal.');
        }

        $goal->delete();

        return redirect()->route('goals.index')->with('success', 'Goal removed successfully.');
    }

    /**
     * Add a milestone to a goal.
     */
    public function addMilestone(GoalMilestoneRequest $request, PersonalGoal $goal): RedirectResponse
    {
        if ($goal->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to goal.');
        }

        $data = $request->validated();
        $goal->milestones()->create([
            'title' => $data['title'],
            'due_date' => $data['due_date'] ?? null,
            'is_completed' => false,
        ]);

        $this->goalService->recalculateProgress($goal);

        return redirect()->back()->with('success', 'Milestone added!');
    }

    /**
     * Toggle a milestone completed / pending.
     */
    public function toggleMilestone(GoalMilestone $milestone): RedirectResponse
    {
        $goal = $milestone->goal;
        if ($goal->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to milestone.');
        }

        $isCompleted = !$milestone->is_completed;
        $milestone->update([
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? now() : null,
        ]);

        $this->goalService->recalculateProgress($goal);

        $msg = $isCompleted ? 'Milestone marked as complete! 🎯' : 'Milestone marked as pending.';

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Delete a milestone.
     */
    public function destroyMilestone(GoalMilestone $milestone): RedirectResponse
    {
        $goal = $milestone->goal;
        if ($goal->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to milestone.');
        }

        $milestone->delete();
        $this->goalService->recalculateProgress($goal);

        return redirect()->back()->with('success', 'Milestone removed.');
    }
}
