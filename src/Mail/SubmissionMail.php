<?php

declare(strict_types=1);

namespace Atrium\Forms\Mail;

use Atrium\Forms\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * A new submission, to the addresses set on the form. "Reply" answers the
 * visitor when the form has an e-mail field.
 */
class SubmissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public FormSubmission $submission) {}

    public function envelope(): Envelope
    {
        $replyTo = collect($this->submission->data)
            ->first(fn (array $item): bool => $item['type'] === 'email' && filter_var($item['value'] ?? null, FILTER_VALIDATE_EMAIL) !== false);

        return new Envelope(
            subject: __('atrium-forms::forms.mail.subject', ['form' => $this->submission->form_name]),
            replyTo: $replyTo === null ? [] : [new Address((string) $replyTo['value'])],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'atrium-forms::mail.submission');
    }
}
