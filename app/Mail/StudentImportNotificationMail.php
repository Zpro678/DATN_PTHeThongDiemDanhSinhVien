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
    public $studentName;
    public $email;
    public $hasAccount;

    /**
     * Create a new message instance.
     */
    public function __construct($className, $classCode, $studentName, $email, $hasAccount = false)
    {
        $this->className = $className;
        $this->classCode = $classCode;
        $this->studentName = $studentName;
        $this->email = $email;
        $this->hasAccount = (bool) $hasAccount;
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
     *
     * Chọn template theo việc SV đã có tài khoản hay chưa:
     *  - Đã có tài khoản  -> hướng dẫn ĐĂNG NHẬP để theo dõi lớp.
     *  - Chưa có tài khoản -> hướng dẫn ĐĂNG KÝ bằng chính email này.
     */
    public function content(): Content
    {
        return new Content(
            markdown: $this->hasAccount
                ? 'emails.student_import_welcome'
                : 'emails.student_import_notification',
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
