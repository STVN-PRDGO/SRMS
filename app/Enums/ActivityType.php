<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ActivityType: string implements HasColor, HasIcon, HasLabel
{
    case Quiz = 'quiz';
    case Activity = 'activity';
    case Project = 'project';
    case Midterm = 'midterm';
    case Final = 'final';
    case Assignment = 'assignment';
    case Exam = 'exam';
    case Participation = 'participation';

    public function getLabel(): string
    {
        return str($this->value)->headline()->toString();
    }

    public function componentName(): string
    {
        return match ($this) {
            self::Quiz => 'Quizzes',
            self::Activity, self::Assignment, self::Participation => 'Activities',
            self::Project => 'Projects',
            self::Midterm => 'Midterm',
            self::Final, self::Exam => 'Final',
        };
    }

    /**
     * @return list<self>
     */
    public static function forRecording(): array
    {
        return [self::Quiz, self::Activity, self::Project, self::Midterm, self::Final];
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Quiz => 'info',
            self::Activity, self::Assignment => 'primary',
            self::Project => 'warning',
            self::Midterm, self::Final, self::Exam => 'danger',
            self::Participation => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Quiz => 'heroicon-m-question-mark-circle',
            self::Activity, self::Assignment => 'heroicon-m-document-text',
            self::Project => 'heroicon-m-folder',
            self::Midterm, self::Final, self::Exam => 'heroicon-m-clipboard-document-check',
            self::Participation => 'heroicon-m-hand-raised',
        };
    }
}
