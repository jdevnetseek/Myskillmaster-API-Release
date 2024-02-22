<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoicePaid extends Notification
{
    use Queueable;

    public $invoice;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($invoice = null)
    {
        $this->invoice = $invoice;
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
        $url = $this->invoice['hosted_invoice_url'];

        return (new MailMessage)
            ->greeting('Hello!')
            ->line('Your invoices has been paid!')
            ->line("Amount paid: {$this->toAmountFormat($this->invoice['amount_paid'])}")
            ->action('View Invoice', $url)
            ->line('Thank you for using our application!');
    }

    public static function toAmountFormat($price): string
    {
        $convert = $price / 100;
        return number_format($convert, 2, '.', '');
    }
}
