<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationPendingNotification extends Notification
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
        $packageName = $this->user->package_name ?: 'Standard';

        return (new MailMessage)
            ->subject('Pendaftaran Member Berhasil Terkirim - nexuscommunity.id')
            ->greeting('Halo ' . $this->user->name . ',')
            ->line('Terima kasih telah melakukan pendaftaran keanggotaan di **nexuscommunity.id**.')
            ->line('Data pendaftaran dan bukti transfer Anda telah berhasil diterima oleh sistem kami.')
            ->line('• **Nama:** ' . $this->user->name)
            ->line('• **Username:** @' . ($this->user->username ?: strtolower(explode(' ', $this->user->name)[0])))
            ->line('• **Email:** ' . $this->user->email)
            ->line('• **Paket Membership:** ' . $packageName)
            ->line('Sistem sedang melakukan verifikasi data pendaftaran. Akun Anda akan otomatis diaktifkan dalam waktu 1 - 10 menit.')
            ->line('Notifikasi email aktivasi beserta rincian akses login akan dikirimkan secara otomatis setelah proses verifikasi selesai.')
            ->line('Terima kasih atas kepercayaan Anda bersama nexuscommunity.id!');
    }
}
