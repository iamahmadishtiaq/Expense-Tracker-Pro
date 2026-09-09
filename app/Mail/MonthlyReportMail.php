<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MonthlyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $reportMonth,
        public float $income,
        public float $expense,
        public float $savings,
        public $topCategories
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Monthly Financial Report - {$this->reportMonth}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.monthly-report',
        );
    }
}