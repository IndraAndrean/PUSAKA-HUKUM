<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Reset Kata Sandi SIPAKEM')
            ->greeting('Halo, '.$notifiable->name)
            ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun SIPAKEM Anda.')
            ->action('Reset Kata Sandi', $url)
            ->line('Tautan reset kata sandi ini berlaku selama '.config('auth.passwords.users.expire').' menit.')
            ->line('Jika Anda tidak meminta reset kata sandi, abaikan email ini.');
    }
}
