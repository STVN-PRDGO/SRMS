<?php

namespace App\Filament\Admin\Pages;

use App\Actions\Grading\ApplyGradingSystem;
use App\Enums\GradingTrack;
use App\Filament\Admin\AdminNavigationGroup;
use App\Models\GradingComponent;
use App\Models\SchoolSetting;
use App\Support\PermissionName;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class GradingSystem extends Page
{
    protected static ?string $navigationLabel = 'Grading system';

    protected static ?string $title = 'Grading system';

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::Grading;

    protected static ?int $navigationSort = 0;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->can(PermissionName::for('view_any', 'grading_component', 'admin'))
            || $user?->can(PermissionName::for('update', 'grading_component', 'admin'));
    }

    public function mount(): void
    {
        $this->data = [
            'grading_track' => SchoolSetting::current()?->grading_track?->value ?? GradingTrack::HighSchool->value,
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Html::make(fn (): HtmlString => new HtmlString(
                '<p>'.e($this->summary()).'</p>'
            )),
            Select::make('grading_track')
                ->label('System')
                ->options(GradingTrack::class)
                ->required(),
        ])->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('apply')
                ->label('Apply grading system')
                ->requiresConfirmation()
                ->modalDescription('This replaces the default component weights and the remark scale. Grades already stored stay until scores are saved again.')
                ->action(function (): void {
                    $track = GradingTrack::from($this->data['grading_track']);
                    app(ApplyGradingSystem::class)($track);

                    Notification::make()
                        ->title($track->getLabel().' is now the grading system')
                        ->success()
                        ->send();
                }),
        ];
    }

    private function summary(): string
    {
        $current = SchoolSetting::current()?->grading_track;
        $weights = GradingComponent::query()
            ->whereNull('subject_id')
            ->orderBy('name')
            ->get()
            ->map(fn (GradingComponent $component): string => $component->name.' '.$component->weight.'%')
            ->join(', ');

        $status = $current === null
            ? 'No grading system is active yet. Choose high school or college, then apply it.'
            : 'Active system: '.$current->getLabel().'.';

        return $status.' Grades and remarks are computed from quizzes, activities, projects, midterms, and finals. Current default weights: '.($weights !== '' ? $weights : 'none').'.';
    }
}
