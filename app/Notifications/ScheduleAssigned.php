<?php

namespace App\Notifications;

use App\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScheduleAssigned extends Notification
{
    use Queueable;

    protected Schedule $schedule;

    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
    }

    public function via($notifiable): array
    {
        return ['mail']; // Hapus 'database' dari array
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Jadwal Pekerjaan Baru')
            ->line('Anda telah ditugaskan untuk pekerjaan baru.')
            ->line('Judul: '.$this->schedule->title)
            ->line('Lokasi: '.$this->schedule->location)
            ->line('Waktu Mulai: '.$this->schedule->start_time->format('d M Y H:i'))
            ->line('Waktu Selesai: '.$this->schedule->end_time->format('d M Y H:i'))
            ->action('Lihat Detail', url('/admin/resources/schedules/'.$this->schedule->id.'/edit'));
    }

    public function toArray($notifiable): array
    {
        return [
            'schedule_id' => $this->schedule->id,
            'title' => $this->schedule->title,
            'start_time' => $this->schedule->start_time,
            'location' => $this->schedule->location,
        ];
    }
}
