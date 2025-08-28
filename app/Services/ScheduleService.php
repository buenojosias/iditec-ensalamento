<?php

namespace App\Services;

class ScheduleService
{
    static public function getWeekday(string $schedule): int|bool
    {
        $weekday = (int) substr((string) $schedule, 0, 1);
        if ($weekday < 2 || $weekday > 8) {
            return false;
        }
        return $weekday;
    }

    static public function getTime(string $schedule): string|bool
    {
        if ($schedule == '8000') {
            return '00:00';
        }

        $timecode = substr((string) $schedule, 2, 1);
        $weekday = self::getWeekday($schedule);

        if ($weekday === 7) {
            return match ($timecode) {
                '2' => '08:30',
                '3' => '10:30',
                '4' => '12:30',

                default => false,
            };
        }

        if ($weekday >= 2 && $weekday <= 6) {
            return match ($timecode) {
                '2' => '09:30',
                '4' => '14:00',
                '5' => '16:00',

                default => false,
            };
        }

        return false;
    }

    static public function getShift(string $schedule): string|bool
    {
        if ($schedule == '8000') {
            return 'Híbrido';
        }

        $time = self::getTime($schedule);

        if (!$time) {
            return false;
        }

        $time = \DateTime::createFromFormat('H:i', $time);

        if ($time < \DateTime::createFromFormat('H:i', '12:00')) {
            return "Manhã";
        } elseif ($time > \DateTime::createFromFormat('H:i', '12:00')) {
            return "Tarde";
        }

        return false;
    }
}
