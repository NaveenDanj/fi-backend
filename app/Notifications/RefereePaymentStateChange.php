<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefereePaymentStateChange extends Notification
{
    use Queueable;
    public $type;
    public $payment;
    public $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user , $payment , $type)
    {
        $this->type = $type;
        $this->payment = $payment;
        $this->user = $type;
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

        if($this->type == 'Reject'){
            return [
                'message' => "We're sorry, but your payment of ".$this->payment->amount." has failed. Please check your payment information and try again."
            ];
        }else{
            return [
                'message' => "Your payment of ".$this->payment->amount." has been successfully processed. Thank you for your transaction."
            ];
        }

    }
}
