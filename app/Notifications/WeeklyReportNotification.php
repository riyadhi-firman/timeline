<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class WeeklyReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $startDate;

    protected $endDate;

    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Laporan Mingguan Tersedia')
            ->line('Laporan mingguan untuk periode '.$this->startDate->format('d M Y').' sampai '.$this->endDate->format('d M Y').' telah tersedia.')
            ->action('Lihat Laporan', url('/admin/dashboard'))
            ->line('Terima kasih telah menggunakan aplikasi kami!');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Laporan Mingguan Tersedia',
            'message' => 'Laporan mingguan untuk periode '.$this->startDate->format('d M Y').' sampai '.$this->endDate->format('d M Y').' telah tersedia.',
            'url' => '/admin/dashboard',
        ];
    }
}
