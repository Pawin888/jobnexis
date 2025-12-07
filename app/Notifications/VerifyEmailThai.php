<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailThai extends BaseVerifyEmail
{
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('ยืนยันอีเมลของคุณ')
            ->greeting('สวัสดีค่ะ/ครับ')
            ->line('โปรดยืนยันอีเมลของคุณเพื่อใช้งาน JobNexis ให้ครบถ้วน')
            ->action('ยืนยันอีเมล', $verificationUrl)
            ->line('หากคุณไม่ได้ทำการสมัครไว้ ไม่จำเป็นต้องดำเนินการใด ๆ');
    }
}

