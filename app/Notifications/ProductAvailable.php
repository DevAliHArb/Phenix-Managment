<?php

namespace App\Notifications;

// use App\Models\CompanySettings;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Product;
use Illuminate\Support\Facades\Config;

class ProductAvailable extends Notification
{
    use Queueable;

    private $product;
    private $siteName;
    private $fromEmail;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Product $product
     * @return void
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    $this->siteName = 'Template'; // CompanySettings reference removed
        $this->fromEmail = $settings->mail_from_address ?? Config::get('mail.from.address');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
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
    $product = $this->product;

    // Find the main product image
    $mainImage = null;
    if ($product && $product->productMedia) {
        $mainImage = $product->productMedia->firstWhere('is_main_image', true)?->image;
    }

    // Create the HTML for the main image
    $mainImageHtml = '';
    if ($mainImage) {
        $mainImageHtml = '<img src="' . $mainImage . '" alt="Product Image" style="display: block; margin: auto; max-width: 60%; height: auto; margin-bottom: 16px;"/>';
    }
    
    $mail = (new MailMessage)
    ->from($this->fromEmail, $this->siteName) 
    ->subject('Product Availability Notification - ' . $this->siteName)
        ->markdown('mails.product.available', [
            'product' => $product,
            'mainImage' => $mainImageHtml,
            'siteName' => $this->siteName   // Pass the HTML to the markdown template
        ]);

    // Attach the main image only if it exists
    // if ($mainImage) {
    //     $mail->attach($mainImage);
    // }

    return $mail;
}


    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'product_id' => $this->product->id,
            'product_title' => $this->product->title,
        ];
    }
}
