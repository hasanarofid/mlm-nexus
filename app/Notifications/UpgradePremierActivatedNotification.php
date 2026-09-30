<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UpgradePremierActivatedNotification extends Notification
{
    use Queueable;

    public $user;

    /**
     * Create a new notification instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $dashboardUrl = route('admin.dashboard');

        return (new MailMessage)
            ->subject('Selamat! Akun Anda Berhasil Upgrade ke Premier Member - NEXUS COMMUNITY')
            ->greeting('Halo ' . $this->user->name . ',')
            ->line('Akun Anda telah resmi diupgrade menjadi **Premier Member** di **Nexus Community**.')
            ->line('• **Status Membership:** Premier Member')
            ->line('• **Username:** @' . $this->user->username)
            ->action('Buka Member Area', $dashboardUrl);
    }
}
