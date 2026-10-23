<?php

namespace App\Modules\Core\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Core\Actions\RecordActivity;
use App\Modules\Core\Http\Requests\Settings\StoreMemberRequest;
use App\Modules\Core\Http\Requests\Settings\UpdateMemberRequest;
use App\Modules\Core\Notifications\MemberInvited;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class MemberController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var User $viewer */
        $viewer = $request->user();

        $members = User::query()->with('roles:id,name')->orderBy('name')->get();

        return Inertia::render('settings/Members', [
            'members' => $members->map(fn (User $member): array => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'role' => $member->getRoleNames()->first(),
                'is_super_admin' => $member->is_super_admin,
                'active' => $member->deactivated_at === null,
                'is_self' => $member->is($viewer),
            ]),
            'roles' => Role::query()->orderBy('id')->pluck('name'),
            'can' => ['manage' => $viewer->can('members.manage')],
        ]);
    }

    /**
     * Invite a member: create the account, then email a link to choose a password.
     */
    public function store(StoreMemberRequest $request, RecordActivity $recordActivity): RedirectResponse
    {
        $member = new User([
            'name' => $request->string('name')->value(),
            'email' => $request->string('email')->value(),
            'password' => Str::password(32),
        ]);
        $member->forceFill(['email_verified_at' => now()])->save();
        $member->assignRole($request->string('role')->value());

        /** @var User $inviter */
        $inviter = $request->user();

        $member->notify(new MemberInvited(Password::broker()->createToken($member), $inviter->name));

        $recordActivity->handle($inviter, "a invité {$member->name}", $member);

        Inertia::flash('toast', ['type' => 'success', 'message' => "Invitation envoyée à {$member->email}."]);

        return to_route('settings.members.index');
    }

    public function update(UpdateMemberRequest $request, User $member, RecordActivity $recordActivity): RedirectResponse
    {
        if ($member->is_super_admin || $member->is($request->user())) {
            throw ValidationException::withMessages(['member' => 'Ce compte ne peut pas être modifié depuis cet écran.']);
        }

        if ($request->has('role')) {
            $member->syncRoles($request->filled('role') ? [$request->string('role')->value()] : []);
        }

        if ($request->has('active')) {
            $member->forceFill(['deactivated_at' => $request->boolean('active') ? null : now()])->save();
        }

        $recordActivity->handle($request->user(), "a modifié le compte de {$member->name}", $member);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Membre mis à jour.']);

        return to_route('settings.members.index');
    }
}
