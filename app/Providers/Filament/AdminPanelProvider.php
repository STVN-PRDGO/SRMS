<?php

namespace App\Providers\Filament;

use App\Filament\Admin\AdminNavigationGroup;
use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Admin\Pages\Profile as AdminProfile;
use App\Filament\Shared\PanelDefaults;
use App\Filament\Shared\WelcomeBanner;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('SRMS Admin')
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups(AdminNavigationGroup::class)
            ->spa(hasPrefetching: true)
            ->colors(['primary' => Color::Amber])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->pages([Dashboard::class])
            ->widgets([WelcomeBanner::class])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Profile')
                    ->icon('heroicon-o-user-circle')
                    ->url(fn (): string => AdminProfile::getUrl()),
                MenuItem::make()
                    ->label('Teacher Panel')
                    ->icon('heroicon-o-academic-cap')
                    ->url(fn (): string => filament()->getPanel('teacher')->getUrl())
                    ->visible(fn (): bool => (bool) auth()->user()?->can('access_teacher_panel')),
                MenuItem::make()
                    ->label('Student Panel')
                    ->icon('heroicon-o-user-group')
                    ->url(fn (): string => filament()->getPanel('student')->getUrl())
                    ->visible(fn (): bool => (bool) auth()->user()?->can('access_student_panel')),
            ])
            ->plugin(
                FilamentShieldPlugin::make()
                    ->navigationGroup(AdminNavigationGroup::Access)
                    ->navigationLabel('Roles')
                    ->navigationSort(2),
            )
            ->middleware(PanelDefaults::middleware())
            ->authMiddleware([Authenticate::class]);
    }
}
