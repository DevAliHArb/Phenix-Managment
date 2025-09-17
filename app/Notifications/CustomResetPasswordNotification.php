<?php

namespace App\Notifications;

// use App\Models\CompanySettings;
use Illuminate\Bus\Queueable;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;

class CustomResetPasswordNotification extends ResetPasswordNotification
{
    use Queueable;

    protected $type;
    private $siteName;
    private $fromEmail;

    public function __construct($token, $type)
    {
        parent::__construct($token);
        $this->type = $type;
    $this->siteName = 'Template'; // CompanySettings reference removed
        $this->fromEmail = $settings->mail_from_address ?? Config::get('mail.from.address');
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $resetUrl = $this->resetUrl($notifiable);
        $subject = $this->getSubjectByType($this->type);
        $fromName = $this->getFromNameByType($this->type);
        $bestRegards = $this->getBestRegardsByType($this->type);
        
        return (new MailMessage)
        ->from($this->fromEmail, $this->siteName) 
            ->subject($subject)
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $resetUrl)
            ->line('If you did not request a password reset, no further action is required.')
            // ->line($bestRegards)
            ;   
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
            'type' => $this->type,
        ], false));
    }
    
    /**
     * Get the email subject based on the type.
     *
     * @param  string  $type
     * @return string
     */
    protected function getSubjectByType($type)
    {
        switch ($type) {
            case 'bookshop':
                return 'Bookshop Password Reset Notification';
                case 'template':
                    return 'Template Password Reset Notification';
            case 'albouraq':
                return 'Albouraq Password Reset Notification';
            case 'maktabox':
                return 'Maktabox Password Reset Notification';
            default:
                return 'Password Reset Notification';
        }
    }

    /**
     * Get the "From" name based on the type.
     *
     * @param  string  $type
     * @return string
     */
    protected function getFromNameByType($type)
    {
        switch ($type) {
            case 'template':
                return 'Template Support';
                case 'bookshop':
                    return 'Bookshop Support';
            case 'albouraq':
                return 'Albouraq Support';
            case 'maktabox':
                return 'Maktabox Support';
            default:
                return 'Support';
        }
    }

    /**
     * Get the "Best regards" line based on the type.
     *
     * @param  string  $type
     * @return string
     */
    protected function getBestRegardsByType($type)
    {
        switch ($type) {
            case 'bookshop':
                return 'Best regards, Bookshop Team';
            case 'template':
                return 'Best regards, template Team';
            case 'albouraq':
                return 'Best regards, Albouraq Team';
            case 'maktabox':
                return 'Best regards, Maktabox Team';
            default:
                return 'Best regards';
        }
    }
}
