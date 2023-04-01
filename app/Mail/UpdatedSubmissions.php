<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UpdatedSubmissions extends Mailable
{
    use Queueable, SerializesModels;

    public $pendingSubmissions;

    public function __construct($pendingSubmissions)
    {
        $this->pendingSubmissions = $pendingSubmissions;
    }

    public function build()
    {
        return $this->view('emails.updated_submissions')
                    ->with([
                        'submissions' => $this->pendingSubmissions,
                    ]);
    }
}