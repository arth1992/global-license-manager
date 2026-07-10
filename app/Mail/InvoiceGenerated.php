<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceGenerated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $invoice;

    /**
     * Create a new message instance.
     */
    public function __construct(\App\Models\Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Apply dynamic SMTP settings if available in system settings
        $settings = \App\Models\SystemSetting::getActive();
        if ($settings->smtp_host) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $settings->smtp_host,
                'mail.mailers.smtp.port' => (int) $settings->smtp_port,
                'mail.mailers.smtp.username' => $settings->smtp_username,
                'mail.mailers.smtp.password' => $settings->smtp_password,
                'mail.mailers.smtp.encryption' => $settings->smtp_encryption === 'none' ? null : $settings->smtp_encryption,
                'mail.from.address' => $settings->smtp_from_address,
                'mail.from.name' => $settings->smtp_from_name ?: config('mail.from.name'),
            ]);

            app('mail.manager')->forgetMailers();
        }

        return new Envelope(
            subject: 'Invoice Generated - ' . $this->invoice->invoice_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        
        if ($this->invoice->pdf_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->invoice->pdf_path)) {
            $attachments[] = Attachment::fromStorageDisk('public', $this->invoice->pdf_path)
                    ->as($this->invoice->invoice_number . '.pdf')
                    ->withMime('application/pdf');
        }

        return $attachments;
    }
}
