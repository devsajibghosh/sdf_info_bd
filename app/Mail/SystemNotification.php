<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SystemNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectLine;
    public $viewName;
    public $data;
    public $user;

    public function __construct(string $subjectLine, string $viewName, array $data, $user = null)
    {
        $this->subjectLine = $subjectLine;
        $this->viewName    = $viewName;
        $this->data        = $data;
        $this->user        = $user;
    }

    public function build()
    {
        return $this->subject($this->subjectLine)
            ->view('emails.' . $this->viewName)
            ->with(array_merge($this->data, ['user' => $this->user]));
    }
}
