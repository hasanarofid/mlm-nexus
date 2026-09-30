<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UpgradePremierPendingNotification extends Notification
{
    use Queueable;

    public $user;
    public $amount;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, float $amount = 5000000)
    {
        $this->user = $user;
        $this->amount = $amount;
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
        $formattedAmount = 'Rp ' . number_format($this->amount, 0, ',', '.');

        return (new MailMessage)
            ->subject('Permintaan Upgrade Premier Diterima - nexuscommunity.id')
            ->greeting('Halo ' . $this->user->name . ',')
            ->line('Permintaan permohonan **Upgrade Premier Member** Anda telah kami terima.')
            ->line('• **Nama Member:** ' . $this->user->name)
            ->line('• **Username:** @' . $this->user->username)
            ->line('• **Nominal Upgrade:** ' . $formattedAmount)
            ->line('Permohonan upgrade sedang diproses oleh sistem. Akun Anda akan otomatis diaktifkan sebagai **Premier Member** dalam waktu 1 - 10 menit.')
            ->line('Setelah aktif, Anda akan menerima email konfirmasi aktivasi Premier beserta hak istimewa perlindungan asuransi hingga Rp 500.000.000.')
            ->line('Terima kasih telah melakukan perpanjangan & upgrade bersama nexuscommunity.id!');
    }
}
