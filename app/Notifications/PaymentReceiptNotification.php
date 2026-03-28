<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceiptNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Payment $payment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->payment->booking;
        $carName = $booking?->car?->name
            ?: trim(($booking?->car?->brand ?? '') . ' ' . ($booking?->car?->model ?? ''))
            ?: 'Booked car';

        return (new MailMessage)
            ->subject('Byahero Payment Receipt')
            ->greeting('Hello ' . ($notifiable->name ?: 'Customer') . ',')
            ->line('Your booking payment record has been created successfully.')
            ->line('Booking Reference: ' . ($booking?->booking_reference_number ?? ('Booking #' . $booking?->id)))
            ->line('Car: ' . $carName)
            ->line('Rental Dates: ' . ($booking?->start_date?->format('M d, Y') ?? '--') . ' to ' . ($booking?->end_date?->format('M d, Y') ?? '--'))
            ->line('Amount: P' . number_format((float) $this->payment->amount, 2))
            ->line('Payment Method: ' . strtoupper((string) $this->payment->method))
            ->line('Payment Status: ' . ucfirst((string) $this->payment->status))
            ->line('Transaction ID: ' . ($this->payment->transaction_id ?: 'Pending assignment'))
            ->line('Please keep this email as your payment receipt.');
    }
}
