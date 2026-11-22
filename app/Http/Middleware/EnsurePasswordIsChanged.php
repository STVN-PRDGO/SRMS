<?php

namespace App\Http\Middleware;

use App\Filament\Admin\Pages\Profile as AdminProfile;
use App\Filament\Student\Pages\Profile as StudentProfile;
use App\Filament\Teacher\Pages\Profile as TeacherProfile;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $panel = Filament::getCurrentPanel();

        if ($user === null || ! $user->must_change_password || $panel === null) {
            return $next($request);
        }

        $profile = match ($panel->getId()) {
            'teacher' => TeacherProfile::class,
            'student' => StudentProfile::class,
            default => AdminProfile::class,
        };

        if ($request->routeIs($profile::getRouteName($panel))) {
            return $next($request);
        }

        return redirect()->to($profile::getUrl(panel: $panel->getId()));
    }
}
