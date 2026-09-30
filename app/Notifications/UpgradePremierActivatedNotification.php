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
        $expiresAt = $this->user->premier_expires_at ? $this->user->premier_expires_at->format('d M Y') : now()->addYear()->format('d M Y');

        return (new MailMessage)
            ->subject('Selamat! Akun Anda Berhasil Upgrade ke Premier Member - nexuscommunity.id')
            ->greeting('Selamat ' . $this->user->name . '!')
            ->line('Akun Anda telah **resmi diaktifkan** sebagai **Premier Member** di **nexuscommunity.id**.')
            ->line('• **Status Membership:** Premier Member')
            ->line('• **Username:** @' . $this->user->username)
            ->line('• **Masa Aktif Hingga:** ' . $expiresAt)
            ->line('• **Fasilitas Spesial:** Proteksi Asuransi hingga Rp 500.000.000 & Pembukaan Hak Komisi Jaringan Premier 10 Generasi.')
            ->action('Buka Member Area', $dashboardUrl)
            ->line('Selamat mengembangkan jaringan dan meraih potensi bonus maksimal bersama nexuscommunity.id!');
    }
}
