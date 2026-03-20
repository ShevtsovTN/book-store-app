<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Notification\Interfaces\NotificationSenderInterface;
use App\Infrastructure\Notification\CompositeNotificationSender;
use App\Infrastructure\Notification\DatabaseNotificationSender;
use App\Infrastructure\Notification\MailNotificationSender;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            NotificationSenderInterface::class,
            fn(Application $app) => new CompositeNotificationSender([
                $app->make(DatabaseNotificationSender::class),
                $app->make(MailNotificationSender::class),
            ]),
        );
    }
}
