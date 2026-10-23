<?php

namespace Tests\Feature\Modules\Core;

use App\Models\User;
use App\Modules\Core\Notifications\MemberInvited;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
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

        $inviter = $this->userWithRole('Administrateur');

        $this->actingAs($inviter)
            ->post(route('settings.members.store'), ['name' => 'Tojo', 'email' => 'tojo@example.com', 'role' => 'Contributeur'])
            ->assertSessionHasNoErrors();

        $member = User::query()->where('email', 'tojo@example.com')->firstOrFail();

        $this->assertTrue($member->hasRole('Contributeur'));
        $this->assertFalse($member->is_super_admin);

        Notification::assertSentTo($member, MemberInvited::class, function (MemberInvited $notification) use ($member, $inviter): bool {
            $mail = $notification->toMail($member);

            return $notification->invitedBy === $inviter->name
                && $mail->subject === 'Invitation à rejoindre '.config('app.name')
                && Password::broker()->tokenExists($member, $notification->token);
        });
    }

    public function test_the_invitation_link_lets_the_member_choose_a_password(): void
    {
        Notification::fake();

        $this->actingAs($this->userWithRole('Administrateur'))
            ->post(route('settings.members.store'), ['name' => 'Tojo', 'email' => 'tojo@example.com', 'role' => 'Contributeur']);

        $member = User::query()->where('email', 'tojo@example.com')->firstOrFail();
        $token = '';
        Notification::assertSentTo($member, MemberInvited::class, function (MemberInvited $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });

        auth()->logout();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'tojo@example.com',
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])->assertSessionHasNoErrors();

        $this->post(route('login'), ['email' => 'tojo@example.com', 'password' => 'nouveau-mot-de-passe']);
        $this->assertAuthenticatedAs($member);
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
