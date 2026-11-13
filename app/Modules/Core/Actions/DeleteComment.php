<?php

namespace App\Modules\Core\Actions;

use App\Models\User;
use App\Modules\Core\Models\Comment;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;

class DeleteComment
{
    /**
     * @param  Closure(Comment, User): bool|null  $extraRule
     */
    public function handle(Comment $comment, User $actor, ?Closure $extraRule = null): void
    {
        $isAuthor = $comment->user_id === $actor->id;

        if (! $isAuthor && ($extraRule === null || ! $extraRule($comment, $actor))) {
            throw new AuthorizationException('Vous ne pouvez pas supprimer ce commentaire.');
        }

        $comment->delete();
    }
}
