<?php

namespace App\Providers\Filament;

use App\Filament\Shared\PanelDefaults;
use App\Filament\Shared\WelcomeBanner;
use App\Filament\Student\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class StudentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('student')
            ->path('student')
            ->login()
            ->brandName('SRMS Student')
            ->topNavigation()
            ->spa(hasPrefetching: true)
            ->globalSearch(false)
            ->maxContentWidth('7xl')
            ->colors(['primary' => Color::Emerald])
            ->viteTheme('resources/css/filament/student/theme.css')
            ->discoverResources(in: app_path('Filament/Student/Resources'), for: 'App\\Filament\\Student\\Resources')
            ->discoverPages(in: app_path('Filament/Student/Pages'), for: 'App\\Filament\\Student\\Pages')
            ->discoverWidgets(in: app_path('Filament/Student/Widgets'), for: 'App\\Filament\\Student\\Widgets')
            ->pages([Dashboard::class])
            ->widgets([WelcomeBanner::class])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Back to Admin Panel')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->url(fn (): string => filament()->getPanel('admin')->getUrl())
                    ->visible(fn (): bool => (bool) auth()->user()?->can('access_admin_panel')),
            ])
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn (): string => auth()->user()?->can('view_as_student')
                    ? Blade::render('<div class="srms-banner bg-emerald-100 text-emerald-950">You are viewing the Student panel as Admin. <a href="{{ $url }}">Back to Admin</a></div>', ['url' => filament()->getPanel('admin')->getUrl()])
                    : '',
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render('@include("filament.shared.liquid-nav-script")'),
            )
            ->middleware(PanelDefaults::middleware())
            ->authMiddleware([Authenticate::class]);
    }
}
