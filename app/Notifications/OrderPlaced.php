<?php

namespace App\Notifications;

// use App\Models\CompanySettings;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\OrderInvoice;
use Illuminate\Support\Facades\Config as FacadesConfig;

class OrderPlaced extends Notification
{
    use Queueable;

    private $orderInvoice;
    private $orderItems;
    private $siteName;
    private $fromEmail;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(OrderInvoice $orderInvoice, $items)
    {
        $this->orderInvoice = $orderInvoice;
        $this->orderItems = $items;
    $this->siteName = 'Template'; // CompanySettings reference removed
        $this->fromEmail = $settings->mail_from_address ?? FacadesConfig::get('mail.from.address');
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
        // Fetch order items associated with the order invoice
        // dd($this->orderInvoice);
        // Return the MailMessage instance with the Markdown view and necessary data
        return (new MailMessage)
        ->from($this->fromEmail, $this->siteName) 
            ->subject('Your Order Has Been Placed')
            ->markdown('mails.order.placed', [
                'order' => $this->orderInvoice,
                'orderItems' => $this->orderItems,
                'siteName' => $this->siteName  
            ]);
    }

    /**
     * Format order items for the table in mail content.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $orderItems
     * @return array
     */
    private function formatOrderItems($orderItems)
    {
        $formattedItems = [];

        foreach ($orderItems as $item) {
            $formattedItems[] = [
                '<img src="' . $item->article->image_url . '" width="50" height="50">',
                $item->article->name,
                $item->price,
                $item->quantity,
            ];
        }

        return $formattedItems;
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
            'order_id' => $this->orderInvoice->id,
            'total_price' => $this->orderInvoice->total_price,
            'address' => $this->orderInvoice->userAddress->address,
        ];
    }
}
