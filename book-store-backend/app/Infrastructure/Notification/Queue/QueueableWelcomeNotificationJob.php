<?php

namespace App\Infrastructure\Notification\Queue;

use App\Application\Notification\Interfaces\NotificationSenderInterface;
use App\Application\Notification\Jobs\SendWelcomeNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class QueueableWelcomeNotificationJob implements ShouldQueue
{
    use Queueable;

    public int    $tries  = 3;
    public int    $backoff = 30;
    public $queue  = 'notifications';

    public function __construct(
        private readonly int    $userId,
        private readonly string $userName,
        private readonly string $userEmail,
    ) {}

    public function handle(NotificationSenderInterface $sender): void
    {
        new SendWelcomeNotificationJob(
            userId:   $this->userId,
            userName: $this->userName,
            userEmail: $this->userEmail,
            sender:   $sender,
        )->handle();
    }
}
