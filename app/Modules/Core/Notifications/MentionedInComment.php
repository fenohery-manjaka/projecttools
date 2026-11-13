<?php

namespace App\Modules\Core\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

class MentionedInComment extends Notification
{
    public function __construct(
        public readonly string $authorName,
        public readonly string $excerpt,
        public readonly string $itemTitle,
        public readonly string $url,
    ) {}

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{author_name: string, excerpt: string, item_title: string, url: string}
     */
    public function toArray(User $notifiable): array
    {
        return [
            'author_name' => $this->authorName,
            'excerpt' => $this->excerpt,
            'item_title' => $this->itemTitle,
            'url' => $this->url,
        ];
    }
}
