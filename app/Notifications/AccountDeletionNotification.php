<?php

namespace App\Notifications;

// use App\Models\CompanySettings;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class AccountDeletionNotification extends Notification
{
    use Queueable;

    protected $user;
    protected $type;
    private $siteName;
    private $fromEmail;

    public function __construct($user)
    {
        $this->user = $user;
        $this->type = $user->type;
    $this->siteName = 'Template'; // CompanySettings reference removed
        $this->fromEmail = $settings->mail_from_address ?? Config::get('mail.from.address');
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        
        $link = $this->getLinkByType($this->type);

        $deleteUrl = URL::temporarySignedRoute(
            'confirm.account.deletion',
            now()->addHour(), // Expiration time for the link
            ['user' => $this->user->id, 'token' => $this->user->delete_token]
        );

        return (new MailMessage)
        ->from($this->fromEmail, $this->siteName) 
            ->subject($this->siteName . ' - Deleting your account')
            ->line($this->siteName . ' - Deleting your account on the site' . " " . $link)
            ->greeting('Hello')
            ->line('To delete your account, please click on the following link:')
            ->action('Confirm Account Deletion', $deleteUrl)
            ->line('Please ignore this e-mail if the account deletion request does not come from you. Rest assured, your account is always secure.')
            ->line('')
            ->line('Sincerely,')
            ->line($this->siteName . " team" . " " . $link)
            ->line('---')
            ->line('We thank you for your confidence in us and hope to see you soon on our website.' . ' ' . $link)
//             ->line("Pour obtenir plus d'informations sur nos conditions générales de vente et sur les droits de rétraction:
// Contact(to be linked to the contact us page) - Librairie - (mentions légales if this page exists)")
;
    }

    protected function getLinkByType($type)
    {
        switch ($type) {
            case 'bookshop':
                return 'https://bookshopwebsite.pages.dev/';
            case 'sofiaco':
                return 'https://sofiaco-website.pages.dev/';
            case 'albouraq':
                return 'https://albouraq-website.pages.dev/';
            case 'maktabox':
                return 'https://maktabox-website.pages.dev/';
            default:
                return 'https://default-website.pages.dev/';
        }
    }
}
