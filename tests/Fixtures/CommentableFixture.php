<?php

namespace Tests\Fixtures;

use App\Modules\Core\Concerns\HasComments;
use App\Modules\Core\Contracts\Commentable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 */
#[Fillable(['name', 'owner_id', 'color', 'status'])]
class CommentableFixture extends Model implements Commentable
{
    use HasComments;

    protected $table = 'projects';

    public function commentTitle(): string
    {
        return $this->name;
    }

    public function commentUrl(): string
    {
        return '/projects/'.$this->getKey();
    }
}
