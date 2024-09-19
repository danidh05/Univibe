<?php

namespace App\Services;

use App\Models\Notification;
use Pusher\Pusher;

class NotificationService
{
    protected $pusher;

    /**
     * Constructor to initialize Pusher configuration.
     */
    public function __construct()
    {
        // Initialize Pusher with configurations
        $this->pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            ['cluster' => config('broadcasting.connections.pusher.options.cluster')]
        );
    }

    /**
     * Create and send a notification.
     *
     * @param int $user_id
     * @param string $type
     * @param string $content
     * @param string $pusher_channel
     * @return Notification
     */
    public function createNotification($user_id, $type, $content, $data, $pusher_channel)
    {
        // Store the notification in the database
        $notification = Notification::create([
            'user_id' => $user_id,
            'type' => $type,
            'content' => $content,
            'is_read' => false,
        ]);

        // Trigger a Pusher event
        $this->pusher->trigger($pusher_channel, 'notification-event', [
            'message' => $content,
            'data' => $data
        ]);

        return $notification;
    }
}
