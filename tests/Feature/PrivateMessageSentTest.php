<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use App\Events\PrivateMessageSent;
use PHPUnit\Framework\Attributes\Test;

class PrivateMessageSentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_dispatches_private_message_sent_event()
    {
        // Arrange
        Event::fake(); // Fake events

        $messageData = [
            'sender_id' => 1,
            'receiver_id' => 2,
            'message' => 'Hello, World!'
        ];

        // Act
        event(new PrivateMessageSent($messageData));

        // Assert
        Event::assertDispatched(PrivateMessageSent::class, function ($event) use ($messageData) {
            return $event->data === $messageData;
        });
    }
}
