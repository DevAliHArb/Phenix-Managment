<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class CustomVerifyEmailGuest extends Notification
{
    use Queueable;

    protected $otp_code;

    // Constructor to pass the 'type' to determine the custom settings
    public function __construct($otp_code)
    {
        $this->otp_code = $otp_code;
    }

    /**
     * Determine the delivery channels for the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // You can add other channels here if needed (like database, SMS, etc.)
        return ['mail']; // Send notification via email
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
{
    $subject = $this->getSubjectByType();
    $fromName = $this->getFromNameByType();
    $bestRegards = $this->getBestRegardsByType();

    return (new MailMessage)
        ->from(Config::get('mail.mailers.smtp.username'), $fromName)
        ->subject($subject)
        ->markdown('mails.VerifyGuest.VerifyGuest', [
            'otp_code' => $this->otp_code,
            'bestRegards' => $bestRegards,
        ]);
}


    /**
     * Get the email subject based on the type.
     *
     * @param  string  $type
     * @return string
     */
    protected function getSubjectByType()
    {
        $websiteName = env('APP_NAME');
        return "{$websiteName} Email verification notification";
    }

    /**
     * Get the "From" name based on the type.
     *
     * @param  string  $type
     * @return string
     */
    protected function getFromNameByType()
    {
        $websiteName = env('APP_NAME');
        return "{$websiteName} Support";
    }

    /**
     * Get the "Best regards" line based on the type.
     *
     * @param  string  $type
     * @return string
     */
    protected function getBestRegardsByType()
    {
        $websiteName = env('APP_NAME');
        return "Best regards, {$websiteName} Team";
    }
}
