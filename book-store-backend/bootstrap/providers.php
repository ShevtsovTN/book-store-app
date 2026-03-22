<?php

use App\Providers\IdentityServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\NotificationServiceProvider;
use App\Providers\ScrambleServiceProvider;

return [
    AppServiceProvider::class,
    IdentityServiceProvider::class,
    NotificationServiceProvider::class,
    ScrambleServiceProvider::class,
];
