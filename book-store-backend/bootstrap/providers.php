<?php

use App\Providers\IdentityServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\NotificationServiceProvider;

return [
    AppServiceProvider::class,
    IdentityServiceProvider::class,
    NotificationServiceProvider::class,
];
