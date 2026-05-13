<?php

namespace App\Mail;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Job $job,
        public array $paymentDetails,
        public string $invoiceNumber,
        public $invoiceDate,
    ) {
    }

    public function build(): self
    {
        return $this->subject('Payment Reminder for Invoice ' . $this->invoiceNumber)
            ->view('emails.invoice-reminder');
    }
}
