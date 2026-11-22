<?php

namespace App\Actions\Reports;

use App\Models\ClassSubject;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class ExportClassRecord
{
    public function __invoke(ClassSubject $classSubject, User $user): string
    {
        if (! $user->can('export_class_record') || ! $classSubject->isManageableBy($user)) {
            throw new AuthorizationException('You cannot export this class record.');
        }

        $classSubject->load('section', 'subject', 'activities.gradingPeriod', 'activities.scores.student.user');

        $path = storage_path('app/class-record-'.$classSubject->id.'-'.now()->format('YmdHis').'.xlsx');
        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(['Student', 'Activity', 'Period', 'Score', 'Status']));

        foreach ($classSubject->activities as $activity) {
            foreach ($activity->scores as $score) {
                $writer->addRow(Row::fromValues([
                    $score->student?->user?->name,
                    $activity->title,
                    $activity->gradingPeriod?->name,
                    $score->score,
                    $score->status?->value,
                ]));
            }
        }

        $writer->close();

        return $path;
    }
}
