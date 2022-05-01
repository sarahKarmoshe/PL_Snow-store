<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;

class VerifyApiEmail extends  VerifyEmailBase
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
protected $user_id,$verification_code;

    public function __construct($id,$verification_code)
    {
        $this->user_id=$id;
        $this->verification_code=$verification_code;
    }

    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            "verificationapi.verify", Carbon::now()->addMinutes(60), ["id" => $notifiable->getKey()]
        ); // this will basically mimic the email endpoint with get request
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */


    public function toMail($notifiable)
    {

        return (new MailMessage)
                    ->line('Verifying Snow App Account')
                    ->line ('your verification code is : '. ''.$this->verification_code)
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
