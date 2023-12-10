<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = app('firebase.messaging');
    }

    public function sendNotification($userId, $title, $body)
    {   

        //userId vai ser o topic (TODO: mudar para token)
        $message = CloudMessage::withTarget('topic', $userId)
            ->withNotification(Notification::create($title, $body));
        
        $this->messaging->send($message);
    }
}
