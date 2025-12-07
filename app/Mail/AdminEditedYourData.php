<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminEditedYourData extends Mailable
{
    use Queueable, SerializesModels;

    public string $section;
    public string $actorEmail;

    public function __construct(string $section, string $actorEmail)
    {
        $this->section = $section;
        $this->actorEmail = $actorEmail;
    }

    public function build()
    {
        return $this->subject('มีการแก้ไขข้อมูลของคุณโดยผู้ดูแลระบบ')
            ->view('emails.admin_edited')
            ->with([
                'section' => $this->section,
                'actorEmail' => $this->actorEmail,
            ]);
    }
}

