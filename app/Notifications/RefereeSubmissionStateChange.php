<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefereeSubmissionStateChange extends Notification
{
    use Queueable;
    public $user;
    public $submission;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user , $submission)
    {
        $this->user = $user;
        $this->submission = $submission;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'Your submission for submission id => '.$this->submission->id.' has been updated to "'. $this->submission->status .'". Contact us if you have any questions.'
        ];
    }
}
