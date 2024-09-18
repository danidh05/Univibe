<?php

namespace Tests\Feature;

use App\Events\NotificationSent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Events\PrivateMessageSent;
use Illuminate\Support\Facades\Event;

class NotificationSentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_notification_sent_event()
    {
        // Arrange
        Event::fake(); // Fake events

        $notification = [
            'user_id' => 1,
            'type' => 'follow',
            'content' => 'user2 started following you',
            'is_read' => false,
        ];

        // Act
        event(new NotificationSent($notification, 'test_channel'));

        // Assert
        Event::assertDispatched(NotificationSent::class, function ($event) use ($notification) {
            return $event->data === $notification;
        });
    }
}
