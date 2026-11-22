<?php

namespace App\Providers\Filament;

use App\Filament\Shared\PanelDefaults;
use App\Filament\Shared\WelcomeBanner;
use App\Filament\Teacher\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class TeacherPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('teacher')
            ->path('teacher')
            ->login()
            ->brandName('SRMS Teacher')
            ->topNavigation()
            ->spa(hasPrefetching: true)
            ->maxContentWidth('full')
            ->colors(['primary' => Color::Blue])
            ->viteTheme('resources/css/filament/teacher/theme.css')
            ->discoverResources(in: app_path('Filament/Teacher/Resources'), for: 'App\\Filament\\Teacher\\Resources')
            ->discoverPages(in: app_path('Filament/Teacher/Pages'), for: 'App\\Filament\\Teacher\\Pages')
            ->discoverWidgets(in: app_path('Filament/Teacher/Widgets'), for: 'App\\Filament\\Teacher\\Widgets')
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
                fn (): string => auth()->user()?->can('view_all_classes')
                    ? Blade::render('<div class="srms-banner bg-blue-100 text-blue-950">You are viewing the Teacher panel as Admin. <a href="{{ $url }}">Back to Admin</a></div>', ['url' => filament()->getPanel('admin')->getUrl()])
                    : '',
            )
            ->middleware(PanelDefaults::middleware())
            ->authMiddleware([Authenticate::class]);
    }
}
