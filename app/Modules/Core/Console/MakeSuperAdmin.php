<?php

namespace App\Modules\Core\Console;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('projecttools:super-admin {email : Email of an existing user} {--revoke : Remove the super admin flag instead}')]
#[Description('Grant (or revoke) the super admin flag. The flag can never be changed from the interface.')]
class MakeSuperAdmin extends Command
{
    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->components->error('No user with this email.');

            return self::FAILURE;
        }

        $user->forceFill(['is_super_admin' => ! $this->option('revoke'), 'deactivated_at' => null])->save();

        $this->components->info($user->is_super_admin
            ? "{$user->email} is now super admin."
            : "{$user->email} is no longer super admin.");

        return self::SUCCESS;
    }
}
