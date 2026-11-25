<?php

namespace App\Modules\Core\Agenda;

enum AgendaItemType: string
{
    case TaskDue = 'task_due';
    case Milestone = 'milestone';
    case ProjectEnd = 'project_end';
}
