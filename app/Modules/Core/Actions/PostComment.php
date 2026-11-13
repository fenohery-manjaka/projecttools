<?php

namespace App\Modules\Core\Actions;

use App\Models\User;
use App\Modules\Core\Contracts\Commentable;
use App\Modules\Core\Models\Comment;
use App\Modules\Core\Notifications\MentionedInComment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostComment
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    /**
     * @param  list<int|string>  $mentionedUserIds
     * @param  list<int|string>  $allowedUserIds
     */
    public function handle(
        Model&Commentable $commentable,
        User $author,
        string $body,
        array $mentionedUserIds,
        array $allowedUserIds,
    ): Comment {
        $body = trim($body);

        if ($body === '') {
            throw new \InvalidArgumentException('Le commentaire ne peut pas être vide.');
        }

        if (Str::length($body) > Comment::MAX_LENGTH) {
            throw new \InvalidArgumentException('Le commentaire ne peut pas dépasser 5 000 caractères.');
        }

        return DB::transaction(function () use ($commentable, $author, $body, $mentionedUserIds, $allowedUserIds): Comment {
            $comment = $commentable->morphMany(Comment::class, 'commentable')->create([
                'user_id' => $author->id,
                'body' => $body,
            ]);

            $this->recordActivity->handle($author, "{$author->name} a ajouté un commentaire à {$commentable->commentTitle()}.", $commentable);

            $recipientIds = collect($mentionedUserIds)
                ->map(fn (mixed $id): int => (int) $id)
                ->intersect(collect($allowedUserIds)->map(fn (int|string $id): int => (int) $id))
                ->reject(fn (int $id): bool => $id === $author->id)
                ->unique()
                ->values();

            User::query()
                ->whereKey($recipientIds->all())
                ->get()
                ->each(function (User $recipient) use ($author, $body, $commentable): void {
                    $recipient->notify(new MentionedInComment(
                        authorName: $author->name,
                        excerpt: Str::substr($body, 0, Comment::MAX_EXCERPT_LENGTH),
                        itemTitle: $commentable->commentTitle(),
                        url: $commentable->commentUrl(),
                    ));
                });

            return $comment;
        });
    }
}
