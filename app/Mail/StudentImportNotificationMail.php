<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentImportNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $className;
    public $classCode;
    public $studentCode;
    public $studentName;
    public $email;

    /**
     * Create a new message instance.
     */
    public function __construct($className, $classCode, $studentCode, $studentName, $email)
    {
        $this->className = $className;
        $this->classCode = $classCode;
        $this->studentCode = $studentCode;
        $this->studentName = $studentName;
        $this->email = $email;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thông báo được thêm vào lớp học',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.student_import_notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
