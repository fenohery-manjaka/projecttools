<?php

namespace App\Modules\Core\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when a member is invited from Settings › Members: a link to choose a password.
 */
class MemberInvited extends Notification
{
    public function __construct(
        public readonly string $token,
        public readonly string $invitedBy,
    ) {}

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $url = route('password.reset', ['token' => $this->token, 'email' => $notifiable->email]);
        $minutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Invitation à rejoindre '.config('app.name'))
            ->greeting("Bonjour {$notifiable->name},")
            ->line("{$this->invitedBy} vous invite à rejoindre ".config('app.name').'.')
            ->line('Choisissez votre mot de passe pour activer votre compte :')
            ->action('Choisir mon mot de passe', $url)
            ->line("Ce lien est valable {$minutes} minutes. Passé ce délai, utilisez « Mot de passe oublié » sur la page de connexion.")
            ->salutation('À bientôt sur '.config('app.name'));
    }
}
