<?php

namespace App\Modules\Core\Contracts;

interface Commentable
{
    public function commentTitle(): string;

    public function commentUrl(): string;
}
