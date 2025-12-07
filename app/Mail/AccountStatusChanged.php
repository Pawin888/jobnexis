<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public string $newStatus;

    public function __construct(string $newStatus)
    {
        $this->newStatus = $newStatus;
    }

    public function build()
    {
        return $this->subject('สถานะบัญชีของคุณมีการเปลี่ยนแปลง')
            ->view('emails.account_status_changed')
            ->with(['newStatus' => $this->newStatus]);
    }
}

