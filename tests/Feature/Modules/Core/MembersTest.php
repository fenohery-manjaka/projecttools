<?php

namespace Tests\Feature\Modules\Core;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MembersTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    public function test_inviting_a_member_creates_the_account_and_emails_a_password_link(): void
    {
        Notification::fake();

        $this->actingAs($this->userWithRole('Administrateur'))
            ->post(route('settings.members.store'), ['name' => 'Tojo', 'email' => 'tojo@example.com', 'role' => 'Contributeur'])
            ->assertSessionHasNoErrors();

        $member = User::query()->where('email', 'tojo@example.com')->firstOrFail();

        $this->assertTrue($member->hasRole('Contributeur'));
        $this->assertFalse($member->is_super_admin);
        Notification::assertSentTo($member, ResetPassword::class);
    }

    public function test_a_member_without_the_manage_permission_cannot_invite(): void
    {
        $this->actingAs($this->userWithRole('Contributeur'))
            ->post(route('settings.members.store'), ['name' => 'Tojo', 'email' => 'tojo@example.com', 'role' => 'Contributeur'])
            ->assertForbidden();
    }

    public function test_changing_the_role_and_deactivating_a_member(): void
    {
        $member = $this->userWithRole('Contributeur');

        $this->actingAs($this->userWithRole('Administrateur'));

        $this->patch(route('settings.members.update', $member), ['role' => 'Responsable'])->assertSessionHasNoErrors();
        $this->assertTrue($member->fresh()?->hasRole('Responsable'));
        $this->assertFalse($member->fresh()?->hasRole('Contributeur'));

        $this->patch(route('settings.members.update', $member), ['active' => false])->assertSessionHasNoErrors();
        $this->assertNotNull($member->fresh()?->deactivated_at);
    }

    public function test_the_super_admin_and_oneself_cannot_be_changed_from_the_screen(): void
    {
        $manager = $this->userWithRole('Administrateur');
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($manager);

        $this->patch(route('settings.members.update', $superAdmin), ['active' => false])->assertSessionHasErrors('member');
        $this->patch(route('settings.members.update', $manager), ['role' => null])->assertSessionHasErrors('member');

        $this->assertNull($superAdmin->fresh()?->deactivated_at);
        $this->assertTrue($manager->fresh()?->hasRole('Administrateur'));
    }
}
