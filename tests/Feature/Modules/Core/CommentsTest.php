<?php

namespace Tests\Feature\Modules\Core;

use App\Models\User;
use App\Modules\Core\Actions\DeleteComment;
use App\Modules\Core\Actions\PostComment;
use App\Modules\Core\Notifications\MentionedInComment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\Fixtures\CommentableFixture;
use Tests\TestCase;

class CommentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Relation::morphMap(['fixture' => CommentableFixture::class]);
    }

    protected function tearDown(): void
    {
        Relation::morphMap([]);

        parent::tearDown();
    }

    public function test_posting_a_comment_records_activity(): void
    {
        $author = User::factory()->create(['name' => 'Marie Rakoto']);
        $item = $this->commentable($author, 'Tâche importante');

        $comment = app(PostComment::class)->handle($item, $author, '  Bonjour l’équipe  ', [], []);

        $this->assertModelExists($comment);
        $this->assertDatabaseHas('activity_log', [
            'user_id' => $author->id,
            'subject_type' => 'fixture',
            'subject_id' => $item->id,
        ]);
        $this->assertSame('Bonjour l’équipe', $comment->body);
    }

    public function test_valid_mentions_send_complete_database_notifications(): void
    {
        $author = User::factory()->create(['name' => 'Marie Rakoto']);
        $mentioned = User::factory()->create(['name' => 'Tojo Andria']);
        $item = $this->commentable($author, 'Tâche importante');

        app(PostComment::class)->handle($item, $author, str_repeat('Bonjour ', 30), [$mentioned->id], [$mentioned->id]);

        $notification = DatabaseNotification::query()->where('notifiable_id', $mentioned->id)->firstOrFail();

        $this->assertSame(MentionedInComment::class, $notification->type);
        $this->assertSame('Marie Rakoto', $notification->data['author_name']);
        $this->assertSame('Tâche importante', $notification->data['item_title']);
        $this->assertSame('/projects/'.$item->id, $notification->data['url']);
        $this->assertSame(120, mb_strlen($notification->data['excerpt']));
    }

    public function test_mentions_outside_the_allowed_list_are_ignored(): void
    {
        $author = User::factory()->create();
        $mentioned = User::factory()->create();
        $item = $this->commentable($author);

        app(PostComment::class)->handle($item, $author, 'Bonjour', [$mentioned->id], []);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_author_is_not_notified_and_duplicate_mentions_are_collapsed(): void
    {
        $author = User::factory()->create();
        $mentioned = User::factory()->create();
        $item = $this->commentable($author);

        app(PostComment::class)->handle($item, $author, 'Bonjour', [$author->id, $mentioned->id, $mentioned->id], [$author->id, $mentioned->id]);

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $mentioned->id]);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $author->id]);
    }

    public function test_author_can_delete_a_comment(): void
    {
        $author = User::factory()->create();
        $item = $this->commentable($author);
        $comment = app(PostComment::class)->handle($item, $author, 'À supprimer', [], []);

        app(DeleteComment::class)->handle($comment, $author);

        $this->assertModelMissing($comment);
    }

    public function test_another_user_cannot_delete_a_comment(): void
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $item = $this->commentable($author);
        $comment = app(PostComment::class)->handle($item, $author, 'Protégé', [], []);

        $this->expectException(AuthorizationException::class);
        app(DeleteComment::class)->handle($comment, $otherUser);
    }

    public function test_comments_are_returned_oldest_first_and_deleted_with_the_item(): void
    {
        $author = User::factory()->create();
        $item = $this->commentable($author);
        $first = app(PostComment::class)->handle($item, $author, 'Premier', [], []);
        $second = app(PostComment::class)->handle($item, $author, 'Second', [], []);

        $this->assertSame([$first->id, $second->id], $item->comments()->pluck('id')->all());

        $item->delete();

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_empty_comments_are_rejected(): void
    {
        $author = User::factory()->create();
        $item = $this->commentable($author);

        $this->expectException(\InvalidArgumentException::class);
        app(PostComment::class)->handle($item, $author, '   ', [], []);
    }

    public function test_overlong_comments_are_rejected(): void
    {
        $author = User::factory()->create();
        $item = $this->commentable($author);

        $this->expectException(\InvalidArgumentException::class);
        app(PostComment::class)->handle($item, $author, str_repeat('a', 5001), [], []);
    }

    private function commentable(User $owner, string $name = 'Élément commentable'): CommentableFixture
    {
        return CommentableFixture::query()->create([
            'name' => $name,
            'owner_id' => $owner->id,
            'color' => '#3F7A64',
            'status' => 'planned',
        ]);
    }
}
