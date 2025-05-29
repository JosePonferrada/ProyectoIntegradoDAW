<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $appUrl = config('app.url');
        $userName = $notifiable->name;
        
        // Intenta obtener la próxima carrera si la tabla existe
        $nextRaceText = "Check the calendar for updates!";
        try {
            $nextRace = \App\Models\Race::where('race_date', '>', now())
                ->orderBy('race_date', 'asc')
                ->first();
            
            if ($nextRace) {
                $nextRaceText = "{$nextRace->name} - " . Carbon::parse($nextRace->race_date)->format('F j, Y');
            }
        } catch (\Exception $e) {
            // Si la tabla no existe o hay algún error, dejamos el texto por defecto
        }
        
        return (new MailMessage)
            ->subject('🏁 Formula 1 Stats - Verify Your Email Address')
            ->markdown('mail.verify-email', [
                'url' => $verificationUrl,
                'userName' => $userName,
                'appUrl' => $appUrl,
                'nextRace' => $nextRaceText
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
