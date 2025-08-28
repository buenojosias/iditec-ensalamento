<?php

namespace App\Observers;

use App\Models\Team;
use App\Services\ScheduleService;

class TeamObserver
{
    public function creating(Team $team): void
    {
        $team->weekday = ScheduleService::getWeekday($team->schedule);
        $team->time = ScheduleService::getTime($team->schedule);
        $team->shift = ScheduleService::getShift($team->schedule);
    }

    public function updating(Team $team): void
    {
        $team->weekday = ScheduleService::getWeekday($team->schedule);
        $team->time = ScheduleService::getTime($team->schedule);
        $team->shift = ScheduleService::getShift($team->schedule);
    }

    public function deleted(Team $team): void
    {
        //
    }

    public function restored(Team $team): void
    {
        //
    }

    public function forceDeleted(Team $team): void
    {
        //
    }
}
