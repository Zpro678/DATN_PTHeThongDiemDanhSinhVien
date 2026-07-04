<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendBroadcastEmailJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = 60; // Đợi 60s trước khi retry

    protected $emails;
    protected $subject;
    protected $content;

    /**
     * Create a new job instance.
     * @param array $emails
     * @param string $subject
     * @param string $content
     */
    public function __construct(array $emails, $subject, $content)
    {
        $this->emails = $emails;
        $this->subject = $subject;
        $this->content = $content;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (empty($this->emails)) return;

        $mailable = new \App\Mail\BroadcastEmail($this->subject, $this->content);
        
        // Gửi qua BCC cho danh sách email để tiết kiệm connection SMTP
        \Illuminate\Support\Facades\Mail::bcc($this->emails)->send($mailable);
    }
}
