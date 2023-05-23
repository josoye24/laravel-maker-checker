<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RequestNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $subject;
     public $body;

    public function __construct(string $subject, array $body)
    {
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $body = $this->body;
        if ($body['email_template'] == 'new_request_template') {
            return $this->markdown('emails.request_notification')->subject($this->subject);
        
        } elseif ($body['email_template'] == 'approve_request_template') {
            return $this->markdown('emails.request_notification_approve')->subject($this->subject);

        } elseif ($body['email_template'] == 'decline_request_template') {
            return $this->markdown('emails.request_notification_decline')->subject($this->subject);
        } 
    }
}
