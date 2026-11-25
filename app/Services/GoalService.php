<?php

namespace App\Services;

use App\Models\GoalMilestone;
use App\Models\PersonalGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GoalService
{
    /**
     * Get goals grouped by status and stats for a user.
     *
     * @return array{
     *     goals: Collection<int, PersonalGoal>,
     *     total: int,
     *     in_progress: int,
     *     completed: int,
     *     on_hold: int,
     *     avg_progress: int,
     *     categories: array<string, int>
     * }
     */
    public function getGoalsDashboard(User $user, ?string $category = null, ?string $type = null): array
    {
        $query = PersonalGoal::with('milestones')
            ->where('user_id', $user->id);

        if ($category && $category !== 'All') {
            $query->where('category', $category);
        }

        if ($type && $type !== 'All') {
            $query->where('goal_type', $type);
        }

        $goals = $query->orderByRaw("CASE status WHEN 'in_progress' THEN 1 WHEN 'on_hold' THEN 2 WHEN 'completed' THEN 3 END")
            ->orderBy('target_date')
            ->get();

        $allUserGoals = PersonalGoal::where('user_id', $user->id)->get();
        $total = $allUserGoals->count();
        $inProgress = $allUserGoals->where('status', 'in_progress')->count();
        $completed = $allUserGoals->where('status', 'completed')->count();
        $onHold = $allUserGoals->where('status', 'on_hold')->count();
        $avgProgress = $total > 0 ? (int) round($allUserGoals->avg('progress_percentage')) : 0;

        $categories = $allUserGoals->groupBy('category')->map->count()->toArray();

        return [
            'goals' => $goals,
            'total' => $total,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'on_hold' => $onHold,
            'avg_progress' => $avgProgress,
            'categories' => $categories,
        ];
    }

    /**
     * Recalculate and update goal progress percentage based on completed milestones.
     */
    public function recalculateProgress(PersonalGoal $goal): void
    {
        $totalMilestones = $goal->milestones()->count();

        if ($totalMilestones > 0) {
            $completedMilestones = $goal->milestones()->where('is_completed', true)->count();
            $percentage = (int) round(($completedMilestones / $totalMilestones) * 100);

            $status = $goal->status;
            $completedAt = $goal->completed_at;

            if ($percentage === 100 && $status !== 'completed') {
                $status = 'completed';
                $completedAt = now();
            } elseif ($percentage < 100 && $status === 'completed') {
                $status = 'in_progress';
                $completedAt = null;
            }

            $goal->update([
                'progress_percentage' => $percentage,
                'status' => $status,
                'completed_at' => $completedAt,
            ]);
        }
    }
}
