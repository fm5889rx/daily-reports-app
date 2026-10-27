<?php

namespace App\Policies;

use App\Models\DailyReport;
use App\Models\User;

class DailyReportPolicy
{
    public function update(User $user, DailyReport $dailyReport): bool
    {
        return $dailyReport->user_id === $user->id;
    }

    public function delete(User $user, DailyReport $dailyReport): bool
    {
        return $dailyReport->user_id === $user->id;
    }
}
