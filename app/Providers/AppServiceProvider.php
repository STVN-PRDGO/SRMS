<?php

namespace App\Providers;

use App\Filament\Shared\FastTables;
use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Score;
use App\Observers\ActivityObserver;
use App\Observers\AttendanceObserver;
use App\Observers\ScoreObserver;
use App\Policies\AuditPolicy;
use App\Support\PermissionName;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use OwenIt\Auditing\Models\Audit;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Audit::class, AuditPolicy::class);

        FilamentShield::buildPermissionKeyUsing(
            function (string $entity, ?string $affix, string $subject): ?string {
                if ($affix === null || $entity === 'custom') {
                    return $subject;
                }

                return PermissionName::for($affix, $subject, PermissionName::panelForEntity($entity));
            }
        );

        FastTables::register();

        Score::observe(ScoreObserver::class);
        Attendance::observe(AttendanceObserver::class);
        Activity::observe(ActivityObserver::class);
    }
}
